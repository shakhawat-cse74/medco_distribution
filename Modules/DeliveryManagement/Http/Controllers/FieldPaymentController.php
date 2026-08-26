<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\FieldPayment;
use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\DeliveryMan;
use App\Models\GiftCard;
use App\Models\Account;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheForget;

class FieldPaymentController extends Controller
{
    use \App\Traits\CacheForget;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('field-payments-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_payment_list = FieldPayment::with(['fieldOrder.deliveryMan', 'fieldOrder.customer'])->get();
            $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();

            return view('backend.delivery_management.field_payment.index', compact('lims_payment_list', 'lims_delivery_man_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function fieldPaymentData(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'reference_no',
            2 => 'field_order_id',
            3 => 'amount',
            4 => 'payment_method',
            5 => 'created_at',
        );

        $totalData = FieldPayment::count();
        $totalFiltered = $totalData;

        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'asc';

        $query = FieldPayment::query()->with(['fieldOrder.deliveryMan', 'fieldOrder.customer']);

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'LIKE', "%{$search}%")
                  ->orWhere('payment_method', 'LIKE', "%{$search}%")
                  ->orWhereHas('fieldOrder', function ($q2) use ($search) {
                      $q2->where('order_number', 'LIKE', "%{$search}%")
                         ->orWhereHas('deliveryMan', function ($q3) use ($search) {
                             $q3->where('name', 'LIKE', "%{$search}%");
                         })
                         ->orWhereHas('customer', function ($q3) use ($search) {
                             $q3->where('name', 'LIKE', "%{$search}%");
                         });
                  });
            });
        }

        if ($request->filled('delivery_man_id')) {
            $query->whereHas('fieldOrder', function ($q) use ($request) {
                $q->where('delivery_man_id', $request->delivery_man_id);
            });
        }

        $payments = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $totalFiltered = $query->count();

        $data = array();
        if (!empty($payments)) {
            foreach ($payments as $key => $payment) {
                $nestedData['id'] = $payment->id;
                $nestedData['key'] = $key;
                $nestedData['reference_no'] = $payment->reference_no;
                $nestedData['field_order'] = '#' . ($payment->fieldOrder->order_number ?? 'N/A');
                $nestedData['delivery_man'] = $payment->fieldOrder->deliveryMan->name ?? 'N/A';
                $nestedData['customer'] = $payment->fieldOrder->customer->name ?? 'N/A';
                $nestedData['payment_method'] = $payment->payment_method;
                $nestedData['amount'] = number_format($payment->amount, 2);
                $nestedData['date'] = $payment->created_at->format('Y-m-d');
                $nestedData['status'] = '<span class="badge badge-' . ($payment->fieldOrder->status == 'paid' ? 'success' : ($payment->fieldOrder->status == 'partial' ? 'warning' : 'danger')) . '">' . ucfirst($payment->fieldOrder->status ?? 'pending') . '</span>';
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <a href="' . route("field-payments.show", $payment->id) . '" class="btn btn-link"><i class="ti ti-eye"></i> '.__("db.View").'</a>
                                </li>
                                <li>
                                    <a href="' . route("field-payments.edit", $payment->id) . '" class="btn btn-link"><i class="ti ti-edit"></i> '.__("db.edit").'</a>
                                </li>
                                <li class="divider"></li>
                                <form action="' . route("field-payments.destroy", $payment->id) . '" method="POST">'.csrf_field().'' . method_field("DELETE") . '
                                <li>
                                  <button type="submit" class="btn btn-link confirm-delete-btn" data-id="'.$payment->id.'" data-name="'.$payment->reference_no.'"><i class="ti ti-trash"></i> '.__("db.delete").'</button>
                                </li></form>
                            </ul>
                        </div>';
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        return response()->json($json_data);
    }

    public function create($field_order_id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('field-payments-add')) {
            $lims_field_order_data = FieldOrder::with(['deliveryMan', 'customer'])->findOrFail($field_order_id);
            $lims_gift_card_list = GiftCard::where('is_active', true)->get();
            $lims_account_list = Account::where('is_active', true)->get();

            return view('backend.delivery_management.field_payment.create', compact('lims_field_order_data', 'lims_gift_card_list', 'lims_account_list'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('field-payments-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'field_order_id' => 'required|exists:field_orders,id',
            'payment_method' => 'required|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();
        $lims_field_order_data = FieldOrder::findOrFail($data['field_order_id']);

        try {
            DB::beginTransaction();

            $fieldPayment = FieldPayment::create($data);

            $lims_field_order_data->paid_amount += $data['amount'];
            $lims_field_order_data->due_amount = $lims_field_order_data->grand_total - $lims_field_order_data->paid_amount;

            if ($lims_field_order_data->due_amount <= 0) {
                $lims_field_order_data->status = 'paid';
            } elseif ($lims_field_order_data->paid_amount > 0) {
                $lims_field_order_data->status = 'partial';
            }

            $lims_field_order_data->save();

            DB::commit();
            $this->cacheForget('field_payment_list');

            return redirect('field-payments')->with('message', __('db.Payment created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Field payment creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Payment creation failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $lims_payment_data = FieldPayment::with(['fieldOrder.deliveryMan', 'fieldOrder.customer', 'giftCard'])->findOrFail($id);
        return view('backend.delivery_management.field_payment.view', compact('lims_payment_data'));
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('field-payments-edit')) {
            $lims_payment_data = FieldPayment::with('fieldOrder')->findOrFail($id);
            $lims_field_order_data = $lims_payment_data->fieldOrder;
            $lims_gift_card_list = \App\Models\GiftCard::where('is_active', true)->get();

            return view('backend.delivery_management.field_payment.edit', compact('lims_payment_data', 'lims_field_order_data', 'lims_gift_card_list'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function update($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('field-payments-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_payment_data = FieldPayment::findOrFail($id);
        $lims_field_order_data = FieldOrder::findOrFail($lims_payment_data->field_order_id);
        $old_amount = $lims_payment_data->amount;
        $data = request()->all();
        // Prevent re-assigning the payment to a different order.
        $data['field_order_id'] = $lims_payment_data->field_order_id;

        try {
            DB::beginTransaction();
            $lims_payment_data->update($data);

            $lims_field_order_data->paid_amount -= $old_amount;
            $lims_field_order_data->paid_amount += $data['amount'];
            $lims_field_order_data->due_amount = $lims_field_order_data->grand_total - $lims_field_order_data->paid_amount;

            if ($lims_field_order_data->due_amount <= 0) {
                $lims_field_order_data->status = 'paid';
            } elseif ($lims_field_order_data->paid_amount > 0) {
                $lims_field_order_data->status = 'partial';
            } else {
                $lims_field_order_data->status = 'pending';
            }

            $lims_field_order_data->save();
            DB::commit();
            $this->cacheForget('field_payment_list');

            return redirect('field-payments')->with('message', __('db.Payment updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Field payment update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Payment update failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('field-payments-delete')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_payment_data = FieldPayment::findOrFail($id);
        $lims_field_order_data = FieldOrder::findOrFail($lims_payment_data->field_order_id);
        $old_amount = $lims_payment_data->amount;

        try {
            DB::beginTransaction();

            $lims_payment_data->delete();

            $lims_field_order_data->paid_amount -= $old_amount;
            $lims_field_order_data->due_amount = $lims_field_order_data->grand_total - $lims_field_order_data->paid_amount;

            if ($lims_field_order_data->due_amount <= 0) {
                $lims_field_order_data->status = 'paid';
            } elseif ($lims_field_order_data->paid_amount > 0) {
                $lims_field_order_data->status = 'partial';
            } else {
                $lims_field_order_data->status = 'pending';
            }

            $lims_field_order_data->save();
            DB::commit();
            $this->cacheForget('field_payment_list');

            return redirect('field-payments')->with('message', __('db.Payment deleted successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Field payment deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Payment deletion failed: ' . $e->getMessage());
        }
    }

    public function receipt($id)
    {
        $lims_payment_data = FieldPayment::with(['fieldOrder.deliveryMan', 'fieldOrder.customer'])->findOrFail($id);
        return view('backend.delivery_management.field_payment.receipt', compact('lims_payment_data'));
    }

    public function splitPayment($order_id)
    {
        $lims_field_order_data = FieldOrder::with('payments')->findOrFail($order_id);
        return view('backend.delivery_management.field_payment.split', compact('lims_field_order_data'));
    }

    public function getOrderPayments($order_id)
    {
        $payments = FieldPayment::where('field_order_id', $order_id)->get();
        return response()->json($payments);
    }

    public function sendReceipt($id)
    {
        $lims_payment_data = FieldPayment::with('fieldOrder.customer')->findOrFail($id);
        $customer = $lims_payment_data->fieldOrder->customer;

        if ($customer && $customer->phone_number) {
            return redirect()->back()->with('message', __('db.Receipt sent successfully'));
        }

        return redirect()->back()->with('not_permitted', __('db.Customer phone number not found'));
    }

    public function dailySummary()
    {
        $today = date('Y-m-d');
        $payments = FieldPayment::whereDate('created_at', $today)->with(['fieldOrder.deliveryMan', 'fieldOrder.customer'])->get();

        $total = $payments->sum('amount');
        $data = [
            'date' => $today,
            'total_payments' => $payments->count(),
            'total_amount' => $total,
            'payments' => $payments,
        ];

        return view('backend.delivery_management.field_payment.daily_summary', compact('data'));
    }

    public function weeklySummary()
    {
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

        $payments = FieldPayment::whereBetween('created_at', [$startOfWeek, $endOfWeek])->with(['fieldOrder.deliveryMan', 'fieldOrder.customer'])->get();

        $total = $payments->sum('amount');
        $data = [
            'start_date' => $startOfWeek,
            'end_date' => $endOfWeek,
            'total_payments' => $payments->count(),
            'total_amount' => $total,
            'payments' => $payments,
        ];

        return view('backend.delivery_management.field_payment.weekly_summary', compact('data'));
    }

    public function monthlySummary()
    {
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        $payments = FieldPayment::whereBetween('created_at', [$startOfMonth, $endOfMonth])->with(['fieldOrder.deliveryMan', 'fieldOrder.customer'])->get();

        $total = $payments->sum('amount');
        $data = [
            'month' => date('F Y'),
            'total_payments' => $payments->count(),
            'total_amount' => $total,
            'payments' => $payments,
        ];

        return view('backend.delivery_management.field_payment.monthly_summary', compact('data'));
    }
}
