@extends('unit.layout')
@section('title', 'Procurement History — ConsoliData')

@section('content')

<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Procurement History</h1>
        <p class="text-sm text-slate-500 mt-0.5">Completed and ongoing purchase requests with final procurement records.</p>
    </div>
    <a href="{{ route('unit.pr.index') }}" class="btn-secondary btn-sm flex-shrink-0">Back to Requests</a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
        <form method="GET" action="{{ route('unit.procurement-history') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="field-label">Search</label>
                <input type="text" name="search" placeholder="Purpose, PR ref, fund source…"
                       value="{{ request('search') }}" class="field-input">
            </div>
            <div class="min-w-36">
                <label class="field-label">Fund Source</label>
                <select name="fund_source_id" class="field-input">
                    <option value="">All</option>
                    @foreach($fundSources as $fs)
                        <option value="{{ $fs->id }}" {{ (string) request('fund_source_id') === (string) $fs->id ? 'selected' : '' }}>{{ $fs->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm">Apply</button>
                <a href="{{ route('unit.procurement-history') }}" class="btn-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
        <span class="text-sm font-semibold text-slate-700">Completed Records</span>
        <span class="text-xs text-slate-400">{{ $purchaseRequests->count() }} record(s)</span>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>PR Ref</th>
                    <th>PPMP Ref</th>
                    <th>Purpose</th>
                    <th>Items</th>
                    <th>Total Budget</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseRequests as $pr)
                    @php
                        $sClass = match ($pr->status) {
                            'Completed' => 'status-badge status-completed',
                            'On-Going'  => 'status-badge status-ongoing',
                            'Confirmed' => 'status-badge status-confirmed',
                            default     => 'status-badge status-draft',
                        };
                    @endphp
                    <tr>
                        <td class="font-medium text-slate-900">#{{ $pr->id }}</td>
                        <td class="text-slate-400">{{ $pr->ppmp_id ? '#'.$pr->ppmp_id : '—' }}</td>
                        <td class="max-w-56">
                            <span class="block truncate text-slate-700" title="{{ $pr->purpose }}">
                                {{ \Illuminate\Support\Str::limit($pr->purpose ?: '—', 80) }}
                            </span>
                        </td>
                        <td class="text-center">{{ $pr->items_count }}</td>
                        <td class="font-medium">₱{{ number_format((float) $pr->total_estimated_budget, 2) }}</td>
                        <td><span class="{{ $sClass }}">{{ $pr->status }}</span></td>
                        <td class="text-slate-400 text-xs whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($pr->confirmed_at ?? $pr->updated_at)->format('M d, Y h:i A') }}
                        </td>
                        <td>
                            <a href="{{ route('unit.pr.show', $pr->id) }}" class="btn-secondary btn-sm">View Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-400 py-12">
                            {{ request()->hasAny(['search','fund_source_id'])
                                ? 'No records matched the selected filters.'
                                : 'No completed procurement history found.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
