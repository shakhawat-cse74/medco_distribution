@extends('backend.layout.main')
@push('css')
    @include('backend.layout.partials.datatable_css')
@endpush
 @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.delivery_proofs')}}</h3>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-b-0">
                        {{__('db.delivery_proofs')}} -
                        {{ $lims_delivery_data->reference_no }}
                    </h5>
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#proofModal">
                        <i class="ti ti-camera"></i> {{__('db.Add Proof')}}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="proofTable" class="table table-striped table-bordered nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{__('db.Type')}}</th>
                                    <th>{{__('db.Photo')}}</th>
                                    <th>{{__('db.Signature')}}</th>
                                    <th>{{__('db.OTP')}}</th>
                                    <th>{{__('db.Verified')}}</th>
                                    <th>{{__('db.Note')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($lims_proof_list as $proof)
                                <tr>
                                    <td>{{ ucfirst($proof->proof_type) }}</td>
                                    <td>
                                        @if($proof->file_path)
                                            <a href="{{ asset('images/delivery_proof/' . $proof->file_path) }}" target="_blank">
                                                <img src="{{ asset('images/delivery_proof/' . $proof->file_path) }}" style="width:60px;height:60px;object-fit:cover;" alt="proof">
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($proof->signature_data)
                                            <span class="badge badge-info">{{__('db.Captured')}}</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $proof->otp_code ?? 'N/A' }}</td>
                                    <td>
                                        @if($proof->is_verified)
                                            <span class="badge badge-success">{{__('db.Verified')}}</span>
                                        @else
                                            <span class="badge badge-warning">{{__('db.Pending')}}</span>
                                        @endif
                                    </td>
                                    <td>{{ $proof->note ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{__('db.No proofs found')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Proof Modal -->
<div id="proofModal" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('delivery-proofs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="delivery_id" value="{{ $lims_delivery_data->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('db.Add Proof')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-x"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{__('db.Type')}}</label>
                        <select class="form-control" name="proof_type" required>
                            <option value="photo">{{__('db.Photo')}}</option>
                            <option value="signature">{{__('db.Signature')}}</option>
                            <option value="otp">{{__('db.OTP')}}</option>
                            <option value="geofence">{{__('db.Geofence')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Photo')}}</label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Signature')}} (Base64)</label>
                        <input type="text" class="form-control" name="signature_data" placeholder="{{__('db.Signature data')}}">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.OTP')}}</label>
                        <input type="text" class="form-control" name="otp_code" placeholder="{{__('db.OTP Code')}}">
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Note')}}</label>
                        <textarea class="form-control" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('db.close')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('db.save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">
    $('#proofTable').DataTable({
        "order": [[0, 'asc']],
        'language': {
            'lengthMenu': '_MENU_ {{__("db.records per page")}}',
            "info": '<small>{{__("db.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search": '{{__("db.Search")}}',
            'paginate': {
                'previous': '<i class="ti ti-chevron-left"></i>',
                'next': '<i class="ti ti-chevron-right"></i>'
            }
        },
        dom: '<"row"lfB>rtip',
    });
</script>
@endpush
