<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('unit.login');
        }

        if ($user->role !== 'unit') {
            return redirect($this->redirectPathForRole($user->role));
        }

        if (($user->status ?? null) !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('unit.login')->withErrors([
                'email' => 'Your account is inactive. Please contact the administrator.',
            ]);
        }

        return $next($request);
    }

    private function redirectPathForRole(?string $role): string
    {
        return match ($role) {
            'admin' => '/admin/dashboard',
            'bac' => '/bac/dashboard',
            'unit' => '/dashboard',
            default => '/',
        };
    }
}