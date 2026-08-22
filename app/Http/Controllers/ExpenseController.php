<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Account;
use App\Models\Warehouse;
use App\Models\CashRegister;
use App\Traits\StaffAccess;
use App\Traits\TenantInfo;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DateHelper;
use DateTime;
use Auth;
use DB;

use App\Services\AccountingService;

class ExpenseController extends Controller
{
    use StaffAccess;
    use TenantInfo;

    public function __construct(public AccountingService $accountingService)
    {
    }

    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('expenses-index')){
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';

            if($request->starting_date) {
                $starting_date = $request->starting_date;
                $ending_date = $request->ending_date;
            }
            else {
                $starting_date = date('Y-m-01', strtotime('-1 year', strtotime(date('Y-m-d'))));
                $ending_date = date("Y-m-d");
            }

            if($request->input('warehouse_id'))
                $warehouse_id = $request->input('warehouse_id');
            else
                $warehouse_id = 0;

            $lims_warehouse_list = Warehouse::select('name', 'id')->where('is_active', true)->get();
            $lims_account_list = Account::where('is_active', true)->get();

            if($request->input('expense_category_id'))
                $expense_category_id = $request->input('expense_category_id');
            else
                $expense_category_id = 0;

            $expense_category_list = DB::table('expense_categories')->where('is_active', true)->get();

            return view('backend.expense.index', compact('lims_account_list', 'lims_warehouse_list', 'all_permission', 'starting_date', 'ending_date', 'warehouse_id', 'expense_category_id', 'expense_category_list'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function expenseData(Request $request)
    {
        // dd($request->all());
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            5 => 'amount',
        );

        $warehouse_id = auth()->user()->warehouse_id ??$request->input('warehouse_id');
        $q = Expense::whereDate('created_at', '>=' ,$request->input('starting_date'))
                     ->whereDate('created_at', '<=' ,$request->input('ending_date'));
        //check staff access
        $this->staffAccessCheck($q);
        if($warehouse_id)
            $q = $q->where('warehouse_id', $warehouse_id);

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'expenses.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if(empty($request->input('search.value'))) {
            $q = Expense::with('warehouse', 'expenseCategory')
                ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);
            //check staff access
            $this->staffAccessCheck($q);
            if($warehouse_id)
                $q = $q->where('warehouse_id', $warehouse_id);
            $expenses = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = Expense::with(['warehouse', 'expenseCategory'])
                ->whereDate('expenses.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                ->orWhereHas('expenseCategory', function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);
            if(Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $expenses =  $q->select('expenses.*')
                                ->where('expenses.user_id', Auth::id())
                                ->orwhere([
                                    ['reference_no', 'LIKE', "%{$search}%"],
                                    ['user_id', Auth::id()]
                                ])
                                ->get();
                $totalFiltered = $q->where('expenses.user_id', Auth::id())->count();
            }
            elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse') {
                $expenses =  $q->select('expenses.*')
                                ->where('expenses.user_id', Auth::id())
                                ->orwhere([
                                    ['reference_no', 'LIKE', "%{$search}%"],
                                    ['warehouse_id', Auth::user()->warehouse_id]
                                ])
                                ->get();
                $totalFiltered = $q->where('expenses.user_id', Auth::id())->count();
            }
            else {
                $expenses =  $q->select('expenses.*')
                                ->with('warehouse', 'expenseCategory')
                                ->orwhere('reference_no', 'LIKE', "%{$search}%")
                                ->get();

                $totalFiltered = $q->orwhere('expenses.reference_no', 'LIKE', "%{$search}%")->count();
            }
        }
        $data = array();
        if(!empty($expenses))
        {
            foreach ($expenses as $key=>$expense)
            {
                $nestedData['id'] = $expense->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($expense->created_at->toDateString()));
                $nestedData['reference_no'] = $expense->reference_no;
                $nestedData['warehouse'] = $expense->warehouse->name;
                $nestedData['expenseCategory'] = $expense->expense_category_id ==0 ? 'Employee Expense' :  $expense->expenseCategory->name;
                $nestedData['amount'] = number_format($expense->amount, config('decimal'));
                $nestedData['note'] = $expense->note;
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">';
                if (in_array("expenses-edit", $request['all_permission'])) {
                    if($expense->document){
                        $nestedData['options'] .= '<li>
                            <a href="'.url('documents/expense/'.$expense->document).'" target="_blank" class="btn btn-link">
                                <i class="ti ti-document"></i> '.__('db.View Document').'
                            </a>
                        </li>';
                    }

                    $nestedData['options'] .= '<li>
                        <button type="button" data-id="'.$expense->id.'" class="open-Editexpense_categoryDialog btn btn-link" data-toggle="modal" data-target="#editModal">
                            <i class="ti ti-edit"></i>'.__('db.edit').'
                        </button>
                    </li>';
                }


                if(in_array("expenses-delete", $request['all_permission']))
                    $nestedData['options'] .= '<form action="' . route("expenses.destroy", $expense->id) . '" method="POST">'.csrf_field().'' . method_field("DELETE") . '
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="ti ti-trash"></i> '.__("db.delete").'</button>
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
        echo json_encode($json_data);
    }

    public function store(Request $request)
    {
        try {
        DB::beginTransaction();

        $data = $request->except('document');
        $data = $request->all();
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            if (!file_exists(public_path("documents/expense")) && !is_dir(public_path("documents/expense"))) {
                mkdir(public_path("documents/expense"), 0755, true);
            }

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/expense'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/expense'), $documentName);
            }
            $data['document'] = $documentName;
        }
        if (isset($data['created_at'])) {
            $data['created_at'] = normalize_to_sql_datetime($data['created_at']);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $data['reference_no'] = 'er-' . date("Ymd") . '-'. date("his");
        $data['user_id'] = Auth::id();
        $data['employee_id'] = $request->employee_id ?? null;
        $data['type'] = $request->type ?? null;

        // record pos page expense in cash register
        if(isset($data['cash_register'])){
            $data['cash_register_id'] = $data['cash_register'];
        }

        $expense = Expense::create($data);

        $accountingResult = $this->accountingService->recordExpense($expense);
        if (!$accountingResult->isSuccess()) {
            $expense->update(['accounting_status' => 'failed']);
            \Log::error('Expense Accounting failed: ' . $accountingResult->getMessage());
        } else {
            $expense->update(['accounting_status' => 'posted']);
        }

        DB::commit();
        return redirect('expenses')->with('message', __('db.Data inserted successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Expense creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Expense creation failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if ($role->hasPermissionTo('expenses-edit')) {
            $lims_expense_data = Expense::find($id);
            $lims_expense_data->date = date('d-m-Y', strtotime($lims_expense_data->created_at->toDateString()));
            return $lims_expense_data;
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function update(Request $request, $id)
    {
        try {
        DB::beginTransaction();

        $data = $request->except('document');
        $data = $request->all();
        $lims_expense_data = Expense::find($data['expense_id']);
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            if (!file_exists(public_path("documents/expense")) && !is_dir(public_path("documents/expense"))) {
                mkdir(public_path("documents/expense"), 0755, true);
            }

            if (!file_exists(public_path("documents/expense/$lims_expense_data->document"))) {

                $this->fileDelete(public_path('documents/expense/'), $lims_expense_data->document);
            }

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/expense'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/expense'), $documentName);
            }
            $data['document'] = $documentName;
        }
        if (isset($data['created_at'])) {
            $data['created_at'] = normalize_to_sql_datetime($data['created_at']);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $expense = Expense::find($data['expense_id']);
        $this->accountingService->reverseTransaction(get_class($expense), $expense->id);

        $expense->update($data);

        $accountingResult = $this->accountingService->recordExpense($expense);
        if (!$accountingResult->isSuccess()) {
            $expense->update(['accounting_status' => 'failed']);
            \Log::error('Expense Accounting failed on update: ' . $accountingResult->getMessage());
        } else {
            $expense->update(['accounting_status' => 'posted']);
        }

        DB::commit();
        return redirect('expenses')->with('message', __('db.Data updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Expense update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Expense update failed: ' . $e->getMessage());
        }
    }

    public function deleteBySelection(Request $request)
    {
        try {
            DB::beginTransaction();
            $expense_id = $request['expenseIdArray'];
            foreach ($expense_id as $id) {
                $expense = Expense::find($id);
                $this->accountingService->reverseTransaction(get_class($expense), $expense->id);
                $expense->delete();
            }
            DB::commit();
            return 'Expense deleted successfully!';
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Expense bulk deletion failed: ' . $e->getMessage());
            return 'Expense bulk deletion failed: ' . $e->getMessage();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $expense = Expense::find($id);
        $this->accountingService->reverseTransaction(get_class($expense), $expense->id);
        $expense->delete();
            DB::commit();
            return redirect('expenses')->with('not_permitted', __('db.Data deleted successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Expense deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Expense deletion failed: ' . $e->getMessage());
        }
    }
}
