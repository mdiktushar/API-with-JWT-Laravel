<?php

use App\Helper\Helper;
use App\Http\Middleware\EnsureGuestJwt;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',

        // custom routes for api
        then: function () {
            Route::middleware([])
                ->prefix('api/auth')
                ->name('auth.')
                ->group(base_path('routes/api/auth.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'guest.api' => EnsureGuestJwt::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e instanceof QueryException) {
                    return Helper::error(500, 'server error', $e->getMessage());
                }
                else if ($e instanceof ModelNotFoundException) {
                    return Helper::error(404, 'not found', $e->getMessage());
                }

                else if ($e instanceof AuthenticationException) {
                    return Helper::error(401, 'unauthorized', $e->getMessage());
                }
                else if ($e instanceof AuthorizationException) {
                    return Helper::error(403, 'forbidden', $e->getMessage());
                }
                // // Dynamically determine the status code if available
                // $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                // // Return a generic server error with the appropriate status code
                // return Helper::error($statusCode, 'server error', $e->getMessage());
            }
        });
    })->create();
