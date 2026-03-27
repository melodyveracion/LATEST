@extends('unit.layout')

@section('title', 'Create Purchase Request - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Create Purchase Request</h1>
        <p>Create a new purchase request from an approved PPMP under the fund source you are currently viewing.</p>
    </div>
    <a href="{{ route('unit.pr.index') }}" class="btn btn-primary">Back to Requests</a>
</div>

<div class="page-card">
    <h2>Unit Account Details</h2>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Department / Unit</span>
            <div class="info-value">{{ $departmentName ?: 'Not assigned yet' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Current Dashboard View</span>
            <div class="info-value">{{ $fundSourceName ?: 'All fund sources' }}</div>
        </div>
    </div>
</div>

<div class="page-card">
    <h2>Create Draft Request</h2>
    @if($approvedPpmps->isEmpty())
        <p class="helper-text">
            You need at least one <strong>approved</strong> PPMP before you can create a purchase request.
        </p>
    @else
        <p class="helper-text">
            Select an approved PPMP, then enter the quantities you want to request from its remaining items.
        </p>

        <form method="GET" action="{{ route('unit.pr.create') }}" style="margin-bottom:20px;">
            <label for="ppmp_selector">Approved PPMP</label>
            <select id="ppmp_selector" name="ppmp_id" onchange="this.form.submit()">
                @foreach($approvedPpmps as $ppmp)
                    <option value="{{ $ppmp->id }}" {{ (string) ($selectedPpmp->id ?? '') === (string) $ppmp->id ? 'selected' : '' }}>
                        {{ $ppmp->ppmp_no ?: ('Draft Ref #' . $ppmp->id) }} | FY {{ $ppmp->fiscal_year ?: '-' }} | {{ $ppmp->status }}
                    </option>
                @endforeach
            </select>
        </form>

        @if($selectedPpmp)
            <form action="{{ route('unit.pr.store') }}" method="POST" data-confirm="Create this purchase request from the selected PPMP?">
                @csrf
                <input type="hidden" name="ppmp_id" value="{{ $selectedPpmp->id }}">

                <div class="info-grid" style="margin-bottom:18px;">
                    <div class="info-item">
                        <span class="info-label">Selected PPMP</span>
                        <div class="info-value">{{ $selectedPpmp->ppmp_no ?: ('Draft Ref #' . $selectedPpmp->id) }}</div>
                        <div class="info-subtext">Fiscal Year {{ $selectedPpmp->fiscal_year ?: '-' }} | {{ $selectedPpmp->status }}</div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Available Item Rows</span>
                        <div class="info-value">{{ $sourceItems->count() }}</div>
                        <div class="info-subtext">Choose the quantities you need from approved PPMP items.</div>
                    </div>
                </div>

                <label for="purpose">Purpose / Justification</label>
                <textarea id="purpose" name="purpose" rows="3" required>{{ old('purpose') }}</textarea>

                <table>
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
                                <td>{{ $item->category_name ?: '-' }}</td>
                                <td>{{ $item->item_name ?: $item->description }}</td>
                                <td>{{ $item->specifications ?: '-' }}</td>
                                <td>{{ $item->unit ?: '-' }}</td>
                                <td>{{ number_format($item->unit_cost, 2) }}</td>
                                <td>{{ $item->remaining_quantity }}</td>
                                <td>{{ number_format($item->remaining_budget, 2) }}</td>
                                <td>
                                    <input
                                        type="number"
                                        name="requested_quantities[{{ $item->id }}]"
                                        min="0"
                                        max="{{ $item->remaining_quantity }}"
                                        value="{{ old('requested_quantities.' . $item->id, 0) }}"
                                    >
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center;">No remaining approved PPMP items are available for this PPMP.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($sourceItems->isNotEmpty())
                    <div class="action-row">
                        <button type="submit" class="btn-create">Create Draft Request</button>
                    </div>
                @endif
            </form>
        @endif
    @endif
</div>
@endsection
