@extends('unit.layout')
@section('title', 'Manage PPMP — ConsoliData')

@section('content')

{{-- Page header --}}
<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Manage PPMP</h1>
        <p class="text-sm text-slate-500 mt-0.5">Create, maintain, and submit procurement plans for admin review.</p>
    </div>
    <div class="flex gap-2 flex-shrink-0">
        <a href="{{ route('unit.ppmp.uploadForm') }}" class="btn-secondary btn-sm">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-8-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload CSV
        </a>
        <a href="{{ route('unit.ppmp.create') }}" class="btn-primary btn-sm">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Create PPMP
        </a>
    </div>
</div>

{{-- Table card --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

    {{-- Filter bar --}}
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
        <form method="GET" action="{{ route('unit.ppmp.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="field-label">Search</label>
                <input type="text" name="search" placeholder="Fiscal year, status…"
                       value="{{ request('search') }}" class="field-input">
            </div>
            <div class="min-w-36">
                <label class="field-label">Status</label>
                <select name="status" class="field-input">
                    <option value="">All statuses</option>
                    @foreach(['Draft','Submitted','Approved','Disapproved'] as $s)
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
                <a href="{{ route('unit.ppmp.index') }}" class="btn-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>

    {{-- Table header row --}}
    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
        <span class="text-sm font-semibold text-slate-700">PPMP Records</span>
        <span class="text-xs text-slate-400">{{ $ppmps->count() }} record(s)</span>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>PPMP No.</th>
                    <th>Fiscal Year</th>
                    <th>Department</th>
                    <th>Fund Source</th>
                    <th>Items</th>
                    <th>Total Budget</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ppmps as $ppmp)
                    @php
                        $sClass = match ($ppmp->status) {
                            'Submitted'   => 'status-badge status-submitted',
                            'Approved'    => 'status-badge status-approved',
                            'Disapproved' => 'status-badge status-disapproved',
                            default       => 'status-badge status-draft',
                        };
                    @endphp
                    <tr>
                        <td class="font-medium text-slate-900">
                            {{ $ppmp->ppmp_no ?: ('Draft #' . $ppmp->id) }}
                        </td>
                        <td>{{ $ppmp->fiscal_year ?: '—' }}</td>
                        <td class="text-slate-500">{{ $ppmp->department_name ?? 'Unassigned' }}</td>
                        <td class="text-slate-500">{{ $ppmp->fund_source_name ?? 'Unassigned' }}</td>
                        <td class="text-center">{{ $ppmp->items_count }}</td>
                        <td class="font-medium">₱{{ number_format((float) $ppmp->total_estimated_budget, 2) }}</td>
                        <td><span class="{{ $sClass }}">{{ $ppmp->status }}</span></td>
                        <td class="text-slate-500 text-xs max-w-48 truncate" title="{{ $ppmp->review_remarks }}">
                            {{ $ppmp->review_remarks ?: '—' }}
                        </td>
                        <td class="text-slate-400 text-xs whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($ppmp->created_at)->format('M d, Y') }}
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('unit.ppmp.edit', $ppmp->id) }}" class="btn-secondary btn-sm">
                                    {{ $ppmp->status === 'Draft' ? 'Edit' : 'View' }}
                                </a>
                                @if($ppmp->status === 'Approved')
                                    <a href="{{ route('unit.pr.create', ['ppmp_id' => $ppmp->id]) }}" class="btn-primary btn-sm">
                                        Create PR
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-slate-400 py-12">
                            <svg class="w-10 h-10 text-slate-200 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v5h5"/>
                            </svg>
                            {{ request()->hasAny(['search','status','category_id','fund_source_id'])
                                ? 'No PPMP records matched the selected filters.'
                                : 'No PPMP records yet. Create one to get started.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
