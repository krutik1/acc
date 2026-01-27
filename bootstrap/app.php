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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\CheckInstalled::class,
        ]);

        $middleware->alias([
            'company' => \App\Http\Middleware\SetCompany::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'blocked.check' => \App\Http\Middleware\CheckDriverBlocked::class,
            'installed' => \App\Http\Middleware\CheckInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
