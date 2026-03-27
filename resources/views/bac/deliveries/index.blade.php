@extends('bac.layout')

@section('title', 'BAC Deliveries - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Delivery & Receiving</h1>
        <p>Record supplier deliveries and update the procurement lifecycle after award.</p>
    </div>
</div>

<div class="page-card">
    <h2>Record Delivery</h2>
    <form action="{{ route('bac.deliveries.store') }}" method="POST" data-confirm="Save this delivery record?">
        @csrf

        <label for="consolidated_item_id">Awarded Consolidated Item</label>
        <select id="consolidated_item_id" name="consolidated_item_id" required>
            <option value="">Select Consolidated Item</option>
            @foreach($awardedItems as $item)
                <option value="{{ $item->id }}">
                    #{{ $item->id }} | {{ $item->item_name }} | Qty {{ $item->total_quantity }}
                </option>
            @endforeach
        </select>

        <label for="purchase_request_id">Related Confirmed Purchase Request</label>
        <select id="purchase_request_id" name="purchase_request_id">
            <option value="">Optional</option>
            @foreach($approvedRequests as $requestItem)
                <option value="{{ $requestItem->id }}">
                    PR #{{ $requestItem->id }} | Status {{ $requestItem->status }}
                </option>
            @endforeach
        </select>

        <div class="info-grid">
            <div>
                <label for="supplier_name">Supplier Name</label>
                <input type="text" id="supplier_name" name="supplier_name" required>
            </div>
            <div>
                <label for="delivery_date">Delivery Date</label>
                <input type="date" id="delivery_date" name="delivery_date" required>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <label for="received_by">Received By</label>
                <input type="text" id="received_by" name="received_by" required>
            </div>
            <div>
                <label for="quantity_delivered">Quantity Delivered</label>
                <input type="number" id="quantity_delivered" name="quantity_delivered" min="1" required>
            </div>
        </div>

        <label for="remarks">Remarks</label>
        <textarea id="remarks" name="remarks" rows="3"></textarea>

        <div class="action-row">
            <button type="submit" class="btn-create">Save Delivery</button>
        </div>
    </form>
</div>

<div class="page-card">
    <h2>Recorded Deliveries</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Consolidated Item</th>
                <th>PR Ref</th>
                <th>Supplier</th>
                <th>Delivery Date</th>
                <th>Received By</th>
                <th>Quantity Delivered</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliveries as $delivery)
                <tr>
                    <td>#{{ $delivery->id }}</td>
                    <td>{{ $delivery->item_name ?: '-' }}</td>
                    <td>{{ $delivery->request_reference ? '#'.$delivery->request_reference : '-' }}</td>
                    <td>{{ $delivery->supplier_name ?: '-' }}</td>
                    <td>{{ $delivery->delivery_date ? \Illuminate\Support\Carbon::parse($delivery->delivery_date)->format('M d, Y') : '-' }}</td>
                    <td>{{ $delivery->received_by ?: '-' }}</td>
                    <td>{{ $delivery->quantity_delivered }}</td>
                    <td>{{ $delivery->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">No deliveries recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
