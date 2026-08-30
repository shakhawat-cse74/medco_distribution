@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Field Order Creation</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Field Orders</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">Create New Field Order</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('field-orders.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="delivery_man_id">Delivery Man <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="delivery_man_id" name="delivery_man_id" data-live-search="true" required>
                                    <option value="">Select Delivery Man</option>
                                    @foreach($lims_delivery_men as $dm)
                                        <option value="{{ $dm->id }}">{{ $dm->name }} - {{ $dm->phone_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="customer_id" name="customer_id" data-live-search="true" required>
                                    <option value="">Select Customer</option>
                                    @foreach($lims_customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="warehouse_id">Warehouse <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="warehouse_id" name="warehouse_id" data-live-search="true" required>
                                    <option value="">Select Warehouse</option>
                                    @foreach($lims_warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h6>Add Product</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group" style="position:relative;">
                                    <div class="input-group">
                                        <input type="text" id="product-search-input" class="form-control" placeholder="Search product by name/code" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="button" id="btn-search-product" class="btn btn-primary"><i class="ti ti-search"></i> {{__('db.Search')}}</button>
                                        </div>
                                    </div>
                                    <div id="product-results-container" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; max-height:200px; overflow-y:auto; width:100%; z-index:1050;"></div>
                                </div>
                            </div>
                        </div>

                        <h6>Product Catalog</h6>
                        <div class="table-responsive">
                            <table class="table table-striped" id="productCatalogTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU/Code</th>
                                        <th>Warehouse Stock</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Sub Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <h6>Order Summary</h6>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Sub Total</label>
                                <input type="text" class="form-control" id="sub_total" readonly>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Discount Amount</label>
                                <input type="text" class="form-control" id="discount_amount" name="discount_amount" value="0">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Tax Amount</label>
                                <input type="text" class="form-control" id="tax_amount" name="tax_amount" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Shipping Cost</label>
                                <input type="text" class="form-control" id="shipping_cost" name="shipping_cost" value="0">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Grand Total</label>
                                <input type="text" class="form-control" id="grand_total" name="grand_total" style="font-weight: bold; background: #f8f9fa;" readonly>
                            </div>
                        </div>

                        <hr>

                        <h6>Order Notes</h6>
                        <div class="form-group">
                            <textarea class="form-control" rows="3" name="special_instructions" placeholder="Special instructions for delivery"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    let typingTimer;
    const doneTypingInterval = 300;
    const $searchInput = $('#product-search-input');
    const $results = $('#product-results-container');

    $searchInput.on('keyup', function(e) {
        clearTimeout(typingTimer);
        const query = $(this).val().trim();
        
        if (e.key === 'Escape') {
            $results.hide();
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            if (query.length >= 2) {
                searchProducts(query);
            }
            return;
        }

        if (query.length < 2) {
            $results.hide();
            return;
        }

        typingTimer = setTimeout(() => {
            searchProducts(query);
        }, doneTypingInterval);
    });

    $('#btn-search-product').on('click', function() {
        const query = $searchInput.val().trim();
        if (query.length >= 2) {
            searchProducts(query);
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#product-search-input, #product-results-container').length) {
            $results.hide();
        }
    });

    function searchProducts(query) {
        const warehouseId = $('#warehouse_id').val();
        if (!warehouseId) {
            alert('Please select a warehouse first');
            $searchInput.val('');
            return;
        }

        $.ajax({
            url: '{{ route("field-orders.searchProducts") }}',
            type: 'GET',
            data: { search: query, warehouse_id: warehouseId },
            success: function(data) {
                console.log('Search success:', data);
                $results.empty();
                if (data && data.length > 0) {
                    data.forEach(function(product) {
                        const item = $('<div class="product-img" style="padding:8px 12px; cursor:pointer; border-bottom:1px solid #eee;"></div>');
                        item.html('<strong>' + product.name + '</strong> (' + product.code + ') | Stock: ' + product.stock + ' | ' + product.price);
                        item.on('click', function() {
                            addProductToTable(product);
                            $results.hide();
                            $searchInput.val('');
                        });
                        $results.append(item);
                    });
                    $results.show();
                } else {
                    $results.html('<div style="padding:8px 12px; color:#999;">No products found</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Search error:', status, error);
                $results.html('<div style="padding:8px 12px; color:red;">Search failed. Please try again.</div>').show();
            }
        });
    }
});

function addProductToTable(product) {
    const table = $('#productCatalogTable tbody');
    const existingRow = table.find('input[name^="products["][name$="[product_id]"][value="' + product.id + '"]').closest('tr');

    if (existingRow.length) {
        const qtyInput = existingRow.find('.qty');
        const currentQty = parseInt(qtyInput.val()) || 0;
        qtyInput.val(currentQty + 1).trigger('input');
        return;
    }

    const row = $('<tr>');
    const rowIndex = table.find('tr').length;
    const qty = 1;
    const unitPrice = parseFloat(product.price);
    const subTotal = qty * unitPrice;

    row.html(`
        <td>${product.name}
            <input type="hidden" name="products[${rowIndex}][product_id]" value="${product.id}">
            <input type="hidden" name="products[${rowIndex}][name]" value="${product.name}">
        </td>
        <td>${product.code || 'N/A'}
            <input type="hidden" name="products[${rowIndex}][code]" value="${product.code || ''}">
        </td>
        <td>${product.stock}</td>
        <td><input type="number" class="form-control qty" name="products[${rowIndex}][qty]" value="${qty}" min="1" style="width:80px;"></td>
        <td><input type="number" class="form-control unit_price" name="products[${rowIndex}][unit_price]" value="${unitPrice}" step="0.01" style="width:100px;"></td>
        <td class="sub-total">${subTotal.toFixed(2)}</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="ti ti-trash"></i></button></td>
    `);

    table.append(row);
    calculateTotal();
}

$(document).on('input', '.qty, .unit_price', function() {
    const row = $(this).closest('tr');
    const qty = parseFloat(row.find('.qty').val()) || 0;
    const price = parseFloat(row.find('.unit_price').val()) || 0;
    row.find('.sub-total').text((qty * price).toFixed(2));
    calculateTotal();
});

$(document).on('click', '.remove-row', function() {
    $(this).closest('tr').remove();
    calculateTotal();
});

function calculateTotal() {
    let subTotal = 0;
    $('#productCatalogTable tbody tr').each(function() {
        subTotal += parseFloat($(this).find('.sub-total').text()) || 0;
    });
    const discount = parseFloat($('#discount_amount').val()) || 0;
    const tax = parseFloat($('#tax_amount').val()) || 0;
    const shipping = parseFloat($('#shipping_cost').val()) || 0;
    const grandTotal = subTotal + tax + shipping - discount;

    $('#sub_total').val(subTotal.toFixed(2));
    $('#grand_total').val(grandTotal.toFixed(2));
}

$('#discount_amount, #tax_amount, #shipping_cost').on('input', calculateTotal);
</script>
@endpush
