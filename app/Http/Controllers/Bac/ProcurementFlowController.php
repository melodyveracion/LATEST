<?php

namespace App\Http\Controllers\Bac;

use App\Http\Controllers\Controller;
use App\Models\Bidding;
use App\Models\ConsolidatedItem;
use App\Models\ConsolidatedItemSource;
use App\Models\Delivery;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcurementFlowController extends Controller
{
    public function consolidationIndex()
    {
        $eligibleItems = $this->eligibleRequestItems()->get();

        $consolidatedItems = ConsolidatedItem::query()
            ->leftJoin('categories', 'consolidated_items.category_id', '=', 'categories.category_id')
            ->select(
                'consolidated_items.*',
                'categories.name as category_name',
                DB::raw('(select count(*) from consolidated_item_sources where consolidated_item_sources.consolidated_item_id = consolidated_items.consol_item_id) as sources_count'),
                DB::raw('(select count(*) from biddings where biddings.consolidated_item_id = consolidated_items.consol_item_id) as bids_count')
            )
            ->orderByDesc('consolidated_items.consol_item_id')
            ->get();

        return view('bac.consolidation.index', compact('eligibleItems', 'consolidatedItems'));
    }

    public function generateConsolidation()
    {
        $eligibleItems = $this->eligibleRequestItems()->get();

        if ($eligibleItems->isEmpty()) {
            return back()->withErrors([
                'error' => 'No confirmed purchase request items are available for consolidation.',
            ]);
        }

        DB::transaction(function () use ($eligibleItems) {
            $groups = $eligibleItems->groupBy(function ($item) {
                return implode('|', [
                    $item->category_id,
                    $item->item_name,
                    $item->specifications,
                    $item->unit,
                    $item->unit_price,
                ]);
            });

            foreach ($groups as $items) {
                $first = $items->first();

                $consolidatedItem = ConsolidatedItem::create([
                    'category_id' => $first->category_id,
                    'item_name' => $first->item_name,
                    'specifications' => $first->specifications,
                    'unit' => $first->unit,
                    'total_quantity' => $items->sum('quantity'),
                    'unit_price' => $first->unit_price,
                    'estimated_budget' => $items->sum('estimated_budget'),
                    'status' => 'Consolidated',
                    'created_by' => Auth::id(),
                ]);

                foreach ($items as $item) {
                    ConsolidatedItemSource::create([
                        'consolidated_item_id' => $consolidatedItem->id,
                        'purchase_request_id' => $item->purchase_request_id,
                        'purchase_request_item_id' => $item->id,
                        'source_quantity' => $item->quantity,
                        'source_amount' => $item->estimated_budget,
                    ]);
                }
            }
        });

        return back()->with('success', 'Confirmed purchase request items were consolidated successfully.');
    }

    public function biddingIndex()
    {
        $consolidatedItems = ConsolidatedItem::query()
            ->leftJoin('categories', 'consolidated_items.category_id', '=', 'categories.category_id')
            ->select('consolidated_items.*', 'categories.name as category_name')
            ->orderByDesc('consolidated_items.consol_item_id')
            ->get();

        $biddings = Bidding::query()
            ->leftJoin('consolidated_items', 'biddings.consolidated_item_id', '=', 'consolidated_items.consol_item_id')
            ->select('biddings.*', 'consolidated_items.item_name')
            ->orderByDesc('biddings.bidding_id')
            ->get();

        return view('bac.biddings.index', compact('consolidatedItems', 'biddings'));
    }

    public function storeBid(Request $request)
    {
        $request->validate([
            'consolidated_item_id' => 'required|integer|exists:consolidated_items,consol_item_id',
            'supplier_name' => 'required|string|max:255',
            'bid_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $consolidatedItem = ConsolidatedItem::findOrFail($request->integer('consolidated_item_id'));

        if ($consolidatedItem->status !== 'Consolidated') {
            return back()->withErrors([
                'error' => 'New bids can only be added while the consolidated item is still in consolidated status.',
            ]);
        }

        if (Bidding::query()
            ->where('consolidated_item_id', $consolidatedItem->id)
            ->where('status', 'Won')
            ->exists()) {
            return back()->withErrors([
                'error' => 'This consolidated item already has an awarded winning bid.',
            ]);
        }

        Bidding::create([
            'consolidated_item_id' => $consolidatedItem->id,
            'supplier_name' => $request->supplier_name,
            'bid_amount' => $request->bid_amount,
            'status' => 'Pending',
            'remarks' => $request->remarks,
            'bid_submitted_at' => now(),
        ]);

        return back()->with('success', 'Supplier bid recorded successfully.');
    }

    public function award($id)
    {
        DB::transaction(function () use ($id) {
            $winningBid = Bidding::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $consolidatedItem = ConsolidatedItem::query()
                ->lockForUpdate()
                ->findOrFail($winningBid->consolidated_item_id);

            if ($winningBid->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'error' => 'Only pending bids can be awarded.',
                ]);
            }

            if ($consolidatedItem->status !== 'Consolidated') {
                throw ValidationException::withMessages([
                    'error' => 'This consolidated item is no longer available for awarding.',
                ]);
            }

            if (Delivery::query()->where('consolidated_item_id', $consolidatedItem->id)->exists()) {
                throw ValidationException::withMessages([
                    'error' => 'This consolidated item already has a delivery record and can no longer be re-awarded.',
                ]);
            }

            if (Bidding::query()
                ->where('consolidated_item_id', $consolidatedItem->id)
                ->where('status', 'Won')
                ->exists()) {
                throw ValidationException::withMessages([
                    'error' => 'A winning bid is already recorded for this consolidated item.',
                ]);
            }

            Bidding::where('consolidated_item_id', $winningBid->consolidated_item_id)
                ->where('bidding_id', '!=', $winningBid->id)
                ->update(['status' => 'Lost']);

            $winningBid->update([
                'status' => 'Won',
                'awarded_at' => now(),
            ]);

            $consolidatedItem->update(['status' => 'Awarded']);
        });

        return back()->with('success', 'Winning supplier awarded successfully.');
    }

    public function deliveriesIndex()
    {
        $deliveries = Delivery::query()
            ->leftJoin('consolidated_items', 'deliveries.consolidated_item_id', '=', 'consolidated_items.consol_item_id')
            ->leftJoin('purchase_requests', 'deliveries.purchase_request_id', '=', 'purchase_requests.pr_id')
            ->select(
                'deliveries.*',
                'consolidated_items.item_name',
                'purchase_requests.pr_id as request_reference'
            )
            ->orderByDesc('deliveries.delivery_id')
            ->get();

        $awardedItems = ConsolidatedItem::query()
            ->where('status', 'Awarded')
            ->orderBy('item_name')
            ->get();

        $approvedRequests = PurchaseRequest::query()
            ->whereIn('status', ['Confirmed', 'Completed'])
            ->orderByDesc('pr_id')
            ->get();

        return view('bac.deliveries.index', compact('deliveries', 'awardedItems', 'approvedRequests'));
    }

    public function inventoryIndex()
    {
        $inventoryItems = Inventory::query()
            ->leftJoin('categories', 'inventories.category_id', '=', 'categories.category_id')
            ->leftJoin('deliveries', 'inventories.last_delivery_id', '=', 'deliveries.delivery_id')
            ->select(
                'inventories.*',
                'categories.name as category_name',
                'deliveries.delivery_date'
            )
            ->orderBy('inventories.item_name')
            ->get();

        return view('bac.inventory.index', compact('inventoryItems'));
    }

    public function storeDelivery(Request $request)
    {
        $request->validate([
            'consolidated_item_id' => 'required|integer|exists:consolidated_items,consol_item_id',
            'purchase_request_id' => 'nullable|integer|exists:purchase_requests,pr_id',
            'supplier_name' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'received_by' => 'required|string|max:255',
            'quantity_delivered' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            $consolidatedItem = ConsolidatedItem::query()
                ->lockForUpdate()
                ->findOrFail($request->integer('consolidated_item_id'));

            if ($consolidatedItem->status !== 'Awarded') {
                throw ValidationException::withMessages([
                    'error' => 'Deliveries can only be recorded for consolidated items that already have an awarded supplier.',
                ]);
            }

            $winningBid = Bidding::query()
                ->where('consolidated_item_id', $consolidatedItem->id)
                ->where('status', 'Won')
                ->first();

            if (!$winningBid) {
                throw ValidationException::withMessages([
                    'error' => 'A delivery cannot be recorded until a winning supplier has been awarded.',
                ]);
            }

            if (strcasecmp(trim((string) $winningBid->supplier_name), trim((string) $request->supplier_name)) !== 0) {
                throw ValidationException::withMessages([
                    'supplier_name' => 'The delivery supplier must match the awarded winning supplier.',
                ]);
            }

            $alreadyDelivered = (int) Delivery::query()
                ->where('consolidated_item_id', $consolidatedItem->id)
                ->sum('quantity_delivered');

            $remainingDeliverable = (int) $consolidatedItem->total_quantity - $alreadyDelivered;

            if ($remainingDeliverable <= 0) {
                throw ValidationException::withMessages([
                    'error' => 'All quantities for this consolidated item have already been delivered.',
                ]);
            }

            $requestedQuantity = $request->integer('quantity_delivered');

            if ($requestedQuantity > $remainingDeliverable) {
                throw ValidationException::withMessages([
                    'quantity_delivered' => 'Delivered quantity exceeds the remaining awarded quantity for this consolidated item.',
                ]);
            }

            $purchaseRequest = null;
            $sourceQuantity = null;

            if ($request->filled('purchase_request_id')) {
                $purchaseRequest = PurchaseRequest::findOrFail($request->integer('purchase_request_id'));

                if (!in_array($purchaseRequest->status, ['Confirmed', 'Completed'], true)) {
                    throw ValidationException::withMessages([
                        'purchase_request_id' => 'Only confirmed purchase requests linked to the awarded consolidated item can receive deliveries.',
                    ]);
                }

                $sourceQuantity = (int) ConsolidatedItemSource::query()
                    ->where('consolidated_item_id', $consolidatedItem->id)
                    ->where('purchase_request_id', $purchaseRequest->id)
                    ->sum('source_quantity');

                if ($sourceQuantity <= 0) {
                    throw ValidationException::withMessages([
                        'purchase_request_id' => 'The selected purchase request is not linked to this consolidated item.',
                    ]);
                }

                $deliveredForRequest = (int) Delivery::query()
                    ->where('consolidated_item_id', $consolidatedItem->id)
                    ->where('purchase_request_id', $purchaseRequest->id)
                    ->sum('quantity_delivered');

                $remainingForRequest = $sourceQuantity - $deliveredForRequest;

                if ($remainingForRequest <= 0) {
                    throw ValidationException::withMessages([
                        'purchase_request_id' => 'The selected purchase request already received its full delivered quantity for this consolidated item.',
                    ]);
                }

                if ($requestedQuantity > $remainingForRequest) {
                    throw ValidationException::withMessages([
                        'quantity_delivered' => 'Delivered quantity exceeds the linked purchase request balance for this consolidated item.',
                    ]);
                }
            }

            $delivery = Delivery::create([
                'purchase_request_id' => $purchaseRequest?->id,
                'consolidated_item_id' => $consolidatedItem->id,
                'supplier_name' => $winningBid->supplier_name,
                'delivery_date' => $request->delivery_date,
                'received_by' => $request->received_by,
                'quantity_delivered' => $requestedQuantity,
                'status' => 'Received',
                'remarks' => $request->remarks,
            ]);

            $inventory = Inventory::query()->firstOrNew([
                'category_id' => $consolidatedItem->category_id,
                'item_name' => $consolidatedItem->item_name,
                'unit' => $consolidatedItem->unit,
            ]);

            $inventory->quantity_on_hand = (int) $inventory->quantity_on_hand + $requestedQuantity;
            $inventory->last_delivery_id = $delivery->id;
            $inventory->save();

            $newDeliveredTotal = $alreadyDelivered + $requestedQuantity;
            $consolidatedItem->update([
                'status' => $newDeliveredTotal >= (int) $consolidatedItem->total_quantity ? 'Delivered' : 'Awarded',
            ]);

            if ($purchaseRequest && $sourceQuantity !== null) {
                $deliveredForRequestAfterSave = (int) Delivery::query()
                    ->where('consolidated_item_id', $consolidatedItem->id)
                    ->where('purchase_request_id', $purchaseRequest->id)
                    ->sum('quantity_delivered');

                if ($deliveredForRequestAfterSave >= $sourceQuantity) {
                    $purchaseRequest->update(['status' => 'Completed']);
                }
            }
        });

        return back()->with('success', 'Delivery recorded successfully.');
    }

    private function eligibleRequestItems()
    {
        return PurchaseRequestItem::query()
            ->join('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.pr_id')
            ->leftJoin('categories', 'purchase_request_items.category_id', '=', 'categories.category_id')
            ->leftJoin('department_units', 'purchase_requests.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('consolidated_item_sources', 'purchase_request_items.pr_item_id', '=', 'consolidated_item_sources.purchase_request_item_id')
            ->where('purchase_requests.status', 'Confirmed')
            ->whereNull('consolidated_item_sources.consol_item_src_id')
            ->select(
                'purchase_request_items.*',
                'categories.name as category_name',
                'department_units.name as department_name'
            )
            ->orderBy('purchase_request_items.pr_item_id');
    }
}
