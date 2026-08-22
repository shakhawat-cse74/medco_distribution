        <div id="expense-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">{{ __('Add Expense') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="ti ti-x"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ __('The field labels marked with are required input fields') }}.</small></p>
                        <form action="{{ route('expenses.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                        <?php
                        $lims_expense_category_list = DB::table('expense_categories')->where('is_active', true)->get();
                        if (Auth::user()->role_id > 2) {
                            $lims_warehouse_list = DB::table('warehouses')
                                ->where([['is_active', true], ['id', Auth::user()->warehouse_id]])
                                ->get();
                        } else {
                            $lims_warehouse_list = DB::table('warehouses')->where('is_active', true)->get();
                        }
                        $lims_account_list = \App\Models\Account::where('is_active', true)->get();
                        ?>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ __('Date') }}</label>
                                <input type="text" name="created_at" class="form-control date"
                                    placeholder="{{ __('db.Choose date') }}"
                                    value="{{ date(gen_setting()->date_format ?? 'Y-m-d', strtotime('now')) }}" />
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Expense Category') }} *</label>
                                <select name="expense_category_id"
                                    class="selectpicker form-control"
                                    required
                                    data-live-search="true"
                                    title="Select Expense Category...">
                                    @foreach ($lims_expense_category_list as $expense_category)
                                        <option value="{{ $expense_category->id }}">{{ $expense_category->name }} ({{ $expense_category->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Warehouse') }} *</label>
                                <select name="warehouse_id" class="selectpicker form-control" required
                                    data-live-search="true" data-live-search-style="begins"
                                    title="Select Warehouse...">
                                    @foreach ($lims_warehouse_list as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ __('Amount') }} *</label>
                                <input type="number" name="amount" step="any" required class="form-control">
                            </div>
                            <div class="col-md-12" id="employee-fields" style="display:none; width:100%">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>{{ __('db.select Employee') }}</label>
                                        <select id="expense-employee" name="employee_id"
                                            class="selectpicker form-control" data-live-search="true"
                                            title="Select Employee...">
                                            @foreach (\App\Models\Employee::where('is_active', 1)->get() as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>{{ __('db.Select Type') }} *</label>
                                        <select id="expense-type" name="expense_type"
                                            class="selectpicker form-control" disabled>
                                            <option value="">{{ __('db.Select Type') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label> {{ __('Account') }}</label>
                                <select class="form-control selectpicker" name="account_id">
                                    @foreach ($lims_account_list as $account)
                                        @if ($account->is_default)
                                            <option selected value="{{ $account->id }}">{{ $account->name }}
                                                [{{ $account->account_no }}]</option>
                                        @else
                                            <option value="{{ $account->id }}">{{ $account->name }}
                                                [{{ $account->account_no }}]</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('db.Attach Document') }}</label>
                                    <i class="ti ti-info-circle" data-toggle="tooltip"
                                        title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                    <input type="file" name="document" class="form-control" />
                                    @if ($errors->has('extension'))
                                        <span>
                                            <strong>{{ $errors->first('extension') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Note') }}</label>
                            <textarea name="note" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ __('submit') }}</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
