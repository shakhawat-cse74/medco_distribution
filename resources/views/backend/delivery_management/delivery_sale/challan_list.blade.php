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
                    <h3 class="page-title">{{ __('db.Challan List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('delivery-sale.index') }}">{{ __('db.Delivery Sales') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('db.Challan List') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="challan-table" class="table" style="width: 100%">
            <thead>
                <tr>
                    <th>{{ __('db.Reference') }}</th>
                    <th>{{ __('db.customer') }}</th>
                    <th>{{ __('db.Warehouse') }}</th>
                    <th>{{ __('db.Delivery Man') }}</th>
                    <th>{{ __('db.grand total') }}</th>
                    <th>{{ __('db.status') }}</th>
                    <th>{{ __('db.date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lims_challan_list as $challan)
                <tr>
                    <td>{{ $challan->reference_no }}</td>
                    <td>{{ $challan->customer->name ?? 'N/A' }}</td>
                    <td>{{ $challan->warehouse->name ?? 'N/A' }}</td>
                    <td>{{ $challan->deliveryMan->name ?? 'N/A' }}</td>
                    <td>{{ number_format($challan->grand_total, 2) }}</td>
                    <td>
                        @if($challan->status == 'final')
                            <span class="badge badge-success">{{ __('db.Finalized') }}</span>
                        @else
                            <span class="badge badge-warning">{{ __('db.Draft') }}</span>
                        @endif
                    </td>
                    <td>{{ date(config('date_format'), strtotime($challan->created_at)) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No challans found</td>
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
        $('#challan-table').DataTable({
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
