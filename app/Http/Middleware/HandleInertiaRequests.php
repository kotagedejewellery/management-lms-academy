<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user?->only(['id', 'name', 'email', 'employee_id', 'status', 'email_verified_at']),
                'isSuperAdmin' => $user?->hasRole('super-admin') ?? false,
                'can' => [
                    'manageUsers' => $user?->hasPermission('manage-users') ?? false,
                    'manageOrganization' => $user?->hasPermission('manage-organization') ?? false,
                    'manageCourses' => $user?->hasPermission('manage-courses') ?? false,
                    'manageLearningPaths' => $user?->hasPermission('manage-learning-paths') ?? false,
                    'manageEnrollments' => $user?->hasPermission('manage-enrollments') ?? false,
                    'viewReports' => $user?->hasPermission('view-reports') ?? false,
                    'viewTeam' => $user?->hasPermission('view-team') ?? false,
                ],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
