@extends('backend.layout.main')

 @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.Map View')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.delivery_management')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Live Map')}}</h5>
                </div>
                <div class="card-body">
                    <div id="map" style="width:100%;height:500px;background:#e9ecef;border:1px dashed #adb5bd;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#6c757d;">
                        <div class="text-center">
                            <i class="ti ti-map-pin" style="font-size:48px;"></i>
                            <p class="mt-2">{{__('db.Map placeholder - configure a map provider to display live delivery tracking')}}</p>
                        </div>
                    </div>
                    {{-- To enable a real map, include a provider SDK (e.g. Leaflet/Google Maps) and
                        plot markers from the deliveries JSON returned by
                        route('delivery-man-delivery.pendingDeliveries') and liveTracking. --}}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Pending Deliveries')}}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group" id="pendingDeliveryList">
                        <li class="list-group-item text-muted">{{__('db.Loading')}}</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">{{__('db.Active Deliveries')}}</h5>
                </div>
                <div class="card-body">
                    @if($lims_delivery_list && $lims_delivery_list->isNotEmpty())
                        <ul class="list-group">
                            @foreach($lims_delivery_list as $delivery)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong>{{ $delivery->reference_no }}</strong><br>
                                        <small>{{ $delivery->deliveryMan ? $delivery->deliveryMan->name : 'N/A' }} &rarr; {{ $delivery->customer ? $delivery->customer->name : 'N/A' }}</small>
                                    </span>
                                    <span class="badge badge-primary">{{ ucfirst($delivery->status) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{__('db.No active deliveries')}}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        $.get('{{ route("delivery-man-delivery.pendingDeliveries") }}', function(data) {
            var list = $('#pendingDeliveryList');
            list.empty();
            if (data && data.length > 0) {
                $.each(data, function(i, d) {
                    var customer = d.customer ? d.customer.name : 'N/A';
                    var dm = d.deliveryMan ? d.deliveryMan.name : 'N/A';
                    list.append(
                        '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                            '<span><strong>' + d.reference_no + '</strong><br><small>' + dm + ' &rarr; ' + customer + '</small></span>' +
                            '<span class="badge badge-warning">' + (d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) : 'Assigned') + '</span>' +
                        '</li>'
                    );
                });
            } else {
                list.append('<li class="list-group-item text-muted">{{__("db.No pending deliveries")}}</li>');
            }
        }).fail(function() {
            $('#pendingDeliveryList').html('<li class="list-group-item text-danger">{{__("db.Failed to load deliveries")}}</li>');
        });
    });
</script>
@endpush
