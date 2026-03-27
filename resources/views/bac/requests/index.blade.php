@extends('bac.layout')

@section('title', 'BAC Purchase Requests - ConsoliData')

@section('content')
<div class="page-shell">
    <div class="page-header">
        <div>
            <h1>Purchase Requests</h1>
            <p>Review approved and confirmed requests. BAC notice upload becomes available after the unit confirms the request.</p>
        </div>
    </div>

    <div class="panel-card">
        <div class="inline-actions" style="justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h2 style="margin:0;">BAC Review List</h2>
            <span class="muted-text">{{ $purchaseRequests->count() }} record(s)</span>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Requester</th>
                        <th>Department / Unit</th>
                        <th>Fund Source</th>
                        <th>Items</th>
                        <th>Total Budget</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $purchaseRequest)
                        @php
                            $statusClass = match ($purchaseRequest->status) {
                                'Approved' => 'status-badge status-approved',
                                'Confirmed' => 'status-badge status-confirmed',
                                'Completed' => 'status-badge status-completed',
                                'Awarded' => 'status-badge status-awarded',
                                'Disapproved' => 'status-badge status-disapproved',
                                'Canceled' => 'status-badge status-disapproved',
                                'On-Going' => 'status-badge status-confirmed',
                                default => 'status-badge status-submitted',
                            };
                        @endphp
                        <tr>
                            <td>#{{ $purchaseRequest->id }}</td>
                            <td class="stacked-meta">
                                <strong>{{ $purchaseRequest->user_name ?? 'Unknown User' }}</strong><br>
                                <small>{{ $purchaseRequest->user_email ?? 'No email' }}</small>
                            </td>
                            <td>{{ $purchaseRequest->department_name ?? 'Unassigned' }}</td>
                            <td>{{ $purchaseRequest->fund_source_name ?? 'Unassigned' }}</td>
                            <td>{{ $purchaseRequest->items_count }}</td>
                            <td>{{ number_format((float) $purchaseRequest->total_estimated_budget, 2) }}</td>
                            <td class="stacked-meta">
                                <span class="{{ $statusClass }}">{{ $purchaseRequest->status }}</span>
                                @if($purchaseRequest->status === 'Approved')
                                    <br><small>Waiting for unit confirmation</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('bac.pr.show', $purchaseRequest->id) }}" class="btn btn-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;">No purchase requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
