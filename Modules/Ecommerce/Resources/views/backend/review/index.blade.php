@extends('backend.layout.main')

@push('css')
    @include('backend.layout.partials.datatable_css')
    <style>
        .review-card-stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .review-card-stat .stat-title {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .review-card-stat .stat-val {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }
        .review-card-stat .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .table td {
            vertical-align: middle !important;
        }
        .status-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-approved {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .action-btns-group {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .action-btns-group .btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 4px;
        }
    </style>
@endpush

@section('content')
@if(session()->has('not_permitted'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session('message') }}</div>
@endif

<section class="p-3">
    <div class="container-fluid">
        
        <!-- Stats Row -->
        <div class="row">
            <div class="col-md-4">
                <div class="review-card-stat">
                    <div>
                        <div class="stat-title">Total Reviews</div>
                        <div class="stat-val">{{ $reviews->count() }}</div>
                    </div>
                    <div class="stat-icon" style="background:#eff6ff; color:#2563eb;">
                        <i class="ti ti-messages"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card-stat">
                    <div>
                        <div class="stat-title">Approved Reviews</div>
                        <div class="stat-val text-success">{{ $reviews->where('approved', 1)->count() }}</div>
                    </div>
                    <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
                        <i class="ti ti-circle-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card-stat">
                    <div>
                        <div class="stat-title">Pending Approval</div>
                        <div class="stat-val text-warning">{{ $reviews->where('approved', 0)->count() }}</div>
                    </div>
                    <div class="stat-icon" style="background:#fffbeb; color:#d97706;">
                        <i class="ti ti-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm" style="border-radius:8px;">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid #f1f5f9;">
                <h5 class="mb-0" style="font-size:16px; font-weight:700; color:#1e293b;">
                    <i class="ti ti-star-filled text-warning mr-1"></i> Product Reviews Management
                </h5>
                <span class="text-muted" style="font-size:13px;">Manage customer feedback & approve before publishing</span>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="review_list_table" class="table table-hover w-100">
                        <thead>
                            <tr style="background:#f8fafc; color:#475569; font-size:13px;">
                                <th style="width:40px;">#</th>
                                <th scope="col">{{ __('Customer Name') }}</th>
                                <th scope="col">{{ __('Product') }}</th>
                                <th scope="col" style="max-width:320px;">{{ __('Review') }}</th>
                                <th scope="col">{{ __('Rating') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col">{{ __('Date') }}</th>
                                <th scope="col" class="not-exported" style="min-width:160px;">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $key => $review)
                            <tr data-id="{{ $review->id }}" style="font-size:13px;">
                                <td class="text-muted font-weight-bold">{{ $key + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ @$review->customer_name ?? 'Verified Buyer' }}</div>
                                </td>
                                <td>
                                    @if(!empty($review->product))
                                    <a href="{{ url('product/' . $review->product->slug . '/' . $review->product->id) }}" target="_blank" class="font-weight-600 text-primary" title="View product on storefront">
                                        {{ $review->product->name }} <i class="ti ti-external-link" style="font-size:11px;"></i>
                                    </a>
                                    @else
                                    <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td style="max-width:320px;">
                                    <div style="line-height:1.45; color:#334155;">{{ @$review->review ?? '--' }}</div>
                                </td>
                                <td>
                                    @php $rating = (int)(@$review->rating ?? 0); @endphp
                                    <div style="white-space:nowrap;">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $rating)
                                                <i class="ti ti-star-filled" style="color:#f59e0b; font-size:15px;"></i>
                                            @else
                                                <i class="ti ti-star" style="color:#cbd5e1; font-size:15px;"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-1 text-muted" style="font-size:11px; font-weight:600;">({{ $rating }}/5)</span>
                                    </div>
                                </td>
                                <td>
                                    @if($review->approved == 1)
                                        <span class="status-badge badge-approved" id="status-badge-{{ $review->id }}">
                                            <i class="ti ti-circle-check"></i> Approved
                                        </span>
                                    @else
                                        <span class="status-badge badge-pending" id="status-badge-{{ $review->id }}">
                                            <i class="ti ti-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted" style="white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($review->created_at)->format('Y-M-d') }}
                                </td>
                                <td>
                                    <div class="action-btns-group">
                                        @if($review->approved == 1)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-warning status-toggle-btn"
                                                id="toggle-btn-{{ $review->id }}"
                                                data-id="{{ $review->id }}"
                                                data-status="1"
                                                title="Unpublish: Click to change to Pending"
                                            >
                                                <i class="ti ti-rotate"></i> Pending
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-success status-toggle-btn"
                                                id="toggle-btn-{{ $review->id }}"
                                                data-id="{{ $review->id }}"
                                                data-status="0"
                                                title="Publish: Click to Approve review"
                                            >
                                                <i class="ti ti-check"></i> Approve
                                            </button>
                                        @endif

                                        <form action="{{ route('reviews.destroy', $review->id) }}" method="GET" class="d-inline delete-review-form">
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-review" title="Delete Review">
                                                <i class="ti ti-trash"></i> Delete
                                            </button>
                                        </form>
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
</section>
@endsection

@push('scripts')
    @include('backend.layout.partials.datatable_js')
<script type="text/javascript">
    "use strict";

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize DataTable with Pagination and Controls
        var table = $('#review_list_table').DataTable({
            "order": [[6, "desc"]], // sort by Date descending
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "language": {
                "lengthMenu": "_MENU_ records per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ reviews",
                "infoEmpty": "No reviews found",
                "search": "Search reviews:",
                "paginate": {
                    "previous": '<i class="ti ti-chevron-left"></i> Prev',
                    "next": 'Next <i class="ti ti-chevron-right"></i>'
                }
            },
            "columnDefs": [
                {
                    "orderable": false,
                    "targets": [0, 4, 7] // disable ordering on index, rating stars, and action
                }
            ],
            dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6 text-sm-right"f>>rt<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6 text-sm-right"p>>'
        });

        // SweetAlert2 Status Toggle (Approve / Pending)
        $(document).on('click', '.status-toggle-btn', function(e) {
            e.preventDefault();
            var btn = $(this);
            var id = btn.data('id');
            var currentStatus = btn.data('status');
            var isApproving = currentStatus == 0;

            Swal.fire({
                title: isApproving ? 'Approve Review?' : 'Mark as Pending?',
                text: isApproving 
                    ? 'This review will be published and visible on the website.' 
                    : 'This review will be unpublished and hidden from the website.',
                icon: isApproving ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: isApproving ? '#16a34a' : '#d97706',
                cancelButtonColor: '#64748b',
                confirmButtonText: isApproving ? '<i class="ti ti-check"></i> Yes, Approve!' : '<i class="ti ti-clock"></i> Yes, Set Pending!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('reviews.toggleStatus') }}",
                        type: "POST",
                        data: {
                            id: id
                        },
                        success: function(response) {
                            btn.prop('disabled', false);
                            if (response.success) {
                                var badge = $('#status-badge-' + id);
                                
                                if (response.new_status == 1) {
                                    // Now Approved
                                    badge
                                        .removeClass('badge-pending')
                                        .addClass('badge-approved')
                                        .html('<i class="ti ti-circle-check"></i> Approved');

                                    btn
                                        .removeClass('btn-success')
                                        .addClass('btn-warning')
                                        .data('status', 1)
                                        .attr('title', 'Unpublish: Click to change to Pending')
                                        .html('<i class="ti ti-rotate"></i> Pending');

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Review Approved!',
                                        text: 'The review is now published live on the website.',
                                        timer: 2200,
                                        showConfirmButton: false
                                    });
                                } else {
                                    // Now Pending
                                    badge
                                        .removeClass('badge-approved')
                                        .addClass('badge-pending')
                                        .html('<i class="ti ti-clock"></i> Pending');

                                    btn
                                        .removeClass('btn-warning')
                                        .addClass('btn-success')
                                        .data('status', 0)
                                        .attr('title', 'Publish: Click to Approve review')
                                        .html('<i class="ti ti-check"></i> Approve');

                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Marked as Pending',
                                        text: 'The review is now unpublished and hidden from the website.',
                                        timer: 2200,
                                        showConfirmButton: false
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: response.message || 'Something went wrong!'
                                });
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false);
                            Swal.fire({
                                icon: 'error',
                                title: 'Request Failed',
                                text: xhr.responseJSON?.message || xhr.statusText
                            });
                        }
                    });
                }
            });
        });

        // SweetAlert2 Confirm Delete
        $(document).on('click', '.btn-delete-review', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Delete Review?',
                text: 'Are you sure you want to permanently delete this review? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="ti ti-trash"></i> Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
