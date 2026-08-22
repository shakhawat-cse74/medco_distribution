<?php

namespace Modules\AIAssistant\Skills;

use Modules\AIAssistant\Contracts\AssistantSkill;
use Modules\AIAssistant\DTO\AssistantMessageData;
use Modules\AIAssistant\DTO\AssistantContextData;
use Modules\AIAssistant\DTO\AssistantResponseData;
use Modules\AIAssistant\DTO\WarehouseScope;
use Modules\AIAssistant\Skills\SalesSummarySkill;
use Modules\AIAssistant\Skills\PurchaseSummarySkill;
use Modules\AIAssistant\Skills\ExpenseSummarySkill;
use Modules\AIAssistant\Skills\LowStockSkill;

class DailySnapshotSkill implements AssistantSkill
{
    public function key(): string
    {
        return 'daily_snapshot';
    }

    public function name(): string
    {
        return 'Daily Business Snapshot';
    }

    public function description(): string
    {
        return 'Provides a combined view of today\'s sales, purchases, expenses, and newly created dues.';
    }

    public function examples(): array
    {
        return [
            'daily business snapshot',
            'today\'s business summary',
            'show today\'s snapshot',
            'how is business today',
            'daily snapshot'
        ];
    }

    public function canHandle(AssistantMessageData $message): bool
    {
        $prompt = strtolower(trim(preg_replace('/[^a-z0-9\s]/i', '', $message->content)));
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        $validIntents = [
            'daily business snapshot',
            'todays business summary',
            'today business summary',
            'show todays snapshot',
            'show today snapshot',
            'how is business today',
            'daily snapshot',
            'todays snapshot'
        ];

        return in_array($prompt, $validIntents, true);
    }

    public function handle(AssistantMessageData $message, AssistantContextData $context): AssistantResponseData
    {
        // 1. Sales Summary
        $salesSkill = new SalesSummarySkill();
        $salesResponse = $salesSkill->handle($message, $context);
        $salesTotal = $salesResponse->cards[2]['value'] ?? 0; // Gross Total is index 2
        $salesDue = $salesResponse->cards[4]['value'] ?? 0; // Due Amount is index 4
        $salesCount = $salesResponse->cards[0]['value'] ?? 0;

        // 2. Purchase Summary
        $purchaseSkill = new PurchaseSummarySkill();
        $purchaseResponse = $purchaseSkill->handle($message, $context);
        $purchaseTotal = $purchaseResponse->cards[2]['value'] ?? 0; // Gross Total is index 2
        $purchaseDue = $purchaseResponse->cards[4]['value'] ?? 0; // Due Amount is index 4
        $purchaseCount = $purchaseResponse->cards[0]['value'] ?? 0;

        // 3. Expense Summary
        $expenseSkill = new ExpenseSummarySkill();
        $expenseResponse = $expenseSkill->handle($message, $context);
        $expenseTotal = $expenseResponse->cards[1]['value'] ?? 0;
        $expenseCount = $expenseResponse->cards[0]['value'] ?? 0;

        // 4. Low Stock
        $warnings = [];
        $lowStockSkill = new LowStockSkill();
        $lowStockResponse = $lowStockSkill->handle($message, $context);
        $lowStockCount = $lowStockResponse->cards[0]['value'] ?? 0;
        
        $scope = WarehouseScope::fromContext($context);

        $cards = [
            ['title' => 'Today\'s Sales', 'value' => $salesTotal],
            ['title' => 'Today\'s Purchases', 'value' => $purchaseTotal],
            ['title' => 'Today\'s Expenses', 'value' => $expenseTotal],
            ['title' => 'Sales Due Created', 'value' => $salesDue],
            ['title' => 'Purchases Due Created', 'value' => $purchaseDue],
            ['title' => 'Total Transactions', 'value' => $salesCount + $purchaseCount + $expenseCount],
        ];

        $failedClosed = false;
        $reason = null;

        if ($scope->isRestricted && empty($scope->warehouseIds)) {
            $warnings[] = 'No warehouse access is available for this request.';
            $failedClosed = true;
            $reason = 'empty_warehouse_scope';
            foreach ($cards as &$card) {
                $card['value'] = 0;
            }
        } elseif ($scope->ownUserId !== null) {
            $warnings[] = 'Low stock metric is global and unavailable for user-restricted scopes.';
            $reason = 'partial_own_access_restriction';
        } else {
            $cards[] = ['title' => 'Low Stock Items', 'value' => $lowStockCount];
        }

        $textSummary = "Today's snapshot: Sales (" . number_format($salesTotal, 2) . "), Purchases (" . number_format($purchaseTotal, 2) . "), Expenses (" . number_format($expenseTotal, 2) . ").";

        return new AssistantResponseData(
            textSummary: $textSummary,
            responseType: 'card',
            cards: $cards,
            table: [],
            links: [
                ['label' => 'View Sales', 'url' => url('/sales')],
                ['label' => 'View Purchases', 'url' => url('/purchases')],
                ['label' => 'View Expenses', 'url' => url('/expenses')]
            ],
            warnings: $warnings,
            metadata: [
                'skill' => $this->key(),
                'date' => \Carbon\Carbon::today()->format('Y-m-d'),
                'failed_closed' => $failedClosed,
                'reason' => $reason,
                'warehouse_ids' => $scope->isRestricted ? $scope->warehouseIds : null,
                'own_user_id' => $scope->ownUserId,
                'sub_skills' => ['sales_summary', 'purchase_summary', 'expense_summary', 'low_stock']
            ]
        );
    }
}
