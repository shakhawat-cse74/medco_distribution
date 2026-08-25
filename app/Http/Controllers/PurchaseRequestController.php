<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\ProductPurchaseRequest;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Tax;
use App\Models\Currency;
use App\Models\CustomField;
use App\Models\Account;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\GeneralSetting;
use App\Models\InvoiceSetting;
use Auth;
use DB;
use Carbon\Carbon;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $warehouse_id = $request->input('warehouse_id', 0);
        $supplier_id = $request->input('supplier_id', 0);
        $status = $request->input('status', 0);
        
        $starting_date = $request->input('starting_date', date('Y-m-d', strtotime('-1 year')));
        $ending_date = $request->input('ending_date', date('Y-m-d'));

        $query = PurchaseRequest::with(['supplier', 'warehouse', 'user', 'productPurchaseRequests'])
            ->whereDate('created_at', '>=', $starting_date)
            ->whereDate('created_at', '<=', $ending_date);

        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }
        if ($supplier_id) {
            $query->where('supplier_id', $supplier_id);
        }
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', '!=', 3);
        }

        $lims_purchase_request_all = $query->orderBy('id', 'desc')->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_supplier_list = Supplier::where('is_active', true)->get();

        return view('backend.purchase_request.index', compact(
            'lims_purchase_request_all',
            'lims_warehouse_list',
            'lims_supplier_list',
            'warehouse_id',
            'supplier_id',
            'status',
            'starting_date',
            'ending_date'
        ));
    }

    public function create()
    {
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        if (Auth::user()->role_id > 2) {
            $lims_warehouse_list = Warehouse::where([
                ['is_active', true],
                ['id', Auth::user()->warehouse_id]
            ])->get();
        } else {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        }
        $lims_tax_list = Tax::where('is_active', true)->get();
        $currency_list = Currency::where('is_active', true)->get();

        return view('backend.purchase_request.create', compact(
            'lims_supplier_list',
            'lims_warehouse_list',
            'lims_tax_list',
            'currency_list'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->except('document');
        
        // Generate Reference No: PUR + sequence number or PR-YYYYMMDD-count
        $lastRequest = PurchaseRequest::withTrashed()->latest('id')->first();
        $nextId = $lastRequest ? ($lastRequest->id + 1) : 1;
        $data['reference_no'] = 'PUR' . $nextId;
        
        $data['user_id'] = Auth::id();
        $data['created_at'] = $request->created_at ? Carbon::parse($request->created_at)->toDateTimeString() : now();

        $document = $request->document;
        if ($document) {
            $v = \Validator::make(
                ['extension' => strtolower($request->document->getClientOriginalExtension())],
                ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
            );
            if ($v->fails()) {
                return redirect()->back()->withErrors($v->errors());
            }
            $documentName = $document->getClientOriginalName();
            $document->move('public/documents/purchase_request', $documentName);
            $data['document'] = $documentName;
        }

        DB::beginTransaction();
        try {
            $purchaseRequest = PurchaseRequest::create($data);

            if (isset($data['product_id']) && is_array($data['product_id'])) {
                foreach ($data['product_id'] as $key => $productId) {
                    $unit = Unit::where('unit_name', $data['purchase_unit'][$key] ?? 'pc')->first();
                    
                    ProductPurchaseRequest::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'product_id' => $productId,
                        'variant_id' => $data['variant_id'][$key] ?? null,
                        'product_batch_id' => $data['product_batch_id'][$key] ?? null,
                        'purchase_unit_id' => $unit ? $unit->id : null,
                        'qty' => $data['qty'][$key] ?? 0,
                        'net_unit_cost' => $data['net_unit_cost'][$key] ?? 0,
                        'discount' => $data['discount'][$key] ?? 0,
                        'tax_rate' => $data['tax_rate'][$key] ?? 0,
                        'tax' => $data['tax'][$key] ?? 0,
                        'total' => $data['subtotal'][$key] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('purchase_requests.index')->with('message', 'Purchase Request created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('not_permitted', 'Error creating purchase request: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $lims_purchase_request_data = PurchaseRequest::findOrFail($id);
        $lims_product_purchase_request_data = ProductPurchaseRequest::where('purchase_request_id', $id)->get();
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $currency_list = Currency::where('is_active', true)->get();

        return view('backend.purchase_request.edit', compact(
            'lims_purchase_request_data',
            'lims_product_purchase_request_data',
            'lims_supplier_list',
            'lims_warehouse_list',
            'lims_tax_list',
            'currency_list'
        ));
    }

    public function update(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $data = $request->except('document');

        $document = $request->document;
        if ($document) {
            $v = \Validator::make(
                ['extension' => strtolower($request->document->getClientOriginalExtension())],
                ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
            );
            if ($v->fails()) {
                return redirect()->back()->withErrors($v->errors());
            }
            $documentName = $document->getClientOriginalName();
            $document->move('public/documents/purchase_request', $documentName);
            $data['document'] = $documentName;
        }

        DB::beginTransaction();
        try {
            $purchaseRequest->update($data);

            // Delete old line items and re-insert
            ProductPurchaseRequest::where('purchase_request_id', $id)->delete();

            if (isset($data['product_id']) && is_array($data['product_id'])) {
                foreach ($data['product_id'] as $key => $productId) {
                    $unit = Unit::where('unit_name', $data['purchase_unit'][$key] ?? 'pc')->first();
                    
                    ProductPurchaseRequest::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'product_id' => $productId,
                        'variant_id' => $data['variant_id'][$key] ?? null,
                        'product_batch_id' => $data['product_batch_id'][$key] ?? null,
                        'purchase_unit_id' => $unit ? $unit->id : null,
                        'qty' => $data['qty'][$key] ?? 0,
                        'net_unit_cost' => $data['net_unit_cost'][$key] ?? 0,
                        'discount' => $data['discount'][$key] ?? 0,
                        'tax_rate' => $data['tax_rate'][$key] ?? 0,
                        'tax' => $data['tax'][$key] ?? 0,
                        'total' => $data['subtotal'][$key] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('purchase_requests.index')->with('message', 'Purchase Request updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('not_permitted', 'Error updating purchase request: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->delete();
        return redirect()->route('purchase_requests.index')->with('message', 'Purchase Request deleted successfully.');
    }

    /**
     * Generate PURCHASE RECORD Invoice Print matching the user's reference image
     */
    public function genInvoice($id)
    {
        $lims_request_data = PurchaseRequest::with(['supplier', 'warehouse', 'user'])->findOrFail($id);
        $lims_product_request_data = ProductPurchaseRequest::with(['product', 'unit'])
            ->where('purchase_request_id', $id)
            ->get();
        $general_setting = GeneralSetting::latest()->first();
        $invoice_settings = InvoiceSetting::active_setting() ?? InvoiceSetting::latest()->first();

        return view('backend.purchase_request.invoice', compact(
            'lims_request_data',
            'lims_product_request_data',
            'general_setting',
            'invoice_settings'
        ));
    }

    /**
     * Convert Purchase Request to Purchase (Receiving goods with item checkboxes)
     */
    public function createPurchase($id)
    {
        $lims_request_data = PurchaseRequest::with(['supplier', 'warehouse'])->findOrFail($id);
        $lims_product_request_data = ProductPurchaseRequest::with(['product', 'unit'])
            ->where('purchase_request_id', $id)
            ->get();
        
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $currency_list = Currency::where('is_active', true)->get();
        $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
        $lims_account_list = Account::select('id', 'name', 'account_no', 'total_balance', 'is_default')
            ->where('is_active', true)
            ->get();

        return view('backend.purchase_request.create_purchase', compact(
            'lims_request_data',
            'lims_product_request_data',
            'lims_supplier_list',
            'lims_warehouse_list',
            'lims_tax_list',
            'currency_list',
            'custom_fields',
            'lims_account_list'
        ));
    }
}
