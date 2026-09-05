@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{ __('db.Add Delivery Sale Return') }}</h3>
            </div>
        </div>
    </div>
</section>
@endsection
