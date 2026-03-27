@extends('unit.layout')

@section('title', 'Procurement History - ConsoliData')

@section('content')
<div class="page-shell">
    <div class="page-header">
        <div>
            <h1>Procurement History</h1>
            <p>Review completed purchase requests and their final procurement records.</p>
        </div>
        <a href="{{ route('unit.pr.index') }}" class="btn btn-secondary">Back to Requests</a>
    </div>

    <div class="panel-card panel-card--table">
        <div class="panel-header">
            <div class="inline-actions">
                <h2>Completed Records</h2>
                <span class="muted-text">{{ $purchaseRequests->count() }} record(s)</span>
            </div>
        </div>

        <form method="GET" action="{{ route('unit.procurement-history') }}" class="filter-bar">
            <div class="filter-field">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" placeholder="Purpose, PR ref, fund source …" value="{{ request('search') }}">
            </div>
            <div class="filter-field">
                <label for="fund_source_id">Fund Source</label>
                <select id="fund_source_id" name="fund_source_id">
                    <option value="">All fund sources</option>
                    @foreach($fundSources as $fs)
                        <option value="{{ $fs->id }}" {{ (string) request('fund_source_id') === (string) $fs->id ? 'selected' : '' }}>{{ $fs->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('unit.procurement-history') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>PR Ref</th>
                        <th>PPMP Ref</th>
                        <th>Purpose</th>
                        <th>Items</th>
                        <th>Total Budget</th>
                        <th>Status</th>
                        <th>Completed</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $purchaseRequest)
                        <tr>
                            <td>#{{ $purchaseRequest->id }}</td>
                            <td>{{ $purchaseRequest->ppmp_id ? '#'.$purchaseRequest->ppmp_id : '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($purchaseRequest->purpose ?: '-', 80) }}</td>
                            <td>{{ $purchaseRequest->items_count }}</td>
                            <td>{{ number_format((float) $purchaseRequest->total_estimated_budget, 2) }}</td>
                            <td>{{ $purchaseRequest->status }}</td>
                            <td>{{ $purchaseRequest->confirmed_at ? \Illuminate\Support\Carbon::parse($purchaseRequest->confirmed_at)->format('M d, Y h:i A') : \Illuminate\Support\Carbon::parse($purchaseRequest->updated_at)->format('M d, Y h:i A') }}</td>
                            <td>
                                <a href="{{ route('unit.pr.show', $purchaseRequest->id) }}" class="btn btn-primary">View Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;">{{ request()->hasAny(['search', 'fund_source_id']) ? 'No records matched the selected filters.' : 'No completed procurement history found.' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
