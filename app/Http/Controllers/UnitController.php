<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Ppmp;
use App\Models\PpmpItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Support\UnitFundDepartmentResolver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $fundSources = $this->accessibleFundSources($user);
        $activeFundSource = $this->activeFundSource($fundSources);
        $activeFundSourceId = $activeFundSource ? (int) $activeFundSource->id : null;

        $ppmpCountQuery = DB::table('ppmps')->where('user_id', $user->id);
        $purchaseRequestCountQuery = DB::table('purchase_requests')->where('user_id', $user->id);
        $notificationCountQuery = DB::table('notifications')->where('user_id', $user->id);

        if ($activeFundSourceId) {
            $ppmpCountQuery->where('fund_source_id', $activeFundSourceId);
            $purchaseRequestCountQuery->where('fund_source_id', $activeFundSourceId);
        }

        $totalPpmpBudgetQuery = DB::table('ppmp_items')
            ->join('ppmps', 'ppmp_items.ppmp_id', '=', 'ppmps.ppmp_id')
            ->where('ppmps.user_id', $user->id)
            ->where('ppmps.status', 'Approved');

        $confirmedPrBudgetQuery = DB::table('purchase_request_items')
            ->join('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.pr_id')
            ->where('purchase_requests.user_id', $user->id)
            ->whereIn('purchase_requests.status', ['Confirmed', 'Completed', 'On-Going']);

        if ($activeFundSourceId) {
            $totalPpmpBudgetQuery->where('ppmps.fund_source_id', $activeFundSourceId);
            $confirmedPrBudgetQuery->where('purchase_requests.fund_source_id', $activeFundSourceId);
        }

        $totalPpmpBudget = (float) $totalPpmpBudgetQuery->sum('ppmp_items.estimated_budget');
        $confirmedPrBudget = (float) $confirmedPrBudgetQuery->sum('purchase_request_items.estimated_budget');

        return view('unit.dashboard', [
            'user' => $user,
            'departmentName' => $this->departmentName($user->department_unit_id),
            'fundSources' => $fundSources,
            'activeFundSourceId' => $activeFundSourceId,
            'activeFundSourceName' => $activeFundSource?->name,
            'currentFundSourceScope' => $this->currentFundSourceScope($fundSources),
            'ppmpCount' => (clone $ppmpCountQuery)->count(),
            'prCount' => (clone $purchaseRequestCountQuery)->count(),
            'pendingPrCount' => (clone $purchaseRequestCountQuery)->where('status', 'Submitted')->count(),
            'approvedPrCount' => (clone $purchaseRequestCountQuery)->where('status', 'Approved')->count(),
            'unreadNotifications' => $notificationCountQuery->where('is_read', false)->count(),
            'totalPpmpBudget' => $totalPpmpBudget,
            'confirmedPrBudget' => $confirmedPrBudget,
            'remainingPpmpBalance' => max(0, $totalPpmpBudget - $confirmedPrBudget),
        ]);
    }

    public function setActiveFundSource(Request $request)
    {
        $fundSources = $this->accessibleFundSources();

        if ($request->filled('fund_source_id')) {
            $fundSource = $fundSources->first(
                fn ($item) => (int) $item->id === (int) $request->input('fund_source_id')
            );

            if (!$fundSource) {
                return back()->withErrors([
                    'fund_source_id' => 'Select a valid fund source for your unit.',
                ]);
            }

            $request->session()->put('unit_active_fund_source_id', (int) $fundSource->id);

            return back();
        }

        $request->session()->forget('unit_active_fund_source_id');

        return back();
    }

    public function index(Request $request)
    {
        $fundSources = $this->accessibleFundSources();
        $categories = $this->categories();
        $activeFundSourceId = $this->activeFundSourceId($fundSources);

        $query = $this->purchaseRequestQuery(false)
            ->where('purchase_requests.user_id', Auth::id())
            ->orderByDesc('purchase_requests.pr_id');

        if ($request->has('fund_source_id')) {
            if ($request->filled('fund_source_id')) {
                $fsId = (int) $request->fund_source_id;
                if ($fundSources->contains('id', $fsId)) {
                    $query->where('purchase_requests.fund_source_id', $fsId);
                }
            }
        } elseif ($activeFundSourceId) {
            $query->where('purchase_requests.fund_source_id', $activeFundSourceId);
        }

        if ($request->filled('status')) {
            $query->where('purchase_requests.status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                    ->from('purchase_request_items')
                    ->whereColumn('purchase_request_items.purchase_request_id', 'purchase_requests.pr_id')
                    ->where('purchase_request_items.category_id', (int) $request->category_id);
            });
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('purchase_requests.purpose', 'like', $term)
                    ->orWhere('purchase_requests.status', 'like', $term)
                    ->orWhere('department_units.name', 'like', $term)
                    ->orWhere('fund_sources.name', 'like', $term);
            });
        }

        $purchaseRequests = $query->get();

        return view('unit.purchase_requests.index', compact('purchaseRequests', 'fundSources', 'categories'));
    }

    public function create(Request $request)
    {
        $fundSources = $this->accessibleFundSources();
        $approvedPpmps = $this->approvedPpmpsQuery(true)->get();

        $selectedPpmpId = (int) ($request->input('ppmp_id') ?: ($approvedPpmps->first()->id ?? 0));
        $selectedPpmp = $approvedPpmps->firstWhere('id', $selectedPpmpId);

        return view('unit.purchase_requests.create', [
            'departmentName' => $this->departmentName(Auth::user()->department_unit_id),
            'fundSourceName' => $selectedPpmp->fund_source_name
                ?? $this->currentFundSourceScope($fundSources),
            'fundSources' => $fundSources,
            'activeFundSourceId' => $this->activeFundSourceId($fundSources),
            'approvedPpmps' => $approvedPpmps,
            'selectedPpmp' => $selectedPpmp,
            'sourceItems' => $selectedPpmp ? $this->availablePpmpItems($selectedPpmp->id) : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'ppmp_id' => 'required|integer',
            'purpose' => 'required|string|max:1000',
            'requested_quantities' => 'required|array',
        ]);

        $ppmp = Ppmp::query()
            ->where('user_id', $user->id)
            ->where('status', 'Approved')
            ->findOrFail($request->ppmp_id);

        $selectedItems = $this->extractRequestedItems(
            $ppmp->id,
            $request->input('requested_quantities', []),
            null
        );

        if ($selectedItems->isEmpty()) {
            return back()->withErrors([
                'requested_quantities' => 'Enter at least one quantity from the approved PPMP items.',
            ])->withInput();
        }

        $purchaseRequest = PurchaseRequest::create([
            'user_id' => $user->id,
            'department_unit_id' => $ppmp->department_unit_id,
            'fund_source_id' => $ppmp->fund_source_id,
            'ppmp_id' => $ppmp->id,
            'purpose' => $request->purpose,
            'status' => 'Draft',
        ]);

        foreach ($selectedItems as $item) {
            PurchaseRequestItem::create($item + [
                'purchase_request_id' => $purchaseRequest->id,
            ]);
        }

        return redirect()
            ->route('unit.pr.show', $purchaseRequest->id)
            ->with('success', 'Purchase request created successfully.');
    }

    public function show($id)
    {
        $purchaseRequest = $this->findOwnedPurchaseRequest($id);
        $items = $this->purchaseRequestItems($purchaseRequest->id);

        return view('unit.purchase_requests.show', [
            'purchaseRequest' => $purchaseRequest,
            'items' => $items,
            'departmentName' => $this->departmentName($purchaseRequest->department_unit_id),
            'fundSourceName' => $this->fundSourceName($purchaseRequest->fund_source_id),
        ]);
    }

    public function print($id)
    {
        $purchaseRequest = $this->findOwnedPurchaseRequest($id);
        $items = $this->purchaseRequestItems($purchaseRequest->id);
        $reviewer = $purchaseRequest->reviewed_by ? User::find($purchaseRequest->reviewed_by) : null;
        $isApprovedState = in_array($purchaseRequest->status, ['Approved', 'Confirmed', 'Completed'], true);

        return view('prints.purchase-request', [
            'purchaseRequest' => $purchaseRequest,
            'items' => $items,
            'departmentName' => $purchaseRequest->department_name ?: $this->departmentName($purchaseRequest->department_unit_id),
            'fundSourceName' => $purchaseRequest->fund_source_name ?: $this->fundSourceName($purchaseRequest->fund_source_id),
            'requesterName' => Auth::user()->name,
            'requesterDesignation' => $purchaseRequest->department_name ?: 'Unit / College Requesting Office',
            'approverName' => $isApprovedState ? $reviewer?->name : null,
            'approverDesignation' => $isApprovedState && $reviewer ? ucfirst((string) $reviewer->role) : null,
            'documentDate' => $purchaseRequest->submitted_at ?: $purchaseRequest->created_at,
            'prNumber' => $this->formattedPurchaseRequestNumber($purchaseRequest),
            'responsibilityCenterCode' => $this->responsibilityCenterCode($purchaseRequest->ppmp_id),
        ]);
    }

    public function edit($id)
    {
        $purchaseRequest = $this->findOwnedPurchaseRequest($id);
        $this->ensureEditableRequest($purchaseRequest->status);

        $approvedPpmps = $this->approvedPpmpsQuery()->get();

        $selectedPpmp = $approvedPpmps->firstWhere('id', $purchaseRequest->ppmp_id);
        $currentQuantities = PurchaseRequestItem::query()
            ->where('purchase_request_id', $purchaseRequest->id)
            ->pluck('quantity', 'ppmp_item_id')
            ->toArray();

        return view('unit.purchase_requests.edit', [
            'purchaseRequest' => $purchaseRequest,
            'approvedPpmps' => $approvedPpmps,
            'selectedPpmp' => $selectedPpmp,
            'sourceItems' => $selectedPpmp
                ? $this->availablePpmpItems($selectedPpmp->id, $purchaseRequest->id, $currentQuantities)
                : collect(),
            'departmentName' => $this->departmentName($purchaseRequest->department_unit_id),
            'fundSourceName' => $this->fundSourceName($purchaseRequest->fund_source_id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::where('user_id', Auth::id())->findOrFail($id);
        $this->ensureEditableRequest($purchaseRequest->status);

        $request->validate([
            'ppmp_id' => 'required|integer',
            'purpose' => 'required|string|max:1000',
            'requested_quantities' => 'required|array',
        ]);

        $ppmp = Ppmp::query()
            ->where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->findOrFail($request->ppmp_id);

        $selectedItems = $this->extractRequestedItems(
            $ppmp->id,
            $request->input('requested_quantities', []),
            $purchaseRequest->id
        );

        if ($selectedItems->isEmpty()) {
            return back()->withErrors([
                'requested_quantities' => 'Enter at least one quantity from the approved PPMP items.',
            ])->withInput();
        }

        $purchaseRequest->update([
            'department_unit_id' => $ppmp->department_unit_id,
            'fund_source_id' => $ppmp->fund_source_id,
            'ppmp_id' => $ppmp->id,
            'purpose' => $request->purpose,
        ]);

        PurchaseRequestItem::where('purchase_request_id', $purchaseRequest->id)->delete();

        foreach ($selectedItems as $item) {
            PurchaseRequestItem::create($item + [
                'purchase_request_id' => $purchaseRequest->id,
            ]);
        }

        return redirect()
            ->route('unit.pr.show', $purchaseRequest->id)
            ->with('success', 'Purchase request updated successfully.');
    }

    public function submit($id)
    {
        $purchaseRequest = PurchaseRequest::where('user_id', Auth::id())->findOrFail($id);
        $this->ensureEditableRequest($purchaseRequest->status);

        $items = PurchaseRequestItem::query()
            ->where('purchase_request_id', $purchaseRequest->id)
            ->get();

        if ($items->isEmpty()) {
            return back()->withErrors([
                'error' => 'Add at least one purchase request item before submitting.',
            ]);
        }

        foreach ($items as $item) {
            if (!$item->ppmp_item_id) {
                continue;
            }

            $sourceItem = PpmpItem::find($item->ppmp_item_id);
            if (!$sourceItem) {
                return back()->withErrors([
                    'error' => 'One or more source PPMP items could not be found.',
                ]);
            }

            $remaining = $this->remainingMetrics($sourceItem->id, $purchaseRequest->id);
            if ($item->quantity > $remaining['remaining_quantity']) {
                return back()->withErrors([
                    'error' => 'Purchase request #' . $purchaseRequest->id . ' exceeds the remaining quantity for ' . $this->ppmpItemLabel($sourceItem) . '.',
                ]);
            }
        }

        $purchaseRequest->update([
            'status' => 'Submitted',
            'submitted_at' => now(),
            'confirmed_at' => null,
            'review_remarks' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $this->notifyReviewers(
            $purchaseRequest,
            'Purchase Request Submitted',
            'Purchase request #' . $purchaseRequest->id . ' was submitted by ' . Auth::user()->name . ' for review.'
        );

        return redirect()
            ->route('unit.pr.show', $purchaseRequest->id)
            ->with('success', 'Purchase request submitted for admin review.');
    }

    public function requestCorrection(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($purchaseRequest->status !== 'Approved') {
            return back()->withErrors([
                'error' => 'Only approved purchase requests can be returned to admin for correction.',
            ]);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $purchaseRequest->update([
            'status' => 'Submitted',
            'confirmed_at' => null,
            'review_remarks' => 'Correction requested by unit: ' . $request->reason,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $this->notifyReviewers(
            $purchaseRequest,
            'Purchase Request Correction Requested',
            'Purchase request #' . $purchaseRequest->id . ' was returned by ' . Auth::user()->name . ' for correction. Reason: ' . $request->reason
        );

        return redirect()
            ->route('unit.pr.show', $purchaseRequest->id)
            ->with('success', 'Purchase request returned to admin for correction.');
    }

    public function confirm($id)
    {
        $purchaseRequest = PurchaseRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($purchaseRequest->status !== 'Approved') {
            return back()->withErrors([
                'error' => 'Only approved purchase requests can be confirmed.',
            ]);
        }

        $items = PurchaseRequestItem::query()
            ->where('purchase_request_id', $purchaseRequest->id)
            ->get();

        if ($items->isEmpty()) {
            return back()->withErrors([
                'error' => 'No purchase request items were found to confirm.',
            ]);
        }

        foreach ($items as $item) {
            if (!$item->ppmp_item_id) {
                continue;
            }

            $sourceItem = PpmpItem::find($item->ppmp_item_id);
            if (!$sourceItem) {
                return back()->withErrors([
                    'error' => 'One or more source PPMP items could not be found.',
                ]);
            }

            $remaining = $this->remainingMetrics($sourceItem->id, $purchaseRequest->id);
            if ($item->quantity > $remaining['remaining_quantity']) {
                return back()->withErrors([
                    'error' => 'The remaining PPMP balance for ' . $this->ppmpItemLabel($sourceItem) . ' is no longer enough to confirm this request.',
                ]);
            }
        }

        $purchaseRequest->update([
            'status' => 'Confirmed',
            'confirmed_at' => now(),
        ]);

        $this->notifyBacUsers(
            $purchaseRequest,
            'Purchase Request Confirmed',
            'Purchase request #' . $purchaseRequest->id . ' was confirmed by ' . Auth::user()->name . ' and is ready for BAC processing.'
        );

        return redirect()
            ->route('unit.pr.show', $purchaseRequest->id)
            ->with('success', 'Purchase request confirmed and forwarded to BAC.');
    }

    public function viewRemainingItems(Request $request)
    {
        $fundSources = $this->accessibleFundSources();
        $categories = $this->categories();
        $activeFundSourceId = $this->activeFundSourceId($fundSources);
        $approvedPpmps = $this->approvedPpmpsQuery(false)->get();

        $selectedPpmpId = $request->filled('ppmp_id') ? (int) $request->ppmp_id : null;

        $query = PpmpItem::query()
            ->join('ppmps', 'ppmp_items.ppmp_id', '=', 'ppmps.ppmp_id')
            ->leftJoin('fund_sources', 'ppmps.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->leftJoin('categories', 'ppmp_items.category_id', '=', 'categories.category_id')
            ->where('ppmps.user_id', Auth::id())
            ->where('ppmps.status', 'Approved')
            ->select(
                'ppmp_items.*',
                'ppmps.ppmp_id as ppmp_reference',
                'ppmps.ppmp_no',
                'ppmps.status as ppmp_status',
                'ppmps.fiscal_year',
                'categories.name as category_name'
            )
            ->orderByDesc('ppmp_items.ppmp_item_id');

        if ($request->has('fund_source_id')) {
            if ($request->filled('fund_source_id')) {
                $fsId = (int) $request->fund_source_id;
                if ($fundSources->contains('id', $fsId)) {
                    $query->where('ppmps.fund_source_id', $fsId);
                }
            }
        } elseif ($activeFundSourceId) {
            $query->where('ppmps.fund_source_id', $activeFundSourceId);
        }

        if ($selectedPpmpId) {
            $query->where('ppmps.ppmp_id', $selectedPpmpId);
        }

        if ($request->filled('category_id')) {
            $query->where('ppmp_items.category_id', (int) $request->category_id);
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('ppmps.fiscal_year', 'like', $term)
                    ->orWhere('ppmps.ppmp_no', 'like', $term)
                    ->orWhere('fund_sources.name', 'like', $term)
                    ->orWhere('categories.name', 'like', $term)
                    ->orWhere('ppmp_items.item_name', 'like', $term)
                    ->orWhere('ppmp_items.description', 'like', $term);
            });
        }

        $items = $query->get()
            ->map(function ($item) {
                $remaining = $this->remainingMetrics($item->id);
                $item->used_quantity = $remaining['used_quantity'];
                $item->used_budget = $remaining['used_budget'];
                $item->remaining_quantity = $remaining['remaining_quantity'];
                $item->remaining_budget = $remaining['remaining_budget'];

                return $item;
            });

        return view('unit.ppmp.remaining', [
            'items' => $items,
            'approvedPpmps' => $approvedPpmps,
            'fundSources' => $fundSources,
            'categories' => $categories,
            'selectedPpmpId' => $selectedPpmpId,
        ]);
    }

    public function history(Request $request)
    {
        $fundSources = $this->accessibleFundSources();
        $activeFundSourceId = $this->activeFundSourceId($fundSources);

        $query = $this->purchaseRequestQuery(false)
            ->where('purchase_requests.user_id', Auth::id())
            ->where('purchase_requests.status', 'Completed')
            ->orderByDesc('purchase_requests.pr_id');

        if ($request->has('fund_source_id')) {
            if ($request->filled('fund_source_id')) {
                $fsId = (int) $request->fund_source_id;
                if ($fundSources->contains('id', $fsId)) {
                    $query->where('purchase_requests.fund_source_id', $fsId);
                }
            }
        } elseif ($activeFundSourceId) {
            $query->where('purchase_requests.fund_source_id', $activeFundSourceId);
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term, $request) {
                $q->where('purchase_requests.purpose', 'like', $term)
                    ->orWhere('department_units.name', 'like', $term)
                    ->orWhere('fund_sources.name', 'like', $term);
                if (is_numeric(trim($request->search))) {
                    $q->orWhere('purchase_requests.pr_id', (int) trim($request->search));
                }
            });
        }

        $purchaseRequests = $query->get();

        return view('unit.purchase_requests.history', compact('purchaseRequests', 'fundSources'));
    }

    public function profile()
    {
        $fundSources = $this->accessibleFundSources();

        return view('unit.profile', [
            'departmentName' => $this->departmentName(Auth::user()->department_unit_id),
            'fundSourceName' => $this->currentFundSourceScope($fundSources),
            'fundSources' => $fundSources,
        ]);
    }

    private function findOwnedPurchaseRequest($id)
    {
        return $this->purchaseRequestQuery()
            ->where('purchase_requests.user_id', Auth::id())
            ->where('purchase_requests.pr_id', $id)
            ->firstOrFail();
    }

    private function purchaseRequestQuery(bool $applyActiveFundFilter = false)
    {
        $query = PurchaseRequest::query()
            ->leftJoin('department_units', 'purchase_requests.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('fund_sources', 'purchase_requests.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->select(
                'purchase_requests.*',
                'department_units.name as department_name',
                'fund_sources.name as fund_source_name',
                DB::raw('(select count(*) from purchase_request_items where purchase_request_items.purchase_request_id = purchase_requests.pr_id) as items_count'),
                DB::raw('(select coalesce(sum(purchase_request_items.estimated_budget), 0) from purchase_request_items where purchase_request_items.purchase_request_id = purchase_requests.pr_id) as total_estimated_budget')
            );

        if ($applyActiveFundFilter && ($activeFundSourceId = $this->activeFundSourceId())) {
            $query->where('purchase_requests.fund_source_id', $activeFundSourceId);
        }

        return $query;
    }

    private function purchaseRequestItems(int $purchaseRequestId)
    {
        return PurchaseRequestItem::query()
            ->leftJoin('categories', 'purchase_request_items.category_id', '=', 'categories.category_id')
            ->leftJoin('ppmp_items', 'purchase_request_items.ppmp_item_id', '=', 'ppmp_items.ppmp_item_id')
            ->select(
                'purchase_request_items.*',
                'categories.name as category_name',
                'ppmp_items.item_name as source_item_name'
            )
            ->where('purchase_request_id', $purchaseRequestId)
            ->orderBy('purchase_request_items.pr_item_id')
            ->get();
    }

    private function departmentName($id): ?string
    {
        if (!$id) {
            return null;
        }

        return UnitFundDepartmentResolver::resolvedDepartmentName($id)
            ?: DB::table('department_units')->where('department_unit_id', $id)->value('name');
    }

    private function fundSourceName($id): ?string
    {
        if (!$id) {
            return null;
        }

        return DB::table('fund_sources')->where('fund_src_id', $id)->value('name');
    }

    private function approvedPpmpsQuery(bool $applyActiveFundFilter = false)
    {
        $query = Ppmp::query()
            ->leftJoin('fund_sources', 'ppmps.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->select('ppmps.*', 'fund_sources.name as fund_source_name');

        if ($applyActiveFundFilter && ($activeFundSourceId = $this->activeFundSourceId())) {
            $query->where('fund_source_id', $activeFundSourceId);
        }

        return $query
            ->orderByDesc('fiscal_year')
            ->orderByDesc('ppmp_id');
    }

    private function accessibleFundSources(?User $user = null): Collection
    {
        $user ??= Auth::user();

        return UnitFundDepartmentResolver::fundSourcesForDepartment($user?->department_unit_id);
    }

    private function categories(): Collection
    {
        if (!Schema::hasTable('categories')) {
            return collect();
        }

        return Category::query()->orderBy('name')->get();
    }

    private function activeFundSource(Collection $fundSources): ?object
    {
        $activeFundSourceId = (int) session('unit_active_fund_source_id');

        return $fundSources->first(
            fn ($fundSource) => (int) $fundSource->id === $activeFundSourceId
        );
    }

    private function activeFundSourceId(?Collection $fundSources = null): ?int
    {
        $fundSource = $this->activeFundSource($fundSources ?? $this->accessibleFundSources());

        return $fundSource ? (int) $fundSource->id : null;
    }

    private function currentFundSourceScope(?Collection $fundSources = null): ?string
    {
        $fundSources ??= $this->accessibleFundSources();
        $activeFundSource = $this->activeFundSource($fundSources);

        if ($activeFundSource) {
            return $activeFundSource->name;
        }

        if ($fundSources->isEmpty()) {
            return null;
        }

        return $fundSources->count() === 1
            ? $fundSources->first()->name
            : 'All fund sources';
    }

    private function responsibilityCenterCode(?int $ppmpId): ?string
    {
        if (!$ppmpId) {
            return null;
        }

        $ppmp = Ppmp::query()->find($ppmpId);

        return $ppmp?->ppmp_no
            ?: 'PPMP-' . str_pad((string) $ppmpId, 3, '0', STR_PAD_LEFT);
    }

    private function availablePpmpItems(int $ppmpId, ?int $excludeRequestId = null, array $currentQuantities = [])
    {
        return PpmpItem::query()
            ->leftJoin('categories', 'ppmp_items.category_id', '=', 'categories.category_id')
            ->where('ppmp_items.ppmp_id', $ppmpId)
            ->select('ppmp_items.*', 'categories.name as category_name')
            ->orderBy('ppmp_items.ppmp_item_id')
            ->get()
            ->map(function ($item) use ($excludeRequestId, $currentQuantities) {
                $remaining = $this->remainingMetrics($item->id, $excludeRequestId);
                $item->used_quantity = $remaining['used_quantity'];
                $item->used_budget = $remaining['used_budget'];
                $item->remaining_quantity = $remaining['remaining_quantity'];
                $item->remaining_budget = $remaining['remaining_budget'];
                $item->selected_quantity = (int) ($currentQuantities[$item->id] ?? 0);

                return $item;
            })
            ->filter(function ($item) {
                return $item->remaining_quantity > 0 || $item->selected_quantity > 0;
            })
            ->values();
    }

    private function extractRequestedItems(int $ppmpId, array $requestedQuantities, ?int $excludeRequestId)
    {
        $selectedItems = collect();
        $sourceItems = $this->availablePpmpItems($ppmpId, $excludeRequestId);

        foreach ($sourceItems as $sourceItem) {
            $requestedQuantity = (int) ($requestedQuantities[$sourceItem->id] ?? 0);

            if ($requestedQuantity <= 0) {
                continue;
            }

            if ($requestedQuantity > $sourceItem->remaining_quantity) {
                throw ValidationException::withMessages([
                    'requested_quantities' => 'Requested quantity for ' . $this->ppmpItemLabel($sourceItem) . ' exceeds the remaining quantity.',
                ]);
            }

            $estimatedBudget = $requestedQuantity * (float) $sourceItem->unit_cost;

            $selectedItems->push([
                'ppmp_item_id' => $sourceItem->id,
                'category_id' => $sourceItem->category_id,
                'item_name' => $sourceItem->item_name ?: $sourceItem->description,
                'specifications' => $sourceItem->specifications,
                'unit' => $sourceItem->unit,
                'quantity' => $requestedQuantity,
                'unit_price' => $sourceItem->unit_cost,
                'q1_total_cost' => 0,
                'q2_total_cost' => 0,
                'q3_total_cost' => 0,
                'q4_total_cost' => 0,
                'mode_of_procurement' => $sourceItem->mode_of_procurement ?: 'N/A',
                'estimated_budget' => $estimatedBudget,
                'jan' => 0,
                'feb' => 0,
                'mar' => 0,
                'apr' => 0,
                'may' => 0,
                'jun' => 0,
                'jul' => 0,
                'aug' => 0,
                'sep' => 0,
                'oct' => 0,
                'nov' => 0,
                'dec' => 0,
            ]);
        }

        return $selectedItems;
    }

    private function remainingMetrics(int $ppmpItemId, ?int $excludeRequestId = null): array
    {
        $ppmpItem = PpmpItem::findOrFail($ppmpItemId);

        $query = PurchaseRequestItem::query()
            ->join('purchase_requests', 'purchase_request_items.purchase_request_id', '=', 'purchase_requests.pr_id')
            ->where('purchase_request_items.ppmp_item_id', $ppmpItemId)
            ->whereIn('purchase_requests.status', ['Confirmed', 'Completed', 'On-Going']);

        if ($excludeRequestId) {
            $query->where('purchase_requests.pr_id', '!=', $excludeRequestId);
        }

        $used = $query->selectRaw('COALESCE(SUM(purchase_request_items.quantity), 0) as total_quantity, COALESCE(SUM(purchase_request_items.estimated_budget), 0) as total_budget')
            ->first();

        $usedQuantity = (int) ($used->total_quantity ?? 0);
        $usedBudget = (float) ($used->total_budget ?? 0);

        return [
            'used_quantity' => $usedQuantity,
            'used_budget' => $usedBudget,
            'remaining_quantity' => max(0, (int) $ppmpItem->quantity - $usedQuantity),
            'remaining_budget' => max(0, (float) $ppmpItem->estimated_budget - $usedBudget),
        ];
    }

    private function ensureEditableRequest(string $status): void
    {
        if (!in_array($status, ['Draft', 'Disapproved', 'Canceled'], true)) {
            abort(403);
        }
    }

    private function ppmpItemLabel($item): string
    {
        return $item->item_name ?: $item->description ?: ('Item #' . $item->id);
    }

    private function formattedPurchaseRequestNumber(PurchaseRequest $purchaseRequest): string
    {
        $date = $purchaseRequest->submitted_at ?: $purchaseRequest->created_at ?: now();
        $documentDate = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $documentDate->format('Y-m-d') . '-' . str_pad((string) $purchaseRequest->id, 3, '0', STR_PAD_LEFT);
    }

    private function notifyReviewers(PurchaseRequest $purchaseRequest, string $title, string $message): void
    {
        $recipients = User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get();

        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'type' => 'purchase_request_submission',
                'reference_id' => $purchaseRequest->id,
                'status' => $purchaseRequest->status,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
            ]);

            if (!empty($recipient->email)) {
                try {
                    Mail::raw($message, function ($mail) use ($recipient, $title) {
                        $mail->to($recipient->email)
                            ->subject('ConsoliData - ' . $title);
                    });
                } catch (\Throwable $exception) {
                    // In-app notification remains the primary channel.
                }
            }
        }
    }

    private function notifyBacUsers(PurchaseRequest $purchaseRequest, string $title, string $message): void
    {
        $recipients = User::query()
            ->where('role', 'bac')
            ->where('status', 'active')
            ->get();

        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'type' => 'purchase_request_confirmation',
                'reference_id' => $purchaseRequest->id,
                'status' => $purchaseRequest->status,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
            ]);

            if (!empty($recipient->email)) {
                try {
                    Mail::raw($message, function ($mail) use ($recipient, $title) {
                        $mail->to($recipient->email)
                            ->subject('ConsoliData - ' . $title);
                    });
                } catch (\Throwable $exception) {
                    // In-app notification remains the primary channel.
                }
            }
        }
    }
}
