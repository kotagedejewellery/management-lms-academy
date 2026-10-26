<?php

use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\EnsureOperationalRoleDoesNotAccessLearnerArea;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RecordAuditActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'active' => EnsureActiveAccount::class,
            'audit' => RecordAuditActivity::class,
            'not-learner-role' => EnsureOperationalRoleDoesNotAccessLearnerArea::class,
        ]);
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if (
                ! $request->header('X-Inertia')
                || $response->getStatusCode() !== 422
                || ! $exception instanceof HttpExceptionInterface
                || blank($exception->getMessage())
            ) {
                return $response;
            }

            return back()->withErrors(['action' => $exception->getMessage()]);
        });
    })->create();
