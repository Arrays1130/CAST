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
        $middleware->alias([
            'teacher' => \App\Http\Middleware\EnsureUserIsTeacher::class,
            'student' => \App\Http\Middleware\EnsureUserIsStudent::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\EnsurePasswordChanged::class);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function () {
            if (auth()->user()?->must_change_password) {
                return route('password.change');
            }

            return route('papers.index');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
