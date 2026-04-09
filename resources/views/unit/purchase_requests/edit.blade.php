@extends('unit.layout')
@section('title', 'Edit Purchase Request — ConsoliData')

@section('content')

<div class="flex items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
            <a href="{{ route('unit.pr.index') }}" class="hover:text-slate-600 transition-colors">Purchase Requests</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            <a href="{{ route('unit.pr.show', $purchaseRequest->id) }}" class="hover:text-slate-600 transition-colors">#{{ $purchaseRequest->id }}</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            <span class="text-slate-600">Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Edit Purchase Request #{{ $purchaseRequest->id }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Update purpose and requested quantities while this request is still editable.</p>
    </div>
    <a href="{{ route('unit.pr.show', $purchaseRequest->id) }}" class="btn-secondary btn-sm flex-shrink-0">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
        Back
    </a>
</div>

@if(!$selectedPpmp)
    <div class="flex items-start gap-3 px-5 py-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
        </svg>
        The approved PPMP linked to this request could not be found.
    </div>
@else
    <div class="ui-card">
        <div class="flex items-center gap-4 mb-5 pb-4 border-b border-slate-100">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Approved PPMP</p>
                <p class="text-sm font-medium text-slate-800">
                    {{ $selectedPpmp->ppmp_no ?: 'Draft Ref #'.$selectedPpmp->id }}
                    <span class="text-slate-400 ml-1">| FY {{ $selectedPpmp->fiscal_year ?: '—' }}</span>
                </p>
            </div>
        </div>

        <form action="{{ route('unit.pr.update', $purchaseRequest->id) }}" method="POST"
              data-confirm="Save changes to this purchase request?">
            @csrf
            <input type="hidden" name="ppmp_id" value="{{ $selectedPpmp->id }}">

            <div class="mb-5">
                <label for="purpose" class="field-label">Purpose / Justification <span class="text-red-500">*</span></label>
                <textarea id="purpose" name="purpose" rows="3" required
                          class="field-input resize-none">{{ old('purpose', $purchaseRequest->purpose) }}</textarea>
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
                                           value="{{ old('requested_quantities.'.$item->id, $item->selected_quantity) }}"
                                           class="field-input w-24 text-center">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-slate-400 py-8">No remaining items available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sourceItems->isNotEmpty())
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            @endif
        </form>
    </div>
@endif

@endsection
