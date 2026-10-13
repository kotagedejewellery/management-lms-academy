<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateReportExport;
use App\Models\Course;
use App\Models\Division;
use App\Models\Enrollment;
use App\Models\ReportExport;
use App\Support\ReportEnrollmentQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);
        $enrollments = ReportEnrollmentQuery::make($filters)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Enrollment $enrollment) => [
                'id' => $enrollment->id,
                'status' => $enrollment->status,
                'due_at' => $enrollment->due_at?->toDateString(),
                'completed_at' => $enrollment->completed_at?->toDateString(),
                'pre_test_score' => $enrollment->pre_test_score === null ? null : (float) $enrollment->pre_test_score,
                'best_post_score' => $enrollment->best_post_score === null ? null : (float) $enrollment->best_post_score,
                'improvement' => $enrollment->pre_test_score === null || $enrollment->best_post_score === null ? null : round((float) $enrollment->best_post_score - (float) $enrollment->pre_test_score, 2),
                'post_test_attempts' => (int) $enrollment->post_test_attempts,
                'user' => $enrollment->user?->only(['employee_id', 'name']),
                'course' => $enrollment->course?->only(['code', 'title']),
            ]);

        return Inertia::render('reports/Index', [
            'enrollments' => $enrollments,
            'courses' => Course::query()->whereHas('enrollments')->orderBy('title')->limit(100)->get(['id', 'code', 'title']),
            'divisions' => Division::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'exports' => $request->user()->reportExports()->latest()->limit(5)->get()->map(fn (ReportExport $export) => [
                'id' => $export->id,
                'status' => $export->status,
                'created_at' => $export->created_at->toDateTimeString(),
                'completed_at' => $export->completed_at?->toDateTimeString(),
                'download_url' => $export->status === 'ready' ? route('reports.exports.download', $export) : null,
            ]),
        ]);
    }

    public function export(Request $request): RedirectResponse
    {
        $filters = $this->filters($request);
        $export = $request->user()->reportExports()->create(['filters' => $filters, 'status' => 'queued']);
        GenerateReportExport::dispatch($export->id);

        return to_route('reports.index', $filters);
    }

    public function download(Request $request, ReportExport $reportExport)
    {
        $this->authorizeReports($request);
        abort_unless($reportExport->user_id === $request->user()->id && $reportExport->status === 'ready' && $reportExport->path && Storage::disk('local')->exists($reportExport->path), 404);

        return Storage::disk('local')->download($reportExport->path, "laporan-pembelajaran-{$reportExport->id}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request): array
    {
        $this->authorizeReports($request);
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::in(['active', 'completed', 'not_passed'])], 'course_id' => ['nullable', 'integer', Rule::exists('courses', 'id')], 'division_id' => ['nullable', 'integer', Rule::exists('divisions', 'id')]]);

        return ['search' => trim($validated['search'] ?? ''), 'status' => $validated['status'] ?? '', 'course_id' => isset($validated['course_id']) ? (int) $validated['course_id'] : null, 'division_id' => isset($validated['division_id']) ? (int) $validated['division_id'] : null];
    }

    private function authorizeReports(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-enrollments') || $request->user()?->hasPermission('view-reports'), 403);
    }
}
