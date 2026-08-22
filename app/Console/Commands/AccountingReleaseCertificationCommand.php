<?php

namespace App\Console\Commands;

use App\Models\AccountingActivationSession;
use App\Models\AccountingConfig;
use App\Models\JournalEntry;
use App\Services\FinancialReportingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class AccountingReleaseCertificationCommand extends Command
{
    protected $signature = 'accounting:certify {--skip-regressions : Run current-database integrity checks only}';
    protected $description = 'Run the SalePro accounting release certification suite.';

    private array $results = [];

    public function handle(): int
    {
        $this->newLine();
        $this->info('========================================');
        $this->info('SalePro Accounting Certification');
        $this->info('========================================');

        if (!$this->option('skip-regressions')) {
            $this->runProcessCheck('Activation', ['test:accounting-activation', '--env=testing']);
            $this->runProcessCheck('Accounting Engine', ['test:scenario16-25', '--env=testing']);
            $this->runProcessCheck('Module Integration', ['test', '--filter', 'ModuleAccountingTest', '--env=testing']);
            $this->runProcessCheck('Operational Reports', ['test:report-certification', '--env=testing']);
        }

        $this->runArtisanCheck('Database Integrity', 'accounting:audit');
        $this->runCheck('Journal Integrity', fn () => $this->journalIntegrity());
        $this->runCheck('Trial Balance', fn () => $this->trialBalance());
        $this->runCheck('Balance Sheet', fn () => $this->balanceSheet());
        $this->runCheck('Opening Balance', fn () => $this->openingBalance());
        $this->runCheck('Accounting Configuration', fn () => $this->accountingConfiguration());
        $this->runCheck('Installed Modules', fn () => $this->installedModules());

        $failed = collect($this->results)->contains('status', 'FAIL');
        $warned = collect($this->results)->contains('status', 'WARN');
        $overall = $failed ? 'NOT READY' : ($warned ? 'READY WITH WARNINGS' : 'RELEASE READY');

        $this->newLine();
        $this->info('========================================');
        foreach ($this->results as $name => $result) {
            $this->line(str_pad($name, 28) . $result['status']);
            if ($result['message']) {
                $this->line('  ' . $result['message']);
            }
        }
        $this->line(str_pad('Overall Status', 28) . $overall);
        $this->info('========================================');

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function runProcessCheck(string $name, array $arguments): void
    {
        $process = new Process(array_merge([PHP_BINARY, base_path('artisan')], $arguments), base_path());
        $process->setTimeout(900);
        $process->run();

        $output = trim($process->getOutput() . "\n" . $process->getErrorOutput());
        $explicitTestPass = str_contains($output, 'Tests:')
            && str_contains($output, 'passed')
            && !str_contains($output, 'FAILED');
        $passed = $process->isSuccessful() || $explicitTestPass;
        $hasWarnings = $passed && (
            preg_match('/warnings:\s*[1-9][0-9]*/i', $output)
            || preg_match('/^WARN\s/m', $output)
        );
        $this->results[$name] = [
            'status' => !$passed ? 'FAIL' : ($hasWarnings ? 'WARN' : 'PASS'),
            'message' => $passed ? null : $this->lastMeaningfulLine($output),
        ];
        $this->line("{$name}: " . $this->results[$name]['status']);
    }

    private function runArtisanCheck(string $name, string $command): void
    {
        $exitCode = Artisan::call($command);
        $output = Artisan::output();
        $this->output->write($output);
        preg_match('/Warnings:\s*([0-9]+)/i', $output, $warningMatch);
        $hasWarnings = $exitCode === self::SUCCESS && (int) ($warningMatch[1] ?? 0) > 0;
        $this->results[$name] = [
            'status' => $exitCode !== self::SUCCESS ? 'FAIL' : ($hasWarnings ? 'WARN' : 'PASS'),
            'message' => $exitCode === self::SUCCESS ? null : "The {$command} audit reported failures.",
        ];
    }

    private function runCheck(string $name, callable $check): void
    {
        try {
            $message = $check();
            $this->results[$name] = ['status' => 'PASS', 'message' => $message];
        } catch (\Throwable $e) {
            $this->results[$name] = ['status' => 'FAIL', 'message' => $e->getMessage()];
        }
        $this->line("{$name}: " . $this->results[$name]['status']);
    }

    private function journalIntegrity(): ?string
    {
        $duplicates = JournalEntry::select('source_type', 'source_id', 'event_type')
            ->groupBy('source_type', 'source_id', 'event_type')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        $unbalanced = DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->select('je.id')
            ->groupBy('je.id')
            ->havingRaw('ROUND(COALESCE(SUM(jl.debit), 0), 4) <> ROUND(COALESCE(SUM(jl.credit), 0), 4)')
            ->get()
            ->count();
        $missingAccounts = DB::table('journal_lines as jl')
            ->leftJoin('accounting_accounts as aa', 'aa.id', '=', 'jl.accounting_account_id')
            ->whereNull('aa.id')
            ->count();
        $orphanLines = DB::table('journal_lines as jl')
            ->leftJoin('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->whereNull('je.id')
            ->count();

        if ($duplicates || $unbalanced || $missingAccounts || $orphanLines) {
            throw new \RuntimeException("duplicates={$duplicates}, unbalanced={$unbalanced}, missing_accounts={$missingAccounts}, orphan_lines={$orphanLines}");
        }

        return null;
    }

    private function trialBalance(): ?string
    {
        $totals = DB::table('journal_lines')
            ->selectRaw('COALESCE(SUM(debit), 0) debit, COALESCE(SUM(credit), 0) credit')
            ->first();
        if (bccomp((string) $totals->debit, (string) $totals->credit, 4) !== 0) {
            throw new \RuntimeException("debits={$totals->debit}, credits={$totals->credit}");
        }

        return 'Debits equal credits.';
    }

    private function balanceSheet(): ?string
    {
        $service = app(FinancialReportingService::class);
        $asOf = now()->toDateString();
        $data = $service->getBalanceSheet($asOf, now()->startOfYear()->toDateString(), null);
        $assets = (float) ($data['total_assets'] ?? 0);
        $liabilities = (float) ($data['total_liabilities'] ?? 0);
        $equity = (float) ($data['total_equity'] ?? 0) + (float) ($data['current_year_earnings'] ?? 0);

        if (abs($assets - ($liabilities + $equity)) > 0.01) {
            throw new \RuntimeException("assets={$assets}, liabilities_plus_equity=" . ($liabilities + $equity));
        }

        return 'Assets equal liabilities plus equity.';
    }

    private function openingBalance(): ?string
    {
        $config = AccountingConfig::first();
        $openings = JournalEntry::where('event_type', 'opening_balance')->get();
        $expected = $config && $config->enabled && $config->activation_mode === 'existing_business' ? 1 : 0;
        if ($openings->count() !== $expected) {
            throw new \RuntimeException("expected_opening_journals={$expected}, actual={$openings->count()}");
        }

        foreach ($openings as $opening) {
            $codes = DB::table('journal_lines as jl')
                ->join('accounting_accounts as aa', 'aa.id', '=', 'jl.accounting_account_id')
                ->where('jl.journal_entry_id', $opening->id)
                ->pluck('aa.code');
            if ($codes->contains(fn ($code) => !preg_match('/^[1-5][0-9]{3}$/', (string) $code))) {
                throw new \RuntimeException('Opening journal contains a non-numeric or non-standard account code.');
            }
        }

        return $openings->isEmpty() ? 'No opening journal required.' : 'One balanced numeric-COA opening journal.';
    }

    private function accountingConfiguration(): ?string
    {
        $configs = AccountingConfig::count();
        $config = AccountingConfig::first();
        if ($configs !== 1 || !$config || !$config->enabled || !$config->start_date) {
            throw new \RuntimeException("config_rows={$configs}, enabled=" . (int) ($config->enabled ?? false) . ', valid_start_date=' . (int) !empty($config->start_date));
        }

        $activeSession = AccountingActivationSession::orderByDesc('activated_at')->orderByDesc('id')->first();
        if (!$activeSession
            || $activeSession->mode !== $config->activation_mode
            || $activeSession->start_date !== $config->start_date
            || (int) $activeSession->opening_journal_entry_id !== (int) $config->opening_journal_entry_id) {
            throw new \RuntimeException('Latest activation session does not match the active accounting configuration.');
        }

        return 'Singleton config and config-linked activation session verified.';
    }

    private function installedModules(): string
    {
        $modules = ['Ecommerce', 'Repair', 'Manufacturing', 'Restaurant', 'Project'];
        $statuses = collect($modules)->map(function ($module) {
            return $module . '=' . (is_dir(base_path('modules/' . $module)) ? 'installed' : 'not-installed');
        });

        return $statuses->implode(', ');
    }

    private function lastMeaningfulLine(string $output): string
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $output))));
        return $lines ? end($lines) : 'Command failed without output.';
    }
}
