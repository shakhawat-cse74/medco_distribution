@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Product Wise Field Sale</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Product Wise Field Sale</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="card">
        <div class="card-header">
            <h5>Product Sales Analysis in Field Operations</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>SKU/Code</th>
                            <th>Total Quantity Sold</th>
                            <th>Total Amount</th>
                            <th>Average Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productSales as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->total_qty }}</td>
                            <td>{{ $product->total_amount }}</td>
                            <td>{{ $product->total_qty > 0 ? round($product->total_amount / $product->total_qty, 2) : 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection