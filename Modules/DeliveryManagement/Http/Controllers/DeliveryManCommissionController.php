<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryManCommission;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\FieldPayment;
use Modules\DeliveryManagement\Models\CashDeposit;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheForget;

class DeliveryManCommissionController extends Controller
{
    use \App\Traits\CacheForget;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-commissions-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_commission_list = DeliveryManCommission::with(['deliveryMan', 'fieldOrder'])->get();
            $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();

            return view('backend.delivery_management.delivery_man_commission.index', compact('lims_commission_list', 'lims_delivery_man_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function settings()
    {
        $lims_commission_settings = \App\Models\DeliverySetting::where('key', 'LIKE', 'commission_%')->get();
        return view('backend.delivery_management.delivery_man_commission.settings', compact('lims_commission_settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                if ($key != '_token') {
                    \App\Models\DeliverySetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'commission']
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-man-commissions/settings')->with('message', __('db.Commission settings updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Commission settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Commission settings update failed: ' . $e->getMessage());
        }
    }

    public function slabs()
    {
        $lims_commission_slabs = \App\Models\DeliverySetting::where('key', 'LIKE', 'commission_slab_%')->get();
        return view('backend.delivery_management.delivery_man_commission.slabs', compact('lims_commission_slabs'));
    }

    public function storeSlabs(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data['keys'] as $index => $key) {
                $value = $data['values'][$index] ?? null;
                if ($key && $value) {
                    \App\Models\DeliverySetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'commission_slab']
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-man-commissions/slabs')->with('message', __('db.Commission slabs updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Commission slabs update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Commission slabs update failed: ' . $e->getMessage());
        }
    }

    public function calculate($field_order_id)
    {
        $fieldOrder = FieldOrder::with('deliveryMan')->findOrFail($field_order_id);
        $commissionRate = \App\Models\DeliverySetting::where('key', 'commission_rate')->value('value') ?? 5;

        $commissionAmount = ($fieldOrder->grand_total * $commissionRate) / 100;

        $commission = DeliveryManCommission::create([
            'delivery_man_id' => $fieldOrder->delivery_man_id,
            'field_order_id' => $fieldOrder->id,
            'commission_type' => 'percentage',
            'commission_rate' => $commissionRate,
            'order_amount' => $fieldOrder->grand_total,
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'commission' => $commission]);
    }

    public function processPayout(Request $request)
    {
        $this->validate($request, [
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $commissions = DeliveryManCommission::where('delivery_man_id', $request->delivery_man_id)
                ->where('status', 'pending')
                ->get();

            $totalCommission = $commissions->sum('commission_amount');

            if ($totalCommission < $request->amount) {
                return redirect()->back()->with('not_permitted', 'Insufficient commission balance');
            }

            foreach ($commissions as $commission) {
                $commission->status = 'paid';
                $commission->paid_at = now();
                $commission->save();
            }

            DB::commit();
            $this->cacheForget('delivery_man_commission_list');

            return redirect('delivery-man-commissions/payout-report')->with('message', __('db.Commission payout processed successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Commission payout failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Commission payout failed: ' . $e->getMessage());
        }
    }

    public function payoutReport()
    {
        $lims_commission_list = DeliveryManCommission::with(['deliveryMan'])
            ->where('status', 'pending')
            ->get();

        $grouped = $lims_commission_list->groupBy('delivery_man_id');

        return view('backend.delivery_management.delivery_man_commission.payout_report', compact('grouped'));
    }

    public function newCustomerIncentives()
    {
        $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
        $report = [];

        foreach ($lims_delivery_man_list as $deliveryMan) {
            $newCustomers = $deliveryMan->fieldOrders()
                ->whereHas('customer', function ($q) {
                    $q->whereDate('created_at', '>=', date('Y-m-01'));
                })
                ->count();

            $report[] = [
                'delivery_man' => $deliveryMan,
                'new_customers' => $newCustomers,
            ];
        }

        return view('backend.delivery_management.delivery_man_commission.new_customer_incentives', compact('report'));
    }

    public function dueCollectionIncentives()
    {
        $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
        $report = [];

        foreach ($lims_delivery_man_list as $deliveryMan) {
            $dueCollected = $deliveryMan->fieldOrders()
                ->where('status', 'completed')
                ->sum('due_amount');

            $report[] = [
                'delivery_man' => $deliveryMan,
                'due_collected' => $dueCollected,
            ];
        }

        return view('backend.delivery_management.delivery_man_commission.due_collection_incentives', compact('report'));
    }
}