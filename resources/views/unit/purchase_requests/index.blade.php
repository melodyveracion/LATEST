@extends('unit.layout')
@section('title', 'Purchase Requests — ConsoliData')

@section('content')

<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Purchase Requests</h1>
        <p class="text-sm text-slate-500 mt-0.5">Create, submit, and confirm purchase requests from approved PPMP plans.</p>
    </div>
    <a href="{{ route('unit.pr.create') }}" class="btn-primary btn-sm flex-shrink-0">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Create Request
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

    {{-- Filters --}}
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
        <form method="GET" action="{{ route('unit.pr.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="field-label">Search</label>
                <input type="text" name="search" placeholder="Purpose, status, fund source…"
                       value="{{ request('search') }}" class="field-input">
            </div>
            <div class="min-w-36">
                <label class="field-label">Status</label>
                <select name="status" class="field-input">
                    <option value="">All statuses</option>
                    @foreach(['Draft','Submitted','Approved','Confirmed','Completed','On-Going','Disapproved','Canceled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
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
                <a href="{{ route('unit.pr.index') }}" class="btn-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
        <span class="text-sm font-semibold text-slate-700">Request List</span>
        <span class="text-xs text-slate-400">{{ $purchaseRequests->count() }} record(s)</span>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>PPMP Ref</th>
                    <th>Purpose</th>
                    <th>Fund Source</th>
                    <th>Items</th>
                    <th>Total Budget</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseRequests as $pr)
                    @php
                        $sClass = match ($pr->status) {
                            'Submitted'   => 'status-badge status-submitted',
                            'Approved'    => 'status-badge status-approved',
                            'Confirmed'   => 'status-badge status-confirmed',
                            'Completed'   => 'status-badge status-completed',
                            'Disapproved' => 'status-badge status-disapproved',
                            'Canceled'    => 'status-badge status-canceled',
                            'On-Going'    => 'status-badge status-ongoing',
                            default       => 'status-badge status-draft',
                        };
                    @endphp
                    <tr>
                        <td class="font-medium text-slate-900">#{{ $pr->id }}</td>
                        <td class="text-slate-400">{{ $pr->ppmp_id ? '#'.$pr->ppmp_id : '—' }}</td>
                        <td class="max-w-56">
                            <span class="block truncate text-slate-700" title="{{ $pr->purpose }}">
                                {{ \Illuminate\Support\Str::limit($pr->purpose ?: '—', 60) }}
                            </span>
                        </td>
                        <td class="text-slate-500 text-xs">{{ $pr->fund_source_name ?? '—' }}</td>
                        <td class="text-center">{{ $pr->items_count }}</td>
                        <td class="font-medium">₱{{ number_format((float) $pr->total_estimated_budget, 2) }}</td>
                        <td>
                            <span class="{{ $sClass }}">{{ $pr->status }}</span>
                            @if($pr->status === 'Approved')
                                <p class="text-xs text-amber-600 mt-0.5">Needs your confirmation</p>
                            @elseif($pr->status === 'Confirmed')
                                <p class="text-xs text-indigo-500 mt-0.5">Forwarded to BAC</p>
                            @endif
                        </td>
                        <td class="text-slate-400 text-xs whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($pr->created_at)->format('M d, Y') }}
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('unit.pr.show', $pr->id) }}" class="btn-secondary btn-sm">View</a>
                                @if(in_array($pr->status, ['Draft','Disapproved'], true))
                                    <a href="{{ route('unit.pr.edit', $pr->id) }}" class="btn-secondary btn-sm">Edit</a>
                                @endif
                                @if($pr->status === 'Approved')
                                    <form action="{{ route('unit.pr.confirm', $pr->id) }}" method="POST"
                                          data-confirm="Confirm this request and forward it to BAC?">
                                        @csrf
                                        <button type="submit" class="btn-success btn-sm">Confirm</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-slate-400 py-12">
                            <svg class="w-10 h-10 text-slate-200 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 9h6M9 13h6M9 17h4"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3a2 2 0 0 0 6 0M7 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1"/>
                            </svg>
                            {{ request()->hasAny(['search','status','fund_source_id'])
                                ? 'No requests matched the selected filters.'
                                : 'No purchase requests yet.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
