<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Position;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordAuditActivity
{
    /** @var list<string> */
    private const EXCLUDED_ATTRIBUTES = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'thumbnail_path',
        'path',
        'created_at',
        'updated_at',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $event = $request->route()?->getName();
        $subject = $event ? $this->subject($request, $event) : null;
        $before = $subject ? $this->snapshot($subject) : [];
        $response = $next($request);

        if ($request->isMethod('GET') || ! $event || ! $this->isAuditable($event) || $response->getStatusCode() >= 400) {
            return $response;
        }

        $freshSubject = $subject?->fresh();
        $after = $freshSubject ? $this->snapshot($freshSubject) : [];
        $changes = $this->changes($before, $after);

        ActivityLog::record($request->user(), $event, $subject, $changes ? ['changes' => $changes] : []);

        return $response;
    }

    private function subject(Request $request, string $event): ?Model
    {
        $subject = collect($request->route()->parameters())
            ->filter(fn ($value) => $value instanceof Model)
            ->last();

        if ($subject instanceof Model) {
            return $subject;
        }

        if ($event !== 'organization.update') {
            return null;
        }

        $model = match ($request->route('type')) {
            'divisions' => Division::class,
            'positions' => Position::class,
            'branches' => Branch::class,
            default => null,
        };

        $record = $request->route('record');

        return $model && is_numeric($record)
            ? $model::query()->whereKey((int) $record)->first()
            : null;
    }

    /** @return array<string, mixed> */
    private function snapshot(Model $subject): array
    {
        $excluded = [...self::EXCLUDED_ATTRIBUTES, ...$subject->getHidden()];
        $attributes = [];

        foreach ($subject->getAttributes() as $attribute => $value) {
            if (! in_array($attribute, $excluded, true)) {
                $attributes[$attribute] = $value;
            }
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{before: mixed, after: mixed}>
     */
    private function changes(array $before, array $after): array
    {
        $changes = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $attribute) {
            $beforeValue = $before[$attribute] ?? null;
            $afterValue = $after[$attribute] ?? null;

            if ($beforeValue !== $afterValue) {
                $changes[$attribute] = ['before' => $beforeValue, 'after' => $afterValue];
            }
        }

        return $changes;
    }

    private function isAuditable(string $event): bool
    {
        return collect(['users.', 'user-imports.', 'organization.', 'course-categories.', 'courses.', 'learning-paths.', 'enrollments.', 'learning-path-assignments.', 'announcements.', 'reports.exports.'])
            ->contains(fn (string $prefix) => str_starts_with($event, $prefix));
    }
}
