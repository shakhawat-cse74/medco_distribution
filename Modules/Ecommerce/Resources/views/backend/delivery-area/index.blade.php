@extends('backend.layout.main')

@section('content')

@if(session()->has('message'))
    <div class="alert alert-success alert-dismissible text-center">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('message') }}
    </div>
@endif

@php
    $symbol   = $currency->symbol ?? $currency->code;
    $rate     = $currency->exchange_rate ?? 1;
    $decimals = $general_setting->decimal ?? 2;
    $position = $general_setting->currency_position ?? 'prefix';

    // Helper: format a raw amount with currency
    function fmtAmount($amount, $rate, $decimals, $position, $symbol) {
        $converted = $amount * $rate;
        $formatted = number_format($converted, $decimals);
        return $position === 'prefix' ? $symbol . ' ' . $formatted : $formatted . ' ' . $symbol;
    }
@endphp

<section class="forms">
    <div class="container-fluid">

        {{-- ── Page Header ── --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="mb-0">{{ __('delivery_area.delivery_areas') }}</h4>
                <span class="text-muted">{{ __('delivery_area.manage_subtitle') }}</span>
            </div>
            <div>
                <button class="btn btn-warning btn-sm" id="btn-bulk-edit">
                    <i class="fa fa-table"></i> {{ __('delivery_area.bulk_edit') }}
                </button>
                <button class="btn btn-info btn-sm ml-1" data-toggle="modal" data-target="#createModal">
                    <i class="fa fa-plus"></i> {{ __('delivery_area.add_area') }}
                </button>
            </div>
        </div>

        {{-- ── Stats ── --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card text-center py-2">
                    <div class="card-body py-2">
                        <h4 class="mb-0 text-info" id="stat-total">{{ $areas->count() }}</h4>
                        <span class="text-muted">{{ __('delivery_area.total_areas') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center py-2">
                    <div class="card-body py-2">
                        <h4 class="mb-0 text-success" id="stat-active">{{ $areas->where('is_active', true)->count() }}</h4>
                        <span class="text-muted">{{ __('delivery_area.active') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center py-2">
                    <div class="card-body py-2">
                        <h4 class="mb-0 text-danger" id="stat-inactive">{{ $areas->where('is_active', false)->count() }}</h4>
                        <span class="text-muted">{{ __('delivery_area.inactive') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             LIST VIEW
        ══════════════════════════════════════════ --}}
        <div id="list-view">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="area-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('delivery_area.area_name') }}</th>
                                    <th>{{ __('delivery_area.charge') }} ({{ $symbol }})</th>
                                    <th>{{ __('delivery_area.est_days') }}</th>
                                    <th>{{ __('delivery_area.status') }}</th>
                                    <th>{{ __('delivery_area.note') }}</th>
                                    <th class="text-center">{{ __('delivery_area.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($areas as $i => $area)
                                <tr id="row-{{ $area->id }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $area->name }}</strong></td>
                                    <td>
                                        <span class="badge badge-light border">
                                            {{ fmtAmount($area->delivery_charge, $rate, $decimals, $position, $symbol) }}
                                        </span>
                                    </td>
                                    <td>{{ $area->estimated_days }} {{ __('delivery_area.days') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $area->is_active ? 'success' : 'secondary' }} status-badge"
                                              style="cursor:pointer"
                                              onclick="toggleStatus({{ $area->id }}, this)">
                                            {{ $area->is_active ? __('delivery_area.active') : __('delivery_area.inactive') }}
                                        </span>
                                    </td>
                                    <td><span class="text-muted">{{ Str::limit($area->note, 40) ?? '—' }}</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-warning" onclick="openEditModal({{ $area->id }})">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger ml-1" onclick="deleteArea({{ $area->id }}, this)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        {{ __('delivery_area.no_areas_found') }}
                                        <a href="#" data-toggle="modal" data-target="#createModal">{{ __('delivery_area.add_one_now') }}</a>.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             BULK EDIT VIEW
        ══════════════════════════════════════════ --}}
        <div id="bulk-view" style="display:none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📝 {{ __('delivery_area.bulk_edit_title') }}</h5>
                    <div>
                        <button class="btn btn-sm btn-secondary" id="btn-add-row">
                            <i class="fa fa-plus"></i> {{ __('delivery_area.add_row') }}
                        </button>
                        <button class="btn btn-sm btn-primary ml-1" id="btn-bulk-save">
                            <i class="fa fa-save"></i> {{ __('delivery_area.save_all') }}
                        </button>
                        <button class="btn btn-sm btn-light ml-1" id="btn-cancel-bulk">
                            <i class="fa fa-times"></i> {{ __('delivery_area.cancel') }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:32%">{{ __('delivery_area.area_name') }} <span class="text-danger">*</span></th>
                                    <th style="width:18%">{{ __('delivery_area.charge') }} ({{ $symbol }}) <span class="text-danger">*</span></th>
                                    <th style="width:13%">{{ __('delivery_area.est_days') }}</th>
                                    <th style="width:8%" class="text-center">{{ __('delivery_area.active') }}</th>
                                    <th style="width:23%">{{ __('delivery_area.note') }}</th>
                                    <th style="width:6%"></th>
                                </tr>
                            </thead>
                            <tbody id="bulk-tbody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button class="btn btn-sm btn-secondary mr-2" id="btn-add-row-bottom">
                        <i class="fa fa-plus"></i> {{ __('delivery_area.add_row') }}
                    </button>
                    <button class="btn btn-primary" id="btn-bulk-save-bottom">
                        <i class="fa fa-save"></i> {{ __('delivery_area.save_all_changes') }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- ══════════════════════════════════════════
     CREATE MODAL
══════════════════════════════════════════ --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-plus-circle"></i> {{ __('delivery_area.add_delivery_area') }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ __('delivery_area.area_name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="c_name" class="form-control" placeholder="{{ __('delivery_area.area_name_placeholder') }}" />
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('delivery_area.delivery_charge') }} ({{ $symbol }}) <span class="text-danger">*</span></label>
                            <input type="number" id="c_charge" class="form-control" value="0" min="0" step="any" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('delivery_area.estimated_days') }}</label>
                            <input type="number" id="c_days" class="form-control" value="1" min="1" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __('delivery_area.note') }}</label>
                    <textarea id="c_note" class="form-control" rows="2" placeholder="{{ __('delivery_area.note_placeholder') }}"></textarea>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="c_active" checked />
                    <label class="form-check-label" for="c_active">{{ __('delivery_area.active') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('delivery_area.cancel') }}</button>
                <button type="button" class="btn btn-info" id="btn-create-save">
                    <i class="fa fa-save"></i> {{ __('delivery_area.create') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     EDIT MODAL
══════════════════════════════════════════ --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fa fa-edit"></i> {{ __('delivery_area.edit_delivery_area') }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="e_id" />
                <div class="form-group">
                    <label>{{ __('delivery_area.area_name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="e_name" class="form-control" />
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('delivery_area.delivery_charge') }} ({{ $symbol }}) <span class="text-danger">*</span></label>
                            <input type="number" id="e_charge" class="form-control" min="0" step="any" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('delivery_area.estimated_days') }}</label>
                            <input type="number" id="e_days" class="form-control" min="1" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __('delivery_area.note') }}</label>
                    <textarea id="e_note" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="e_active" />
                    <label class="form-check-label" for="e_active">{{ __('delivery_area.active') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('delivery_area.cancel') }}</button>
                <button type="button" class="btn btn-warning" id="btn-edit-save">
                    <i class="fa fa-save"></i> {{ __('delivery_area.update') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ── Sidebar Active ─────────────────────────────────────────────────────
    $("ul#ecommerce").siblings('a').attr('aria-expanded', 'true');
    $("ul#ecommerce").addClass("show");
    $("ul#ecommerce #delivery-area-menu").addClass("active");

    // ── Currency config passed from PHP ────────────────────────────────────
    var currencySymbol   = @json($symbol);
    var currencyRate     = @json($rate);
    var currencyDecimals = @json($decimals);
    var currencyPosition = @json($position);

    var existingAreas = @json($areas);

    // ── Format amount with dynamic currency ───────────────────────────────
    function formatAmount(rawAmount) {
        var converted = (parseFloat(rawAmount) || 0) * currencyRate;
        var formatted = converted.toFixed(currencyDecimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return currencyPosition === 'prefix'
            ? currencySymbol + ' ' + formatted
            : formatted + ' ' + currencySymbol;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CREATE
    // ═══════════════════════════════════════════════════════════════════════
    $('#btn-create-save').on('click', function () {
        var name = $('#c_name').val().trim();
        if (!name) { alert('{{ __("delivery_area.area_name_required") }}'); return; }

        $.post('{{ route("delivery.area.store") }}', {
            name:            name,
            delivery_charge: $('#c_charge').val(),
            estimated_days:  $('#c_days').val(),
            is_active:       $('#c_active').is(':checked') ? 1 : 0,
            note:            $('#c_note').val().trim(),
        }, function (res) {
            if (res.success) {
                showToast(res.message, 'success');
                $('#createModal').modal('hide');
                setTimeout(function () { location.reload(); }, 800);
            }
        }).fail(function () { showToast('{{ __("delivery_area.something_went_wrong") }}', 'danger'); });
    });

    // ═══════════════════════════════════════════════════════════════════════
    // EDIT MODAL
    // ═══════════════════════════════════════════════════════════════════════
    function openEditModal(id) {
        $.get('{{ url("delivery-area/edit") }}/' + id, function (area) {
            $('#e_id').val(area.id);
            $('#e_name').val(area.name);
            $('#e_charge').val(area.delivery_charge);
            $('#e_days').val(area.estimated_days);
            $('#e_note').val(area.note);
            $('#e_active').prop('checked', area.is_active == 1);
            $('#editModal').modal('show');
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // UPDATE
    // ═══════════════════════════════════════════════════════════════════════
    $('#btn-edit-save').on('click', function () {
        var id   = $('#e_id').val();
        var name = $('#e_name').val().trim();
        if (!name) { alert('{{ __("delivery_area.area_name_required") }}'); return; }

        $.post('{{ url("delivery-area/update") }}/' + id, {
            name:            name,
            delivery_charge: $('#e_charge').val(),
            estimated_days:  $('#e_days').val(),
            is_active:       $('#e_active').is(':checked') ? 1 : 0,
            note:            $('#e_note').val().trim(),
        }, function (res) {
            if (res.success) {
                showToast(res.message, 'success');
                $('#editModal').modal('hide');
                setTimeout(function () { location.reload(); }, 800);
            }
        }).fail(function () { showToast('{{ __("delivery_area.something_went_wrong") }}', 'danger'); });
    });

    // ═══════════════════════════════════════════════════════════════════════
    // TOGGLE STATUS
    // ═══════════════════════════════════════════════════════════════════════
    function toggleStatus(id, el) {
        $.get('{{ url("delivery-area/toggle") }}/' + id, function (res) {
            if (res.success) {
                var $badge = $(el);
                if (res.is_active) {
                    $badge.removeClass('badge-secondary').addClass('badge-success').text('{{ __("delivery_area.active") }}');
                } else {
                    $badge.removeClass('badge-success').addClass('badge-secondary').text('{{ __("delivery_area.inactive") }}');
                }
                showToast(res.message, 'success');
                updateStats();
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // DELETE
    // ═══════════════════════════════════════════════════════════════════════
    function deleteArea(id) {
        if (!confirm('{{ __("delivery_area.delete_confirm") }}')) return;
        $.get('{{ url("delivery-area/delete") }}/' + id, function (res) {
            if (res.success) {
                $('#row-' + id).fadeOut(300, function () { $(this).remove(); updateStats(); });
                showToast(res.message, 'success');
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // BULK EDIT
    // ═══════════════════════════════════════════════════════════════════════
    $('#btn-bulk-edit').on('click', function () {
        buildBulkTable(existingAreas);
        $('#list-view').hide();
        $('#bulk-view').show();
    });

    $('#btn-cancel-bulk').on('click', function () {
        $('#bulk-view').hide();
        $('#list-view').show();
    });

    function buildBulkTable(areas) {
        var tbody = $('#bulk-tbody');
        tbody.empty();
        $.each(areas, function (i, area) {
            tbody.append(makeBulkRow(area));
        });
    }

    function makeBulkRow(area) {
        var rowId   = area.id || ('new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5));
        var checked = (area.is_active == 1 || area.is_active === true) ? 'checked' : '';

        return '<tr data-row-id="' + rowId + '">'
            + '<td><input type="text" class="form-control form-control-sm b-name" value="' + (area.name || '') + '" placeholder="{{ __("delivery_area.area_name") }} *" /></td>'
            + '<td><input type="number" class="form-control form-control-sm b-charge" value="' + (area.delivery_charge || 0) + '" min="0" step="any" /></td>'
            + '<td><input type="number" class="form-control form-control-sm b-days" value="' + (area.estimated_days || 1) + '" min="1" /></td>'
            + '<td class="text-center"><input type="checkbox" class="b-active" ' + checked + ' /></td>'
            + '<td><input type="text" class="form-control form-control-sm b-note" value="' + (area.note || '') + '" placeholder="{{ __("delivery_area.note") }}" /></td>'
            + '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-bulk-row"><i class="fa fa-times"></i></button></td>'
            + '</tr>';
    }

    function addEmptyRow() {
        $('#bulk-tbody').append(makeBulkRow({
            id: null, name: '', delivery_charge: 0, estimated_days: 1, is_active: 1, note: ''
        }));
    }

    $('#btn-add-row, #btn-add-row-bottom').on('click', function () { addEmptyRow(); });

    $(document).on('click', '.remove-bulk-row', function () {
        $(this).closest('tr').remove();
    });

    // ── Bulk Save ──────────────────────────────────────────────────────────
    function doBulkSave() {
        var rows   = [];
        var hasErr = false;

        $('#bulk-tbody tr').each(function () {
            var $tr  = $(this);
            var name = $tr.find('.b-name').val().trim();

            if (!name) {
                $tr.find('.b-name').addClass('is-invalid');
                hasErr = true;
                return;
            }
            $tr.find('.b-name').removeClass('is-invalid');

            var rowId = $tr.attr('data-row-id');
            var id    = (rowId && !String(rowId).startsWith('new_')) ? rowId : null;

            rows.push({
                id:              id,
                name:            name,
                delivery_charge: $tr.find('.b-charge').val(),
                estimated_days:  $tr.find('.b-days').val(),
                is_active:       $tr.find('.b-active').is(':checked') ? 1 : 0,
                note:            $tr.find('.b-note').val().trim(),
            });
        });

        if (hasErr) { showToast('{{ __("delivery_area.fill_required_names") }}', 'warning'); return; }
        if (!rows.length) { showToast('{{ __("delivery_area.no_rows_to_save") }}', 'warning'); return; }

        $('#btn-bulk-save, #btn-bulk-save-bottom').prop('disabled', true).text('{{ __("delivery_area.saving") }}...');

        $.ajax({
            url:  '{{ route("delivery.area.bulk.update") }}',
            type: 'POST',
            data: { rows: rows },
            success: function (res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    setTimeout(function () { location.reload(); }, 900);
                }
            },
            error: function () {
                showToast('{{ __("delivery_area.something_went_wrong") }}', 'danger');
                $('#btn-bulk-save, #btn-bulk-save-bottom').prop('disabled', false).text('{{ __("delivery_area.save_all") }}');
            }
        });
    }

    $('#btn-bulk-save, #btn-bulk-save-bottom').on('click', doBulkSave);

    // ═══════════════════════════════════════════════════════════════════════
    // STATS UPDATE
    // ═══════════════════════════════════════════════════════════════════════
    function updateStats() {
        $('#stat-total').text($('#area-table tbody tr[id]').length);
        $('#stat-active').text($('#area-table tbody .badge-success').length);
        $('#stat-inactive').text($('#area-table tbody .badge-secondary').length);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TOAST
    // ═══════════════════════════════════════════════════════════════════════
    function showToast(msg, type) {
        var bg = { success: '#28a745', danger: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
        var $t = $('<div>').css({
            position: 'fixed', bottom: '20px', right: '20px', zIndex: 9999,
            background: bg[type] || '#333', color: '#fff', padding: '10px 20px',
            borderRadius: '6px', boxShadow: '0 3px 10px rgba(0,0,0,.2)', fontSize: '14px'
        }).text(msg).appendTo('body');
        setTimeout(function () { $t.fadeOut(400, function () { $t.remove(); }); }, 3000);
    }
</script>
@endpush
