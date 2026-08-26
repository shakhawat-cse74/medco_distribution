<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\DeliveryManagement\Models\CashDeposit;
use Modules\DeliveryManagement\Models\DeliveryMan;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Traits\CacheForget;
use App\Traits\MailInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CashDepositController extends Controller
{
    use CacheForget;
    use MailInfo;

    public function __construct()
    {
        $this->middleware('permission:cash-deposits-index');
    }

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('cash-deposits-index')) {
            $lims_cash_deposits = CashDeposit::with(['deliveryMan', 'verifiedBy'])
                ->latest()
                ->paginate(20);
            $lims_delivery_men = DeliveryMan::where('is_active', true)->get();
            $lims_warehouses = Warehouse::where('is_active', true)->get();
            return view('backend.delivery_management.cash_deposit.index', compact(
                'lims_cash_deposits', 'lims_delivery_men', 'lims_warehouses'
            ));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('cash-deposits-add')) {
            $lims_delivery_men = DeliveryMan::where('is_active', true)->get();
            return view('backend.delivery_management.cash_deposit.create', compact('lims_delivery_men'));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $this->validate($request, [
                'delivery_man_id' => 'required',
                'amount' => 'required|numeric',
                'deposit_method' => 'required',
                'bank_name' => 'required_if:deposit_method,bank',
                'account_number' => 'required_if:deposit_method,bank',
                'reference_no' => 'nullable',
            ]);

            $data = $request->all();
            $data['status'] = 'pending';
            $data['verified_by'] = null;
            $data['verified_at'] = null;

            $deposit = CashDeposit::create($data);
            $this->cacheForget('cash_deposit_list');

            DB::commit();
            return redirect('cash-deposits')->with('message', __('db.Cash deposit created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Cash deposit creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Cash deposit creation failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('cash-deposits-index')) {
            $lims_deposit = CashDeposit::with(['deliveryMan', 'verifiedBy'])->find($id);
            return view('backend.delivery_management.cash_deposit.show', compact('lims_deposit'));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function verify($id)
    {
        try {
            DB::beginTransaction();
            $lims_deposit = CashDeposit::find($id);
            $lims_deposit->status = 'verified';
            $lims_deposit->verified_by = Auth::id();
            $lims_deposit->verified_at = now();
            $lims_deposit->save();
            DB::commit();
            return redirect()->back()->with('message', __('db.Cash deposit verified successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Cash deposit verification failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Cash deposit verification failed: ' . $e->getMessage());
        }
    }

    public function summary()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('cash-deposits-index')) {
            $startOfDay = today()->startOfDay();
            $endOfDay = today()->endOfDay();
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $dailySummary = CashDeposit::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('amount');
            $weeklySummary = CashDeposit::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('amount');
            $monthlySummary = CashDeposit::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount');

            $totalDeposited = CashDeposit::where('status', 'verified')->sum('amount');
            $pendingDeposits = CashDeposit::where('status', 'pending')->sum('amount');

            return view('backend.delivery_management.cash_deposit.summary', compact(
                'dailySummary', 'weeklySummary', 'monthlySummary',
                'totalDeposited', 'pendingDeposits'
            ));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function deliveryManSummary($delivery_man_id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('cash-deposits-index')) {
            $lims_deposits = CashDeposit::where('delivery_man_id', $delivery_man_id)
                ->latest()
                ->paginate(20);
            $lims_delivery_man = DeliveryMan::find($delivery_man_id);
            return view('backend.delivery_management.cash_deposit.delivery_man_summary', compact(
                'lims_deposits', 'lims_delivery_man'
            ));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function pendingDeposits()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('cash-deposits-index')) {
            $lims_pending_deposits = CashDeposit::where('status', 'pending')
                ->latest()
                ->paginate(20);
            return view('backend.delivery_management.cash_deposit.pending_deposits', compact('lims_pending_deposits'));
        }
        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }
}
