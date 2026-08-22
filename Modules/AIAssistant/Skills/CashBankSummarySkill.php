<?php

namespace Modules\AIAssistant\Skills;

use Modules\AIAssistant\Contracts\AssistantSkill;
use Modules\AIAssistant\DTO\AssistantMessageData;
use Modules\AIAssistant\DTO\AssistantContextData;
use Modules\AIAssistant\DTO\AssistantResponseData;
use Modules\AIAssistant\DTO\WarehouseScope;
use Illuminate\Support\Facades\DB;

class CashBankSummarySkill implements AssistantSkill
{
    public function key(): string
    {
        return 'cash_bank_summary';
    }

    public function name(): string
    {
        return 'Cash and Bank Summary';
    }

    public function description(): string
    {
        return 'Summarises all active cash and bank account balances.';
    }

    public function examples(): array
    {
        return [
            'cash and bank summary',
            'show account balances',
            'how much cash do we have',
            'bank balances'
        ];
    }

    public function canHandle(AssistantMessageData $message): bool
    {
        $prompt = strtolower(trim(preg_replace('/[^a-z0-9\s]/i', '', $message->content)));
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        $validIntents = [
            'cash and bank summary',
            'cash bank summary',
            'show account balances',
            'how much cash do we have',
            'bank balances'
        ];

        return in_array($prompt, $validIntents, true);
    }

    public function handle(AssistantMessageData $message, AssistantContextData $context): AssistantResponseData
    {
        $scope = WarehouseScope::fromContext($context);

        if ($scope->isRestricted && empty($scope->warehouseIds)) {
            return $this->buildResponse(0.0, 0, [], true, 'empty_warehouse_scope');
        }
        
        if ($scope->isRestricted || $scope->ownUserId !== null) {
            return $this->buildResponse(0.0, 0, [], true, $scope->isRestricted ? 'global_data_restriction' : 'own_access_restriction');
        }

        // Matches logic in AccountsController::index()
        // Credit = payments (from sales) + purchase returns + income + transfers in + initial balance
        // Debit = payments (for purchases) + sales returns + expenses + payrolls + transfers out

        $creditSql = "(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE account_id = accounts.id AND sale_id IS NOT NULL AND return_id IS NULL) + " .
                     "(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE account_id = accounts.id AND purchase_return_id IS NOT NULL) + " .
                     "(SELECT COALESCE(SUM(amount), 0) FROM money_transfers WHERE to_account_id = accounts.id) + " .
                     "(SELECT COALESCE(SUM(amount), 0) FROM incomes WHERE account_id = accounts.id) + " .
                     "COALESCE(initial_balance, 0)";

        $debitSql = "(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE account_id = accounts.id AND return_id IS NOT NULL) + " .
                    "(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE account_id = accounts.id AND purchase_id IS NOT NULL AND purchase_return_id IS NULL) + " .
                    "(SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE account_id = accounts.id) + " .
                    "(SELECT COALESCE(SUM(amount), 0) FROM payrolls WHERE account_id = accounts.id) + " .
                    "(SELECT COALESCE(SUM(amount), 0) FROM money_transfers WHERE from_account_id = accounts.id)";

        $query = DB::table('accounts')
            ->select('name', 'account_no', DB::raw("($creditSql) - ($debitSql) as balance"))
            ->where('is_active', true);

        $accountsCount = DB::query()->fromSub($query, 'sub')->count();
        $totalBalance = (float) DB::query()->fromSub($query, 'sub')->sum('balance');

        $rows = $query->orderBy('name', 'asc')->get();

        $tableRows = [];
        foreach ($rows as $row) {
            $tableRows[] = [
                'name_number' => $row->name . ($row->account_no ? ' (' . $row->account_no . ')' : ''),
                'balance' => (float) $row->balance,
            ];
        }

        return $this->buildResponse($totalBalance, $accountsCount, $tableRows, false);
    }

    private function buildResponse(float $totalBalance, int $accountsCount, array $tableRows, bool $failedClosed = false, ?string $reason = null): AssistantResponseData
    {
        $textSummary = "Total balance across {$accountsCount} active account(s) is " . number_format($totalBalance, 2) . '.';

        $warnings = [];
        if ($failedClosed) {
            $textSummary = 'You do not have permission to view global account balances.';
            if ($reason === 'empty_warehouse_scope') {
                $warnings[] = 'No warehouse access is available for this request.';
            } else {
                $warnings[] = 'Account balances are global. Warehouse or user-restricted scopes are not permitted to view this data.';
            }
        }

        $cards = [
            ['title' => 'Active Accounts', 'value' => $accountsCount],
            ['title' => 'Total Balance', 'value' => round($totalBalance, 2)],
        ];

        $table = [];
        if (!empty($tableRows)) {
            $table = [
                'columns' => ['Account Name (No.)', 'Current Balance'],
                'rows' => array_map(fn($r) => [
                    $r['name_number'],
                    $r['balance'],
                ], $tableRows),
            ];
        }

        return new AssistantResponseData(
            textSummary: $textSummary,
            responseType: 'card',
            cards: $cards,
            table: $table,
            links: [
                ['label' => 'View Accounts', 'url' => url('/accounts')]
            ],
            warnings: $warnings,
            metadata: [
                'skill' => $this->key(),
                'failed_closed' => $failedClosed,
                'reason' => $reason,
                'note' => 'Account balances are global and not bound to warehouses.'
            ]
        );
    }
}
