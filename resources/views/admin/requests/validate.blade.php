@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div>
        <h1 class="page-title">Validate Purchase Requests</h1>
        <p class="page-subtitle">
            Review submitted purchase requests, check the owner, assignment, requested purpose, and return clear correction notes when a request must be revised.
        </p>
    </div>

    <div class="panel-card panel-card--table">
        <div class="panel-header">
            <div class="inline-actions">
                <h2>Request Review Queue</h2>
                <span class="muted-text">{{ $requests->count() }} record(s)</span>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.validate-request.index') }}" class="filter-bar">
            <div class="filter-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['Draft', 'Submitted', 'Approved', 'Disapproved', 'Confirmed', 'Completed'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="department_unit_id">Unit</label>
                <select id="department_unit_id" name="department_unit_id">
                    <option value="">All units</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) request('department_unit_id') === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('admin.validate-request.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Requester</th>
                        <th>Department / Unit</th>
                        <th>Fund Source</th>
                        <th>Purpose</th>
                        <th>Items</th>
                        <th>Total Budget</th>
                        <th>Status</th>
                        <th>Review Remarks</th>
                        <th width="320">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $requestItem)
                        @php
                            $statusClass = match ($requestItem->status) {
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
                            <td>#{{ $requestItem->id }}</td>
                            <td class="stacked-meta">
                                <strong>{{ $requestItem->user_name ?? 'Unknown User' }}</strong><br>
                                <small>{{ $requestItem->user_email ?? 'No email' }}</small>
                            </td>
                            <td>{{ $requestItem->department_name ?? 'Unassigned' }}</td>
                            <td>{{ $requestItem->fund_source_name ?? 'Unassigned' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($requestItem->purpose ?: '-', 70) }}</td>
                            <td>{{ $requestItem->items_count }}</td>
                            <td>{{ number_format((float) $requestItem->total_estimated_budget, 2) }}</td>
                            <td>
                                <span class="{{ $statusClass }}">{{ $requestItem->status }}</span>
                            </td>
                            <td>{{ $requestItem->review_remarks ?: '-' }}</td>
                            <td>
                                <div class="action-stack">
                                    @if($requestItem->status === 'Submitted')
                                        <form action="{{ route('admin.validate-request.approve', $requestItem->id) }}" method="POST" data-confirm="Approve this purchase request?">
                                            @csrf
                                            <button type="submit" class="btn-primary">Approve</button>
                                        </form>

                                        <form action="{{ route('admin.validate-request.disapprove', $requestItem->id) }}" method="POST" data-confirm="Disapprove this purchase request?">
                                            @csrf
                                            <textarea name="reason" rows="2" placeholder="Explain the mistakes that must be corrected" required></textarea>
                                            <button type="submit" class="btn-danger">Disapprove</button>
                                        </form>
                                    @elseif($requestItem->status === 'Confirmed')
                                        <span class="muted-text">Already confirmed by unit and forwarded to BAC.</span>
                                    @elseif($requestItem->status === 'Approved')
                                        <span class="muted-text">Waiting for unit confirmation.</span>
                                    @else
                                        <span class="muted-text">No review action available.</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;">No purchase requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
