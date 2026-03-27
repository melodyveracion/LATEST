<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Ppmp;
use App\Models\PpmpItem;
use App\Models\PresetItem;
use App\Support\UnitFundDepartmentResolver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PpmpController extends Controller
{
    public function index(Request $request)
    {
        $fundSources = $this->accessibleFundSources();
        $categories = $this->categories();
        $activeFundSourceId = $this->activeFundSourceId($fundSources);

        $query = Ppmp::query()
            ->leftJoin('department_units', 'ppmps.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('fund_sources', 'ppmps.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->where('ppmps.user_id', Auth::id())
            ->select(
                'ppmps.*',
                'department_units.name as department_name',
                'fund_sources.name as fund_source_name',
                DB::raw('(select count(*) from ppmp_items where ppmp_items.ppmp_id = ppmps.ppmp_id) as items_count'),
                DB::raw('(select coalesce(sum(ppmp_items.estimated_budget), 0) from ppmp_items where ppmp_items.ppmp_id = ppmps.ppmp_id) as total_estimated_budget')
            )
            ->orderByDesc('ppmps.ppmp_id');

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

        if ($request->filled('status')) {
            $query->where('ppmps.status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                    ->from('ppmp_items')
                    ->whereColumn('ppmp_items.ppmp_id', 'ppmps.ppmp_id')
                    ->where('ppmp_items.category_id', (int) $request->category_id);
            });
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('ppmps.fiscal_year', 'like', $term)
                    ->orWhere('ppmps.ppmp_no', 'like', $term)
                    ->orWhere('ppmps.status', 'like', $term)
                    ->orWhere('fund_sources.name', 'like', $term);
            });
        }

        $ppmps = $query->get();

        return view('unit.ppmp.index', compact('ppmps', 'fundSources', 'categories'));
    }

    public function create()
    {
        $fundSources = $this->accessibleFundSources();

        return view('unit.ppmp.create', [
            'departmentName' => $this->departmentName(Auth::user()->department_unit_id),
            'fundSourceName' => $this->currentFundSourceScope($fundSources),
            'fundSources' => $fundSources,
            'activeFundSourceId' => $this->activeFundSourceId($fundSources),
            'currentYear' => now()->year,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $fundSources = $this->accessibleFundSources($user);

        if (!$user->department_unit_id || $fundSources->isEmpty()) {
            return back()->withErrors([
                'error' => 'Your account must have an assigned unit with at least one fund source before creating a PPMP.',
            ]);
        }

        $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'fund_source_id' => 'required|integer',
        ]);

        $selectedFundSource = $this->resolveAccessibleFundSource((int) $request->fund_source_id, $fundSources);

        if (!$selectedFundSource) {
            return back()->withErrors([
                'fund_source_id' => 'Select a valid fund source for this PPMP.',
            ])->withInput();
        }

        $status = $this->normalizeStatus($request->input('status') === 'Submitted' ? 'Submitted' : 'Draft');

        $ppmp = Ppmp::create([
            'user_id' => $user->id,
            'department_unit_id' => $selectedFundSource->department_unit_id,
            'fund_source_id' => $selectedFundSource->id,
            'fiscal_year' => $request->fiscal_year,
            'status' => $status,
            'submitted_at' => $status === 'Submitted' ? now() : null,
            'review_remarks' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        if ($status === 'Submitted') {
            $this->notifyReviewers(
                $ppmp,
                'PPMP Submitted',
                $this->ppmpReferenceLabel($ppmp) . ' was submitted by ' . $user->name . ' for review.'
            );
        }

        return redirect()
            ->route('unit.ppmp.edit', $ppmp->id)
            ->with('success', 'PPMP created successfully. You can now add items.');
    }

    public function showUploadForm()
    {
        $fundSources = $this->accessibleFundSources();

        return view('unit.ppmp.upload', [
            'departmentName' => $this->departmentName(Auth::user()->department_unit_id),
            'fundSourceName' => $this->currentFundSourceScope($fundSources),
            'fundSources' => $fundSources,
            'activeFundSourceId' => $this->activeFundSourceId($fundSources),
            'currentYear' => now()->year,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'fund_source_id' => 'required|integer',
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $user = Auth::user();
        $fundSources = $this->accessibleFundSources($user);

        if (!$user->department_unit_id || $fundSources->isEmpty()) {
            return back()->withErrors([
                'csv_file' => 'Your account must have an assigned unit with at least one fund source before uploading a PPMP.',
            ]);
        }

        $selectedFundSource = $this->resolveAccessibleFundSource((int) $request->fund_source_id, $fundSources);

        if (!$selectedFundSource) {
            return back()->withErrors([
                'fund_source_id' => 'Select a valid fund source for this PPMP upload.',
            ])->withInput();
        }

        $ppmp = Ppmp::create([
            'user_id' => $user->id,
            'department_unit_id' => $selectedFundSource->department_unit_id,
            'fund_source_id' => $selectedFundSource->id,
            'fiscal_year' => $request->fiscal_year,
            'status' => $this->normalizeStatus('Submitted'),
            'submitted_at' => now(),
            'review_remarks' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $file = fopen($request->file('csv_file')->getRealPath(), 'r');
        $headerMap = null;
        $imported = 0;

        while (($row = fgetcsv($file)) !== false) {
            $row = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            if ($headerMap === null && $this->looksLikeHeader($row)) {
                $headerMap = array_map(fn ($value) => strtolower((string) $value), $row);
                continue;
            }

            $itemData = $this->extractItemData($row, $headerMap);

            if ($itemData['description'] === '') {
                continue;
            }

            PpmpItem::create([
                'ppmp_id' => $ppmp->id,
                'category_id' => $itemData['category_id'],
                'item_name' => $itemData['item_name'],
                'specifications' => $itemData['specifications'],
                'uacs_code' => $itemData['uacs_code'],
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit' => $itemData['unit'],
                'unit_cost' => $itemData['unit_cost'],
                'estimated_budget' => $itemData['estimated_budget'],
                'mode_of_procurement' => $itemData['mode_of_procurement'] ?: 'N/A',
                'quantity_q1' => $itemData['quantity_q1'],
                'quantity_q2' => $itemData['quantity_q2'],
                'quantity_q3' => $itemData['quantity_q3'],
                'quantity_q4' => $itemData['quantity_q4'],
                'q1_total_cost' => $itemData['q1_total_cost'],
                'q2_total_cost' => $itemData['q2_total_cost'],
                'q3_total_cost' => $itemData['q3_total_cost'],
                'q4_total_cost' => $itemData['q4_total_cost'],
            ]);

            $imported++;
        }

        fclose($file);

        if ($imported === 0) {
            $ppmp->delete();

            return back()->withErrors([
                'csv_file' => 'No valid PPMP items were found in the uploaded file.',
            ]);
        }

        $this->notifyReviewers(
            $ppmp,
            'PPMP Submitted',
            $this->ppmpReferenceLabel($ppmp) . ' was uploaded and submitted by ' . $user->name . ' for review.'
        );

        return redirect()
            ->route('unit.ppmp.edit', $ppmp->id)
            ->with('success', 'PPMP uploaded successfully and submitted for validation.');
    }

    public function edit($id)
    {
        $ppmp = Ppmp::where('user_id', Auth::id())->findOrFail($id);
        $items = PpmpItem::where('ppmp_id', $ppmp->id)
            ->latest()
            ->get();

        return view('unit.ppmp.edit', [
            'ppmp' => $ppmp,
            'items' => $items,
            'categories' => $this->categories(),
            'presetItems' => $this->presetItems(),
            'departmentName' => $this->departmentName($ppmp->department_unit_id),
            'fundSourceName' => $this->fundSourceName($ppmp->fund_source_id),
        ]);
    }

    public function addItem(Request $request, $id)
    {
        $ppmp = Ppmp::where('user_id', Auth::id())->findOrFail($id);

        if ($request->input('action_type') === 'submit') {
            if ($ppmp->status !== 'Draft') {
                return back()->withErrors([
                    'error' => 'Only draft PPMPs can be submitted for validation.',
                ]);
            }

            if (!PpmpItem::where('ppmp_id', $ppmp->id)->exists()) {
                return back()->withErrors([
                    'error' => 'Add at least one PPMP item before submitting.',
                ]);
            }

            $ppmp->update([
                'status' => $this->normalizeStatus('Submitted'),
                'submitted_at' => now(),
                'review_remarks' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);

            $this->notifyReviewers(
                $ppmp,
                'PPMP Submitted',
                $this->ppmpReferenceLabel($ppmp) . ' was submitted by ' . Auth::user()->name . ' for review.'
            );

            return back()->with('success', 'PPMP submitted for validation.');
        }

        if ($ppmp->status !== 'Draft') {
            return back()->withErrors([
                'error' => 'Only draft PPMPs can be edited.',
            ]);
        }

        $request->validate([
            'preset_item_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'uacs_code' => 'nullable|string|max:255',
            'item_name' => 'nullable|string|max:255',
            'specifications' => 'nullable|string|max:1000',
            'quantity_q1' => 'required|integer|min:0',
            'quantity_q2' => 'required|integer|min:0',
            'quantity_q3' => 'required|integer|min:0',
            'quantity_q4' => 'required|integer|min:0',
            'unit' => 'nullable|string|max:100',
            'unit_cost' => 'nullable|numeric|min:0',
            'mode_of_procurement' => 'nullable|string|max:255',
        ]);

        $presetItem = $request->filled('preset_item_id')
            ? $this->findPresetItem((int) $request->preset_item_id)
            : null;

        if ($request->filled('preset_item_id') && !$presetItem) {
            return back()->withErrors([
                'preset_item_id' => 'The selected preset item was not found.',
            ])->withInput();
        }

        $itemName = trim((string) ($request->item_name ?: ($presetItem->item_name ?? '')));

        if ($itemName === '') {
            return back()->withErrors([
                'item_name' => 'Select an item from the dropdown or enter an item name.',
            ])->withInput();
        }

        if (!$request->filled('unit_cost') && !$presetItem) {
            return back()->withErrors([
                'unit_cost' => 'Select a preset item or enter a unit cost.',
            ])->withInput();
        }

        $categoryId = $request->filled('category_id')
            ? (int) $request->category_id
            : (int) ($presetItem->category_id ?? 0);

        if ($categoryId <= 0) {
            return back()->withErrors([
                'category_id' => 'Select a category or choose a preset item with a category.',
            ])->withInput();
        }

        $uacsCode = $request->filled('uacs_code')
            ? $request->uacs_code
            : (string) ($presetItem->part_label ?? '');

        $unit = $request->filled('unit')
            ? $request->unit
            : (string) ($presetItem->unit ?? '');

        $unitCost = $request->filled('unit_cost')
            ? (float) $request->unit_cost
            : $this->parseDecimal($presetItem->price ?? 0);

        $quantityQ1 = (int) $request->quantity_q1;
        $quantityQ2 = (int) $request->quantity_q2;
        $quantityQ3 = (int) $request->quantity_q3;
        $quantityQ4 = (int) $request->quantity_q4;
        $totalQuantity = $quantityQ1 + $quantityQ2 + $quantityQ3 + $quantityQ4;

        if ($totalQuantity <= 0) {
            return back()->withErrors([
                'quantity_q1' => 'Enter at least one quantity in Q1, Q2, Q3, or Q4.',
            ])->withInput();
        }

        $q1Total = $quantityQ1 * $unitCost;
        $q2Total = $quantityQ2 * $unitCost;
        $q3Total = $quantityQ3 * $unitCost;
        $q4Total = $quantityQ4 * $unitCost;
        $estimatedBudget = $q1Total + $q2Total + $q3Total + $q4Total;

        PpmpItem::create([
            'ppmp_id' => $ppmp->id,
            'category_id' => $categoryId,
            'item_name' => $itemName,
            'specifications' => $request->specifications,
            'uacs_code' => $uacsCode !== '' ? $uacsCode : null,
            'description' => $itemName,
            'quantity' => $totalQuantity,
            'unit' => $unit !== '' ? $unit : null,
            'unit_cost' => $unitCost,
            'estimated_budget' => $estimatedBudget,
            'mode_of_procurement' => $request->mode_of_procurement ?: 'N/A',
            'quantity_q1' => $quantityQ1,
            'quantity_q2' => $quantityQ2,
            'quantity_q3' => $quantityQ3,
            'quantity_q4' => $quantityQ4,
            'q1_total_cost' => $q1Total,
            'q2_total_cost' => $q2Total,
            'q3_total_cost' => $q3Total,
            'q4_total_cost' => $q4Total,
        ]);

        return back()->with('success', 'PPMP item added successfully.');
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

    private function accessibleFundSources(?User $user = null): Collection
    {
        $user ??= Auth::user();

        return UnitFundDepartmentResolver::fundSourcesForDepartment($user?->department_unit_id);
    }

    private function activeFundSourceId(?Collection $fundSources = null): ?int
    {
        $fundSources ??= $this->accessibleFundSources();
        $activeFundSourceId = (int) session('unit_active_fund_source_id');

        $activeFundSource = $fundSources->first(
            fn ($fundSource) => (int) $fundSource->id === $activeFundSourceId
        );

        return $activeFundSource ? (int) $activeFundSource->id : null;
    }

    private function currentFundSourceScope(?Collection $fundSources = null): ?string
    {
        $fundSources ??= $this->accessibleFundSources();
        $activeFundSourceId = $this->activeFundSourceId($fundSources);

        if ($activeFundSourceId) {
            return $fundSources->firstWhere('id', $activeFundSourceId)?->name;
        }

        if ($fundSources->isEmpty()) {
            return null;
        }

        return $fundSources->count() === 1
            ? $fundSources->first()->name
            : 'All fund sources';
    }

    private function resolveAccessibleFundSource(int $fundSourceId, ?Collection $fundSources = null): ?object
    {
        $fundSources ??= $this->accessibleFundSources();

        return $fundSources->first(
            fn ($fundSource) => (int) $fundSource->id === $fundSourceId
        );
    }

    private function ppmpReferenceLabel(Ppmp $ppmp): string
    {
        return $ppmp->ppmp_no
            ? 'PPMP ' . $ppmp->ppmp_no
            : 'PPMP draft ref #' . $ppmp->id;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ((string) $value !== '') {
                return false;
            }
        }

        return true;
    }

    private function looksLikeHeader(array $row): bool
    {
        $joined = strtolower(implode(',', $row));

        return str_contains($joined, 'description')
            || str_contains($joined, 'uacs')
            || str_contains($joined, 'unit_cost')
            || str_contains($joined, 'mode_of_procurement');
    }

    private function extractItemData(array $row, ?array $headerMap): array
    {
        if ($headerMap) {
            $data = [];

            foreach ($headerMap as $index => $column) {
                $data[$column] = $row[$index] ?? null;
            }

            $itemName = trim((string) ($data['item_name'] ?? $data['description'] ?? ''));
            $presetItem = $itemName !== '' ? $this->findPresetItemByName($itemName) : null;

            $quantityQ1 = $this->parseInteger($data['quantity_q1'] ?? $data['q1'] ?? 0);
            $quantityQ2 = $this->parseInteger($data['quantity_q2'] ?? $data['q2'] ?? 0);
            $quantityQ3 = $this->parseInteger($data['quantity_q3'] ?? $data['q3'] ?? 0);
            $quantityQ4 = $this->parseInteger($data['quantity_q4'] ?? $data['q4'] ?? 0);
            $quantity = $this->parseInteger($data['quantity'] ?? 0);

            if (($quantityQ1 + $quantityQ2 + $quantityQ3 + $quantityQ4) === 0 && $quantity > 0) {
                $quantityQ1 = $quantity;
            }

            $unitCost = $this->parseDecimal($data['unit_cost'] ?? $data['unit price'] ?? $data['price'] ?? ($presetItem->price ?? 0));
            $estimatedBudget = $data['estimated_budget'] ?? $data['estimated cost'] ?? null;
            $q1Total = $data['q1_total_cost'] ?? null;
            $q2Total = $data['q2_total_cost'] ?? null;
            $q3Total = $data['q3_total_cost'] ?? null;
            $q4Total = $data['q4_total_cost'] ?? null;

            return [
                'category_id' => $this->normalizeCategoryId($data['category_id'] ?? $presetItem->category_id ?? null),
                'item_name' => $itemName,
                'specifications' => (string) ($data['specifications'] ?? $data['description'] ?? ''),
                'uacs_code' => (string) ($data['uacs_code'] ?? $data['uacs'] ?? $presetItem->part_label ?? ''),
                'description' => $itemName,
                'quantity' => max(1, $quantityQ1 + $quantityQ2 + $quantityQ3 + $quantityQ4),
                'quantity_q1' => max(0, $quantityQ1),
                'quantity_q2' => max(0, $quantityQ2),
                'quantity_q3' => max(0, $quantityQ3),
                'quantity_q4' => max(0, $quantityQ4),
                'unit' => (string) ($data['unit'] ?? $presetItem->unit ?? ''),
                'unit_cost' => $unitCost,
                'estimated_budget' => $estimatedBudget !== null && $estimatedBudget !== ''
                    ? $this->parseDecimal($estimatedBudget)
                    : (($quantityQ1 + $quantityQ2 + $quantityQ3 + $quantityQ4) * $unitCost),
                'q1_total_cost' => $q1Total !== null && $q1Total !== '' ? $this->parseDecimal($q1Total) : ($quantityQ1 * $unitCost),
                'q2_total_cost' => $q2Total !== null && $q2Total !== '' ? $this->parseDecimal($q2Total) : ($quantityQ2 * $unitCost),
                'q3_total_cost' => $q3Total !== null && $q3Total !== '' ? $this->parseDecimal($q3Total) : ($quantityQ3 * $unitCost),
                'q4_total_cost' => $q4Total !== null && $q4Total !== '' ? $this->parseDecimal($q4Total) : ($quantityQ4 * $unitCost),
                'mode_of_procurement' => (string) ($data['mode_of_procurement'] ?? $data['mode'] ?? ''),
            ];
        }

        $itemName = trim((string) ($row[1] ?? ''));
        $presetItem = $itemName !== '' ? $this->findPresetItemByName($itemName) : null;
        $quantityQ1 = $this->parseInteger($row[2] ?? 0);
        $quantityQ2 = $this->parseInteger($row[3] ?? 0);
        $quantityQ3 = $this->parseInteger($row[4] ?? 0);
        $quantityQ4 = $this->parseInteger($row[5] ?? 0);
        $unitCost = $this->parseDecimal($row[7] ?? $presetItem->price ?? 0);

        return [
            'category_id' => $this->normalizeCategoryId($row[6] ?? $presetItem->category_id ?? null),
            'item_name' => $itemName,
            'specifications' => (string) ($row[8] ?? ''),
            'uacs_code' => (string) ($row[0] ?? $presetItem->part_label ?? ''),
            'description' => $itemName,
            'quantity' => max(1, $quantityQ1 + $quantityQ2 + $quantityQ3 + $quantityQ4),
            'quantity_q1' => max(0, $quantityQ1),
            'quantity_q2' => max(0, $quantityQ2),
            'quantity_q3' => max(0, $quantityQ3),
            'quantity_q4' => max(0, $quantityQ4),
            'unit' => (string) ($row[9] ?? $presetItem->unit ?? ''),
            'unit_cost' => $unitCost,
            'estimated_budget' => isset($row[10]) && $row[10] !== ''
                ? $this->parseDecimal($row[10])
                : (($quantityQ1 + $quantityQ2 + $quantityQ3 + $quantityQ4) * $unitCost),
            'q1_total_cost' => $quantityQ1 * $unitCost,
            'q2_total_cost' => $quantityQ2 * $unitCost,
            'q3_total_cost' => $quantityQ3 * $unitCost,
            'q4_total_cost' => $quantityQ4 * $unitCost,
            'mode_of_procurement' => (string) ($row[11] ?? 'N/A'),
        ];
    }

    private function parseInteger($value): int
    {
        return (int) preg_replace('/[^0-9\-]/', '', (string) $value);
    }

    private function parseDecimal($value): float
    {
        return (float) preg_replace('/[^0-9.\-]/', '', (string) $value);
    }

    private function normalizeStatus(string $preferredStatus): string
    {
        $allowedStatuses = $this->allowedStatuses();

        if (in_array($preferredStatus, $allowedStatuses, true)) {
            return $preferredStatus;
        }

        if (in_array('Submitted', $allowedStatuses, true)) {
            return 'Submitted';
        }

        return $allowedStatuses[0] ?? $preferredStatus;
    }

    private function allowedStatuses(): array
    {
        try {
            $columns = DB::select("SHOW COLUMNS FROM ppmps LIKE 'status'");
            $type = $columns[0]->Type ?? '';

            preg_match_all("/'([^']+)'/", $type, $matches);

            return $matches[1] ?? [];
        } catch (\Throwable $exception) {
            return ['Draft', 'Submitted', 'Approved', 'Disapproved'];
        }
    }

    private function presetItems()
    {
        if (!Schema::hasTable('preset_items')) {
            return collect();
        }

        return PresetItem::query()
            ->select('project_id', 'category_id', 'part_label', 'item_name', 'unit', 'price')
            ->orderBy('item_name')
            ->get();
    }

    private function findPresetItem(int $id): ?object
    {
        if (!Schema::hasTable('preset_items')) {
            return null;
        }

        return PresetItem::query()
            ->select('project_id', 'category_id', 'part_label', 'item_name', 'unit', 'price')
            ->find($id);
    }

    private function findPresetItemByName(string $itemName): ?object
    {
        if (!Schema::hasTable('preset_items')) {
            return null;
        }

        return PresetItem::query()
            ->select('project_id', 'category_id', 'part_label', 'item_name', 'unit', 'price')
            ->where('item_name', $itemName)
            ->first();
    }

    private function categories()
    {
        if (!Schema::hasTable('categories')) {
            return collect();
        }

        return Category::query()->orderBy('name')->get();
    }

    private function notifyReviewers(Ppmp $ppmp, string $title, string $message): void
    {
        $recipients = User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get();

        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'type' => 'ppmp_submission',
                'reference_id' => $ppmp->id,
                'status' => $ppmp->status,
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
                    // Notification record remains the guaranteed channel.
                }
            }
        }
    }

    private function normalizeCategoryId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value ?: null;
    }
}
