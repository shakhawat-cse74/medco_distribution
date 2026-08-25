@extends('backend.layout.main')

@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-header mt-2 d-flex justify-content-between align-items-center">
                <h4 class="font-weight-bold mb-0"><i class="ti ti-credit-card-pay mr-2 text-primary"></i>Purchase Request List</h4>
                <a href="{{ route('purchase_requests.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus"></i> Add Purchase Request
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('purchase_requests.index') }}" method="get">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>Choose Date Range</strong></label>
                                <div class="input-group">
                                    <input type="text" class="daterangepicker-field form-control" value="{{$starting_date}} To {{$ending_date}}" required />
                                    <input type="hidden" name="starting_date" value="{{$starting_date}}" />
                                    <input type="hidden" name="ending_date" value="{{$ending_date}}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>Choose Supplier</strong></label>
                                <select id="supplier_id" name="supplier_id" class="selectpicker form-control" data-live-search="true">
                                    <option value="0">All Suppliers</option>
                                    @foreach($lims_supplier_list as $supplier)
                                        <option value="{{$supplier->id}}" @if($supplier->id == $supplier_id) selected @endif>{{$supplier->name . ' (' . ($supplier->company_name ?: 'Individual') . ')'}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>Choose Warehouse</strong></label>
                                <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true">
                                    <option value="0">All Warehouses</option>
                                    @foreach($lims_warehouse_list as $warehouse)
                                        <option value="{{$warehouse->id}}" @if($warehouse->id == $warehouse_id) selected @endif>{{$warehouse->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><strong>Status</strong></label>
                                <select id="status" name="status" class="selectpicker form-control">
                                    <option value="0">All Status</option>
                                    <option value="1" @if($status == 1) selected @endif>Pending</option>
                                    <option value="2" @if($status == 2) selected @endif>Ordered</option>
                                    <option value="3" @if($status == 3) selected @endif>Completed / Converted</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button class="btn btn-primary btn-block" type="submit"><i class="ti ti-filter"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="purchase-request-table" class="table table-hover" style="width: 100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Supplier</th>
                                <th>Warehouse</th>
                                <th>Total Items</th>
                                <th>Total Qty</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                                <th class="not-exported">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lims_purchase_request_all as $key => $request_data)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request_data->created_at)->format(gen_setting()->date_format ?? 'd-m-Y') }}</td>
                                    <td>
                                        <strong class="text-primary font-weight-bold">{{ $request_data->reference_no }}</strong>
                                    </td>
                                    <td>{{ $request_data->supplier ? ($request_data->supplier->company_name ?: $request_data->supplier->name) : 'N/A' }}</td>
                                    <td>{{ $request_data->warehouse ? $request_data->warehouse->name : 'N/A' }}</td>
                                    <td>{{ $request_data->item }}</td>
                                    <td>{{ number_format($request_data->total_qty, 2) }}</td>
                                    <td><strong>{{ config('currency') ?? '$' }} {{ number_format($request_data->grand_total, 2) }}</strong></td>
                                    <td>
                                        @if($request_data->status == 1)
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($request_data->status == 2)
                                            <span class="badge badge-info">Ordered</span>
                                        @elseif($request_data->status == 3)
                                            <span class="badge badge-success">Completed</span>
                                        @else
                                            <span class="badge badge-danger">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{ route('purchase_requests.edit', $request_data->id) }}" class="btn btn-primary btn-sm mr-1" title="Edit Purchase Request" style="padding: 3px 8px;">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <a href="{{ route('purchase_requests.create_purchase', $request_data->id) }}" class="btn btn-success btn-sm mr-1" title="Convert to Purchase" style="padding: 3px 8px;">
                                                <i class="ti ti-shopping-cart"></i>
                                            </a>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 3px 8px;">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu" style="min-width: 190px;">
                                                    <li>
                                                        <a href="{{ route('purchase_requests.invoice', $request_data->id) }}" class="btn btn-link">
                                                            <i class="ti ti-printer mr-1"></i> Print Purchase Record
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('purchase_requests.create_purchase', $request_data->id) }}" class="btn btn-link text-success font-weight-bold">
                                                            <i class="ti ti-shopping-cart mr-1"></i> Convert to Purchase
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('purchase_requests.edit', $request_data->id) }}" class="btn btn-link">
                                                            <i class="ti ti-edit mr-1"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('purchase_requests.destroy', $request_data->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this purchase request?');" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link text-danger">
                                                                <i class="ti ti-trash mr-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('#purchase-request-table').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            dom: '<"row"<"col-md-6"B><"col-md-6"f>r>t<"row"<"col-md-6"i><"col-md-6"p>>',
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="ti ti-copy mr-1"></i> Copy',
                    className: 'btn btn-outline-secondary btn-sm'
                },
                {
                    extend: 'excel',
                    text: '<i class="ti ti-file-spreadsheet mr-1"></i> Excel',
                    className: 'btn btn-outline-secondary btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="ti ti-file-text mr-1"></i> PDF',
                    className: 'btn btn-outline-secondary btn-sm'
                },
                {
                    extend: 'print',
                    text: '<i class="ti ti-printer mr-1"></i> Print',
                    className: 'btn btn-outline-secondary btn-sm'
                }
            ]
        });

        $(".daterangepicker-field").daterangepicker({
            callback: function(startDate, endDate, period){
                var starting_date = startDate.format('YYYY-MM-DD');
                var ending_date = endDate.format('YYYY-MM-DD');
                var title = starting_date + ' To ' + ending_date;
                $(this).val(title);
                $('input[name="starting_date"]').val(starting_date);
                $('input[name="ending_date"]').val(ending_date);
            }
        });
    });
</script>
@endpush
