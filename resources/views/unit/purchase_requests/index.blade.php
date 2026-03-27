@extends('unit.layout')

@section('title', 'Purchase Requests - ConsoliData')

@section('content')
<div class="page-shell">
    <div class="page-header">
        <div>
            <h1>Purchase Requests</h1>
            <p>Create, review, submit, and confirm your purchase requests.</p>
        </div>
        <a href="{{ route('unit.pr.create') }}" class="btn btn-primary">Create Request</a>
    </div>

    <div class="panel-card panel-card--table">
        <div class="panel-header">
            <div class="inline-actions">
                <h2>Request List</h2>
                <span class="muted-text">{{ $purchaseRequests->count() }} record(s)</span>
            </div>
        </div>

        <form method="GET" action="{{ route('unit.pr.index') }}" class="filter-bar">
            <div class="filter-field">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" placeholder="Purpose, status, fund source …" value="{{ request('search') }}">
            </div>
            <div class="filter-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['Draft', 'Submitted', 'Approved', 'Confirmed', 'Completed', 'On-Going', 'Disapproved', 'Canceled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
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
                <a href="{{ route('unit.pr.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>PPMP Ref</th>
                        <th>Purpose</th>
                        <th>Department / Unit</th>
                        <th>Fund Source</th>
                        <th>Items</th>
                        <th>Total Budget</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $purchaseRequest)
                        @php
                            $statusClass = match ($purchaseRequest->status) {
                                'Submitted' => 'status-badge status-submitted',
                                'Approved' => 'status-badge status-approved',
                                'Confirmed' => 'status-badge status-confirmed',
                                'Completed' => 'status-badge status-completed',
                                'Disapproved' => 'status-badge status-disapproved',
                                'Canceled' => 'status-badge status-disapproved',
                                'On-Going' => 'status-badge status-confirmed',
                                default => 'status-badge status-draft',
                            };
                        @endphp
                        <tr>
                            <td>#{{ $purchaseRequest->id }}</td>
                            <td>{{ $purchaseRequest->ppmp_id ? '#'.$purchaseRequest->ppmp_id : '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($purchaseRequest->purpose ?: '-', 60) }}</td>
                            <td>{{ $purchaseRequest->department_name ?? 'Unassigned' }}</td>
                            <td>{{ $purchaseRequest->fund_source_name ?? 'Unassigned' }}</td>
                            <td>{{ $purchaseRequest->items_count }}</td>
                            <td>{{ number_format((float) $purchaseRequest->total_estimated_budget, 2) }}</td>
                            <td class="stacked-meta">
                                <span class="{{ $statusClass }}">{{ $purchaseRequest->status }}</span>
                                @if($purchaseRequest->status === 'Approved')
                                    <br><small>Waiting for your confirmation before BAC can proceed</small>
                                @elseif($purchaseRequest->status === 'Confirmed')
                                    <br><small>Forwarded to BAC for notice handling</small>
                                @endif
                            </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($purchaseRequest->created_at)->format('M d, Y h:i A') }}</td>
                            <td>
                                <div class="inline-actions">
                                    <a href="{{ route('unit.pr.show', $purchaseRequest->id) }}" class="btn btn-primary">View</a>
                                    @if(in_array($purchaseRequest->status, ['Draft', 'Disapproved'], true))
                                        <a href="{{ route('unit.pr.edit', $purchaseRequest->id) }}" class="btn btn-edit">Edit</a>
                                    @endif
                                    @if($purchaseRequest->status === 'Approved')
                                        <form action="{{ route('unit.pr.confirm', $purchaseRequest->id) }}" method="POST" data-confirm="Confirm this approved request and forward it to BAC?">
                                            @csrf
                                            <button type="submit" class="btn-create">Confirm</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;">{{ request()->hasAny(['search','status','category_id','fund_source_id']) ? 'No requests matched the selected filters.' : 'No purchase requests found.' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
