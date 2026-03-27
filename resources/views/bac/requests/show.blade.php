@extends('bac.layout')

@section('title', 'BAC Request Details - ConsoliData')

@section('content')
@php
    $statusClass = match ($purchaseRequest->status) {
        'Approved' => 'status-badge status-approved',
        'Confirmed' => 'status-badge status-confirmed',
        'Completed' => 'status-badge status-completed',
        'Disapproved' => 'status-badge status-disapproved',
        'Canceled' => 'status-badge status-disapproved',
        'On-Going' => 'status-badge status-confirmed',
        default => 'status-badge status-submitted',
    };
@endphp
<div class="page-header">
    <div>
        <h1>Purchase Request #{{ $purchaseRequest->id }}</h1>
        <p>Review the request header and upload the corresponding BAC notice (Awarded, Canceled, Failed, or On-going).</p>
    </div>
    <div class="inline-actions">
        <a href="{{ route('bac.pr.print', $purchaseRequest->id) }}" target="_blank" rel="noopener" class="btn btn-edit">Print PR</a>
        <a href="{{ route('bac.pr.index') }}" class="btn btn-primary">Back to Requests</a>
    </div>
</div>

<div class="page-card">
    <h2>Request Details</h2>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">PPMP Reference</span>
            <div class="info-value">{{ $purchaseRequest->ppmp_id ? '#'.$purchaseRequest->ppmp_id : '-' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Requester</span>
            <div class="info-value">{{ $purchaseRequest->user_name ?? 'Unknown User' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Email</span>
            <div class="info-value">{{ $purchaseRequest->user_email ?? 'No email' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Department / Unit</span>
            <div class="info-value">{{ $purchaseRequest->department_name ?? 'Unassigned' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Fund Source</span>
            <div class="info-value">{{ $purchaseRequest->fund_source_name ?? 'Unassigned' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Purpose</span>
            <div class="info-value">{{ $purchaseRequest->purpose ?: 'No purpose provided.' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Status</span>
            <div class="info-value"><span class="{{ $statusClass }}">{{ $purchaseRequest->status }}</span></div>
        </div>
        <div class="info-item">
            <span class="info-label">Created</span>
            <div class="info-value">{{ \Illuminate\Support\Carbon::parse($purchaseRequest->created_at)->format('M d, Y h:i A') }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Submitted At</span>
            <div class="info-value">{{ $purchaseRequest->submitted_at ? \Illuminate\Support\Carbon::parse($purchaseRequest->submitted_at)->format('M d, Y h:i A') : 'Not yet submitted' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Confirmed At</span>
            <div class="info-value">{{ $purchaseRequest->confirmed_at ? \Illuminate\Support\Carbon::parse($purchaseRequest->confirmed_at)->format('M d, Y h:i A') : 'Waiting for unit confirmation' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Review Remarks</span>
            <div class="info-value">{{ $purchaseRequest->review_remarks ?: 'No remarks' }}</div>
        </div>
    </div>
</div>

<div class="page-card">
    <h2>Requested Items</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Item</th>
                <th>Specifications</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Estimated Budget</th>
                <th>Mode of Procurement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->category_name ?: '-' }}</td>
                    <td>{{ $item->item_name ?: '-' }}</td>
                    <td>{{ $item->specifications ?: '-' }}</td>
                    <td>{{ $item->unit ?: '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->estimated_budget, 2) }}</td>
                    <td>{{ $item->mode_of_procurement ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">No purchase request items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('partials.bac-notice-section', ['purchaseRequest' => $purchaseRequest])
@endsection
