@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Commission Settings</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Commission Settings</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Basic Commission Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('delivery-man-commissions.settings.update') }}">
                        @csrf
                        <div class="form-group">
                            <label>Commission Rate (%)</label>
                            <input type="number" name="commission_rate" class="form-control" value="{{ $lims_commission_settings->where('type', 'commission')->value('value') ?? 5 }}" step="0.01">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Settings</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Commission Slabs</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('delivery-man-commissions.slabs.store') }}">
                        @csrf
                        <div id="slabs-container">
                            <div class="slab-row input-group mb-2">
                                <input type="text" name="keys[]" class="form-control" placeholder="Key (e.g., commission_slab_1_50)">
                                <input type="text" name="values[]" class="form-control" placeholder="Value (e.g., 5%)">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-slab">-</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary mb-3" id="add-slab">+ Add Slab</button>
                        <button type="submit" class="btn btn-primary">Save Slabs</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection