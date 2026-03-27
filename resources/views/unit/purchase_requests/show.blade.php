@extends('unit.layout')

@section('title', 'Purchase Request Details - ConsoliData')

@section('content')
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
<div class="page-header">
    <div>
        <h1>Purchase Request #{{ $purchaseRequest->id }}</h1>
        <p>Review your request status and any notices uploaded by BAC.</p>
    </div>
    <div class="inline-actions">
        <a href="{{ route('unit.pr.print', $purchaseRequest->id) }}" target="_blank" rel="noopener" class="btn btn-edit">Print PR</a>
        <a href="{{ route('unit.pr.index') }}" class="btn btn-primary">Back to Requests</a>
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
            <span class="info-label">Department / Unit</span>
            <div class="info-value">{{ $departmentName ?: 'Unassigned' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Fund Source</span>
            <div class="info-value">{{ $fundSourceName ?: 'Unassigned' }}</div>
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
            <span class="info-label">Review Remarks</span>
            <div class="info-value">{{ $purchaseRequest->review_remarks ?: 'No remarks' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Submitted At</span>
            <div class="info-value">{{ $purchaseRequest->submitted_at ? \Illuminate\Support\Carbon::parse($purchaseRequest->submitted_at)->format('M d, Y h:i A') : 'Not yet submitted' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Confirmed At</span>
            <div class="info-value">{{ $purchaseRequest->confirmed_at ? \Illuminate\Support\Carbon::parse($purchaseRequest->confirmed_at)->format('M d, Y h:i A') : 'Not yet confirmed' }}</div>
        </div>
    </div>
</div>

@if($purchaseRequest->status === 'Approved')
    <div class="alert-info" style="margin-bottom:18px;">
        This purchase request was approved by admin and is now waiting for your confirmation. Confirm it below to forward it to BAC and unlock BAC notice uploads.
    </div>
@endif

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
                    <td>{{ $item->item_name ?: $item->source_item_name ?: '-' }}</td>
                    <td>{{ $item->specifications ?: '-' }}</td>
                    <td>{{ $item->unit ?: '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->estimated_budget, 2) }}</td>
                    <td>{{ $item->mode_of_procurement ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">No request items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $noticePath = $purchaseRequest->bac_notice_path ?? $purchaseRequest->award_notice_path ?? $purchaseRequest->failure_notice_path ?? null;
    $noticeType = $purchaseRequest->bac_notice_type ?? ($purchaseRequest->award_notice_path ? 'awarded' : ($purchaseRequest->failure_notice_path ? 'failed' : null));
    $noticeLabel = match ($noticeType ?? '') {
        'awarded' => 'Award Notice',
        'canceled' => 'Canceled Notice',
        'failed' => 'Failure Notice',
        'on_going' => 'On-Going Notice',
        default => 'BAC Notice',
    };
@endphp
<div class="page-card">
    <h2>Decision & BAC Notices</h2>
    <table>
        <tr>
            <th width="220">{{ $noticeLabel }}</th>
            <td>
                @if($noticePath)
                    <a href="{{ asset('storage/' . $noticePath) }}" target="_blank">Open {{ $noticeLabel }}</a>
                @else
                    Not available
                @endif
            </td>
        </tr>
    </table>

    <div class="action-row">
        @if(in_array($purchaseRequest->status, ['Draft', 'Disapproved', 'Canceled'], true))
            <a href="{{ route('unit.pr.edit', $purchaseRequest->id) }}" class="btn btn-primary">Edit Request</a>
        @endif

        @if(in_array($purchaseRequest->status, ['Draft', 'Disapproved', 'Canceled'], true))
            <form action="{{ route('unit.pr.submit', $purchaseRequest->id) }}" method="POST" data-confirm="Submit this purchase request for validation?">
                @csrf
                <button type="submit" class="btn-create">Submit Request</button>
            </form>
        @endif

        @if($purchaseRequest->status === 'Approved')
            <form action="{{ route('unit.pr.confirm', $purchaseRequest->id) }}" method="POST" data-confirm="Confirm this approved request and forward it to BAC?">
                @csrf
                <button type="submit" class="btn-create">Confirm and Forward to BAC</button>
            </form>
        @endif
    </div>

    @if($purchaseRequest->status === 'Approved')
        <form action="{{ route('unit.pr.requestCorrection', $purchaseRequest->id) }}" method="POST" style="margin-top:18px;" data-confirm="Return this approved request to admin for correction?">
            @csrf
            <label for="reason">Need correction from admin?</label>
            <textarea id="reason" name="reason" rows="3" placeholder="State why this approved request should be returned to admin." required></textarea>
            <div class="action-row">
                <button type="submit" class="btn btn-danger">Return to Admin for Correction</button>
            </div>
        </form>
    @endif

    @if($purchaseRequest->status === 'Confirmed')
        <div class="alert-info" style="margin-top:18px;">
            This purchase request was confirmed by the unit and forwarded to BAC for processing.
        </div>
    @endif

    @if($purchaseRequest->status === 'Completed')
        <div class="alert-success" style="margin-top:18px;">
            This purchase request already appears in your procurement history as completed.
        </div>
    @endif
</div>
@endsection
