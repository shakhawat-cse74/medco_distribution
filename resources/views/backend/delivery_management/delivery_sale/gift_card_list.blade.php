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
                    <h3 class="page-title">{{ __('db.Gift Card List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('delivery-sale.index') }}">{{ __('db.Delivery Sales') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('db.Gift Card List') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="gift-card-table" class="table" style="width: 100%">
            <thead>
                <tr>
                    <th>{{ __('db.Card No') }}</th>
                    <th>{{ __('db.Customer') }}</th>
                    <th>{{ __('db.Amount') }}</th>
                    <th>{{ __('db.Expense') }}</th>
                    <th>{{ __('db.Balance') }}</th>
                    <th>{{ __('db.Expired Date') }}</th>
                    <th>{{ __('db.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lims_gift_card_list as $gift_card)
                <tr>
                    <td>{{ $gift_card->card_no }}</td>
                    <td>{{ $gift_card->customer->name ?? 'N/A' }}</td>
                    <td>{{ number_format($gift_card->amount, 2) }}</td>
                    <td>{{ number_format($gift_card->expense, 2) }}</td>
                    <td>{{ number_format($gift_card->amount - $gift_card->expense, 2) }}</td>
                    <td>{{ $gift_card->expired_date }}</td>
                    <td>
                        @if($gift_card->is_active)
                            <span class="badge badge-success">{{ __('db.Active') }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('db.Inactive') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
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
        $('#gift-card-table').DataTable({
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
