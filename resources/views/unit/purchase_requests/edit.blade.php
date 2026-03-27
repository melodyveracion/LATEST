@extends('unit.layout')

@section('title', 'Edit Purchase Request - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Edit Purchase Request #{{ $purchaseRequest->id }}</h1>
        <p>Update the purpose and requested quantities while this request is still editable.</p>
    </div>
    <a href="{{ route('unit.pr.show', $purchaseRequest->id) }}" class="btn btn-primary">Back to Request</a>
</div>

<div class="page-card">
    <h2>Request Items</h2>
    <p class="helper-text">
        Edit the quantities requested from the approved PPMP items below.
    </p>

    @if(!$selectedPpmp)
        <p class="helper-text">The approved PPMP linked to this request could not be found.</p>
    @else
        <form action="{{ route('unit.pr.update', $purchaseRequest->id) }}" method="POST" data-confirm="Save changes to this purchase request?">
            @csrf
            <input type="hidden" name="ppmp_id" value="{{ $selectedPpmp->id }}">

            <label>Approved PPMP</label>
            <input type="text" value="{{ $selectedPpmp->ppmp_no ?: ('Draft Ref #' . $selectedPpmp->id) }} | FY {{ $selectedPpmp->fiscal_year ?: '-' }} | {{ $selectedPpmp->status }}" disabled>

            <label for="purpose">Purpose / Justification</label>
            <textarea id="purpose" name="purpose" rows="3" required>{{ old('purpose', $purchaseRequest->purpose) }}</textarea>

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
                                    value="{{ old('requested_quantities.' . $item->id, $item->selected_quantity) }}"
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;">No remaining approved PPMP items are available for this request.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($sourceItems->isNotEmpty())
                <div class="action-row">
                    <button type="submit" class="btn-create">Save Changes</button>
                </div>
            @endif
        </form>
    @endif
</div>
@endsection
