<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperationalRoleDoesNotAccessLearnerArea
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if(
            $user?->hasRole('trainer') || $user?->hasPermission('manage-enrollments'),
            403,
        );

        return $next($request);
    }
}
