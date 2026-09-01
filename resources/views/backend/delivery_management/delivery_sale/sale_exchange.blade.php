@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-12">
                    <h3 class="page-title">{{ __('db.Sale Exchange') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('delivery-sale.index') }}">{{ __('db.Delivery Sales') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('db.Sale Exchange') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle"></i>
                    Sale Exchange functionality is available through the main Sale module.
                    Please use the Sale Return feature to process exchanges.
                </div>
                <a href="{{ route('delivery-sale.saleReturn') }}" class="btn btn-primary">
                    <i class="ti ti-arrow-back"></i> {{ __('db.Go to Sale Return') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#delivery").siblings('a').attr('aria-expanded','true');
    $("ul#delivery").addClass("show");
    $("ul#delivery #delivery-sale-menu").addClass("active");
</script>
@endpush
