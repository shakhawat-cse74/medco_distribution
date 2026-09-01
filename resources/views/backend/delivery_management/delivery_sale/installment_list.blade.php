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
                    <h3 class="page-title">{{ __('db.Installment List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('delivery-sale.index') }}">{{ __('db.Delivery Sales') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('db.Installment List') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="installment-table" class="table" style="width: 100%">
            <thead>
                <tr>
                    <th>{{ __('db.date') }}</th>
                    <th>{{ __('db.Reference') }}</th>
                    <th>{{ __('db.customer') }}</th>
                    <th>{{ __('db.Total Amount') }}</th>
                    <th>{{ __('db.Paid Amount') }}</th>
                    <th>{{ __('db.Due Amount') }}</th>
                    <th>{{ __('db.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lims_installment_list as $installment)
                <tr>
                    <td>{{ date(config('date_format'), strtotime($installment->created_at)) }}</td>
                    <td>{{ $installment->reference_no ?? 'N/A' }}</td>
                    <td>{{ $installment->customer->name ?? 'N/A' }}</td>
                    <td>{{ number_format($installment->total_amount, 2) }}</td>
                    <td>{{ number_format($installment->paid_amount, 2) }}</td>
                    <td>{{ number_format($installment->total_amount - $installment->paid_amount, 2) }}</td>
                    <td>
                        @if($installment->status == 'active')
                            <span class="badge badge-success">{{ __('db.Active') }}</span>
                        @elseif($installment->status == 'completed')
                            <span class="badge badge-info">{{ __('db.Completed') }}</span>
                        @else
                            <span class="badge badge-warning">{{ __('db.Pending') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No installments found</td>
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
        $('#installment-table').DataTable({
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
