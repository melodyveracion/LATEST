@extends('unit.layout')
@section('title', 'Edit PPMP — ConsoliData')

@section('content')

@php
    $isDraft = $ppmp->status === 'Draft';
    $sClass  = match ($ppmp->status) {
        'Submitted'   => 'status-badge status-submitted',
        'Approved'    => 'status-badge status-approved',
        'Disapproved' => 'status-badge status-disapproved',
        default       => 'status-badge status-draft',
    };
@endphp

{{-- Page header --}}
<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
            <a href="{{ route('unit.ppmp.index') }}" class="hover:text-slate-600 transition-colors">PPMP</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            <span class="text-slate-600">{{ $ppmp->ppmp_no ?: 'Draft #'.$ppmp->id }}</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
            {{ $ppmp->ppmp_no ?: 'PPMP Draft #'.$ppmp->id }}
            <span class="{{ $sClass }}">{{ $ppmp->status }}</span>
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">Add item entries, then submit the PPMP for admin validation.</p>
    </div>
    <a href="{{ route('unit.ppmp.index') }}" class="btn-secondary btn-sm flex-shrink-0">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
        Back
    </a>
</div>

{{-- PPMP Details --}}
<div class="ui-card">
    <h2 class="ui-card-title">PPMP Details</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-4">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Fiscal Year</p>
            <p class="text-sm font-medium text-slate-800">{{ $ppmp->fiscal_year ?: '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Department</p>
            <p class="text-sm font-medium text-slate-800">{{ $departmentName ?: '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Fund Source</p>
            <p class="text-sm font-medium text-slate-800">{{ $fundSourceName ?: '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Created</p>
            <p class="text-sm text-slate-600">{{ \Illuminate\Support\Carbon::parse($ppmp->created_at)->format('M d, Y') }}</p>
        </div>
        @if($ppmp->submitted_at)
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Submitted</p>
            <p class="text-sm text-slate-600">{{ \Illuminate\Support\Carbon::parse($ppmp->submitted_at)->format('M d, Y h:i A') }}</p>
        </div>
        @endif
        @if($ppmp->review_remarks)
        <div class="col-span-2 sm:col-span-3">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Review Remarks</p>
            <p class="text-sm text-slate-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                {{ $ppmp->review_remarks }}
            </p>
        </div>
        @endif
    </div>
</div>

{{-- Add item form (only for Draft) --}}
<div class="ui-card" x-data="ppmpItemForm()">
    <h2 class="ui-card-title">{{ $isDraft ? 'Add Item' : 'Item Form' }}</h2>

    @if(!$isDraft)
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800 mb-4">
            <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            </svg>
            This PPMP is <strong class="mx-1">{{ $ppmp->status }}</strong> — items can only be edited while in Draft status.
        </div>
    @endif

    <form id="add-ppmp-item-form"
          action="{{ route('unit.ppmp.addItem', $ppmp->id) }}"
          method="POST"
          data-confirm="Add this item to the PPMP?">
        @csrf

        {{-- Preset selector --}}
        <div class="mb-5">
            <label class="field-label">Preset Item</label>
            <select name="preset_item_id" x-model="presetId" @change="applyPreset($event)"
                    {{ !$isDraft ? 'disabled' : '' }} class="field-input">
                <option value="">— Select from preset list (optional) —</option>
                @foreach($presetItems as $pi)
                    <option value="{{ $pi->id }}"
                            data-category-id="{{ $pi->category_id }}"
                            data-part-label="{{ $pi->part_label }}"
                            data-item-name="{{ $pi->item_name }}"
                            data-unit="{{ $pi->unit }}"
                            data-price="{{ $pi->price }}"
                            {{ (string) old('preset_item_id') === (string) $pi->id ? 'selected' : '' }}>
                        {{ $pi->item_name }}{{ $pi->unit ? ' · '.$pi->unit : '' }}{{ $pi->price ? ' · ₱'.$pi->price : '' }}
                    </option>
                @endforeach
            </select>
            <p class="field-hint">Selecting a preset auto-fills category, name, unit, and price.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="field-label">Category</label>
                <select name="category_id" x-model="form.category_id" {{ !$isDraft ? 'disabled' : '' }} class="field-input">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) old('category_id') === (string) $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">UACS / Item Code</label>
                <input type="text" name="uacs_code" x-model="form.uacs_code"
                       {{ !$isDraft ? 'disabled' : '' }} class="field-input"
                       value="{{ old('uacs_code') }}">
            </div>
            <div>
                <label class="field-label">Item Name</label>
                <input type="text" name="item_name" x-model="form.item_name"
                       {{ !$isDraft ? 'disabled' : '' }} class="field-input"
                       value="{{ old('item_name') }}">
            </div>
            <div>
                <label class="field-label">Unit</label>
                <input type="text" name="unit" x-model="form.unit"
                       {{ !$isDraft ? 'disabled' : '' }} class="field-input"
                       value="{{ old('unit') }}">
            </div>
        </div>

        <div class="mb-4">
            <label class="field-label">Description / Specifications</label>
            <textarea name="specifications" rows="2" {{ !$isDraft ? 'disabled' : '' }}
                      class="field-input resize-none">{{ old('specifications') }}</textarea>
        </div>

        {{-- Quarterly quantities --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            @foreach([1,2,3,4] as $q)
                <div>
                    <label class="field-label">Q{{ $q }} Quantity</label>
                    <input type="number" name="quantity_q{{ $q }}" min="0"
                           x-model.number="form.q{{ $q }}"
                           @input="recalc()"
                           value="{{ old('quantity_q'.$q, 0) }}"
                           required {{ !$isDraft ? 'disabled' : '' }}
                           class="field-input">
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
            <div>
                <label class="field-label">Total Quantity</label>
                <input type="number" :value="totalQty" readonly class="field-input bg-slate-50 text-slate-500 cursor-default">
            </div>
            <div>
                <label class="field-label">Unit Price (₱)</label>
                <input type="number" name="unit_cost" min="0" step="0.01"
                       x-model.number="form.unit_cost"
                       @input="recalc()"
                       value="{{ old('unit_cost') }}"
                       {{ !$isDraft ? 'disabled' : '' }} class="field-input">
            </div>
            <div>
                <label class="field-label">Estimated Budget (₱)</label>
                <input type="number" :value="estimatedBudget" readonly class="field-input bg-slate-50 text-slate-500 cursor-default">
            </div>
        </div>

        <div class="mb-5">
            <label class="field-label">Mode of Procurement</label>
            <input type="text" name="mode_of_procurement"
                   value="{{ old('mode_of_procurement', 'N/A') }}"
                   {{ !$isDraft ? 'disabled' : '' }} class="field-input sm:max-w-xs">
        </div>

        @if($isDraft)
            <div class="flex justify-end">
                <button type="submit" class="btn-success">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Item
                </button>
            </div>
        @endif
    </form>
</div>

{{-- Current items table --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-5">
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-800">Current Items</h2>
        <span class="text-xs text-slate-400">{{ count($items) }} item(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Code</th>
                    <th>Item Name</th>
                    <th>Specs</th>
                    <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
                    <th>Total Qty</th>
                    <th>Unit</th>
                    <th>Unit Cost</th>
                    <th>Est. Budget</th>
                    <th>Mode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="text-slate-400">{{ $loop->iteration }}</td>
                        <td>{{ $categories->firstWhere('id', $item->category_id)?->name ?? '—' }}</td>
                        <td class="text-slate-400 text-xs">{{ $item->uacs_code ?: '—' }}</td>
                        <td class="font-medium text-slate-900">{{ $item->item_name ?: $item->description }}</td>
                        <td class="text-slate-500 text-xs max-w-40 truncate" title="{{ $item->specifications }}">{{ $item->specifications ?: '—' }}</td>
                        <td class="text-center">{{ $item->quantity_q1 }}</td>
                        <td class="text-center">{{ $item->quantity_q2 }}</td>
                        <td class="text-center">{{ $item->quantity_q3 }}</td>
                        <td class="text-center">{{ $item->quantity_q4 }}</td>
                        <td class="text-center font-medium">{{ $item->quantity }}</td>
                        <td>{{ $item->unit ?: '—' }}</td>
                        <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="font-medium text-emerald-700">₱{{ number_format($item->estimated_budget, 2) }}</td>
                        <td class="text-slate-400 text-xs">{{ $item->mode_of_procurement ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center text-slate-400 py-10">No items added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($isDraft && count($items) > 0)
        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-4">
            <p class="text-sm text-slate-500">Ready to submit? This will send the PPMP to the admin for validation.</p>
            <form action="{{ route('unit.ppmp.addItem', $ppmp->id) }}" method="POST"
                  data-confirm="Submit this PPMP for admin validation?">
                @csrf
                <input type="hidden" name="action_type" value="submit">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit PPMP
                </button>
            </form>
        </div>
    @endif
</div>

<script>
function ppmpItemForm() {
    return {
        presetId: '{{ old('preset_item_id', '') }}',
        form: {
            category_id: '{{ old('category_id', '') }}',
            uacs_code:   '{{ old('uacs_code', '') }}',
            item_name:   '{{ old('item_name', '') }}',
            unit:        '{{ old('unit', '') }}',
            q1: {{ old('quantity_q1', 0) }},
            q2: {{ old('quantity_q2', 0) }},
            q3: {{ old('quantity_q3', 0) }},
            q4: {{ old('quantity_q4', 0) }},
            unit_cost: {{ old('unit_cost', 0) }},
        },
        get totalQty()       { return (this.form.q1||0)+(this.form.q2||0)+(this.form.q3||0)+(this.form.q4||0); },
        get estimatedBudget(){ return (this.totalQty * (this.form.unit_cost||0)).toFixed(2); },
        recalc() {},
        applyPreset(event) {
            const opt = event.target.options[event.target.selectedIndex];
            if (!opt || !opt.value) return;
            this.form.category_id = opt.dataset.categoryId || '';
            this.form.uacs_code   = opt.dataset.partLabel  || '';
            this.form.item_name   = opt.dataset.itemName   || '';
            this.form.unit        = opt.dataset.unit        || '';
            this.form.unit_cost   = parseFloat(opt.dataset.price || '0');

            // Also update non-Alpine selects/inputs
            const form = document.getElementById('add-ppmp-item-form');
            if (form) {
                const catSel = form.querySelector('[name="category_id"]');
                if (catSel) catSel.value = this.form.category_id;
            }
        }
    };
}
</script>

@endsection
