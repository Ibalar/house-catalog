<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SetSecurityHeaders::class,
        ]);

        $middleware->throttleApi('60,1');

        $middleware->limitRequests('leads', 10);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $e) {
            if ($this->app->environment('production')) {
                \Illuminate\Support\Facades\Log::error($e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });
    })->create();
