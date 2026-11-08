<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [SecurityHeaders::class, AuthenticateSession::class]);
        $middleware->trustHosts(at: fn () => ['^'.preg_quote(parse_url(config('app.url'), PHP_URL_HOST) ?: '127.0.0.1', '/').'$'], subdomains: false);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
