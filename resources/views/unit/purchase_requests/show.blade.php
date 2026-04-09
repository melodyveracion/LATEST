@extends('unit.layout')
@section('title', 'Purchase Request #{{ $purchaseRequest->id }} — ConsoliData')

@section('content')

@php
    $status = $purchaseRequest->status;
    $sClass = match ($status) {
        'Submitted'   => 'status-badge status-submitted',
        'Approved'    => 'status-badge status-approved',
        'Confirmed'   => 'status-badge status-confirmed',
        'Completed'   => 'status-badge status-completed',
        'Disapproved' => 'status-badge status-disapproved',
        'Canceled'    => 'status-badge status-canceled',
        'On-Going'    => 'status-badge status-ongoing',
        default       => 'status-badge status-draft',
    };
    $noticePath  = $purchaseRequest->bac_notice_path ?? $purchaseRequest->award_notice_path ?? $purchaseRequest->failure_notice_path ?? null;
    $noticeType  = $purchaseRequest->bac_notice_type ?? ($purchaseRequest->award_notice_path ? 'awarded' : ($purchaseRequest->failure_notice_path ? 'failed' : null));
    $noticeLabel = match ($noticeType ?? '') {
        'awarded'  => 'Award Notice',
        'canceled' => 'Canceled Notice',
        'failed'   => 'Failure Notice',
        'on_going' => 'On-Going Notice',
        default    => 'BAC Notice',
    };
@endphp

{{-- Header --}}
<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
            <a href="{{ route('unit.pr.index') }}" class="hover:text-slate-600 transition-colors">Purchase Requests</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            <span class="text-slate-600">#{{ $purchaseRequest->id }}</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
            Purchase Request #{{ $purchaseRequest->id }}
            <span class="{{ $sClass }}">{{ $status }}</span>
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">Review request details, notices uploaded by BAC, and take action.</p>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <a href="{{ route('unit.pr.print', $purchaseRequest->id) }}" target="_blank" rel="noopener"
           class="btn-secondary btn-sm">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8" rx="1"/>
            </svg>
            Print
        </a>
        <a href="{{ route('unit.pr.index') }}" class="btn-secondary btn-sm">Back</a>
    </div>
</div>

{{-- Status banner for Approved --}}
@if($status === 'Approved')
    <div class="flex items-start gap-3 px-5 py-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm mb-5">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
        </svg>
        <p>This request was <strong>approved by admin</strong> and is now waiting for your confirmation before BAC can proceed.</p>
    </div>
@endif

{{-- Details card --}}
<div class="ui-card">
    <h2 class="ui-card-title">Request Details</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">PPMP Reference</p>
            <p class="text-sm font-medium text-slate-800">{{ $purchaseRequest->ppmp_id ? '#'.$purchaseRequest->ppmp_id : '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Department</p>
            <p class="text-sm font-medium text-slate-800">{{ $departmentName ?: '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Fund Source</p>
            <p class="text-sm font-medium text-slate-800">{{ $fundSourceName ?: '—' }}</p>
        </div>
        <div class="col-span-2 sm:col-span-3">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Purpose</p>
            <p class="text-sm text-slate-700">{{ $purchaseRequest->purpose ?: 'No purpose provided.' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Created</p>
            <p class="text-sm text-slate-600">{{ \Illuminate\Support\Carbon::parse($purchaseRequest->created_at)->format('M d, Y h:i A') }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Submitted</p>
            <p class="text-sm text-slate-600">{{ $purchaseRequest->submitted_at ? \Illuminate\Support\Carbon::parse($purchaseRequest->submitted_at)->format('M d, Y h:i A') : '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Confirmed</p>
            <p class="text-sm text-slate-600">{{ $purchaseRequest->confirmed_at ? \Illuminate\Support\Carbon::parse($purchaseRequest->confirmed_at)->format('M d, Y h:i A') : '—' }}</p>
        </div>
        @if($purchaseRequest->review_remarks)
            <div class="col-span-2 sm:col-span-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Review Remarks</p>
                <p class="text-sm text-slate-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    {{ $purchaseRequest->review_remarks }}
                </p>
            </div>
        @endif
    </div>
</div>

{{-- Items table --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-5">
    <div class="px-5 py-3.5 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-800">Requested Items</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Specifications</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Est. Budget</th>
                    <th>Mode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->category_name ?: '—' }}</td>
                        <td class="font-medium text-slate-900">{{ $item->item_name ?: $item->source_item_name ?: '—' }}</td>
                        <td class="text-slate-500 text-xs max-w-40 truncate" title="{{ $item->specifications }}">{{ $item->specifications ?: '—' }}</td>
                        <td>{{ $item->unit ?: '—' }}</td>
                        <td class="text-center font-semibold">{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                        <td class="font-medium text-emerald-700">₱{{ number_format($item->estimated_budget, 2) }}</td>
                        <td class="text-slate-400 text-xs">{{ $item->mode_of_procurement ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">No items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- BAC Notice & Actions --}}
<div class="ui-card">
    <h2 class="ui-card-title">Decision & BAC Notices</h2>

    <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-slate-50 border border-slate-200 text-sm mb-5">
        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 1 0 2.828 2.828l6.414-6.586a4 4 0 0 0-5.656-5.656l-6.415 6.585a6 6 0 1 0 8.486 8.486L20.5 13"/>
        </svg>
        <span class="text-slate-600 font-medium">{{ $noticeLabel }}:</span>
        @if($noticePath)
            <a href="{{ asset('storage/'.$noticePath) }}" target="_blank" class="text-blue-600 hover:underline">
                Open {{ $noticeLabel }}
            </a>
        @else
            <span class="text-slate-400">Not available yet</span>
        @endif
    </div>

    {{-- Action buttons --}}
    <div class="flex flex-wrap items-center gap-3">
        @if(in_array($status, ['Draft','Disapproved','Canceled'], true))
            <a href="{{ route('unit.pr.edit', $purchaseRequest->id) }}" class="btn-secondary">Edit Request</a>
            <form action="{{ route('unit.pr.submit', $purchaseRequest->id) }}" method="POST"
                  data-confirm="Submit this purchase request for admin validation?">
                @csrf
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit Request
                </button>
            </form>
        @endif

        @if($status === 'Approved')
            <form action="{{ route('unit.pr.confirm', $purchaseRequest->id) }}" method="POST"
                  data-confirm="Confirm this approved request and forward it to BAC?">
                @csrf
                <button type="submit" class="btn-success">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Confirm & Forward to BAC
                </button>
            </form>
        @endif

        @if($status === 'Confirmed')
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 text-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
                Confirmed and forwarded to BAC for processing.
            </div>
        @endif

        @if($status === 'Completed')
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
                This request has been completed and appears in procurement history.
            </div>
        @endif
    </div>

    {{-- Request correction (for Approved only) --}}
    @if($status === 'Approved')
        <div class="mt-6 pt-5 border-t border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Request Correction from Admin</h3>
            <form action="{{ route('unit.pr.requestCorrection', $purchaseRequest->id) }}" method="POST"
                  data-confirm="Return this approved request to admin for correction?">
                @csrf
                <label for="reason" class="field-label">Reason for correction</label>
                <textarea id="reason" name="reason" rows="3" required
                          class="field-input resize-none mb-3"
                          placeholder="Explain why this request needs to be returned to admin…"></textarea>
                <button type="submit" class="btn-danger btn-sm">
                    Return to Admin for Correction
                </button>
            </form>
        </div>
    @endif
</div>

@endsection
