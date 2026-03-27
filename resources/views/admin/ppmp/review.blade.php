@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div class="inline-actions" style="justify-content:space-between; align-items:flex-start;">
        <div>
            <h1 class="page-title">Review PPMP {{ $ppmp->ppmp_no ?: ('Draft Ref #' . $ppmp->id) }}</h1>
            <p class="page-subtitle">
                Choose to approve this PPMP or return it to the unit for corrections.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.ppmp.show', $ppmp->id) }}" class="btn-edit">View Details</a>
            <a href="{{ route('admin.ppmp.validate') }}" class="btn-edit">Back to Queue</a>
        </div>
    </div>

    <div class="panel-card" style="margin-bottom:16px;">
        <div class="detail-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
            <div>
                <span class="detail-label">Owner</span>
                <div class="detail-value" style="font-size:15px;">{{ $ppmp->user_name ?? 'Unknown' }}</div>
            </div>
            <div>
                <span class="detail-label">Department / Unit</span>
                <div class="detail-value" style="font-size:15px;">{{ $ppmp->department_name ?? 'Unassigned' }}</div>
            </div>
            <div>
                <span class="detail-label">Fund Source</span>
                <div class="detail-value" style="font-size:15px;">{{ $ppmp->fund_source_name ?? 'Unassigned' }}</div>
            </div>
            <div>
                <span class="detail-label">Items / Budget</span>
                <div class="detail-value" style="font-size:15px;">{{ $ppmp->items_count }} item(s) · {{ number_format((float) $ppmp->total_estimated_budget, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="review-actions-grid">
        <div class="panel-card">
            <h2 style="margin-top:0;">Approve PPMP</h2>
            <p class="muted-text" style="margin:0 0 16px;">Assign an official PPMP number and approve. Next: passed to unit to create purchase requests.</p>
            <form action="{{ route('admin.ppmp.approve', $ppmp->id) }}" method="POST" data-confirm-title="Approve PPMP" data-confirm="Approve this PPMP? Next: passed to unit to create purchase requests from it.">
                @csrf
                <div style="margin-bottom:12px;">
                    <label for="ppmp_no" class="detail-label">Official PPMP Number</label>
                    <input type="text" id="ppmp_no" name="ppmp_no" placeholder="Enter official PPMP number" value="{{ old('ppmp_no') }}" required
                        style="width:100%; max-width:280px; margin-top:6px; padding:10px 12px; border:1px solid var(--portal-border); border-radius:8px; font-size:14px;{{ $errors->has('ppmp_no') ? ' border-color:var(--portal-danger);' : '' }}">
                    @error('ppmp_no')
                        <span class="muted-text" style="color:var(--portal-danger); font-size:12px; display:block; margin-top:4px;">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn-primary">Approve PPMP</button>
            </form>
        </div>

        <div class="panel-card">
            <h2 style="margin-top:0;">Return to Draft</h2>
            <p class="muted-text" style="margin:0 0 16px;">State the corrections the unit must fix. The PPMP will return to draft status.</p>
            <form action="{{ route('admin.ppmp.disapprove', $ppmp->id) }}" method="POST" data-confirm="Return this PPMP to draft? The unit will receive your remarks and can revise.">
                @csrf
                <div style="margin-bottom:12px;">
                    <label for="reason" class="detail-label">Corrections / Remarks</label>
                    <textarea id="reason" name="reason" rows="4" placeholder="State the corrections the unit must fix" required
                        style="width:100%; margin-top:6px; padding:10px 12px; border:1px solid var(--portal-border); border-radius:8px; font-size:14px; resize:vertical;{{ $errors->has('reason') ? ' border-color:var(--portal-danger);' : '' }}">{{ old('reason') }}</textarea>
                    @error('reason')
                        <span class="muted-text" style="color:var(--portal-danger); font-size:12px; display:block; margin-top:4px;">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn-danger">Return to Draft</button>
            </form>
        </div>
    </div>
</div>

@endsection
