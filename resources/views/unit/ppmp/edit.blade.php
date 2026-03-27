@extends('unit.layout')

@section('title', 'Edit PPMP - ConsoliData')

@section('content')
@php
    $statusClass = match ($ppmp->status) {
        'Submitted' => 'status-badge status-submitted',
        'Approved' => 'status-badge status-approved',
        default => 'status-badge status-draft',
    };
@endphp
<div class="page-header">
    <div>
        <h1>{{ $ppmp->ppmp_no ?: ('PPMP Draft Ref #' . $ppmp->id) }}</h1>
        <p>Add item entries, review the current item list, and submit the PPMP once everything is ready.</p>
    </div>
    <a href="{{ route('unit.ppmp.index') }}" class="btn btn-primary">Back to PPMP List</a>
</div>

<div class="page-card">
    <h2>PPMP Details</h2>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Fiscal Year</span>
            <div class="info-value">{{ $ppmp->fiscal_year ?: '-' }}</div>
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
            <span class="info-label">Status</span>
            <div class="info-value"><span class="{{ $statusClass }}">{{ $ppmp->status }}</span></div>
        </div>
        <div class="info-item">
            <span class="info-label">Created</span>
            <div class="info-value">{{ \Illuminate\Support\Carbon::parse($ppmp->created_at)->format('M d, Y h:i A') }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Review Remarks</span>
            <div class="info-value">{{ $ppmp->review_remarks ?: 'No remarks' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Submitted At</span>
            <div class="info-value">{{ $ppmp->submitted_at ? \Illuminate\Support\Carbon::parse($ppmp->submitted_at)->format('M d, Y h:i A') : 'Not yet submitted' }}</div>
        </div>
    </div>
</div>

<div class="page-card">
    <h2>Add PPMP Item</h2>
    @if($ppmp->status !== 'Draft')
        <p class="helper-text">
            This PPMP is currently <strong>{{ $ppmp->status }}</strong>. Items can only be edited while the PPMP is in Draft status.
        </p>
    @endif

    <form id="add-ppmp-item-form" action="{{ route('unit.ppmp.addItem', $ppmp->id) }}" method="POST" data-confirm="Add this PPMP item?">
        @csrf

        <label for="preset_item_id">Preset Item</label>
        <select name="preset_item_id" id="preset_item_id" {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            <option value="">Select item from preset list</option>
            @foreach($presetItems as $presetItem)
                <option
                    value="{{ $presetItem->id }}"
                    data-category-id="{{ $presetItem->category_id }}"
                    data-part-label="{{ $presetItem->part_label }}"
                    data-item-name="{{ $presetItem->item_name }}"
                    data-unit="{{ $presetItem->unit }}"
                    data-price="{{ $presetItem->price }}"
                    {{ (string) old('preset_item_id') === (string) $presetItem->id ? 'selected' : '' }}
                >
                    {{ $presetItem->item_name }}{{ $presetItem->unit ? ' | ' . $presetItem->unit : '' }}{{ $presetItem->price ? ' | PHP ' . $presetItem->price : '' }}
                </option>
            @endforeach
        </select>
        <p class="helper-text" style="margin-top:8px;">
            Selecting a preset item will auto-fill the category, item code, item name, unit, and unit price.
        </p>

        <div class="info-grid">
            <div>
                <label>Category</label>
                <select name="category_id" id="category_id" {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>UACS / Item Code</label>
                <input type="text" name="uacs_code" value="{{ old('uacs_code') }}" {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <label>Item Name</label>
                <input type="text" name="item_name" value="{{ old('item_name') }}" {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
            <div>
                <label>Unit</label>
                <input type="text" name="unit" value="{{ old('unit') }}" {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
        </div>

        <label>Description / Specifications</label>
        <textarea name="specifications" rows="3" {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>{{ old('specifications') }}</textarea>

        <div class="info-grid" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
            <div>
                <label>Q1 Quantity</label>
                <input type="number" name="quantity_q1" min="0" value="{{ old('quantity_q1', 0) }}" required {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
            <div>
                <label>Q2 Quantity</label>
                <input type="number" name="quantity_q2" min="0" value="{{ old('quantity_q2', 0) }}" required {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
            <div>
                <label>Q3 Quantity</label>
                <input type="number" name="quantity_q3" min="0" value="{{ old('quantity_q3', 0) }}" required {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
            <div>
                <label>Q4 Quantity</label>
                <input type="number" name="quantity_q4" min="0" value="{{ old('quantity_q4', 0) }}" required {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <label>Total Quantity</label>
                <input type="number" id="total_quantity_display" value="0" readonly>
            </div>
            <div>
                <label>Unit Price</label>
                <input type="number" name="unit_cost" min="0" step="0.01" value="{{ old('unit_cost') }}" {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
            <div>
                <label>Estimated Budget</label>
                <input type="number" id="estimated_budget_display" value="0.00" readonly>
            </div>
            <div>
                <label>Mode of Procurement</label>
                <input type="text" name="mode_of_procurement" value="{{ old('mode_of_procurement', 'N/A') }}" {{ $ppmp->status !== 'Draft' ? 'disabled' : '' }}>
            </div>
        </div>

        @if($ppmp->status === 'Draft')
            <div class="action-row">
                <button type="submit" class="btn-create">Add Item</button>
            </div>
        @endif
    </form>
</div>

<div class="page-card">
    <h2>Current Items</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>UACS / Code</th>
                <th>Item Name</th>
                <th>Specifications</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Total Qty</th>
                <th>Unit</th>
                <th>Unit Cost</th>
                <th>Estimated Budget</th>
                <th>Mode of Procurement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $categories->firstWhere('id', $item->category_id)?->name ?? '-' }}
                    </td>
                    <td>{{ $item->uacs_code ?: '-' }}</td>
                    <td>{{ $item->item_name ?: $item->description }}</td>
                    <td>{{ $item->specifications ?: '-' }}</td>
                    <td>{{ $item->quantity_q1 }}</td>
                    <td>{{ $item->quantity_q2 }}</td>
                    <td>{{ $item->quantity_q3 }}</td>
                    <td>{{ $item->quantity_q4 }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit ?: '-' }}</td>
                    <td>{{ number_format($item->unit_cost, 2) }}</td>
                    <td>{{ number_format($item->estimated_budget, 2) }}</td>
                    <td>{{ $item->mode_of_procurement ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center;">No PPMP items added yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($ppmp->status === 'Draft')
        <form action="{{ route('unit.ppmp.addItem', $ppmp->id) }}" method="POST" style="margin-top:18px;" data-confirm="Submit this PPMP for validation?">
            @csrf
            <input type="hidden" name="action_type" value="submit">
            <button type="submit" class="btn btn-primary">Submit PPMP</button>
        </form>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('add-ppmp-item-form');
        if (!form) return;

        const presetSelect = form.querySelector('#preset_item_id');
        const categorySelect = form.querySelector('#category_id');
        const codeInput = form.querySelector('input[name="uacs_code"]');
        const itemNameInput = form.querySelector('input[name="item_name"]');
        const specificationsInput = form.querySelector('textarea[name="specifications"]');
        const unitInput = form.querySelector('input[name="unit"]');
        const q1Input = form.querySelector('input[name="quantity_q1"]');
        const q2Input = form.querySelector('input[name="quantity_q2"]');
        const q3Input = form.querySelector('input[name="quantity_q3"]');
        const q4Input = form.querySelector('input[name="quantity_q4"]');
        const unitCostInput = form.querySelector('input[name="unit_cost"]');
        const totalQuantityDisplay = form.querySelector('#total_quantity_display');
        const estimatedBudgetDisplay = form.querySelector('#estimated_budget_display');

        if (!presetSelect) return;

        function recalculateTotals() {
            if (!q1Input || !q2Input || !q3Input || !q4Input || !unitCostInput || !totalQuantityDisplay || !estimatedBudgetDisplay) return;
            const quantity = [
                parseFloat(q1Input.value || '0'),
                parseFloat(q2Input.value || '0'),
                parseFloat(q3Input.value || '0'),
                parseFloat(q4Input.value || '0')
            ].reduce((sum, current) => sum + current, 0);
            const unitCost = parseFloat(unitCostInput.value || '0');
            totalQuantityDisplay.value = quantity;
            estimatedBudgetDisplay.value = (quantity * unitCost).toFixed(2);
        }

        function applyPreset() {
            const option = presetSelect.options[presetSelect.selectedIndex];
            if (!option || !option.value) {
                if (categorySelect) categorySelect.value = '';
                if (codeInput) codeInput.value = '';
                if (itemNameInput) itemNameInput.value = '';
                if (unitInput) unitInput.value = '';
                if (unitCostInput) unitCostInput.value = '';
                recalculateTotals();
                return;
            }

            if (categorySelect) categorySelect.value = String(option.dataset.categoryId ?? '');
            if (codeInput) codeInput.value = String(option.dataset.partLabel ?? '');
            if (itemNameInput) itemNameInput.value = String(option.dataset.itemName ?? '');
            if (unitInput) unitInput.value = String(option.dataset.unit ?? '');
            if (unitCostInput) unitCostInput.value = String(option.dataset.price ?? '');
            if (specificationsInput && !specificationsInput.value.trim()) specificationsInput.value = '';

            recalculateTotals();
        }

        presetSelect.addEventListener('change', applyPreset);
        presetSelect.addEventListener('input', applyPreset);

        [q1Input, q2Input, q3Input, q4Input, unitCostInput].forEach(function (input) {
            if (input) input.addEventListener('input', recalculateTotals);
        });

        if (presetSelect.value) applyPreset();
        recalculateTotals();
    });
</script>
@endsection
