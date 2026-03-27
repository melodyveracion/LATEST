<?php

namespace App\Http\Controllers\Bac;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $purchaseRequests = $this->baseQuery()
            ->orderByDesc('purchase_requests.pr_id')
            ->get();

        return view('bac.requests.index', compact('purchaseRequests'));
    }

    public function uploadNoticeIndex()
    {
        $purchaseRequests = $this->baseQuery()
            ->whereIn('purchase_requests.status', ['Confirmed', 'Completed', 'On-Going'])
            ->orderByDesc('purchase_requests.pr_id')
            ->get();

        $formLabels = [
            'awarded' => 'Awarded',
            'canceled' => 'Canceled',
            'failed' => 'Failed',
            'on_going' => 'On-going',
        ];
        return view('bac.upload-notice.index', [
            'purchaseRequests' => $purchaseRequests,
            'noticeTypes' => self::BAC_NOTICE_TYPES,
            'noticeLabels' => $formLabels,
        ]);
    }

    public function show($id)
    {
        $purchaseRequest = $this->baseQuery()
            ->where('purchase_requests.pr_id', $id)
            ->firstOrFail();

        $items = PurchaseRequestItem::query()
            ->leftJoin('categories', 'purchase_request_items.category_id', '=', 'categories.category_id')
            ->select('purchase_request_items.*', 'categories.name as category_name')
            ->where('purchase_request_id', $purchaseRequest->id)
            ->orderBy('purchase_request_items.pr_item_id')
            ->get();

        return view('bac.requests.show', compact('purchaseRequest', 'items'));
    }

    public function print($id)
    {
        $purchaseRequest = $this->baseQuery()
            ->where('purchase_requests.pr_id', $id)
            ->firstOrFail();

        $items = PurchaseRequestItem::query()
            ->leftJoin('categories', 'purchase_request_items.category_id', '=', 'categories.category_id')
            ->select('purchase_request_items.*', 'categories.name as category_name')
            ->where('purchase_request_id', $purchaseRequest->id)
            ->orderBy('purchase_request_items.pr_item_id')
            ->get();

        $reviewer = $purchaseRequest->reviewed_by ? User::find($purchaseRequest->reviewed_by) : null;
        $isApprovedState = in_array($purchaseRequest->status, ['Approved', 'Confirmed', 'Completed'], true);

        return view('prints.purchase-request', [
            'purchaseRequest' => $purchaseRequest,
            'items' => $items,
            'departmentName' => $purchaseRequest->department_name,
            'fundSourceName' => $purchaseRequest->fund_source_name,
            'requesterName' => $purchaseRequest->user_name ?? 'Unknown User',
            'requesterDesignation' => $purchaseRequest->department_name ?: 'Unit / College Requesting Office',
            'approverName' => $isApprovedState ? $reviewer?->name : null,
            'approverDesignation' => $isApprovedState && $reviewer ? ucfirst((string) $reviewer->role) : null,
            'documentDate' => $purchaseRequest->submitted_at ?: $purchaseRequest->created_at,
            'prNumber' => $this->formattedPurchaseRequestNumber($purchaseRequest),
            'responsibilityCenterCode' => $purchaseRequest->ppmp_no
                ?: ($purchaseRequest->ppmp_id
                    ? 'PPMP-' . str_pad((string) $purchaseRequest->ppmp_id, 3, '0', STR_PAD_LEFT)
                    : null),
        ]);
    }

    private const BAC_NOTICE_TYPES = ['awarded', 'canceled', 'failed', 'on_going'];

    private const NOTICE_TO_STATUS = [
        'awarded' => 'Completed',
        'canceled' => 'Canceled',
        'failed' => 'Disapproved',
        'on_going' => 'On-Going',
    ];

    private const NOTICE_LABELS = [
        'awarded' => 'Award notice',
        'canceled' => 'Canceled notice',
        'failed' => 'Failure notice',
        'on_going' => 'On-going notice',
    ];

    public function uploadNotice(Request $request, $id)
    {
        $request->validate([
            'notice_type' => 'required|in:' . implode(',', self::BAC_NOTICE_TYPES),
            'notice_file' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:5120',
        ]);

        $purchaseRequest = PurchaseRequest::findOrFail($id);

        if (!in_array($purchaseRequest->status, ['Confirmed', 'Completed', 'On-Going'], true)) {
            return back()->withErrors([
                'error' => 'BAC can upload notices only after the unit confirms the approved purchase request.',
            ]);
        }

        if ($this->hasExistingNotice($purchaseRequest)) {
            return back()->withErrors([
                'error' => 'A BAC notice already exists for this purchase request.',
            ]);
        }

        $path = $request->file('notice_file')->store('bac-notices', 'public');
        $noticeType = $request->notice_type;
        $newStatus = self::NOTICE_TO_STATUS[$noticeType];
        $label = self::NOTICE_LABELS[$noticeType];

        $updates = [
            'bac_notice_type' => $noticeType,
            'bac_notice_path' => $path,
            'status' => $newStatus,
        ];
        if ($noticeType === 'awarded' && Schema::hasColumn('purchase_requests', 'award_notice_path')) {
            $updates['award_notice_path'] = $path;
        }
        if ($noticeType === 'failed' && Schema::hasColumn('purchase_requests', 'failure_notice_path')) {
            $updates['failure_notice_path'] = $path;
        }

        $purchaseRequest->update($updates);

        $message = "A {$label} was uploaded for purchase request #{$purchaseRequest->id}.";

        $owner = User::find($purchaseRequest->user_id);
        if ($owner) {
            Notification::create([
                'user_id' => $owner->id,
                'type' => 'purchase_request_notice',
                'reference_id' => $purchaseRequest->id,
                'status' => $purchaseRequest->status,
                'title' => 'BAC Notice Uploaded',
                'message' => $message,
                'is_read' => false,
            ]);

            if (!empty($owner->email)) {
                try {
                    Mail::raw($message, function ($mail) use ($owner) {
                        $mail->to($owner->email)
                            ->subject('ConsoliData BAC Notice Update');
                    });
                } catch (\Throwable $exception) {
                    // Keep uploaded notice even when email sending is unavailable.
                }
            }
        }

        return back()->with('success', 'Notice uploaded successfully.');
    }

    public function profile()
    {
        return view('bac.profile');
    }

    private function baseQuery()
    {
        return PurchaseRequest::query()
            ->leftJoin('users', 'purchase_requests.user_id', '=', 'users.user_id')
            ->leftJoin('department_units', 'purchase_requests.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('fund_sources', 'purchase_requests.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->leftJoin('ppmps', 'purchase_requests.ppmp_id', '=', 'ppmps.ppmp_id')
            ->whereIn('purchase_requests.status', ['Approved', 'Confirmed', 'Completed', 'Disapproved', 'Canceled', 'On-Going'])
            ->select(
                'purchase_requests.*',
                'users.name as user_name',
                'users.email as user_email',
                'department_units.name as department_name',
                'fund_sources.name as fund_source_name',
                'ppmps.ppmp_no',
                DB::raw('(select count(*) from purchase_request_items where purchase_request_items.purchase_request_id = purchase_requests.pr_id) as items_count'),
                DB::raw('(select coalesce(sum(purchase_request_items.estimated_budget), 0) from purchase_request_items where purchase_request_items.purchase_request_id = purchase_requests.pr_id) as total_estimated_budget')
            );
    }

    private function hasExistingNotice(PurchaseRequest $pr): bool
    {
        if (!empty($pr->bac_notice_path)) {
            return true;
        }
        if (Schema::hasColumn('purchase_requests', 'award_notice_path') && !empty($pr->award_notice_path)) {
            return true;
        }
        if (Schema::hasColumn('purchase_requests', 'failure_notice_path') && !empty($pr->failure_notice_path)) {
            return true;
        }
        return false;
    }

    private function formattedPurchaseRequestNumber(PurchaseRequest $purchaseRequest): string
    {
        $date = $purchaseRequest->submitted_at ?: $purchaseRequest->created_at ?: now();
        $documentDate = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $documentDate->format('Y-m-d') . '-' . str_pad((string) $purchaseRequest->id, 3, '0', STR_PAD_LEFT);
    }
}
