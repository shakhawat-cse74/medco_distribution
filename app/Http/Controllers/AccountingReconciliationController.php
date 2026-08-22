<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountingSyncQueue;
use App\Services\AccountingService;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Returns;
use App\Models\ReturnPurchase;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Payroll;
use App\Models\MoneyTransfer;
use DB;

class AccountingReconciliationController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index()
    {
        $queue = AccountingSyncQueue::orderBy('updated_at', 'desc')->paginate(50);
        
        $stats = [
            'total' => AccountingSyncQueue::count(),
            'failed' => AccountingSyncQueue::where('status', 'failed')->count(),
            'pending' => AccountingSyncQueue::where('status', 'pending')->count(),
        ];

        return view('backend.accounting.reconciliation.index', compact('queue', 'stats'));
    }

    public function retry($id)
    {
        $record = AccountingSyncQueue::findOrFail($id);
        
        $modelClass = $record->source_type;
        if (!class_exists($modelClass)) {
            return redirect()->back()->with('error', 'Model class not found: ' . $modelClass);
        }
        
        $model = $modelClass::find($record->source_id);
        if (!$model) {
            $record->status = 'failed';
            $record->last_error = 'Source record deleted or not found.';
            $record->save();
            return redirect()->back()->with('error', 'Source record deleted. Marking as failed.');
        }

        // Determine which method to call based on model
        $result = null;
        switch ($modelClass) {
            case Sale::class:
                $result = $this->accountingService->recordSale($model);
                break;
            case Purchase::class:
                $result = $this->accountingService->recordPurchase($model);
                break;
            case Returns::class:
                $result = $this->accountingService->recordSaleReturn($model);
                break;
            case ReturnPurchase::class:
                $result = $this->accountingService->recordPurchaseReturn($model);
                break;
            case Payment::class:
                $result = $this->accountingService->recordPayment($model);
                break;
            case Expense::class:
                $result = $this->accountingService->recordExpense($model);
                break;
            case Income::class:
                $result = $this->accountingService->recordIncome($model);
                break;
            case Payroll::class:
                $result = $this->accountingService->recordPayroll($model);
                break;
            case MoneyTransfer::class:
                $result = $this->accountingService->recordMoneyTransfer($model);
                break;
            // Additional types can be added here
            default:
                return redirect()->back()->with('error', 'Retry logic not implemented for this model type.');
        }

        if ($result && $result->success) {
            return redirect()->back()->with('message', 'Successfully re-journaled the transaction.');
        } else {
            return redirect()->back()->with('error', 'Retry failed: ' . ($result ? $result->error : 'Unknown error'));
        }
    }
}
