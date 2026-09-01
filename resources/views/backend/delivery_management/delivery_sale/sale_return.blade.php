@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-12">
                    <h3 class="page-title">{{ __('db.Sale Return List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('delivery-sale.index') }}">{{ __('db.Delivery Sales') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('db.Sale Return') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="return-table" class="table" style="width: 100%">
            <thead>
                <tr>
                    <th>{{ __('db.date') }}</th>
                    <th>{{ __('db.Reference') }}</th>
                    <th>{{ __('db.customer') }}</th>
                    <th>{{ __('db.Delivery Man') }}</th>
                    <th>{{ __('db.grand total') }}</th>
                    <th>{{ __('db.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lims_return_list as $return)
                <tr>
                    <td>{{ date(config('date_format'), strtotime($return->created_at)) }}</td>
                    <td>{{ $return->reference_no }}</td>
                    <td>{{ $return->customer->name ?? 'N/A' }}</td>
                    <td>{{ $return->deliveryMan->name ?? 'N/A' }}</td>
                    <td>{{ number_format($return->grand_total, 2) }}</td>
                    <td>
                        @if($return->sale_status == 1)
                            <span class="badge badge-success">{{ __('db.Completed') }}</span>
                        @elseif($return->sale_status == 2)
                            <span class="badge badge-warning">{{ __('db.Pending') }}</span>
                        @else
                            <span class="badge badge-secondary">{{ __('db.N/A') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No returns found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">
    $("ul#delivery").siblings('a').attr('aria-expanded','true');
    $("ul#delivery").addClass("show");
    $("ul#delivery #delivery-sale-menu").addClass("active");

    $(document).ready(function() {
        $('#return-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'pdf', text: '<i class="ti ti-file-type-pdf"></i>' },
                { extend: 'excel', text: '<i class="ti ti-file-type-xls"></i>' },
                { extend: 'csv', text: '<i class="ti ti-file-type-csv"></i>' },
                { extend: 'print', text: '<i class="ti ti-printer"></i>' }
            ]
        });
    });
</script>
@endpush
