<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->baseQuery();

        if ($request->filled('status')) {
            $query->where('purchase_requests.status', $request->status);
        }

        if ($request->filled('department_unit_id')) {
            $query->where('purchase_requests.department_unit_id', $request->department_unit_id);
        }

        $requests = $query->orderByDesc('purchase_requests.pr_id')->get();
        $departments = DB::table('department_units')
            ->selectRaw('department_unit_id as id, name')
            ->orderBy('name')
            ->get();

        return view('admin.requests.validate', compact('requests', 'departments'));
    }

    public function approve($id)
    {
        $purchaseRequest = PurchaseRequest::query()
            ->whereKey($id)
            ->where('status', 'Submitted')
            ->first();

        if (!$purchaseRequest) {
            return back()->withErrors([
                'error' => 'Only submitted purchase requests can be approved.',
            ]);
        }

        $purchaseRequest->update([
            'status' => 'Approved',
            'review_remarks' => null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->notifyRequestStatus($purchaseRequest, 'Approved');

        return back()->with('success', 'Purchase request approved successfully.');
    }

    public function disapprove(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $purchaseRequest = PurchaseRequest::query()
            ->whereKey($id)
            ->where('status', 'Submitted')
            ->first();

        if (!$purchaseRequest) {
            return back()->withErrors([
                'error' => 'Only submitted purchase requests can be disapproved.',
            ]);
        }

        $purchaseRequest->update([
            'status' => 'Disapproved',
            'review_remarks' => $request->reason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->notifyRequestStatus($purchaseRequest, 'Disapproved', $request->reason);

        return back()->with('success', 'Purchase request disapproved successfully.');
    }

    public function consolidated()
    {
        $rows = DB::table('purchase_requests')
            ->leftJoin('department_units', 'purchase_requests.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('fund_sources', 'purchase_requests.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->whereIn('purchase_requests.status', ['Awarded', 'Completed'])
            ->select(
                DB::raw('COALESCE(MAX(department_units.name), "Unassigned") as department_name'),
                DB::raw('COALESCE(MAX(fund_sources.name), "Unassigned") as fund_source_name'),
                'purchase_requests.status',
                DB::raw('COUNT(*) as total_requests')
            )
            ->groupBy('purchase_requests.department_unit_id', 'purchase_requests.fund_source_id', 'purchase_requests.status')
            ->orderBy('department_name')
            ->orderBy('fund_source_name')
            ->get();

        return view('admin.requests.consolidated', compact('rows'));
    }

    public function printConsolidated(Request $request)
    {
        $items = DB::table('consolidated_items')
            ->whereIn('consol_item_id', function ($query) {
                $query->select('consolidated_item_sources.consolidated_item_id')
                    ->from('consolidated_item_sources')
                    ->join('purchase_requests', 'consolidated_item_sources.purchase_request_id', '=', 'purchase_requests.pr_id')
                    ->whereIn('purchase_requests.status', ['Awarded', 'Completed']);
            })
            ->select(
                'consolidated_items.consol_item_id',
                'consolidated_items.category_id',
                'consolidated_items.item_name',
                'consolidated_items.specifications',
                'consolidated_items.unit',
                'consolidated_items.unit_price',
                'consolidated_items.estimated_budget',
                DB::raw('consolidated_items.total_quantity as quantity')
            )
            ->orderBy('consolidated_items.consol_item_id')
            ->get();

        return view('prints.consolidated-request', [
            'items' => $items,
            'documentDate' => now(),
            'prNumber' => now()->format('Y-m-d') . '-001',
            'purposeFooter' => trim((string) $request->query('purpose', '')),
        ]);
    }

    public function reports()
    {
        $statusCounts = DB::table('purchase_requests')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $departmentCounts = DB::table('purchase_requests')
            ->leftJoin('department_units', 'purchase_requests.department_unit_id', '=', 'department_units.department_unit_id')
            ->select(
                DB::raw('COALESCE(department_units.name, "Unassigned") as department_name'),
                DB::raw('COUNT(*) as total_requests')
            )
            ->groupBy('department_units.name')
            ->orderBy('department_units.name')
            ->get();

        $recentRequests = $this->baseQuery()
            ->orderByDesc('purchase_requests.pr_id')
            ->limit(10)
            ->get();

        return view('admin.requests.reports', compact('statusCounts', 'departmentCounts', 'recentRequests'));
    }

    private function baseQuery()
    {
        return PurchaseRequest::query()
            ->leftJoin('users', 'purchase_requests.user_id', '=', 'users.user_id')
            ->leftJoin('department_units', 'purchase_requests.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('fund_sources', 'purchase_requests.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->select(
                'purchase_requests.*',
                DB::raw('purchase_requests.pr_id as id'),
                'users.name as user_name',
                'users.email as user_email',
                'department_units.name as department_name',
                'fund_sources.name as fund_source_name',
                DB::raw('(select count(*) from purchase_request_items where purchase_request_items.purchase_request_id = purchase_requests.pr_id) as items_count'),
                DB::raw('(select coalesce(sum(purchase_request_items.estimated_budget), 0) from purchase_request_items where purchase_request_items.purchase_request_id = purchase_requests.pr_id) as total_estimated_budget')
            );
    }

    private function notifyRequestStatus(PurchaseRequest $purchaseRequest, string $status, ?string $reason = null): void
    {
        $owner = User::find($purchaseRequest->user_id);
        $bacUsers = User::where('role', 'bac')
            ->where('status', 'active')
            ->get();

        $departmentName = DB::table('department_units')
            ->where('department_unit_id', $purchaseRequest->department_unit_id)
            ->value('name') ?: 'Unassigned Unit';

        $baseMessage = 'Purchase request #' . $purchaseRequest->id . ' was ' . strtolower($status) . '.';
        if ($reason) {
            $baseMessage .= ' Reason: ' . $reason;
        }

        $recipients = collect([$owner])
            ->filter()
            ->merge($bacUsers)
            ->unique('id');

        foreach ($recipients as $recipient) {
            $message = $recipient->role === 'bac'
                ? 'Purchase request #' . $purchaseRequest->id . ' for ' . $departmentName . ' was ' . strtolower($status) . '.' . ($reason ? ' Reason: ' . $reason : '')
                : $baseMessage;

            Notification::create([
                'user_id' => $recipient->user_id,
                'type' => 'purchase_request',
                'reference_id' => $purchaseRequest->id,
                'status' => $status,
                'title' => 'Purchase Request ' . $status,
                'message' => $message,
                'is_read' => false,
            ]);

            if (!empty($recipient->email)) {
                try {
                    Mail::raw($message, function ($mail) use ($recipient, $status) {
                        $mail->to($recipient->email)
                            ->subject('ConsoliData Purchase Request ' . $status);
                    });
                } catch (\Throwable $exception) {
                    // Notification record is the primary channel; email failures should not block approval flow.
                }
            }
        }
    }
}
