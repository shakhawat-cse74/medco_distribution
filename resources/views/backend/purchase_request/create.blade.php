@extends('backend.layout.main') 

@section('content')

<x-error-message key="not_permitted" />

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{__('Add Purchase Request')}}</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{__('db.The field labels marked with are required input fields')}}.</small></p>
                        <form action="{{ route('purchase_requests.store') }}" method="POST" enctype="multipart/form-data" id="purchase-request-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{__('db.Supplier')}} *</label>
                                                <select required name="supplier_id" class="selectpicker form-control" data-live-search="true" id="supplier-id" title="Select Supplier...">
                                                    @foreach($lims_supplier_list as $supplier)
                                                    <option value="{{$supplier->id}}">{{$supplier->name . ' (' . ($supplier->company_name ?: 'Individual') . ')'}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{__('db.Warehouse')}} *</label>
                                                <select id="warehouse_id" name="warehouse_id" required class="selectpicker form-control" data-live-search="true" title="Select warehouse...">
                                                    @foreach($lims_warehouse_list as $warehouse)
                                                    <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mt-2">
                                            <label>{{__('db.Select Product')}}</label>
                                            <div class="search-box input-group position-relative">
                                                <button type="button" class="btn btn-secondary"><i class="ti ti-barcode"></i></button>
                                                <input type="text" name="product_code_name" id="lims_productcodeSearch" placeholder="{{__('db.Please type product code and select')}}" class="form-control" autocomplete="off" />
                                                <div id="pr-product-results-container" class="position-absolute bg-white border shadow w-100" style="display:none; top: 100%; left: 0; z-index: 1050; max-height: 280px; overflow-y: auto;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-5">
                                        <div class="col-md-12">
                                            <h5>{{__('db.Order Table')}} *</h5>
                                            <div class="table-responsive mt-3">
                                                <table id="myTable" class="table table-hover order-list">
                                                    <thead>
                                                        <tr>
                                                            <th>{{__('db.name')}}</th>
                                                            <th>{{__('db.Code')}}</th>
                                                            <th>{{__('db.Quantity')}}</th>
                                                            <th>{{__('db.Net Unit Cost')}}</th>
                                                            <th>{{__('db.Discount')}}</th>
                                                            <th>{{__('db.Tax')}}</th>
                                                            <th>{{__('db.Subtotal')}}</th>
                                                            <th><i class="ti ti-trash"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="pr-order-tbody">
                                                    </tbody>
                                                    <tfoot class="tfoot active">
                                                        <th colspan="2">{{__('db.Total')}}</th>
                                                        <th id="total-qty">0.00</th>
                                                        <th></th>
                                                        <th id="total-discount">0.00</th>
                                                        <th id="total-tax">0.00</th>
                                                        <th id="total">0.00</th>
                                                        <th><i class="ti ti-trash"></i></th>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_qty" id="hidden-total-qty" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_discount" id="hidden-total-discount" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_tax" id="hidden-total-tax" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_cost" id="hidden-total-cost" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="item" id="hidden-item" />
                                                <input type="hidden" name="order_tax" id="hidden-order-tax" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="grand_total" id="hidden-grand-total" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Order Tax')}}</label>
                                                <select class="form-control" name="order_tax_rate" id="order-tax-rate">
                                                    <option value="0">{{__('db.No Tax')}}</option>
                                                    @foreach($lims_tax_list as $tax)
                                                    <option value="{{$tax->rate}}">{{$tax->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Order Discount')}}</label>
                                                <input type="number" name="order_discount" id="order-discount" class="form-control" step="any" value="0" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Shipping Cost')}}</label>
                                                <input type="number" name="shipping_cost" id="shipping-cost" class="form-control" step="any" value="0" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Status')}}</label>
                                                <select class="form-control" name="status">
                                                    <option value="1">{{__('db.Pending')}}</option>
                                                    <option value="2">{{__('db.Sent')}}</option>
                                                    <option value="3">{{__('db.Ordered')}}</option>
                                                    <option value="4">{{__('db.Draft')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Attach Document')}}</label>
                                                <i class="ti ti-info-circle" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                                <input type="file" name="document" class="form-control" />
                                                @if($errors->has('extension'))
                                                    <span>
                                                       <strong class="text-danger">{{ $errors->first('extension') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>{{__('db.Note')}}</label>
                                                <textarea rows="5" class="form-control" name="note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" value="{{__('db.submit')}}" class="btn btn-primary" id="submit-button">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        const $searchInput = $('#lims_productcodeSearch');
        const $results = $('#pr-product-results-container');
        let searchXhr = null;
        let searchTimer = null;

        function searchProducts(query) {
            query = (query || '').trim();
            if (query.length === 0) {
                if (searchXhr) searchXhr.abort();
                $results.empty().hide();
                return;
            }

            if (searchXhr) searchXhr.abort();
            let warehouse_id = $('#warehouse_id').val() || 1;

            searchXhr = $.ajax({
                url: '{{ url("/sales/search") }}',
                type: 'GET',
                data: {
                    warehouse_id: warehouse_id,
                    search: query
                },
                success: function(data) {
                    $results.empty();
                    if (data && data.length > 0) {
                        data.forEach(function(product) {
                            let cost = product.cost !== undefined ? parseFloat(product.cost).toFixed(2) : (parseFloat(product.price || 0).toFixed(2));
                            let itemHtml = `
                                <div class="pr-item-result p-2 border-bottom d-flex justify-content-between align-items-center"
                                     style="cursor: pointer;"
                                     data-id="${product.id}"
                                     data-name="${product.name}"
                                     data-code="${product.code}"
                                     data-cost="${cost}"
                                     data-unit="${product.unit_name || 'PC'}">
                                    <div>
                                        <div class="font-weight-bold text-dark">${product.name}</div>
                                        <small class="text-muted"><i class="ti ti-barcode mr-1"></i>${product.code}</small>
                                    </div>
                                    <div class="text-primary font-weight-bold">
                                        {{ config('currency') ?? '$' }} ${cost}
                                    </div>
                                </div>`;
                            $results.append(itemHtml);
                        });
                        $results.show();
                    } else {
                        $results.html('<div class="p-2 text-muted text-center">No products found</div>').show();
                    }
                }
            });
        }

        $searchInput.on('input', function() {
            let val = $(this).val().trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => searchProducts(val), 50);
        });

        // Click to add product
        $(document).on('click', '.pr-item-result', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let code = $(this).data('code');
            let cost = parseFloat($(this).data('cost')) || 0;
            let unit = $(this).data('unit') || 'PC';

            let existingRow = $(`#pr-order-tbody tr[data-id="${id}"]`);
            if (existingRow.length > 0) {
                let qtyInput = existingRow.find('.qty');
                qtyInput.val(parseFloat(qtyInput.val() || 0) + 1);
            } else {
                let rowHtml = `
                    <tr data-id="${id}">
                        <td>
                            <strong>${name}</strong>
                            <input type="hidden" class="product-id" name="product_id[]" value="${id}"/>
                            <input type="hidden" class="product-code" name="product_code[]" value="${code}"/>
                            <input type="hidden" class="purchase-unit" name="purchase_unit[]" value="${unit}"/>
                        </td>
                        <td>${code}</td>
                        <td><input type="number" class="form-control qty" name="qty[]" value="1" step="any" min="1" required/></td>
                        <td><input type="number" class="form-control net_unit_cost" name="net_unit_cost[]" value="${cost.toFixed(2)}" step="any" min="0" required/></td>
                        <td><input type="number" class="form-control discount" name="discount[]" value="0.00" step="any" min="0"/></td>
                        <td class="tax-cell">0.00</td>
                        <td><input type="text" class="form-control subtotal" name="subtotal[]" value="${cost.toFixed(2)}" readonly/></td>
                        <td><button type="button" class="ibtnDel btn btn-md btn-danger"><i class="ti ti-trash"></i></button></td>
                    </tr>`;
                $('#pr-order-tbody').append(rowHtml);
            }

            $searchInput.val('');
            $results.empty().hide();
            calculateGrandTotal();
        });

        // Close dropdown on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#pr-product-results-container, #lims_productcodeSearch').length) {
                $results.empty().hide();
            }
        });

        // Recalculate on input change
        $(document).on('input', '.qty, .net_unit_cost, .discount, #order-discount, #shipping-cost, #order-tax-rate', function() {
            let row = $(this).closest('tr');
            if (row.length > 0) {
                let qty = parseFloat(row.find('.qty').val()) || 0;
                let cost = parseFloat(row.find('.net_unit_cost').val()) || 0;
                let discount = parseFloat(row.find('.discount').val()) || 0;
                let subtotal = (qty * cost) - discount;
                row.find('.subtotal').val(subtotal.toFixed(2));
            }
            calculateGrandTotal();
        });

        // Delete row
        $(document).on('click', '.ibtnDel', function() {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        });

        function calculateGrandTotal() {
            let totalQty = 0;
            let totalDiscount = 0;
            let totalTax = 0;
            let totalCost = 0;
            let itemCount = 0;

            $('#pr-order-tbody tr').each(function() {
                itemCount++;
                let qty = parseFloat($(this).find('.qty').val()) || 0;
                let cost = parseFloat($(this).find('.net_unit_cost').val()) || 0;
                let discount = parseFloat($(this).find('.discount').val()) || 0;
                let subtotal = (qty * cost) - discount;

                totalQty += qty;
                totalDiscount += discount;
                totalCost += subtotal;
            });

            let orderTaxRate = parseFloat($('#order-tax-rate').val()) || 0;
            let orderDiscount = parseFloat($('#order-discount').val()) || 0;
            let shippingCost = parseFloat($('#shipping-cost').val()) || 0;

            let orderTax = (totalCost * orderTaxRate) / 100;
            let grandTotal = totalCost + orderTax + shippingCost - orderDiscount;

            $('#total-qty').text(totalQty.toFixed(2));
            $('#total-discount').text(totalDiscount.toFixed(2));
            $('#total-tax').text(orderTax.toFixed(2));
            $('#total').text(totalCost.toFixed(2));

            $('#hidden-total-qty').val(totalQty);
            $('#hidden-total-discount').val(totalDiscount);
            $('#hidden-total-tax').val(orderTax);
            $('#hidden-total-cost').val(totalCost);
            $('#hidden-item').val(itemCount);
            $('#hidden-order-tax').val(orderTax);
            $('#hidden-grand-total').val(grandTotal);
        }

        $('#purchase-request-form').on('submit', function(e) {
            if ($('#pr-order-tbody tr').length === 0) {
                e.preventDefault();
                alert("Please insert product to order table!");
                return false;
            }
        });
    });
</script>
@endpush
