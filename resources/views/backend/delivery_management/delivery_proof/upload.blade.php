@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Delivery Proof</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Delivery Proof</li>
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
                    <h5 class="m-b-0">Capture Delivery Proof</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('delivery-proofs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="delivery_id" value="{{ $delivery_id }}">

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Proof Type</label>
                                <select class="form-control" name="proof_type">
                                    <option value="photo">Photo Capture</option>
                                    <option value="signature">Customer Signature</option>
                                    <option value="otp">OTP Verification</option>
                                    <option value="geofence">Geofencing</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Delivery ID</label>
                                <input type="text" class="form-control" readonly value="{{ $delivery_id }}">
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Photo Capture</label>
                                <input type="file" class="form-control" name="photo" accept="image/*">
                                <small class="text-muted">Maximum 5MB, JPG/PNG format</small>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Signature (Base64)</label>
                                <input type="text" class="form-control" name="signature_data" placeholder="Enter base64 signature data">
                                <small class="text-muted">Optional - customer digital signature</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>OTP Code</label>
                                <input type="text" class="form-control" name="otp_code" placeholder="Enter OTP for verification">
                                <small class="text-muted">Optional - OTP verification code</small>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Note</label>
                                <textarea class="form-control" rows="3" name="note" placeholder="Additional notes"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Proof</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection