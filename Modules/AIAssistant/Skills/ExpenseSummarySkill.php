<?php

namespace Modules\AIAssistant\Skills;

use Modules\AIAssistant\Contracts\AssistantSkill;
use Modules\AIAssistant\DTO\AssistantMessageData;
use Modules\AIAssistant\DTO\AssistantContextData;
use Modules\AIAssistant\DTO\AssistantResponseData;
use Modules\AIAssistant\DTO\WarehouseScope;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseSummarySkill implements AssistantSkill
{
    private const RESULT_LIMIT = 10;

    public function key(): string
    {
        return 'expense_summary';
    }

    public function name(): string
    {
        return 'Today\'s Expense Summary';
    }

    public function description(): string
    {
        return 'Summarises today\'s expenses, including a category breakdown.';
    }

    public function examples(): array
    {
        return [
            'today\'s expense summary',
            'expense summary',
            'show today\'s expenses',
            'how much did we spend today',
            'expenses today'
        ];
    }

    public function canHandle(AssistantMessageData $message): bool
    {
        $prompt = strtolower(trim(preg_replace('/[^a-z0-9\s]/i', '', $message->content)));
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        $validIntents = [
            'todays expense summary',
            'todays expenses',
            'expense summary',
            'show todays expenses',
            'how much did we spend today',
            'expenses today',
            'today expense summary',
            'show today expenses'
        ];

        return in_array($prompt, $validIntents, true);
    }

    public function handle(AssistantMessageData $message, AssistantContextData $context): AssistantResponseData
    {
        $scope = WarehouseScope::fromContext($context);

        if ($scope->isRestricted && empty($scope->warehouseIds)) {
            return $this->buildResponse(0.0, 0, [], $scope->isRestricted, $scope->warehouseIds, $scope->ownUserId, true, 'empty_warehouse_scope');
        }

        $query = DB::table('expenses')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereDate('expenses.created_at', Carbon::today()->format('Y-m-d'));

        if ($scope->isRestricted) {
            $query->whereIn('expenses.warehouse_id', $scope->warehouseIds);
        }

        if ($scope->ownUserId !== null) {
            $query->where('expenses.user_id', $scope->ownUserId);
        }

        // Get total amount and count
        $totalExpense = (float) $query->sum('expenses.amount');
        $expenseCount = $query->count();

        // Get category breakdown
        $breakdownQuery = clone $query;
        $categoryBreakdowns = $breakdownQuery
            ->select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderBy('total', 'desc')
            ->orderBy('category', 'asc')
            ->limit(self::RESULT_LIMIT)
            ->get();

        $tableRows = [];
        foreach ($categoryBreakdowns as $cat) {
            $tableRows[] = [
                'category' => $cat->category,
                'amount' => (float) $cat->total,
            ];
        }

        return $this->buildResponse($totalExpense, $expenseCount, $tableRows, $scope->isRestricted, $scope->warehouseIds, $scope->ownUserId);
    }

    private function buildResponse(float $total, int $count, array $tableRows, bool $isRestricted, mixed $warehouseIds, ?int $ownUserId, bool $failedClosed = false, ?string $reason = null): AssistantResponseData
    {
        $textSummary = $count === 0
            ? 'No expenses recorded for today.'
            : "Today's expenses total " . number_format($total, 2) . " across {$count} categories.";

        $warnings = [];
        if ($failedClosed && $reason === 'empty_warehouse_scope') {
            $textSummary = 'Explicitly empty warehouse scope provided. No expenses are available.';
            $warnings[] = 'No warehouse access is available for this request.';
        }

        $cards = [
            ['title' => 'Today\'s Expenses', 'value' => $count],
            ['title' => 'Total Amount', 'value' => round($total, 2)],
        ];

        $table = [];
        if (!empty($tableRows)) {
            $table = [
                'columns' => ['Category', 'Amount'],
                'rows' => array_map(fn($r) => [
                    $r['category'],
                    $r['amount'],
                ], $tableRows),
            ];
        }

        return new AssistantResponseData(
            textSummary: $textSummary,
            responseType: 'card',
            cards: $cards,
            table: $table,
            links: [
                ['label' => 'View Expenses', 'url' => url('/expenses')]
            ],
            warnings: $warnings,
            metadata: [
                'skill' => $this->key(),
                'result_limit' => self::RESULT_LIMIT,
                'failed_closed' => $failedClosed,
                'reason' => $reason,
                'warehouse_ids' => $warehouseIds,
                'own_user_id' => $ownUserId,
                'date' => Carbon::today()->format('Y-m-d'),
            ]
        );
    }
}
