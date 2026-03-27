<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // =========================
    // VIEW USERS
    // =========================
    public function index()
    {
        $users = User::query()
            ->leftJoin('department_units', 'users.department_unit_id', '=', 'department_units.department_unit_id')
            ->leftJoin('fund_sources', 'users.fund_source_id', '=', 'fund_sources.fund_src_id')
            ->where('users.role', '!=', 'admin')
            ->select(
                'users.*',
                'department_units.name as department_name',
                'fund_sources.name as fund_source_name'
            )
            ->orderBy('users.name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    // =========================
    // SHOW CREATE FORM
    // =========================
    public function create()
    {
        $departments = DB::table('department_units')
            ->selectRaw('department_unit_id as id, name')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.users.create', compact('departments'));
    }

    // =========================
    // SHOW EDIT FORM
    // =========================
    public function edit($id)
    {
        $user = $this->findManageableUserOrFail((int) $id);

        $departments = DB::table('department_units')
            ->selectRaw('department_unit_id as id, name')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.users.edit', compact('user', 'departments'));
    }

    // =========================
    // UPDATE USER
    // =========================
    public function update(Request $request, $id)
    {
        $user = $this->findManageableUserOrFail((int) $id);
        $request->merge([
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $request->validate([
            'name' => 'required|string|max:255|regex:/^[A-Za-z\s]+$/',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'contact_number' => 'required|digits_between:7,15',
            'status' => 'required|in:active,inactive',
            'department_unit_id' => 'nullable|exists:department_units,department_unit_id',
        ]);

        $email = strtolower(trim((string) $request->email));

        // UNIT validation
        if ($user->role === 'unit') {
            if (!$request->department_unit_id) {
                return back()->withErrors([
                    'error' => 'College / Unit is required for Unit accounts.'
                ])->withInput();
            }

            $existingUnitUser = $this->findUnitUserByDepartment((int) $request->department_unit_id, $user->id);

            if ($existingUnitUser) {
                return back()->withErrors([
                    'department_unit_id' => 'This unit already has a user account: ' . $existingUnitUser->name . '.'
                ])->withInput();
            }
        }

        $user->update([
            'name' => $request->name,
            'email' => $email,
            'contact_number' => $request->contact_number,
            'status' => $request->status,
            'department_unit_id' => $user->role === 'unit'
                ? $request->department_unit_id
                : null,
            'fund_source_id' => null,
        ]);

        return redirect('/admin/users')
            ->with('success', 'User updated successfully.');
    }

    // =========================
    // DISABLE USER
    // =========================
    public function disable($id)
    {
        $user = $this->findManageableUserOrFail((int) $id);

        if (Auth::check() && $user->id === Auth::user()->id) {
            return back()->withErrors([
                'error' => 'You cannot disable your own account.'
            ]);
        }

        if ($user->status === 'inactive') {
            return back()->withErrors([
                'error' => 'This user account is already inactive.',
            ]);
        }

        $user->status = 'inactive';
        $user->save();

        return back()->with('success', 'User disabled successfully.');
    }

    // =========================
    // AJAX FUND SOURCE LOAD
    // =========================
    public function getFundSources($departmentId)
    {
        $fundSources = DB::table('fund_sources')
            ->selectRaw('fund_src_id as id, name')
            ->where('department_unit_id', $departmentId)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($fundSources);
    }

    // =========================
    // STORE USER
    // =========================
    public function store(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $request->validate([
            'role' => 'required|in:unit,bac',
            'name' => 'required|string|max:255|regex:/^[A-Za-z\s]+$/',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|digits_between:7,15',
            'status' => 'required|in:active,inactive'
        ]);

        $email = strtolower(trim((string) $request->email));

        $temporaryPassword = Str::random(10);

        if ($request->role === 'unit') {
            if (!$request->department_unit_id) {
                return back()->withErrors([
                    'error' => 'College / Unit is required for Unit accounts.'
                ])->withInput();
            }

            $existingUnitUser = $this->findUnitUserByDepartment((int) $request->department_unit_id);

            if ($existingUnitUser) {
                return back()->withErrors([
                    'department_unit_id' => 'This unit already has a user account: ' . $existingUnitUser->name . '.'
                ])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'contact_number' => $request->contact_number,
                'role' => $request->role,
                'department_unit_id' => $request->role === 'unit' ? $request->department_unit_id : null,
                'fund_source_id' => null,
                'status' => $request->status,
                'password' => Hash::make($temporaryPassword),
                'must_change_password' => true
            ]);

            $loginUrl = match ($user->role) {
                'bac' => url('/bac/login'),
                'unit' => url('/unit/login'),
                default => url('/admin/login'),
            };

            Mail::send('emails.new-user-temp-password', [
                'name' => $user->name,
                'email' => $user->email,
                'temporaryPassword' => $temporaryPassword,
                'loginUrl' => $loginUrl,
                'roleLabel' => strtoupper($user->role),
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('ConsoliData Temporary Password');
            });

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'User was not created because the email could not be sent. Please check the mailer settings and try again.'
            ])->withInput();
        }

        return redirect('/admin/users')
            ->with('success', 'User created successfully. The temporary password was sent to the user email.');
    }

    private function findUnitUserByDepartment(int $departmentUnitId, ?int $ignoreUserId = null): ?User
    {
        return User::query()
            ->where('role', 'unit')
            ->where('department_unit_id', $departmentUnitId)
            ->when($ignoreUserId, fn ($query) => $query->where('user_id', '!=', $ignoreUserId))
            ->first();
    }

    private function findManageableUserOrFail(int $id): User
    {
        return User::query()
            ->whereIn('role', ['unit', 'bac'])
            ->findOrFail($id);
    }
}