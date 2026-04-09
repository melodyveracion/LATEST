@extends('unit.layout')
@section('title', 'Create Purchase Request — ConsoliData')

@section('content')

<div class="flex items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
            <a href="{{ route('unit.pr.index') }}" class="hover:text-slate-600 transition-colors">Purchase Requests</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            <span class="text-slate-600">Create</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Create Purchase Request</h1>
        <p class="text-sm text-slate-500 mt-0.5">Select an approved PPMP and enter quantities from remaining items.</p>
    </div>
    <a href="{{ route('unit.pr.index') }}" class="btn-secondary btn-sm flex-shrink-0">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
        Back
    </a>
</div>

{{-- Unit info --}}
<div class="ui-card">
    <h2 class="ui-card-title">Unit Details</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Department / Unit</p>
            <p class="text-sm font-medium text-slate-800">{{ $departmentName ?: 'Not assigned' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Fund Source View</p>
            <p class="text-sm font-medium text-slate-800">{{ $fundSourceName ?: 'All fund sources' }}</p>
        </div>
    </div>
</div>

@if($approvedPpmps->isEmpty())
    <div class="flex items-start gap-3 px-5 py-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        </svg>
        <div>
            <p class="font-semibold mb-0.5">No approved PPMP found</p>
            <p>You need at least one <strong>Approved</strong> PPMP before creating a purchase request.</p>
        </div>
    </div>
@else
    {{-- PPMP selector --}}
    <div class="ui-card">
        <h2 class="ui-card-title">Select PPMP</h2>
        <form method="GET" action="{{ route('unit.pr.create') }}" class="flex items-end gap-3">
            <div class="flex-1">
                <label for="ppmp_selector" class="field-label">Approved PPMP</label>
                <select id="ppmp_selector" name="ppmp_id" onchange="this.form.submit()" class="field-input">
                    @foreach($approvedPpmps as $ppmp)
                        <option value="{{ $ppmp->id }}" {{ (string) ($selectedPpmp->id ?? '') === (string) $ppmp->id ? 'selected' : '' }}>
                            {{ $ppmp->ppmp_no ?: 'Draft Ref #'.$ppmp->id }} | FY {{ $ppmp->fiscal_year ?: '—' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($selectedPpmp)
        <div class="ui-card">
            <div class="flex items-center justify-between mb-5">
                <h2 class="ui-card-title mb-0">Draft Request</h2>
                <div class="flex items-center gap-4 text-sm text-slate-500">
                    <span>PPMP <strong class="text-slate-700">{{ $selectedPpmp->ppmp_no ?: '#'.$selectedPpmp->id }}</strong></span>
                    <span>FY <strong class="text-slate-700">{{ $selectedPpmp->fiscal_year ?: '—' }}</strong></span>
                    <span class="status-badge status-approved">{{ $selectedPpmp->status }}</span>
                </div>
            </div>

            <form action="{{ route('unit.pr.store') }}" method="POST"
                  data-confirm="Create this purchase request from the selected PPMP?">
                @csrf
                <input type="hidden" name="ppmp_id" value="{{ $selectedPpmp->id }}">

                <div class="mb-5">
                    <label for="purpose" class="field-label">Purpose / Justification <span class="text-red-500">*</span></label>
                    <textarea id="purpose" name="purpose" rows="3" required
                              class="field-input resize-none"
                              placeholder="Describe the purpose of this purchase request…">{{ old('purpose') }}</textarea>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 mb-5">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Item</th>
                                <th>Specifications</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Remaining Qty</th>
                                <th>Remaining Budget</th>
                                <th>Request Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sourceItems as $item)
                                <tr>
                                    <td>{{ $item->category_name ?: '—' }}</td>
                                    <td class="font-medium text-slate-900">{{ $item->item_name ?: $item->description }}</td>
                                    <td class="text-slate-500 text-xs max-w-40 truncate" title="{{ $item->specifications }}">{{ $item->specifications ?: '—' }}</td>
                                    <td>{{ $item->unit ?: '—' }}</td>
                                    <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="text-center font-semibold text-emerald-600">{{ $item->remaining_quantity }}</td>
                                    <td class="text-emerald-600">₱{{ number_format($item->remaining_budget, 2) }}</td>
                                    <td>
                                        <input type="number"
                                               name="requested_quantities[{{ $item->id }}]"
                                               min="0" max="{{ $item->remaining_quantity }}"
                                               value="{{ old('requested_quantities.'.$item->id, 0) }}"
                                               class="field-input w-24 text-center">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-400 py-8">
                                        No remaining approved PPMP items available for this plan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($sourceItems->isNotEmpty())
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Draft Request
                        </button>
                    </div>
                @endif
            </form>
        </div>
    @endif
@endif

@endsection
