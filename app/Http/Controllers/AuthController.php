<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\UnitFundDepartmentResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRoleSelect(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.role-select');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function showAdminLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.admin-login');
    }

    public function showUnitLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.unit-login');
    }

    public function showBacLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.bac-login');
    }

    public function showAdminRegister(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        if (!$this->adminRegistrationAllowed()) {
            return redirect('/admin/login')->withErrors([
                'email' => 'Admin self-registration is disabled. Please contact the system administrator.',
            ]);
        }

        return view('auth.admin-register');
    }

    public function login(Request $request)
    {
        // Always treat a login submit as a fresh authentication attempt.
        // This prevents a stale authenticated session from surviving behind a cached login form.
        $this->resetAuthenticatedSessionForLogin($request);

        $credentials = $this->validateLoginRequest($request);

        $email = $credentials['email'];
        $attemptsKey = 'login_attempts_' . sha1($email);
        $lockedKey = 'login_locked_' . sha1($email);

        if ($request->session()->get($lockedKey, false)) {
            return back()->withErrors([
                'email' => 'Too many failed attempts. Please check your email for verification or use Forgot Password.',
            ])->withInput();
        }

        if (!Auth::attempt(['email' => $email, 'password' => $credentials['password']])) {
            $attempts = (int) $request->session()->get($attemptsKey, 0) + 1;
            $request->session()->put($attemptsKey, $attempts);

            if ($attempts >= 3) {
                $request->session()->put($lockedKey, true);

                $resetUrl = url('/forgot-password?email=' . urlencode($email));

                Mail::send('emails.login-attempt-alert', [
                    'email' => $email,
                    'resetUrl' => $resetUrl,
                ], function ($message) use ($email) {
                    $message->to($email)
                        ->subject('ConsoliData - Login Attempts Alert');
                });

                return back()->withErrors([
                    'email' => 'Too many failed attempts. We sent a verification link to your email.',
                ])->withInput();
            }

            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput();
        }

        $request->session()->regenerate();
        $request->session()->forget([$attemptsKey, $lockedKey]);

        /** @var User|null $user */
        $user = Auth::user();
        if (!$user instanceof User) {
            return back()->withErrors([
                'email' => 'Could not restore your session. Please try logging in again.',
            ])->withInput();
        }
        $currentPath = $request->path();

        if (($user->status ?? null) === 'inactive') {
            return $this->forceRedirect(
                $request,
                $this->loginUrlForRole($user->role),
                'Account is inactive. Please contact administrator.'
            );
        }

        if ($user->role === 'admin' && $currentPath !== 'admin/login') {
            return $this->forceRedirect($request, '/admin/login', 'Please use the Admin login page.');
        }

        if ($user->role === 'unit' && $currentPath !== 'unit/login') {
            return $this->forceRedirect($request, '/unit/login', 'Please use the Unit login page.');
        }

        if ($user->role === 'unit' && $currentPath === 'unit/login') {
            $fundAccessResponse = $this->ensureUnitFundAccess($request, $user);

            if ($fundAccessResponse) {
                return $fundAccessResponse;
            }
        }

        if ($user->role === 'bac' && $currentPath !== 'bac/login') {
            return $this->forceRedirect($request, '/bac/login', 'Please use the BAC login page.');
        }

        if (($user->must_change_password ?? false) === true) {
            return redirect('/change-password')
                ->with('info', 'Please change your temporary password before continuing.');
        }

        return $this->redirectByRole($user);
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function showResetPasswordChoice(Request $request)
    {
        $user = $this->resolvePasswordResetUser($request);

        if (!$user) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'This password reset link is invalid or has expired.',
                ]);
        }

        return view('auth.reset-password-choice', [
            'email' => $user->email,
            'formAction' => $request->fullUrl(),
            'loginUrl' => $this->loginUrlForRole($user->role),
        ]);
    }

    public function processForgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($data['email']));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'We could not find an account with that email.',
            ])->withInput();
        }

        $temporaryPassword = Str::random(10);

        $user->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        // Clear local session lock if this reset happens in same browser
        $attemptsKey = 'login_attempts_' . sha1($email);
        $lockedKey = 'login_locked_' . sha1($email);
        $request->session()->forget([$attemptsKey, $lockedKey]);

        $loginUrl = $this->loginUrlForRole($user->role);
        $resetUrl = URL::temporarySignedRoute(
            'password.reset.choice',
            now()->addMinutes(60),
            [
                'email' => $user->email,
                'hash' => sha1($user->password),
            ]
        );

        Mail::send('emails.temporary-password', [
            'name' => $user->name,
            'temporaryPassword' => $temporaryPassword,
            'loginUrl' => $loginUrl,
            'resetUrl' => $resetUrl,
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('ConsoliData Password Recovery Options');
        });

        return redirect()->back()->with('success', 'Recovery options were sent to your email. You can either reset your password now or use the temporary password later.');
    }

    public function processResetPasswordChoice(Request $request)
    {
        $user = $this->resolvePasswordResetUser($request);

        if (!$user) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'This password reset link is invalid or has expired.',
                ]);
        }

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect($this->loginUrlForRole($user->role))
            ->with('success', 'Password reset successful. You can now sign in with your new password.');
    }

    public function registerAdmin(Request $request)
    {
        if (!$this->adminRegistrationAllowed()) {
            return $this->forceRedirect(
                $request,
                '/admin/login',
                'Admin self-registration is disabled. Please contact the system administrator.'
            );
        }

        $request->merge([
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'name.regex' => 'Name must contain letters only (no numbers or symbols).',
        ]);

        if (User::where('role', 'admin')->exists()) {
            return redirect('/admin/login')->withErrors([
                'email' => 'Admin account already exists.',
            ]);
        }

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'status' => 'active',
            'must_change_password' => false,
        ]);

        Auth::login($admin);

        return redirect('/admin/dashboard');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = User::findOrFail(Auth::id());

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return $this->redirectByRole($user)
            ->with('success', 'Password updated successfully.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectByRole($user)
    {
        return match ($user->role) {
            'admin' => redirect('/admin/dashboard'),
            'bac' => redirect('/bac/dashboard'),
            'unit' => redirect('/dashboard'),
            default => redirect('/'),
        };
    }

    private function forceRedirect(Request $request, string $route, string $message)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($route)->withErrors([
            'email' => $message,
        ]);
    }

    private function resetAuthenticatedSessionForLogin(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function validateLoginRequest(Request $request): array
    {
        $payload = [
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'password' => is_string($request->input('password')) ? $request->input('password') : null,
        ];

        $rules = [
            'email' => 'required|email',
            'password' => 'required|string',
        ];

        return validator($payload, $rules, [
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
        ])->validate();
    }

    private function ensureUnitFundAccess(Request $request, User $user)
    {
        if (!$user->department_unit_id) {
            return $this->logoutWithFundSourceError(
                $request,
                'This unit account is not assigned to a college/unit yet. Please contact the administrator.'
            );
        }

        $resolvedDepartmentId = UnitFundDepartmentResolver::resolvedDepartmentId($user->department_unit_id);
        $availableFundSources = UnitFundDepartmentResolver::fundSourcesForDepartment($user->department_unit_id);

        if (!$resolvedDepartmentId || $availableFundSources->isEmpty()) {
            return $this->logoutWithFundSourceError(
                $request,
                'No valid fund sources are available for this unit account yet. Please contact the administrator.'
            );
        }

        $activeFundSourceId = (int) $request->session()->get('unit_active_fund_source_id');
        $activeFundSource = $availableFundSources->first(
            fn ($fundSource) => (int) $fundSource->id === $activeFundSourceId
        );

        if ($activeFundSource) {
            return null;
        }

        if ($availableFundSources->count() === 1) {
            $request->session()->put('unit_active_fund_source_id', (int) $availableFundSources->first()->id);
        } else {
            $request->session()->forget('unit_active_fund_source_id');
        }

        return null;
    }

    private function logoutWithFundSourceError(Request $request, string $message)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/unit/login')
            ->withErrors([
                'email' => $message,
            ])
            ->withInput($request->except('password'));
    }

    private function loginUrlForRole(?string $role): string
    {
        return match ($role) {
            'admin' => url('/admin/login'),
            'bac' => url('/bac/login'),
            default => url('/unit/login'),
        };
    }

    private function adminRegistrationAllowed(): bool
    {
        return app()->environment('local') && !User::where('role', 'admin')->exists();
    }

    private function resolvePasswordResetUser(Request $request): ?User
    {
        $email = strtolower(trim((string) $request->query('email', $request->input('email', ''))));
        $hash = (string) $request->query('hash', $request->input('hash', ''));

        if ($email === '' || $hash === '') {
            return null;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        return hash_equals($hash, sha1($user->password)) ? $user : null;
    }
}
