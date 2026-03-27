<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BacMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('bac.login');
        }

        if ($user->role !== 'bac') {
            return redirect($this->redirectPathForRole($user->role));
        }

        if (($user->status ?? null) !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('bac.login')->withErrors([
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
