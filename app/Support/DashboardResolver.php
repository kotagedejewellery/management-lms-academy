<?php

namespace App\Support;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardResolver
{
    /** @return array<string, mixed> */
    public function for(User $user): array
    {
        if ($user->hasPermission('manage-enrollments')) {
            return $this->operations();
        }

        if ($user->hasPermission('manage-courses')) {
            return $this->trainer($user);
        }

        if ($user->hasPermission('view-team')) {
            return $this->manager($user);
        }

        if ($user->hasPermission('view-reports')) {
            return $this->executive();
        }

        return $this->learner($user);
    }

    /** @return array<string, mixed> */
    private function learner(User $user): array
    {
        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending_review', 'completed', 'not_passed']);
        $stats = $this->enrollmentStats($enrollments);
        $active = (clone $enrollments)
            ->where('status', 'active')
            ->with('course:id,code,title,prerequisite_course_id')
            ->orderByRaw('due_at is null, due_at')
            ->limit(5)
            ->get();
        $enrollmentRows = (clone $enrollments)->get(['id', 'course_id', 'status']);
        $availability = app(LearningAvailability::class)->for(
            $user,
            $active->pluck('course')->filter(),
            $enrollmentRows,
        );
        $enrollmentIds = $enrollmentRows->pluck('id');
        $courseIds = $enrollmentRows->pluck('course_id')->unique();
        $completedLessons = LessonProgress::query()
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->whereIn('enrollment_id', $enrollmentIds)
            ->where('status', 'completed')
            ->where('lessons.is_required', true)
            ->selectRaw('lesson_progress.enrollment_id, count(*) as total')
            ->groupBy('lesson_progress.enrollment_id')
            ->pluck('total', 'lesson_progress.enrollment_id');
        $requiredLessons = DB::table('lessons')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->whereIn('modules.course_id', $courseIds)
            ->where('lessons.is_required', true)
            ->selectRaw('modules.course_id, count(*) as total')
            ->groupBy('modules.course_id')
            ->pluck('total', 'modules.course_id');
        $assessments = Assessment::query()
            ->whereIn('course_id', $courseIds)
            ->whereNull('module_id')
            ->whereIn('type', ['pre_test', 'post_test'])
            ->withCount('questions')
            ->get(['id', 'course_id', 'type', 'is_required']);
        $submittedAttempts = AssessmentAttempt::query()
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->whereIn('enrollment_id', $enrollmentIds)
            ->whereNotNull('submitted_at')
            ->select(['assessment_id', 'enrollment_id'])
            ->get()
            ->mapWithKeys(fn (AssessmentAttempt $attempt) => [$attempt->assessment_id.'-'.$attempt->enrollment_id => true]);
        $preTestPending = 0;
        $postTestReady = 0;
        foreach ($enrollmentRows->where('status', 'active') as $enrollment) {
            if ($availability[$enrollment->course_id]['locked'] ?? false) {
                continue;
            }

            $courseAssessments = $assessments->where('course_id', $enrollment->course_id);
            $preTest = $courseAssessments->firstWhere('type', 'pre_test');
            $postTest = $courseAssessments->firstWhere('type', 'post_test');
            $hasCompletedRequiredLessons = ($requiredLessons[$enrollment->course_id] ?? 0) <= ($completedLessons[$enrollment->id] ?? 0);

            if ($preTest && $preTest->questions_count > 0 && ! isset($submittedAttempts[$preTest->id.'-'.$enrollment->id])) {
                $preTestPending++;
            }
            if ($postTest && $postTest->questions_count > 0 && $hasCompletedRequiredLessons && ! isset($submittedAttempts[$postTest->id.'-'.$enrollment->id])) {
                $postTestReady++;
            }
        }
        $totalRequiredLessons = $enrollmentRows->sum(fn (Enrollment $enrollment) => (int) ($requiredLessons[$enrollment->course_id] ?? 0));
        $completedRequiredLessons = $enrollmentRows->sum(fn (Enrollment $enrollment) => min((int) ($completedLessons[$enrollment->id] ?? 0), (int) ($requiredLessons[$enrollment->course_id] ?? 0)));

        return [
            'mode' => 'learner',
            'title' => 'Pembelajaran saya',
            'description' => 'Lanjutkan materi dan pantau pencapaian Anda.',
            'stats' => [
                ['label' => 'Sedang dipelajari', 'value' => $stats['active']],
                ['label' => 'Course selesai', 'value' => $stats['completed']],
                ['label' => 'Tenggat terlewat', 'value' => $stats['overdue']],
            ],
            'learning_summary' => [
                'overall_progress' => $totalRequiredLessons ? (int) round(($completedRequiredLessons / $totalRequiredLessons) * 100) : 0,
                'pre_test_pending' => $preTestPending,
                'post_test_ready' => $postTestReady,
            ],
            'primary_action' => ['label' => 'Lihat pembelajaran saya', 'href' => '/my-learning'],
            'items_title' => 'Lanjutkan pembelajaran',
            'empty_title' => 'Belum ada pembelajaran aktif',
            'empty_description' => 'Course yang ditetapkan untuk Anda akan muncul di sini.',
            'items' => $active->map(function (Enrollment $enrollment) use ($availability): array {
                $state = $availability[$enrollment->course_id] ?? ['locked' => false, 'reason' => null];

                return [
                    ...$this->item(
                        $enrollment->id,
                        $enrollment->course->title,
                        $enrollment->course->code,
                        $state['locked'] ? $state['reason'] : ($enrollment->due_at ? 'Tenggat '.$enrollment->due_at->toDateString() : 'Tanpa tenggat'),
                        $state['locked'] ? null : '/my-learning/'.$enrollment->course_id,
                    ),
                    'locked' => $state['locked'],
                    'lock_reason' => $state['reason'],
                ];
            })->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function trainer(User $user): array
    {
        $courses = $user->managedCourses()
            ->withCount('modules')
            ->orderByRaw("case when status = 'published' then 1 else 0 end")
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['courses.id', 'courses.code', 'courses.title', 'courses.status']);

        return [
            'mode' => 'trainer',
            'title' => 'Course yang saya kelola',
            'description' => 'Lengkapi materi dan evaluasi pada course yang ditugaskan kepada Anda.',
            'stats' => [
                ['label' => 'Course ditugaskan', 'value' => $user->managedCourses()->count()],
                ['label' => 'Draf', 'value' => $user->managedCourses()->where('courses.status', 'draft')->count()],
                ['label' => 'Diterbitkan', 'value' => $user->managedCourses()->where('courses.status', 'published')->count()],
                ['label' => 'Tanpa modul', 'value' => $user->managedCourses()->doesntHave('modules')->count()],
            ],
            'primary_action' => ['label' => 'Kelola course', 'href' => '/courses'],
            'items_title' => 'Course yang membutuhkan perhatian',
            'empty_title' => 'Belum ada course yang ditugaskan',
            'empty_description' => 'LMS Admin dapat menugaskan course kepada Anda dari pengaturan course.',
            'items' => $courses->map(fn (Course $course) => $this->item(
                $course->id,
                $course->title,
                $course->code,
                $course->modules_count.' modul · '.$this->courseStatus($course->status),
                '/courses/'.$course->id.'/content',
            ))->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function manager(User $user): array
    {
        $team = $user->directReports()->where('status', 'active');
        $teamIds = $team->select('id');
        $enrollments = Enrollment::query()
            ->whereIn('user_id', $teamIds);
        $stats = $this->enrollmentStats($enrollments);
        $overdue = (clone $enrollments)
            ->where('status', 'active')
            ->where('due_at', '<', now())
            ->with(['user:id,name', 'course:id,code,title'])
            ->orderByRaw('due_at is null, due_at')
            ->limit(5)
            ->get();

        return [
            'mode' => 'manager',
            'title' => 'Ringkasan tim saya',
            'description' => 'Pantau pembelajaran anggota tim yang melapor langsung kepada Anda.',
            'stats' => [
                ['label' => 'Anggota tim', 'value' => $team->count()],
                ['label' => 'Assignment aktif', 'value' => $stats['active']],
                ['label' => 'Tenggat terlewat', 'value' => $stats['overdue']],
                ['label' => 'Course selesai', 'value' => $stats['completed']],
            ],
            'primary_action' => ['label' => 'Lihat progres tim', 'href' => '/team-progress'],
            'items_title' => 'Perlu perhatian',
            'empty_title' => 'Belum ada assignment pada tim',
            'empty_description' => 'Assignment anggota tim akan muncul di sini.',
            'items' => $overdue->map(fn (Enrollment $enrollment) => $this->item(
                $enrollment->id,
                $enrollment->user->name,
                $enrollment->course->title,
                'Tenggat '.$enrollment->due_at->toDateString(),
                '/team-progress',
            ))->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function operations(): array
    {
        return Cache::remember('dashboard:management', now()->addMinutes(5), function (): array {
            $recent = Enrollment::query()
                ->with(['user:id,name', 'course:id,code,title'])
                ->latest()
                ->limit(5)
                ->get();

            return [
                'mode' => 'operations',
                'title' => 'Ringkasan operasional',
                'description' => 'Pantau assignment dan penyelesaian course terbaru.',
                'stats' => [
                    ['label' => 'Karyawan aktif', 'value' => User::query()->where('status', 'active')->count()],
                    ['label' => 'Course diterbitkan', 'value' => Course::query()->where('status', 'published')->count()],
                    ['label' => 'Assignment aktif', 'value' => Enrollment::query()->where('status', 'active')->count()],
                    ['label' => 'Course selesai', 'value' => Enrollment::query()->where('status', 'completed')->count()],
                ],
                'primary_action' => ['label' => 'Kelola penugasan', 'href' => '/enrollments'],
                'items_title' => 'Assignment terbaru',
                'empty_title' => 'Belum ada assignment',
                'empty_description' => 'Tetapkan course kepada karyawan untuk mulai memantau progres.',
                'items' => $recent->map(fn (Enrollment $enrollment) => $this->item(
                    $enrollment->id,
                    $enrollment->user->name,
                    $enrollment->course->title,
                    $this->enrollmentStatus($enrollment->status),
                    '/enrollments',
                ))->all(),
            ];
        });
    }

    /** @return array<string, mixed> */
    private function executive(): array
    {
        $enrollments = Enrollment::query()->with(['user:id,name', 'course:id,code,title'])->latest()->limit(5)->get();

        return [
            'mode' => 'executive',
            'title' => 'Ringkasan pembelajaran organisasi',
            'description' => 'Lihat capaian organisasi dan tindak lanjuti pembelajaran yang memerlukan perhatian.',
            'stats' => [
                ['label' => 'Karyawan aktif', 'value' => User::query()->where('status', 'active')->count()],
                ['label' => 'Assignment aktif', 'value' => Enrollment::query()->where('status', 'active')->count()],
                ['label' => 'Course selesai', 'value' => Enrollment::query()->where('status', 'completed')->count()],
                ['label' => 'Tenggat terlewat', 'value' => Enrollment::query()->where('status', 'active')->whereDate('due_at', '<', today())->count()],
            ],
            'primary_action' => ['label' => 'Buka laporan', 'href' => '/reports'],
            'items_title' => 'Aktivitas pembelajaran terbaru',
            'empty_title' => 'Belum ada aktivitas pembelajaran',
            'empty_description' => 'Aktivitas assignment akan muncul setelah program pembelajaran berjalan.',
            'items' => $enrollments->map(fn (Enrollment $enrollment) => $this->item(
                $enrollment->id,
                $enrollment->user->name,
                $enrollment->course->title,
                $this->enrollmentStatus($enrollment->status),
                '/reports',
            ))->all(),
        ];
    }

    /**
     * @param  Builder<Enrollment>  $enrollments
     * @return array{active: int, pending_review: int, completed: int, not_passed: int, overdue: int}
     */
    private function enrollmentStats(Builder $enrollments): array
    {
        return [
            'active' => (clone $enrollments)->where('status', 'active')->count(),
            'pending_review' => (clone $enrollments)->where('status', 'pending_review')->count(),
            'completed' => (clone $enrollments)->where('status', 'completed')->count(),
            'not_passed' => (clone $enrollments)->where('status', 'not_passed')->count(),
            'overdue' => (clone $enrollments)->where('status', 'active')->where('due_at', '<', now())->count(),
        ];
    }

    /** @return array{id: int, title: string, description: string, meta: string, href: string|null} */
    private function item(int $id, string $title, string $description, string $meta, ?string $href): array
    {
        return compact('id', 'title', 'description', 'meta', 'href');
    }

    private function courseStatus(string $status): string
    {
        return ['draft' => 'Draf', 'published' => 'Diterbitkan', 'archived' => 'Diarsipkan'][$status];
    }

    private function enrollmentStatus(string $status): string
    {
        return [
            'active' => 'Aktif',
            'pending_review' => 'Menunggu evaluasi akhir',
            'completed' => 'Selesai',
            'not_passed' => 'Perlu diulang',
        ][$status];
    }
}
