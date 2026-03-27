<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Ppmp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PpmpController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->baseQuery();

        if ($request->filled('status')) {
            $query->where('ppmps.status', $request->status);
        }

        if ($request->filled('department_unit_id')) {
            $query->where('ppmps.department_unit_id', $request->department_unit_id);
        }

        $ppmps = $query->orderByDesc('ppmps.ppmp_id')->get();
        $departments = DB::table('department_units')
            ->selectRaw('department_unit_id as id, name')
            ->orderBy('name')
            ->get();
        $summary = [
            'total' => DB::table('ppmps')->count(),
            'submitted' => DB::table('ppmps')->where('status', 'Submitted')->count(),
            'approved' => DB::table('ppmps')->where('status', 'Approved')->count(),
            'draft' => DB::table('ppmps')->where('status', 'Draft')->count(),
        ];

        return view('admin.ppmp.validate', compact('ppmps', 'departments', 'summary'));
    }

    public function show($id)
    {
        $ppmp = $this->baseQuery()
            ->where('ppmps.ppmp_id', $id)
            ->firstOrFail();

        $items = DB::table('ppmp_items')
            ->leftJoin('categories', 'ppmp_items.category_id', '=', 'categories.category_id')
            ->where('ppmp_items.ppmp_id', $id)
            ->select('ppmp_items.*', 'categories.name as category_name')
            ->orderBy('ppmp_items.ppmp_item_id')
            ->get();

        $totals = [
            'items_count' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'total_budget' => $items->sum('estimated_budget'),
            'q1_total' => $items->sum('q1_total_cost'),
            'q2_total' => $items->sum('q2_total_cost'),
            'q3_total' => $items->sum('q3_total_cost'),
            'q4_total' => $items->sum('q4_total_cost'),
        ];

        $reviewerName = null;
        if ($ppmp->reviewed_by) {
            $reviewerName = DB::table('users')
                ->where('user_id', $ppmp->reviewed_by)
                ->value('name');
        }

        return view('admin.ppmp.show', compact('ppmp', 'items', 'totals', 'reviewerName'));
    }

    public function review($id)
    {
        $ppmp = $this->baseQuery()
            ->where('ppmps.ppmp_id', $id)
            ->firstOrFail();

        if ($ppmp->status !== 'Submitted') {
            return redirect()->route('admin.ppmp.validate')
                ->with('error', 'Only submitted PPMP records can be reviewed.');
        }

        return view('admin.ppmp.review', compact('ppmp'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'ppmp_no' => 'required|string|max:255|unique:ppmps,ppmp_no',
        ]);

        $ppmp = Ppmp::query()
            ->whereKey($id)
            ->where('status', 'Submitted')
            ->first();

        if (!$ppmp) {
            return back()->withErrors([
                'error' => 'Only submitted PPMP records can be approved.',
            ]);
        }

        $ppmp->update([
            'status' => 'Approved',
            'ppmp_no' => trim((string) $request->ppmp_no),
            'review_remarks' => null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->notifyPpmpStatus($ppmp, 'Approved');

        return redirect()->route('admin.ppmp.validate')
            ->with('success', 'PPMP approved. Next: passed to unit to create purchase requests.');
    }

    public function disapprove(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $ppmp = Ppmp::query()
            ->whereKey($id)
            ->where('status', 'Submitted')
            ->first();

        if (!$ppmp) {
            return back()->withErrors([
                'error' => 'Only submitted PPMP records can be returned to draft.',
            ]);
        }

        $ppmp->update([
            'status' => 'Draft',
            'review_remarks' => $request->reason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->notifyPpmpStatus($ppmp, 'Returned to Draft', $request->reason);

        return redirect()->route('admin.ppmp.validate')
            ->with('success', 'PPMP returned to draft. Unit will revise and resubmit.');
    }

    public function consolidated()
    {
        $rows = $this->consolidatedRowsQuery()->get();

        return view('admin.ppmp.consolidated', compact('rows'));
    }

    public function printConsolidated(Request $request)
    {
        $rows = $this->consolidatedRowsQuery()->get();
        $uniqueFundSources = $rows->pluck('fund_source_name')
            ->filter(fn($value) => !empty($value) && $value !== 'Unassigned')
            ->unique()
            ->values();

        $fundCluster = match (true) {
            $uniqueFundSources->count() === 1 => $uniqueFundSources->first(),
            $uniqueFundSources->count() > 1 => 'Multiple Fund Sources',
            default => 'Unassigned',
        };

        return view('prints.consolidated-ppmp', [
            'rows' => $rows,
            'generatedAt' => now(),
            'generatedBy' => Auth::user()?->name ?? 'System',
            'fundCluster' => $fundCluster,
            'reportNumber' => 'CPPMP-' . now()->format('Ymd') . '-001',
            'totalPpmps' => $rows->sum('total_ppmps'),
        ]);
    }

    public function unit()
    {
        $ppmps = $this->baseQuery()
            ->orderBy('department_name')
            ->orderByDesc('ppmps.ppmp_id')
            ->get();

        return view('admin.ppmp.unit', compact('ppmps'));
    }

    private function baseQuery()
    {
        return Ppmp::query()
            ->leftJoin('users', 'ppmps.user_id', '=', 'users.user_id')
            ->leftJoin('department_units', 'ppmps.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('fund_sources', 'ppmps.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->select(
                'ppmps.*',
                DB::raw('ppmps.ppmp_id as id'),
                'users.name as user_name',
                'users.email as user_email',
                'department_units.name as department_name',
                'fund_sources.name as fund_source_name',
                DB::raw('(select count(*) from ppmp_items where ppmp_items.ppmp_id = ppmps.ppmp_id) as items_count'),
                DB::raw('(select coalesce(sum(ppmp_items.estimated_budget), 0) from ppmp_items where ppmp_items.ppmp_id = ppmps.ppmp_id) as total_estimated_budget')
            );
    }

    private function consolidatedRowsQuery()
    {
        return DB::table('ppmps')
            ->leftJoin('department_units', 'ppmps.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('fund_sources', 'ppmps.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->select(
                DB::raw('COALESCE(department_units.name, "Unassigned") as department_name'),
                DB::raw('COALESCE(fund_sources.name, "Unassigned") as fund_source_name'),
                'ppmps.status',
                DB::raw('COUNT(*) as total_ppmps')
            )
            ->groupBy('department_units.name', 'fund_sources.name', 'ppmps.status')
            ->orderBy('department_units.name')
            ->orderBy('fund_sources.name');
    }

    private function notifyPpmpStatus(Ppmp $ppmp, string $status, ?string $reason = null): void
    {
        $owner = User::find($ppmp->user_id);

        if (!$owner) {
            return;
        }

        $message = $this->ppmpReferenceLabel($ppmp) . ' was ' . strtolower($status) . '.';
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        Notification::create([
            'user_id' => $owner->user_id,
            'type' => 'ppmp',
            'reference_id' => $ppmp->id,
            'status' => $status,
            'title' => 'PPMP ' . $status,
            'message' => $message,
            'is_read' => false,
        ]);

        if (!empty($owner->email)) {
            try {
                Mail::raw($message, function ($mail) use ($owner, $status) {
                    $mail->to($owner->email)
                        ->subject('ConsoliData PPMP ' . $status);
                });
            } catch (\Throwable $exception) {
                // In-app notification already preserves the status update.
            }
        }
    }

    private function ppmpReferenceLabel(Ppmp $ppmp): string
    {
        return $ppmp->ppmp_no
            ? 'PPMP ' . $ppmp->ppmp_no
            : 'PPMP draft ref #' . $ppmp->id;
    }
}
