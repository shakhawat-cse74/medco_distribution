@extends('backend.layout.main')
@section('content')
<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{__('db.Accounting Reconciliation Dashboard')}}</h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-primary text-center">
                            <div class="card-body">
                                <h5>Total Queued</h5>
                                <h3>{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning text-center">
                            <div class="card-body">
                                <h5>Pending</h5>
                                <h3>{{ $stats['pending'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-danger text-center">
                            <div class="card-body">
                                <h5>Failed</h5>
                                <h3>{{ $stats['failed'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="accounting-reconciliation-table" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>ID</th>
                                <th>Status</th>
                                <th>Attempts</th>
                                <th>Last Error</th>
                                <th>Last Attempt</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queue as $q)
                            <tr>
                                <td>{{ class_basename($q->source_type) }}</td>
                                <td>{{ $q->source_id }}</td>
                                <td>
                                    @if($q->status == 'posted')
                                        <div class="badge badge-success">{{ $q->status }}</div>
                                    @elseif($q->status == 'failed')
                                        <div class="badge badge-danger">{{ $q->status }}</div>
                                    @else
                                        <div class="badge badge-warning">{{ $q->status }}</div>
                                    @endif
                                </td>
                                <td>{{ $q->attempts }}</td>
                                <td>{{ Str::limit($q->last_error, 50) }}</td>
                                <td>{{ $q->last_attempt_at }}</td>
                                <td>
                                    @if($q->status == 'failed' || $q->status == 'pending')
                                    <form action="{{ route('accounting.reconciliation.retry', $q->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Retry</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $queue->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
