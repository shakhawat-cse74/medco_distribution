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
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <h3 class="page-title">{{ __('db.Delivery List') }}</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i>
                                        Dashboard</a></li>
                                <li class="breadcrumb-item active">{{ __('db.delivery_management') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="page-block">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-b-0">{{ __('db.Assign Delivery') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('delivery-man-delivery.assign') }}" method="POST" class="form-inline">
                            @csrf
                            <div class="form-group mr-2">
                                <label class="mr-2">{{ __('db.Field Order ID') }}</label>
                                <input type="number" name="field_order_id" class="form-control" required
                                    placeholder="{{ __('db.Field Order ID') }}">
                            </div>
                            <div class="form-group mr-2">
                                <label class="mr-2">{{ __('db.Delivery Man') }}</label>
                                <select name="delivery_man_id" class="form-control" required>
                                    <option value="">{{ __('db.Select Delivery Man') }}</option>
                                    @foreach ($lims_delivery_man_list as $dm)
                                        <option value="{{ $dm->id }}">{{ $dm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="ti ti-check"></i>
                                {{ __('db.Assign') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-b-0">{{ __('db.Delivery List') }}</h5>
                        <a href="{{ route('delivery-man-delivery.mapView') }}" class="btn btn-info btn-sm"><i
                                class="ti ti-map-2"></i> {{ __('db.Map View') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="deliveryTable" class="table table-striped table-bordered nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('db.Reference') }}</th>
                                        <th>{{ __('db.Delivery Man') }}</th>
                                        <th>{{ __('db.customer') }}</th>
                                        <th>{{ __('db.Address') }}</th>
                                        <th>{{ __('db.status') }}</th>
                                        <th>{{ __('db.Priority') }}</th>
                                        <th class="not-exported">{{ __('db.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lims_delivery_list as $delivery)
                                        <tr>
                                            <td>{{ $delivery->reference_no }}</td>
                                            <td>{{ $delivery->deliveryMan ? $delivery->deliveryMan->name : 'N/A' }}</td>
                                            <td>{{ $delivery->customer ? $delivery->customer->name : 'N/A' }}</td>
                                            <td>{{ $delivery->address }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $delivery->status == 'completed' ? 'success' : ($delivery->status == 'assigned' ? 'primary' : 'warning') }}">
                                                    {{ ucfirst($delivery->status) }}
                                                </span>
                                            </td>
                                            <td>{{ ucfirst($delivery->priority) }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-link p-0 dropdown-toggle hide-arrow"
                                                        type="button" data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="ti ti-dots-horizontal"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-default">
                                                        <button type="button" class="dropdown-item open-EditCategoryDialog"
                                                            data-id="{{ $delivery->id }}" data-toggle="modal"
                                                            data-target="#editModal"><i class="ti ti-edit"></i>
                                                            {{ __('db.edit') }}</button>
                                                        <form
                                                            action="{{ route('delivery-man-delivery.delete', $delivery->id) }}"
                                                            method="POST" style="display:inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('{{ __('db.Are you sure you want to delete this delivery?') }}')"><i
                                                                    class="ti ti-trash"></i> {{ __('db.delete') }}</button>
                                                        </form>
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
        </div>
    </div>

    <!-- Edit Delivery Modal -->
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="editDeliveryLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <form id="editDeliveryForm" action="" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 id="editDeliveryLabel" class="modal-title">{{ __('db.Update Delivery Status') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="ti ti-x"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('db.status') }} *</label>
                            <select name="status" class="form-control" required>
                                <option value="assigned">{{ __('db.Assigned') }}</option>
                                <option value="started">{{ __('db.Started') }}</option>
                                <option value="completed">{{ __('db.Completed') }}</option>
                                <option value="failed">{{ __('db.Failed') }}</option>
                                <option value="due">{{ __('db.Due') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('db.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('db.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
    <script type="text/javascript">
        $("ul#delivery").siblings('a').attr('aria-expanded', 'true');
        $("ul#delivery").addClass("show");
        $("ul#delivery #delivery-list-menu").addClass("active");

        $('#deliveryTable').DataTable({
            "order": [
                [0, 'desc']
            ],
            'language': {
                'lengthMenu': '_MENU_ {{ __('db.records per page') }}',
                "info": '<small>{{ __('db.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ __('db.Search') }}',
                'paginate': {
                    'previous': '<i class="ti ti-chevron-left"></i>',
                    'next': '<i class="ti ti-chevron-right"></i>'
                }
            },
            dom: '<"row"lfB>rtip',
        });

        $(document).on("click", ".open-EditCategoryDialog", function() {
            var id = $(this).data('id').toString();
            $('#editDeliveryForm').attr('action', 'delivery-man-delivery/update-status/' + id);
            $('#editModal').modal('show');
        });
    </script>
@endpush
