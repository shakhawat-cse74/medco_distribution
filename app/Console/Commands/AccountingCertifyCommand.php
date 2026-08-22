<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\AccountingSyncQueue;
use App\Models\AccountingAccount;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Returns;
use App\Models\ReturnPurchase;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Payroll;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\GiftCard;
use App\Models\Account;
use App\Models\MoneyTransfer;
use App\Models\RewardPointSetting;
use DB;
use App\Services\FinancialReportingService;
use Carbon\Carbon;
class AccountingCertifyCommand extends Command
{
    protected $signature = 'accounting:audit';
    protected $description = 'Certify the integrity of the Accounting Engine before proceeding to Financial Reporting.';

    protected $criticalFailures = 0;
    protected $highFailures = 0;
    protected $warnings = 0;

    public function handle()
    {
        $this->info("==================================================");
        $this->info('Starting Accounting Engine Certification...');
        $this->info("==================================================\n");

        $this->layer1_JournalIntegrity();
        $this->layer2_OperationalCoverage();
        $this->layer2_5_AccountingStatusConsistency();
        $this->layer3_ARReconciliation();
        $this->layer4_APReconciliation();
        $this->layer5_CustomerDeposits();
        $this->layer6_GiftCards();
        $this->layer7_Rewards();
        $this->layer8_CashBank();
        
        $tbFailed = $this->layer9_TrialBalance();

        $this->layer10_ChartOfAccounts();
        $this->layer11_OrphanJournals();
        
        // Phase 3C.5 - Financial Statement Certification Layers
        $reportingService = app(FinancialReportingService::class);
        $asOfDate = date('Y-m-d');
        // Let's assume fiscal year starts on Jan 1 of current year for certification purposes.
        $fiscalYearStart = date('Y') . '-01-01';
        $previousFiscalYearStart = (date('Y') - 1) . '-01-01';

        $this->layer12_FinancialStatementBalanceSheetValidation($reportingService, $asOfDate, $fiscalYearStart);
        $this->layer13_CurrentYearEarningsValidation($reportingService, $asOfDate, $fiscalYearStart);
        $this->layer14_RetainedEarningsValidation($reportingService, $fiscalYearStart);
        $this->layer15_TrialBalanceCrossVerification($reportingService, $asOfDate, $fiscalYearStart);
        $this->layer16_RetainedEarningsRollforward($reportingService, $fiscalYearStart, $previousFiscalYearStart);
        $this->layer17_CashFlowReconciliation($reportingService, $fiscalYearStart, $asOfDate);
        $this->layer18_CashCoverage($reportingService, $fiscalYearStart, $asOfDate);
        $this->layer19_InternalTransfers($reportingService, $fiscalYearStart, $asOfDate);

        $this->info("\n==================================================");
        $this->info("CERTIFICATION SUMMARY");
        $this->info("==================================================");
        $this->line("Critical Failures: " . $this->criticalFailures);
        $this->line("High Failures: " . $this->highFailures);
        $this->line("Warnings: " . $this->warnings);

        if ($this->criticalFailures > 0 || $this->highFailures > 0 || $tbFailed) {
            $this->error("\nACCOUNTING ENGINE CERTIFICATION FAILED! Critical or High failures found, or Trial Balance is out of balance.");
            return 1;
        }

        $this->info("\nACCOUNTING ENGINE CERTIFIED. The system is ready for Phase 3 (Financial Reporting).");
        return 0;
    }

    private function layer10_ChartOfAccounts()
    {
        $this->info("\nLayer 10: Chart of Accounts Validation");
        
        $accounts = Account::where('is_active', true)->get();
        $warnings = [];
        $failures = [];

        foreach ($accounts as $account) {
            $no = (string)$account->account_no;
            $type = strtolower($account->type);
            
            if (str_starts_with($no, '1')) {
                if ($type !== 'asset' && $account->type !== 'Bank Account') {
                    $failures[] = "Account {$no} marked \"{$account->type}\" (Expected: asset)";
                } elseif ($account->type === 'Bank Account') {
                    $warnings[] = "Account {$no} marked \"Bank Account\"";
                }
            } elseif (str_starts_with($no, '2')) {
                if ($type !== 'liability') {
                    $failures[] = "Account {$no} marked \"{$account->type}\" (Expected: liability)";
                }
            } elseif (str_starts_with($no, '3')) {
                if ($type !== 'equity') {
                    $failures[] = "Account {$no} marked \"{$account->type}\" (Expected: equity)";
                }
            } elseif (str_starts_with($no, '4')) {
                if ($no === '4150' || $no === '4160') {
                    if ($type !== 'contra_revenue' && $type !== 'contra revenue') {
                        $failures[] = "Account {$no} marked \"{$account->type}\" (Expected: contra revenue)";
                    }
                } else {
                    if ($type !== 'revenue') {
                        $failures[] = "Account {$no} marked \"{$account->type}\" (Expected: revenue)";
                    }
                }
            } elseif (str_starts_with($no, '5')) {
                if ($type !== 'expense') {
                    $failures[] = "Account {$no} marked \"{$account->type}\" (Expected: expense)";
                }
            }
        }

        if (count($warnings) > 0) {
            $this->warn("  Warnings:");
            foreach ($warnings as $w) {
                $this->warn("  - " . $w);
                $this->warnings++;
            }
        }

        if (count($failures) > 0) {
            $this->error("  High Failures:");
            foreach ($failures as $f) {
                $this->error("  - " . $f);
                $this->highFailures++;
            }
        }

        if (count($warnings) == 0 && count($failures) == 0) {
            $this->info("[PASS] Chart of Accounts");
        }
    }

    private function layer11_OrphanJournals()
    {
        $this->info("\nLayer 11: Orphan Journal Detection");

        $journals = JournalEntry::all();
        $failures = [];

        foreach ($journals as $journal) {
            $class = $journal->source_type;
            if ($class && class_exists($class)) {
                $exists = $class::find($journal->source_id);
                if (!$exists) {
                    $shortClass = class_basename($class);
                    $failures[] = "Journal #{$journal->id}: Source {$shortClass} #{$journal->source_id} does not exist.";
                }
            }
        }

        if (count($failures) > 0) {
            $this->error("  High Failures:");
            foreach ($failures as $f) {
                $this->error("  - " . $f);
                $this->highFailures++;
            }
        } else {
            $this->info("[PASS] Orphan Journals");
        }
    }

    private function layer1_JournalIntegrity()
    {
        $this->info('Layer 1: Journal Integrity');
        $failed = false;

        // Balanced Journals
        $unbalanced = JournalEntry::with('lines')->get()->filter(function ($entry) {
            $debits = $entry->lines->sum('debit');
            $credits = $entry->lines->sum('credit');
            return round($debits, 2) !== round($credits, 2);
        });
        if ($unbalanced->count() > 0) {
            $this->error("[FAIL] Balanced Journals: Found {$unbalanced->count()} unbalanced entries.");
            $this->criticalFailures++;
            $failed = true;
        }

        // Orphan Lines
        $orphanLines = JournalLine::whereDoesntHave('journalEntry')->count();
        if ($orphanLines > 0) {
            $this->error("[FAIL] Orphan Lines: Found {$orphanLines} lines without a parent entry.");
            $this->criticalFailures++;
            $failed = true;
        }

        // Reversal Targets
        $missingReversals = JournalEntry::where('event_type', 'like', '%_reversed')
                                        ->whereNull('related_journal_entry_id')
                                        ->count();
        if ($missingReversals > 0) {
            $this->error("[FAIL] Missing Reversal Targets: {$missingReversals} reversal entries have no related_journal_entry_id.");
            $this->highFailures++;
            $failed = true;
        }

        if (!$failed) {
            $this->info("[PASS] Journal Integrity");
        }
    }

    private function layer2_OperationalCoverage()
    {
        $this->info("\nLayer 2: Operational Coverage Audit");
        $modelsToCheck = [
            'Sale' => [Sale::class, 'sales', 'accounting_status'],
            'Purchase' => [Purchase::class, 'purchases', 'accounting_status'],
            'Return' => [Returns::class, 'returns', 'accounting_status'],
            'ReturnPurchase' => [ReturnPurchase::class, 'return_purchases', 'accounting_status'],
            'Payment' => [Payment::class, 'payments', 'accounting_status'],
            'Expense' => [Expense::class, 'expenses', 'accounting_status'],
            'Income' => [Income::class, 'incomes', 'accounting_status'],
            'Payroll' => [Payroll::class, 'payrolls', 'accounting_status'],
            'MoneyTransfer' => [MoneyTransfer::class, 'money_transfers', 'accounting_status'],
        ];

        $missingCount = 0;
        foreach ($modelsToCheck as $label => $config) {
            $class = $config[0];
            $table = $config[1];
            $statusCol = $config[2];

            if (\Schema::hasColumn($table, $statusCol)) {
                $missing = $class::where($statusCol, 'posted')
                    ->whereNotIn('id', function($q) use ($class) {
                        $q->select('source_id')->from('journal_entries')->where('source_type', $class);
                    })->count();
                if ($missing > 0) {
                    $this->error("  - [FAIL] $label: $missing posted records have NO journal entry.");
                    $missingCount += $missing;
                }
            }
            
            // Bidirectional orphan detection (Journal -> Source)
            $orphanJournals = JournalEntry::where('source_type', $class)
                ->whereNotIn('source_id', function($q) use ($table) {
                    $q->select('id')->from($table);
                })->count();
            if ($orphanJournals > 0) {
                $this->error("  - [FAIL] $label: $orphanJournals journals point to a deleted/missing source record.");
                $missingCount += $orphanJournals;
            }
        }

        if ($missingCount > 0) {
            $this->error("[FAIL] Operational Coverage: Missing journal coverage exists.");
            $this->criticalFailures++;
        } else {
            $this->info("[PASS] Operational Coverage");
        }
    }

    private function layer2_5_AccountingStatusConsistency()
    {
        $this->info("\nLayer 2.5: Accounting Status Consistency Audit");
        $models = [Sale::class, Purchase::class, Returns::class, ReturnPurchase::class, Payment::class, Expense::class, Income::class, Payroll::class, MoneyTransfer::class];
        
        $failed = false;

        foreach ($models as $modelClass) {
            $modelInstance = new $modelClass();
            $table = $modelInstance->getTable();

            if (!\Schema::hasColumn($table, 'accounting_status')) continue;

            // Posted w/o journal
            $postedNoJournal = $modelClass::where('accounting_status', 'posted')
                ->whereNotIn('id', function($q) use ($modelClass) {
                    $q->select('source_id')->from('journal_entries')->where('source_type', $modelClass);
                })->count();
            if ($postedNoJournal > 0) {
                $this->error("  - [FAIL CRITICAL] $table: $postedNoJournal records are 'posted' but have NO journal.");
                $this->criticalFailures++;
                $failed = true;
            }

            // Failed w/ journal
            $failedWJournal = $modelClass::where('accounting_status', 'failed')
                ->whereIn('id', function($q) use ($modelClass) {
                    $q->select('source_id')->from('journal_entries')->where('source_type', $modelClass);
                })->count();
            if ($failedWJournal > 0) {
                $this->error("  - [FAIL HIGH] $table: $failedWJournal records are 'failed' but DO have a journal.");
                $this->highFailures++;
                $failed = true;
            }

            // Reversed w/o reversal journal
            $reversedNoJournal = $modelClass::where('accounting_status', 'reversed')
                ->whereNotIn('id', function($q) use ($modelClass) {
                    $q->select('source_id')->from('journal_entries')->where('source_type', $modelClass)->where('event_type', 'like', '%_reversed');
                })->count();
            if ($reversedNoJournal > 0) {
                $this->error("  - [FAIL HIGH] $table: $reversedNoJournal records are 'reversed' but lack a reversal journal.");
                $this->highFailures++;
                $failed = true;
            }

            // Pending > 24 hours
            $pendingOld = $modelClass::where('accounting_status', 'pending')
                ->where('created_at', '<', now()->subHours(24))
                ->count();
            if ($pendingOld > 0) {
                $this->warn("  - [WARNING] $table: $pendingOld records have been 'pending' for > 24 hours.");
                $this->warnings++;
            }
        }

        if (!$failed) {
            $this->info("[PASS] Accounting Status Consistency");
        }
    }

    private function layer3_ARReconciliation()
    {
        $this->info("\nLayer 3: Accounts Receivable Reconciliation");
        
        $sales = Sale::whereNull('deleted_at')->get(['id', 'grand_total', 'paid_amount']);
        $legacyAR = $sales->sum(function ($sale) {
            $returns = Returns::where('sale_id', $sale->id)->sum('grand_total');
            $refunds = Payment::where('sale_id', $sale->id)->whereNotNull('return_id')->sum('amount');
            return (float) $sale->grand_total - (float) $sale->paid_amount - (float) $returns + (float) $refunds;
        });

        $arAccountId = AccountingAccount::where('code', '1100')->value('id');
        $glAR = 0;
        if ($arAccountId) {
            $glAR = DB::table('journal_lines')
                ->where('accounting_account_id', $arAccountId)
                ->selectRaw('SUM(debit - credit) as balance')
                ->value('balance') ?: 0;
        }

        $variance = round($legacyAR - $glAR, 2);

        $this->line("  Legacy AR: " . number_format($legacyAR, 2));
        $this->line("  GL AR:     " . number_format($glAR, 2));

        if ($variance != 0) {
            $this->error("[FAIL] Accounts Receivable. Variance: " . number_format($variance, 2));
            $this->highFailures++;
        } else {
            $this->info("[PASS] Accounts Receivable");
        }
    }

    private function layer4_APReconciliation()
    {
        $this->info("\nLayer 4: Accounts Payable Reconciliation");
        
        $purchases = Purchase::whereNull('deleted_at')->get(['id', 'grand_total', 'paid_amount']);
        $legacyAP = $purchases->sum(function ($purchase) {
            $returns = ReturnPurchase::where('purchase_id', $purchase->id)->sum('grand_total');
            $refunds = Payment::where('purchase_id', $purchase->id)
                ->where(function ($query) {
                    $query->whereNotNull('return_id')->orWhereNotNull('purchase_return_id');
                })
                ->sum('amount');
            return (float) $purchase->grand_total - (float) $purchase->paid_amount - (float) $returns + (float) $refunds;
        });

        $apAccountId = AccountingAccount::where('code', '2100')->value('id');
        $glAP = 0;
        if ($apAccountId) {
            $glAP = DB::table('journal_lines')
                ->where('accounting_account_id', $apAccountId)
                ->selectRaw('SUM(credit - debit) as balance')
                ->value('balance') ?: 0;
        }

        $variance = round($legacyAP - $glAP, 2);

        $this->line("  Legacy AP: " . number_format($legacyAP, 2));
        $this->line("  GL AP:     " . number_format($glAP, 2));

        if ($variance != 0) {
            $this->error("[FAIL] Accounts Payable. Variance: " . number_format($variance, 2));
            $this->highFailures++;
        } else {
            $this->info("[PASS] Accounts Payable");
        }
    }

    private function layer5_CustomerDeposits()
    {
        $this->info("\nLayer 5: Customer Deposits");
        
        $legacyDeposits = Customer::sum('deposit') - Customer::sum('expense');
        
        $depositAccountId = Account::where('name', 'Customer Deposits Liability')->value('id');
        $glDeposits = 0;
        if ($depositAccountId) {
            $glDeposits = DB::table('journal_lines')
                ->where('accounting_account_id', $depositAccountId)
                ->selectRaw('SUM(credit - debit) as balance')
                ->value('balance') ?: 0;
        }

        $variance = round($legacyDeposits - $glDeposits, 2);
        
        $this->line("  Legacy Deposits: " . number_format($legacyDeposits, 2));
        $this->line("  GL Deposits:     " . number_format($glDeposits, 2));

        if ($variance != 0) {
            $this->error("[FAIL] Deposits. Variance: " . number_format($variance, 2));
            $this->highFailures++;
        } else {
            $this->info("[PASS] Deposits");
        }
    }

    private function layer6_GiftCards()
    {
        $this->info("\nLayer 6: Gift Card Liability");
        
        $legacyGiftCards = GiftCard::where('is_active', true)->selectRaw('SUM(amount - expense) as total')->value('total') ?: 0;

        $giftCardAccountId = Account::where('name', 'Gift Card Liability')->value('id');
        $glGiftCards = 0;
        if ($giftCardAccountId) {
            $glGiftCards = DB::table('journal_lines')
                ->where('accounting_account_id', $giftCardAccountId)
                ->selectRaw('SUM(credit - debit) as balance')
                ->value('balance') ?: 0;
        }

        $variance = round($legacyGiftCards - $glGiftCards, 2);
        
        $this->line("  Legacy Gift Cards: " . number_format($legacyGiftCards, 2));
        $this->line("  GL Gift Cards:     " . number_format($glGiftCards, 2));

        if ($variance != 0) {
            $this->error("[FAIL] Gift Cards. Variance: " . number_format($variance, 2));
            $this->highFailures++;
        } else {
            $this->info("[PASS] Gift Cards");
        }
    }

    private function layer7_Rewards()
    {
        $this->info("\nLayer 7: Rewards Liability");
        $this->warn("  * Note: Rewards liability is valued using the current redemption rate. Historical rate changes may create expected variances.");
        
        $settings = RewardPointSetting::latest()->first();
        $rate = $settings ? $settings->redeem_amount_per_unit_rp : 0;
        $legacyPoints = Customer::sum('points');
        $legacyRewards = $legacyPoints * $rate;

        $rewardsAccountId = Account::where('name', 'Customer Rewards Liability')->value('id');
        $glRewards = 0;
        if ($rewardsAccountId) {
            $glRewards = DB::table('journal_lines')
                ->where('accounting_account_id', $rewardsAccountId)
                ->selectRaw('SUM(credit - debit) as balance')
                ->value('balance') ?: 0;
        }

        $variance = round($legacyRewards - $glRewards, 2);
        
        $this->line("  Legacy Rewards: " . number_format($legacyRewards, 2));
        $this->line("  GL Rewards:     " . number_format($glRewards, 2));

        if ($variance != 0) {
            $this->error("[FAIL] Rewards. Variance: " . number_format($variance, 2));
            $this->highFailures++;
        } else {
            $this->info("[PASS] Rewards");
        }
    }

    private function layer8_CashBank()
    {
        $this->info("\nLayer 8: Cash & Bank Reconciliation");
        $accounts = Account::where('is_active', true)->where('name', '!=', 'Accounts Receivable')->where('name', '!=', 'Accounts Payable')
            ->where('name', '!=', 'Customer Deposits Liability')->where('name', '!=', 'Gift Card Liability')
            ->where('name', '!=', 'Customer Rewards Liability')->get();
        
        $failed = false;
        foreach ($accounts as $acc) {
            $glBalance = DB::table('journal_lines')
                ->where('accounting_account_id', $acc->id)
                ->selectRaw('SUM(debit - credit) as balance')
                ->value('balance') ?: 0;

            $variance = round($acc->total_balance - $glBalance, 2);
            if ($variance != 0) {
                $this->warn("  [WARNING] Legacy Balance Drift for {$acc->name}");
                $this->line("    Legacy Balance: " . number_format($acc->total_balance, 2));
                $this->line("    GL Balance:     " . number_format($glBalance, 2));
                $this->line("    Variance:       " . number_format($variance, 2));
                $this->warnings++;
                $failed = true;
            }
        }

        if (!$failed) {
            $this->info("[PASS] Cash & Bank");
        }
    }

    private function layer9_TrialBalance()
    {
        $this->info("\nLayer 9: Trial Balance Certification");
        
        $totalDebits = JournalLine::sum('debit') ?: 0;
        $totalCredits = JournalLine::sum('credit') ?: 0;
        $difference = round($totalDebits - $totalCredits, 2);

        $this->line("  Total Debits:  " . number_format($totalDebits, 2));
        $this->line("  Total Credits: " . number_format($totalCredits, 2));
        
        if ($difference != 0) {
            $this->error("[FAIL] Trial Balance. Difference: " . number_format($difference, 2));
            $this->criticalFailures++;
            return true;
        } else {
            $this->info("[PASS] Trial Balance");
            return false;
        }
    }

    private function layer12_FinancialStatementBalanceSheetValidation(FinancialReportingService $service, $asOfDate, $fiscalYearStart)
    {
        $this->info("\nLayer 12: Financial Statement Balance Sheet Validation");
        
        $result = $service->validateBalanceSheet($asOfDate, $fiscalYearStart);
        
        $this->line("  Calculated Assets:        " . number_format($result['calculated_value'], 2));
        $this->line("  Calculated Liab + Equity: " . number_format($result['expected_value'], 2));
        
        if ($result['status'] === 'FAIL') {
            $this->error("[FAIL CRITICAL] Balance Sheet out of balance. Variance: " . number_format($result['variance'], 2));
            $this->criticalFailures++;
        } else {
            $this->info("[PASS] Balance Sheet Validation");
        }
    }

    private function layer13_CurrentYearEarningsValidation(FinancialReportingService $service, $asOfDate, $fiscalYearStart)
    {
        $this->info("\nLayer 13: Current Year Earnings Validation");
        
        $result = $service->validateCurrentYearEarnings($fiscalYearStart, $asOfDate);
        
        $this->line("  Current Year Earnings (BS): " . number_format($result['calculated_value'], 2));
        $this->line("  Net Profit (P&L):           " . number_format($result['expected_value'], 2));
        
        if ($result['status'] === 'FAIL') {
            $this->error("[FAIL CRITICAL] BS Current Year Earnings does not match P&L Net Profit. Variance: " . number_format($result['variance'], 2));
            $this->criticalFailures++;
        } else {
            $this->info("[PASS] Current Year Earnings Validation");
        }
    }

    private function layer14_RetainedEarningsValidation(FinancialReportingService $service, $fiscalYearStart)
    {
        $this->info("\nLayer 14: Retained Earnings Validation");
        
        $result = $service->validateRetainedEarnings($fiscalYearStart);
        
        $this->line("  Retained Earnings (BS):         " . number_format($result['calculated_value'], 2));
        $this->line("  Historical Net Profit (P&L):    " . number_format($result['expected_value'], 2));
        
        if ($result['status'] === 'FAIL') {
            $this->error("[FAIL HIGH] BS Retained Earnings does not match historical Net Profit. Variance: " . number_format($result['variance'], 2));
            $this->highFailures++;
        } else {
            $this->info("[PASS] Retained Earnings Validation");
        }
    }

    private function layer15_TrialBalanceCrossVerification(FinancialReportingService $service, $asOfDate, $fiscalYearStart)
    {
        $this->info("\nLayer 15: Trial Balance Cross-Verification");
        
        $result = $service->validateTrialBalanceConsistency($asOfDate, $fiscalYearStart);
        
        $this->line("  Variances:");
        $this->line("    Assets:      " . number_format($result['variances']['assets'], 2));
        $this->line("    Liabilities: " . number_format($result['variances']['liabilities'], 2));
        $this->line("    Equity:      " . number_format($result['variances']['equity'], 2));
        $this->line("    Revenue:     " . number_format($result['variances']['revenue'], 2));
        $this->line("    Expenses:    " . number_format($result['variances']['expenses'], 2));

        if ($result['status'] === 'FAIL') {
            $this->error("[FAIL CRITICAL] Trial Balance totals do not match Financial Statements. Total Variance: " . number_format($result['variance'], 2));
            $this->criticalFailures++;
        } else {
            $this->info("[PASS] Trial Balance Cross-Verification");
        }
    }

    private function layer16_RetainedEarningsRollforward(FinancialReportingService $service, $fiscalYearStart, $previousFiscalYearStart)
    {
        $this->info("\nLayer 16: Retained Earnings Rollforward Validation");
        
        $result = $service->validateRetainedEarningsRollforward($fiscalYearStart, $previousFiscalYearStart);
        
        $this->line("  Calculated Retained Earnings: " . number_format($result['calculated_value'], 2));
        $this->line("  Expected (Opening + Prior Y): " . number_format($result['expected_value'], 2));
        
        if ($result['status'] === 'FAIL') {
            $this->error("[FAIL HIGH] Retained Earnings rollforward failed. Variance: " . number_format($result['variance'], 2));
            $this->highFailures++;
        } else {
            $this->info("[PASS] Retained Earnings Rollforward");
        }
    }

    private function layer17_CashFlowReconciliation(FinancialReportingService $service, $fiscalYearStart, $asOfDate)
    {
        $this->info("\nLayer 17: Cash Flow Statement Reconciliation");
        
        $result = $service->validateCashFlowReconciliation($fiscalYearStart, $asOfDate);
        
        $this->line("  Calculated Closing Cash: " . number_format($result['calculated_closing'], 2));
        $this->line("  Expected Closing (Equation): " . number_format($result['expected_equation'], 2));
        $this->line("  Expected Closing (Balance Sheet): " . number_format($result['expected_bs'], 2));
        
        if ($result['status'] === 'FAIL') {
            $this->error("[FAIL CRITICAL] Cash Flow Reconciliation Failed.");
            $this->error("  Variance (Equation): " . number_format($result['variance_equation'], 2));
            $this->error("  Variance (Balance Sheet): " . number_format($result['variance_bs'], 2));
            $this->criticalFailures++;
        } else {
            $this->info("[PASS] Cash Flow Reconciliation");
        }
    }

    private function layer18_CashCoverage(FinancialReportingService $service, $fiscalYearStart, $asOfDate)
    {
        $this->info("\nLayer 18: Cash Coverage Audit");

        $result = $service->validateCashCoverage($fiscalYearStart, $asOfDate);

        if ($result['status'] === 'FAIL') {
            $this->error("[FAIL HIGH] Uncategorized cash movements found:");
            foreach ($result['uncategorized'] as $uncat) {
                $this->error("  - Journal ID {$uncat['entry_id']} | Ref {$uncat['reference_no']} | Date {$uncat['date']} | Account {$uncat['account']}");
            }
            $this->highFailures++;
        } else {
            $this->info("[PASS] Cash Coverage Audit");
        }
    }

    private function layer19_InternalTransfers(FinancialReportingService $service, $fiscalYearStart, $asOfDate)
    {
        $this->info("\nLayer 19: Internal Transfer Validation");

        $result = $service->validateInternalTransfers($fiscalYearStart, $asOfDate);

        if ($result['status'] === 'FAIL') {
            $this->error("[FAIL HIGH] Invalid internal transfers detected (Net Cash Change != 0):");
            foreach ($result['failed_entries'] as $entryId) {
                $this->error("  - Journal ID {$entryId}");
            }
            $this->highFailures++;
        } else {
            $this->info("[PASS] Internal Transfer Validation");
        }
    }
}
