@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Commission Slabs</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Commission Slabs</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        @foreach($lims_commission_slabs as $slab)
            <div class="col-md-4 mb-4">
                <div class="card commission-slab-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">{{ $slab->key }}</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="text-primary">{{ $slab->value }}</h4>
                        <p class="text-muted">{{ $slab->description ?? 'No description' }}</p>
                        <div class="d-flex justify-content-between mt-3">
                            <small class="text-muted">Type: {{ $slab->type }}</small>
                            <small class="text-muted">Created: {{ $slab->created_at->format('Y-m-d') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection