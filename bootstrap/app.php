<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->command('scheduled-mail:process')->everyMinute();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'payhere/notify',
        ]);
        $middleware->alias([
            'redirect.authenticated' => \App\Http\Middleware\RedirectAuthenticatedToApp::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
        });
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, $request) {
            if (($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') && $response->getStatusCode() >= 500) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Server error occurred. Please try again or contact support.',
                ], $response->getStatusCode());
            }

            return $response;
        });
    })->create();
