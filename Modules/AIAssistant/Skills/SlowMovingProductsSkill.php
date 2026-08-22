<?php

namespace Modules\AIAssistant\Skills;

use Modules\AIAssistant\Contracts\AssistantSkill;
use Modules\AIAssistant\DTO\AssistantMessageData;
use Modules\AIAssistant\DTO\AssistantContextData;
use Modules\AIAssistant\DTO\AssistantResponseData;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Modules\AIAssistant\DTO\WarehouseScope;

class SlowMovingProductsSkill implements AssistantSkill
{
    private const RESULT_LIMIT = 10;
    private const LOOKBACK_DAYS = 30;

    public function key(): string
    {
        return 'slow_moving_products';
    }

    public function name(): string
    {
        return 'Slow Moving Products Summary';
    }

    public function description(): string
    {
        return 'Identifies active stocked products with zero sales in the last ' . self::LOOKBACK_DAYS . ' days.';
    }

    public function examples(): array
    {
        return [
            'show slow moving products',
            'slow moving products',
            'products not selling',
            'products with low sales',
            'dead stock'
        ];
    }

    public function canHandle(AssistantMessageData $message): bool
    {
        $prompt = strtolower(trim(preg_replace('/[^a-z0-9\s]/i', '', $message->content)));
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        $validIntents = [
            'show slow moving products',
            'slow moving products',
            'products not selling',
            'products with low sales',
            'dead stock'
        ];

        return in_array($prompt, $validIntents, true);
    }

    public function handle(AssistantMessageData $message, AssistantContextData $context): AssistantResponseData
    {
        $scope = WarehouseScope::fromContext($context);

        // Fast path: explicitly empty warehouse restriction returns empty
        if ($scope->isRestricted && empty($scope->warehouseIds)) {
            return $this->buildResponse(0, [], $scope->isRestricted, $scope->warehouseIds, $scope->ownUserId, true, 'empty_warehouse_scope');
        }

        // Fast path: own access cannot safely view global slow moving products as inventory is global
        if ($scope->ownUserId !== null) {
            return $this->buildResponse(0, [], $scope->isRestricted, null, $scope->ownUserId, true, 'own_access_restriction');
        }

        $lookbackDate = now()->subDays(self::LOOKBACK_DAYS)->startOfDay();

        $recentSalesQuery = DB::table('product_sales')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->selectRaw('COALESCE(SUM(product_sales.qty), 0)')
            ->whereColumn('product_sales.product_id', 'products.id')
            ->whereNull('sales.deleted_at')
            ->where('sales.sale_status', '!=', 3)
            ->where(function($q) {
                $q->where('sales.sale_type', '!=', 'Opening balance')
                  ->orWhereNull('sales.sale_type');
            })
            ->where('sales.created_at', '>=', $lookbackDate);

        if ($scope->isRestricted) {
            $recentSalesQuery->whereIn('sales.warehouse_id', $scope->warehouseIds);
        }

        $recentReturnsQuery = DB::table('product_returns')
            ->join('returns', 'product_returns.return_id', '=', 'returns.id')
            ->selectRaw('COALESCE(SUM(product_returns.qty), 0)')
            ->whereColumn('product_returns.product_id', 'products.id')
            ->where('returns.created_at', '>=', $lookbackDate);

        if ($scope->isRestricted) {
            $recentReturnsQuery->whereIn('returns.warehouse_id', $scope->warehouseIds);
        }

        $lastSaleQuery = DB::table('product_sales')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->selectRaw('MAX(sales.created_at)')
            ->whereColumn('product_sales.product_id', 'products.id')
            ->whereNull('sales.deleted_at')
            ->where('sales.sale_status', '!=', 3)
            ->where(function($q) {
                $q->where('sales.sale_type', '!=', 'Opening balance')
                  ->orWhereNull('sales.sale_type');
            });
        
        if ($scope->isRestricted) {
            $lastSaleQuery->whereIn('sales.warehouse_id', $scope->warehouseIds);
        }

        $currentStockQuery = DB::table('product_warehouse')
            ->selectRaw('COALESCE(SUM(qty), 0)')
            ->whereColumn('product_warehouse.product_id', 'products.id');
        
        if ($scope->isRestricted) {
            $currentStockQuery->whereIn('product_warehouse.warehouse_id', $scope->warehouseIds);
        }

        $query = DB::table('products')
            ->select('name', 'code')
            ->selectSub($currentStockQuery, 'current_stock')
            ->selectSub($recentSalesQuery, 'recent_sales')
            ->selectSub($recentReturnsQuery, 'recent_returns')
            ->selectSub($lastSaleQuery, 'last_sale_date')
            ->where('is_active', true)
            ->having('current_stock', '>', 0)
            ->havingRaw('(recent_sales - recent_returns) <= 0');

        $totalSlowProducts = DB::query()->fromSub($query, 'sub')->count();

        $rows = $query->orderByRaw('last_sale_date IS NOT NULL') // nulls first
            ->orderBy('last_sale_date', 'asc')
            ->orderByRaw('(recent_sales - recent_returns) asc')
            ->orderBy('name', 'asc')
            ->orderBy('code', 'asc')
            ->limit(self::RESULT_LIMIT)
            ->get();

        $tableRows = [];
        foreach ($rows as $row) {
            $tableRows[] = [
                'name_code' => $row->name . ' (' . $row->code . ')',
                'stock' => (float) $row->current_stock,
                'sales' => (float) ($row->recent_sales - $row->recent_returns),
                'last_sale' => $row->last_sale_date ? \Carbon\Carbon::parse($row->last_sale_date)->format('Y-m-d') : 'No Sale',
            ];
        }

        return $this->buildResponse($totalSlowProducts, $tableRows, $scope->isRestricted, $scope->warehouseIds, $scope->ownUserId);
    }

    private function buildResponse(int $totalSlowProducts, array $tableRows, bool $isRestricted, mixed $warehouseIds, ?int $ownUserId = null, bool $failedClosed = false, ?string $reason = null): AssistantResponseData
    {
        $textSummary = $totalSlowProducts === 0
            ? 'No slow moving products found in the last ' . self::LOOKBACK_DAYS . ' days.'
            : "Found {$totalSlowProducts} active stocked product(s) with zero net sales in the last " . self::LOOKBACK_DAYS . ' days.';

        $warnings = [];
        if ($failedClosed) {
            $textSummary = 'You do not have permission to view global slow moving products.';
            if ($reason === 'empty_warehouse_scope') {
                $warnings[] = 'No warehouse access is available for this request.';
            } else {
                $warnings[] = 'Slow moving products are global or warehouse-wide. User-restricted scopes are not permitted to view this data.';
            }
        }

        $cards = [
            ['title' => 'Slow Moving Products', 'value' => $totalSlowProducts],
            ['title' => 'Lookback Period', 'value' => self::LOOKBACK_DAYS . ' Days'],
        ];

        $table = [];
        if (!empty($tableRows)) {
            $table = [
                'columns' => ['Product (Code)', 'Current Stock', 'Sold (30d)', 'Last Sale'],
                'rows' => array_map(fn($r) => [
                    $r['name_code'],
                    $r['stock'],
                    $r['sales'],
                    $r['last_sale'],
                ], $tableRows),
            ];
        }

        return new AssistantResponseData(
            textSummary: $textSummary,
            responseType: 'card',
            cards: $cards,
            table: $table,
            links: [
                ['label' => 'View Quantity Alert Report', 'url' => route('report.qtyAlert')]
            ],
            warnings: $warnings,
            metadata: [
                'skill' => $this->key(),
                'result_limit' => self::RESULT_LIMIT,
                'failed_closed' => $failedClosed,
                'reason' => $reason,
                'lookback_days' => self::LOOKBACK_DAYS,
                'warehouse_ids' => $warehouseIds,
                'own_user_id' => $ownUserId,
                'warehouse_scope_note' => 'Applies strict warehouse boundaries when restricted.',
            ]
        );
    }
}
