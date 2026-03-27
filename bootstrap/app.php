<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'bac'   => \App\Http\Middleware\BacMiddleware::class,
            'unit'  => \App\Http\Middleware\UnitMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $renderExpiredSession = function (Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            $target = match (true) {
                $request->is('admin/login') => '/admin/login',
                $request->is('unit/login') => '/unit/login',
                $request->is('bac/login') => '/bac/login',
                $request->is('forgot-password') => '/forgot-password',
                $request->is('password/reset-choice') => url()->previous() ?: '/forgot-password',
                default => url()->previous() ?: '/',
            };

            $message = 'Your session expired. Please refresh the page and try again.';

            if ($request->is('admin/login', 'unit/login', 'bac/login', 'forgot-password', 'password/reset-choice')) {
                return redirect($target)
                    ->withErrors(['email' => $message])
                    ->withInput($request->except(['password', '_token']));
            }

            return redirect($target)
                ->with('error', $message)
                ->withInput($request->except(['password', '_token']));
        };

        $exceptions->render(function (TokenMismatchException $exception, Request $request) use ($renderExpiredSession) {
            return $renderExpiredSession($request);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($renderExpiredSession) {
            if ($exception->getStatusCode() === 419) {
                return $renderExpiredSession($request);
            }

            return null;
        });
    })->create();