<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordAuditActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $event = $request->route()?->getName();

        if ($request->isMethod('GET') || ! $event || ! $this->isAuditable($event) || $response->getStatusCode() >= 400) {
            return $response;
        }

        $subject = collect($request->route()->parameters())->first(fn ($value) => $value instanceof Model);
        // shortcut: records successful actions, add field-level diffs when compliance requires before/after values.
        ActivityLog::record($request->user(), $event, $subject);

        return $response;
    }

    private function isAuditable(string $event): bool
    {
        return collect(['users.', 'user-imports.', 'organization.', 'course-categories.', 'courses.', 'learning-paths.', 'enrollments.', 'learning-path-assignments.', 'announcements.', 'reports.exports.'])
            ->contains(fn (string $prefix) => str_starts_with($event, $prefix));
    }
}
