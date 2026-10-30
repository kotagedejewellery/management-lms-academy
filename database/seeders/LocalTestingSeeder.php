<?php

namespace Database\Seeders;

use App\Jobs\IssueCertificate;
use App\Models\Announcement;
use App\Models\Answer;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentResponse;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Division;
use App\Models\Enrollment;
use App\Models\LearningPath;
use App\Models\LearningPathAssignment;
use App\Models\LearningPathCourse;
use App\Models\LearningPathRule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonResource;
use App\Models\Position;
use App\Models\Question;
use App\Models\ReportExport;
use App\Models\Role;
use App\Models\User;
use App\Models\UserImport;
use App\Models\UserNotification;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LocalTestingSeeder extends Seeder
{
    public function run(): void
    {
        if (! in_array(app()->environment(), ['local', 'testing'], true)) {
            $this->command->warn('LocalTestingSeeder hanya dapat dijalankan pada environment local atau testing.');

            return;
        }

        $this->call(RolePermissionSeeder::class);

        DB::transaction(function (): void {
            $this->seed();
        });
    }

    private function seed(): void
    {
        $now = now();
        $password = Hash::make('password');

        $branches = collect([
            'KGJ-KTG' => ['name' => 'KGJ Kotagede'],
            'KGJ-MRB' => ['name' => 'KGJ Malioboro'],
            'KGJ-SLM' => ['name' => 'KGJ Sleman'],
        ])->map(fn (array $data, string $code) => Branch::query()->updateOrCreate(['code' => $code], [...$data, 'is_active' => true]));

        $divisions = collect([
            'SLS' => ['name' => 'Sales'],
            'OPS' => ['name' => 'Operasional'],
            'HR' => ['name' => 'Human Resources'],
        ])->map(fn (array $data, string $code) => Division::query()->updateOrCreate(['code' => $code], [...$data, 'is_active' => true]));

        $positions = collect([
            'sales_associate' => ['division_id' => $divisions['SLS']->id, 'name' => 'Sales Associate'],
            'senior_sales' => ['division_id' => $divisions['SLS']->id, 'name' => 'Senior Sales Associate'],
            'store_leader' => ['division_id' => $divisions['OPS']->id, 'name' => 'Store Leader'],
            'hr_officer' => ['division_id' => $divisions['HR']->id, 'name' => 'HR Officer'],
        ])->map(fn (array $data) => Position::query()->updateOrCreate(
            ['division_id' => $data['division_id'], 'name' => $data['name']],
            ['is_active' => true],
        ));

        $roles = Role::query()->whereIn('slug', ['super-admin', 'lms-admin', 'trainer', 'employee'])->get()->keyBy('slug');
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@kgj.test'],
            ['employee_id' => 'LOCAL-ADMIN', 'name' => 'Local Super Admin', 'password' => $password, 'status' => 'active'],
        );
        $admin->forceFill(['email_verified_at' => $now])->save();
        $admin->roles()->syncWithoutDetaching([$roles['super-admin']->id]);

        $lmsAdmin = $this->upsertUser($roles['lms-admin'], [
            'employee_id' => 'KGJ-001', 'name' => 'Maya Pradipta', 'email' => 'lms.admin@kgj.test', 'phone' => '081200000001',
            'division_id' => $divisions['HR']->id, 'position_id' => $positions['hr_officer']->id, 'branch_id' => $branches['KGJ-KTG']->id, 'status' => 'active',
        ], $password, $now);
        $trainer = $this->upsertUser($roles['trainer'], [
            'employee_id' => 'KGJ-002', 'name' => 'Raka Wicaksana', 'email' => 'trainer@kgj.test', 'phone' => '081200000002',
            'division_id' => $divisions['HR']->id, 'position_id' => $positions['hr_officer']->id, 'branch_id' => $branches['KGJ-KTG']->id, 'status' => 'active',
        ], $password, $now);
        $manager = $this->upsertUser($roles['employee'], [
            'employee_id' => 'KGJ-010', 'name' => 'Dewi Kurniasari', 'email' => 'manager@kgj.test', 'phone' => '081200000010',
            'division_id' => $divisions['SLS']->id, 'position_id' => $positions['senior_sales']->id, 'branch_id' => $branches['KGJ-MRB']->id, 'status' => 'active',
        ], $password, $now);
        $anisa = $this->upsertUser($roles['employee'], [
            'employee_id' => 'KGJ-101', 'name' => 'Anisa Putri', 'email' => 'anisa@kgj.test', 'phone' => '081200000101',
            'division_id' => $divisions['SLS']->id, 'position_id' => $positions['sales_associate']->id, 'branch_id' => $branches['KGJ-MRB']->id, 'manager_id' => $manager->id, 'status' => 'active',
        ], $password, $now);
        $bima = $this->upsertUser($roles['employee'], [
            'employee_id' => 'KGJ-102', 'name' => 'Bima Aditya', 'email' => 'bima@kgj.test', 'phone' => '081200000102',
            'division_id' => $divisions['SLS']->id, 'position_id' => $positions['sales_associate']->id, 'branch_id' => $branches['KGJ-KTG']->id, 'manager_id' => $manager->id, 'status' => 'active',
        ], $password, $now);
        $citra = $this->upsertUser($roles['employee'], [
            'employee_id' => 'KGJ-103', 'name' => 'Citra Lestari', 'email' => 'citra@kgj.test', 'phone' => '081200000103',
            'division_id' => $divisions['SLS']->id, 'position_id' => $positions['sales_associate']->id, 'branch_id' => $branches['KGJ-SLM']->id, 'manager_id' => $manager->id, 'status' => 'active',
        ], $password, $now);
        $this->upsertUser($roles['employee'], [
            'employee_id' => 'KGJ-104', 'name' => 'Dimas Pratama', 'email' => 'dimas@kgj.test', 'phone' => '081200000104',
            'division_id' => $divisions['OPS']->id, 'position_id' => $positions['store_leader']->id, 'branch_id' => $branches['KGJ-KTG']->id, 'status' => 'inactive',
        ], $password, $now);
        $this->upsertUser($roles['lms-admin'], [
            'employee_id' => 'KGJ-900', 'name' => 'Nadia Utami', 'email' => 'owner@kgj.test', 'phone' => '081200000900',
            'division_id' => $divisions['OPS']->id, 'position_id' => $positions['store_leader']->id, 'branch_id' => $branches['KGJ-KTG']->id, 'status' => 'active',
        ], $password, $now);

        $categories = collect([
            'onboarding' => 'Onboarding',
            'product' => 'Pengetahuan Produk',
            'service' => 'Layanan Pelanggan',
            'operations' => 'Operasional Toko',
        ])->map(fn (string $name) => CourseCategory::query()->updateOrCreate(['name' => $name], ['is_active' => true]));

        $onboarding = $this->upsertCourse($categories['onboarding'], [
            'code' => 'ONB-001', 'title' => 'Orientasi KotaGede Jewellery', 'description' => 'Pengenalan budaya kerja, nilai merek, dan standar dasar KGJ.',
            'estimated_minutes' => 75, 'is_mandatory' => true, 'passing_grade' => 70, 'certificate_enabled' => true, 'status' => 'published', 'published_at' => $now->copy()->subDays(21),
        ]);
        $product = $this->upsertCourse($categories['product'], [
            'code' => 'PRD-101', 'title' => 'Fundamental Produk Perhiasan', 'description' => 'Dasar material, kualitas, dan cara menjelaskan produk kepada pelanggan.',
            'estimated_minutes' => 90, 'is_mandatory' => true, 'passing_grade' => 75, 'status' => 'published', 'published_at' => $now->copy()->subDays(14),
            'prerequisite_course_id' => $onboarding->id,
        ]);
        $service = $this->upsertCourse($categories['service'], [
            'code' => 'SVC-201', 'title' => 'Standar Layanan Pelanggan', 'description' => 'Praktik komunikasi dan layanan yang konsisten di seluruh cabang KGJ.',
            'estimated_minutes' => 60, 'is_mandatory' => true, 'passing_grade' => 80, 'status' => 'published', 'published_at' => $now->copy()->subDays(7),
            'division_id' => $divisions['SLS']->id, 'position_id' => $positions['sales_associate']->id,
        ]);
        $this->upsertCourse($categories['operations'], [
            'code' => 'OPS-301', 'title' => 'Visual Display Perhiasan', 'description' => 'Materi persiapan tampilan produk untuk operasional toko.',
            'estimated_minutes' => 45, 'is_mandatory' => false, 'passing_grade' => 70, 'status' => 'draft', 'published_at' => null,
        ]);
        $this->upsertCourse($categories['operations'], [
            'code' => 'OPS-101', 'title' => 'SOP Pembukaan Toko', 'description' => 'Versi lama SOP pembukaan toko untuk contoh status arsip.',
            'estimated_minutes' => 30, 'is_mandatory' => false, 'passing_grade' => 70, 'status' => 'archived', 'published_at' => null,
        ]);
        $trainer->managedCourses()->syncWithoutDetaching([$onboarding->id, $product->id, $service->id]);

        $onboardingLessons = $this->seedLessons($onboarding, [
            ['title' => 'Mengenal KGJ', 'lessons' => [
                ['title' => 'Selamat Datang di KGJ', 'content' => 'Kenali sejarah, nilai, dan tujuan pembelajaran internal KGJ.'],
                ['title' => 'Nilai Layanan KotaGede Jewellery', 'content' => 'Setiap interaksi pelanggan mencerminkan ketelitian dan keramahan KGJ.'],
                ['title' => 'Pedoman Etika Kerja', 'type' => 'pdf', 'content' => 'Unduh pedoman etika kerja contoh pada bagian sumber belajar.'],
            ]],
            ['title' => 'Standar Dasar', 'lessons' => [
                ['title' => 'Komunikasi Profesional', 'content' => 'Gunakan bahasa yang jelas, sopan, dan sesuai kebutuhan pelanggan.'],
                ['title' => 'Keselamatan dan Kerapian Area Kerja', 'content' => 'Jaga area kerja, produk, dan peralatan sesuai standar operasional.'],
            ]],
        ]);
        $productLessons = $this->seedLessons($product, [
            ['title' => 'Dasar Produk', 'lessons' => [
                ['title' => 'Mengenal Material Perhiasan', 'content' => 'Pahami istilah umum untuk emas, batu, dan material pendukung.'],
                ['title' => 'Standar Pemeriksaan Produk', 'content' => 'Gunakan daftar pemeriksaan sederhana sebelum produk ditampilkan.'],
            ]],
            ['title' => 'Konsultasi Pelanggan', 'lessons' => [
                ['title' => 'Menggali Kebutuhan Pelanggan', 'content' => 'Ajukan pertanyaan terbuka untuk memahami kebutuhan pelanggan.'],
                ['title' => 'Merekomendasikan Produk', 'content' => 'Hubungkan kebutuhan pelanggan dengan manfaat produk yang relevan.'],
            ]],
        ]);
        $serviceLessons = $this->seedLessons($service, [
            ['title' => 'Fondasi Layanan', 'lessons' => [
                ['title' => 'Menyambut Pelanggan', 'content' => 'Bangun kesan pertama yang hangat dan profesional.'],
                ['title' => 'Menangani Pertanyaan', 'content' => 'Berikan informasi yang akurat sebelum menawarkan solusi.'],
            ]],
            ['title' => 'Penyelesaian Layanan', 'lessons' => [
                ['title' => 'Menutup Konsultasi', 'content' => 'Konfirmasi kebutuhan pelanggan sebelum mengakhiri konsultasi.'],
                ['title' => 'Tindak Lanjut', 'content' => 'Catat komitmen tindak lanjut secara ringkas dan tepat waktu.'],
            ]],
        ]);

        $resourcePath = 'demo-resources/pedoman-etika-kerja.pdf';
        $resourceContent = $this->demoPdf();
        Storage::disk('local')->put($resourcePath, $resourceContent);
        LessonResource::query()->updateOrCreate(
            ['lesson_id' => $onboardingLessons['Pedoman Etika Kerja']->id, 'path' => $resourcePath],
            ['disk' => 'local', 'original_name' => 'Pedoman Etika Kerja KGJ (contoh).pdf', 'mime_type' => 'application/pdf', 'size' => strlen($resourceContent)],
        );

        $preTest = $this->seedAssessment($onboarding, 'pre_test', 0, 10, [
            ['prompt' => 'Nilai yang harus terlihat pada setiap layanan KGJ adalah?', 'answers' => ['Ketelitian dan keramahan' => true, 'Kecepatan tanpa informasi' => false, 'Penawaran tanpa mendengar kebutuhan' => false]],
            ['prompt' => 'Apa yang perlu dilakukan sebelum memberi rekomendasi produk?', 'answers' => ['Menggali kebutuhan pelanggan' => true, 'Langsung menunjukkan produk termahal' => false, 'Menunda respons pelanggan' => false]],
        ]);
        $postTest = $this->seedAssessment($onboarding, 'post_test', 70, 20, [
            ['prompt' => 'Bagaimana cara menjaga komunikasi tetap profesional?', 'answers' => ['Memberi informasi yang jelas dan sopan' => true, 'Menggunakan istilah yang membingungkan' => false, 'Menjawab tanpa memeriksa kebutuhan' => false]],
            ['prompt' => 'Apa tujuan standar keselamatan area kerja?', 'answers' => ['Menjaga produk, tim, dan pelanggan' => true, 'Membatasi pelayanan pelanggan' => false, 'Mempercepat pekerjaan tanpa pengecekan' => false]],
        ]);

        $onboardingPath = LearningPath::query()->updateOrCreate(
            ['name' => 'Onboarding Karyawan Baru'],
            ['description' => 'Jalur wajib bagi karyawan baru sebelum menjalankan tugas layanan.', 'status' => 'published', 'published_at' => $now->copy()->subDays(20)],
        );
        $frontlinerPath = LearningPath::query()->updateOrCreate(
            ['name' => 'Kompetensi Frontliner'],
            ['description' => 'Penguatan pengetahuan produk dan standar layanan untuk tim sales.', 'status' => 'published', 'published_at' => $now->copy()->subDays(10)],
        );
        LearningPath::query()->updateOrCreate(
            ['name' => 'Pengembangan Store Leader'],
            ['description' => 'Program lanjutan untuk calon pemimpin toko.', 'status' => 'draft', 'published_at' => null],
        );
        $this->seedPathCourses($onboardingPath, [$onboarding, $product]);
        $this->seedPathCourses($frontlinerPath, [$product, $service]);
        LearningPathRule::query()->updateOrCreate([
            'learning_path_id' => $onboardingPath->id,
            'division_id' => $divisions['SLS']->id,
            'position_id' => $positions['sales_associate']->id,
            'branch_id' => $branches['KGJ-MRB']->id,
        ]);
        LearningPathRule::query()->updateOrCreate([
            'learning_path_id' => $frontlinerPath->id,
            'division_id' => $divisions['SLS']->id,
            'position_id' => $positions['sales_associate']->id,
            'branch_id' => $branches['KGJ-SLM']->id,
        ]);
        foreach ([[$anisa, $onboardingPath], [$bima, $onboardingPath], [$citra, $frontlinerPath]] as [$user, $path]) {
            LearningPathAssignment::query()->firstOrCreate(['user_id' => $user->id, 'learning_path_id' => $path->id]);
        }

        $anisaOnboarding = $this->seedEnrollment($anisa, $onboarding, 'completed', $now->copy()->subDays(12), $now->copy()->subDays(6));
        $anisaProduct = $this->seedEnrollment($anisa, $product, 'active', $now->copy()->addDays(7));
        $anisaService = $this->seedEnrollment($anisa, $service, 'active', $now->copy()->addDays(14));
        $bimaOnboarding = $this->seedEnrollment($bima, $onboarding, 'active', $now->copy()->subDay());
        $citraOnboarding = $this->seedEnrollment($citra, $onboarding, 'completed', $now->copy()->subDays(18), $now->copy()->subDays(10));
        $citraProduct = $this->seedEnrollment($citra, $product, 'completed', $now->copy()->subDays(3), $now->copy()->subDay());
        $citraService = $this->seedEnrollment($citra, $service, 'active', $now->copy()->addDays(5));

        $this->completeLessons($anisaOnboarding, $onboardingLessons, 5, $now->copy()->subDays(6));
        $this->completeLessons($anisaProduct, $productLessons, 2, $now->copy()->subDays(2));
        $this->completeLessons($anisaService, $serviceLessons, 1, $now->copy()->subDay());
        $this->completeLessons($bimaOnboarding, $onboardingLessons, 1, $now->copy()->subDays(2));
        $this->completeLessons($citraOnboarding, $onboardingLessons, 5, $now->copy()->subDays(10));
        $this->completeLessons($citraProduct, $productLessons, 4, $now->copy()->subDay());
        $this->completeLessons($citraService, $serviceLessons, 2, $now->copy()->subDay());

        $this->seedAttempt($preTest, $anisaOnboarding, 1, 1, true, $now->copy()->subDays(12));
        $this->seedAttempt($postTest, $anisaOnboarding, 1, 2, true, $now->copy()->subDays(6));
        $this->seedAttempt($preTest, $bimaOnboarding, 1, 1, true, $now->copy()->subDays(4));
        $this->seedAttempt($preTest, $citraOnboarding, 1, 1, true, $now->copy()->subDays(18));
        $this->seedAttempt($postTest, $citraOnboarding, 1, 1, false, $now->copy()->subDays(11));
        $this->seedAttempt($postTest, $citraOnboarding, 2, 2, true, $now->copy()->subDays(10));
        (new IssueCertificate($anisaOnboarding->id))->handle();
        (new IssueCertificate($citraOnboarding->id))->handle();

        $announcement = Announcement::query()->updateOrCreate(
            ['title' => 'Selamat datang di KGJ Academy'],
            ['user_id' => $lmsAdmin->id, 'body' => 'Lengkapi materi yang telah ditetapkan dan pantau progres belajar Anda secara berkala.', 'published_at' => $now->copy()->subDays(5)],
        );
        $reminder = Announcement::query()->updateOrCreate(
            ['title' => 'Pengingat tenggat pembelajaran'],
            ['user_id' => $lmsAdmin->id, 'body' => 'Periksa course aktif Anda dan selesaikan materi sebelum tenggat waktu.', 'published_at' => $now->copy()->subDay()],
        );
        $this->seedNotifications([$admin, $lmsAdmin, $trainer, $manager, $anisa, $bima, $citra], $announcement, $reminder, $now);

        $reportPath = 'exports/demo-progres-pembelajaran.csv';
        Storage::disk('local')->put($reportPath, "ID Karyawan,Nama,Course,Status\nKGJ-101,Anisa Putri,ONB-001,completed\nKGJ-102,Bima Aditya,ONB-001,active\n");
        ReportExport::query()->updateOrCreate(
            ['user_id' => $lmsAdmin->id, 'path' => $reportPath],
            ['filters' => ['status' => 'active'], 'status' => 'ready', 'error' => null, 'completed_at' => $now->copy()->subHours(2)],
        );
        ReportExport::query()->updateOrCreate(
            ['user_id' => $lmsAdmin->id, 'path' => 'exports/pending-demo-report.csv'],
            ['filters' => ['course_id' => $onboarding->id], 'status' => 'queued', 'error' => null, 'completed_at' => null],
        );
        UserImport::query()->updateOrCreate(
            ['user_id' => $lmsAdmin->id, 'original_name' => 'data-karyawan-contoh.csv'],
            ['path' => 'user-imports/data-karyawan-contoh.csv', 'status' => 'completed', 'total_rows' => 3, 'imported_rows' => 2, 'skipped_rows' => 1, 'errors' => [['line' => 4, 'message' => 'Email duplikat dilewati.']], 'completed_at' => $now->copy()->subDays(2)],
        );

        $this->seedActivity($lmsAdmin, 'course.published', $onboarding, ['course_code' => $onboarding->code], $now->copy()->subDays(21));
        $this->seedActivity($lmsAdmin, 'learning-path.assigned', $anisaOnboarding, ['learning_path' => $onboardingPath->name], $now->copy()->subDays(12));
        $this->seedActivity($anisa, 'lesson.completed', $onboardingLessons['Pedoman Etika Kerja'], ['course_code' => $onboarding->code], $now->copy()->subDays(7));
        $this->seedActivity($citra, 'assessment.submitted', $postTest, ['score' => 100, 'passed' => true], $now->copy()->subDays(10));
        $this->seedActivity($lmsAdmin, 'announcement.published', $reminder, ['recipients' => 7], $now->copy()->subDay());
    }

    /** @param array<string, mixed> $attributes */
    private function upsertUser(Role $role, array $attributes, string $password, CarbonInterface $verifiedAt): User
    {
        $user = User::query()->updateOrCreate(['email' => $attributes['email']], [...$attributes, 'password' => $password]);
        $user->forceFill(['email_verified_at' => $verifiedAt])->save();
        $user->roles()->sync([$role->id]);

        return $user;
    }

    /** @param array<string, mixed> $attributes */
    private function upsertCourse(CourseCategory $category, array $attributes): Course
    {
        return Course::query()->updateOrCreate(['code' => $attributes['code']], [...$attributes, 'category_id' => $category->id]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $modules
     * @return Collection<string, Lesson>
     */
    private function seedLessons(Course $course, array $modules): Collection
    {
        $lessons = collect();

        foreach ($modules as $moduleSequence => $moduleData) {
            $module = CourseModule::query()->updateOrCreate(
                ['course_id' => $course->id, 'sequence' => $moduleSequence + 1],
                ['title' => $moduleData['title']],
            );

            foreach ($moduleData['lessons'] as $lessonSequence => $lessonData) {
                $lesson = Lesson::query()->updateOrCreate(
                    ['module_id' => $module->id, 'sequence' => $lessonSequence + 1],
                    [
                        'title' => $lessonData['title'],
                        'type' => $lessonData['type'] ?? 'article',
                        'content' => $lessonData['content'],
                        'resource_url' => null,
                        'is_required' => true,
                    ],
                );
                $lessons->put($lesson->title, $lesson);
            }
        }

        return $lessons;
    }

    /** @param array<int, array<string, mixed>> $questions */
    private function seedAssessment(Course $course, string $type, int $passingGrade, int $timeLimit, array $questions): Assessment
    {
        $assessment = Assessment::query()->updateOrCreate(
            ['course_id' => $course->id, 'type' => $type],
            ['passing_grade' => $passingGrade, 'attempt_limit' => $type === 'pre_test' ? 1 : 3, 'time_limit_minutes' => $timeLimit],
        );

        foreach ($questions as $questionSequence => $questionData) {
            $question = Question::query()->updateOrCreate(
                ['assessment_id' => $assessment->id, 'sequence' => $questionSequence + 1],
                ['prompt' => $questionData['prompt'], 'points' => 1],
            );

            foreach (array_keys($questionData['answers']) as $answerSequence => $content) {
                Answer::query()->updateOrCreate(
                    ['question_id' => $question->id, 'sequence' => $answerSequence + 1],
                    ['content' => $content, 'is_correct' => $questionData['answers'][$content]],
                );
            }
        }

        return $assessment;
    }

    /** @param array<int, Course> $courses */
    private function seedPathCourses(LearningPath $path, array $courses): void
    {
        foreach ($courses as $sequence => $course) {
            LearningPathCourse::query()->updateOrCreate(
                ['learning_path_id' => $path->id, 'course_id' => $course->id],
                ['sequence' => $sequence + 1],
            );
        }
    }

    private function seedEnrollment(User $user, Course $course, string $status, CarbonInterface $dueAt, ?CarbonInterface $completedAt = null): Enrollment
    {
        return Enrollment::query()->updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => $status, 'due_at' => $dueAt, 'started_at' => $dueAt->copy()->subDays(7), 'completed_at' => $completedAt],
        );
    }

    /** @param Collection<string, Lesson> $lessons */
    private function completeLessons(Enrollment $enrollment, Collection $lessons, int $count, CarbonInterface $completedAt): void
    {
        $lessons->take($count)->each(function (Lesson $lesson) use ($enrollment, $completedAt): void {
            LessonProgress::query()->updateOrCreate(
                ['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id],
                ['status' => 'completed', 'completed_at' => $completedAt],
            );
        });
    }

    private function seedAttempt(Assessment $assessment, Enrollment $enrollment, int $attemptNumber, int $correctAnswers, bool $passed, CarbonInterface $submittedAt): void
    {
        $questions = $assessment->questions()->with('answers')->get();
        $attempt = AssessmentAttempt::query()->updateOrCreate(
            ['assessment_id' => $assessment->id, 'enrollment_id' => $enrollment->id, 'attempt_number' => $attemptNumber],
            [
                'score' => round(100 * $correctAnswers / $questions->count(), 2),
                'passed' => $passed,
                'started_at' => $submittedAt->copy()->subMinutes(5),
                'expires_at' => $submittedAt->copy()->addMinutes($assessment->time_limit_minutes),
                'submitted_at' => $submittedAt,
            ],
        );

        $questions->values()->each(function (Question $question, int $index) use ($attempt, $correctAnswers): void {
            $answer = $question->answers->firstWhere('is_correct', $index < $correctAnswers)
                ?? $question->answers->firstWhere('is_correct', $index >= $correctAnswers);

            AssessmentResponse::query()->updateOrCreate(
                ['assessment_attempt_id' => $attempt->id, 'question_id' => $question->id],
                ['answer_id' => $answer?->id, 'is_correct' => $index < $correctAnswers],
            );
        });
    }

    /** @param array<int, User> $users */
    private function seedNotifications(array $users, Announcement $announcement, Announcement $reminder, CarbonInterface $now): void
    {
        foreach ($users as $index => $user) {
            UserNotification::query()->updateOrCreate(
                ['user_id' => $user->id, 'announcement_id' => $announcement->id],
                ['title' => $announcement->title, 'body' => $announcement->body, 'read_at' => $index % 2 === 0 ? $now->copy()->subDays(4) : null, 'whatsapp_status' => 'sent', 'whatsapp_error' => null],
            );
            UserNotification::query()->updateOrCreate(
                ['user_id' => $user->id, 'announcement_id' => $reminder->id],
                [
                    'title' => $reminder->title,
                    'body' => $reminder->body,
                    'read_at' => null,
                    'whatsapp_status' => match ($index % 3) {
                        0 => 'queued', 1 => 'skipped', default => 'failed'
                    },
                    'whatsapp_error' => $index % 3 === 2 ? 'Nomor WhatsApp contoh belum dapat dihubungi.' : null,
                ],
            );
        }
    }

    /** @param array<string, mixed> $properties */
    private function seedActivity(User $actor, string $event, Model $subject, array $properties, CarbonInterface $createdAt): void
    {
        DB::table('activity_logs')->updateOrInsert(
            ['actor_id' => $actor->id, 'event' => $event, 'subject_type' => $subject::class, 'subject_id' => $subject->getKey()],
            ['properties' => json_encode($properties, JSON_THROW_ON_ERROR), 'created_at' => $createdAt],
        );
    }

    private function demoPdf(): string
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            "<< /Length 59 >>\nstream\nBT\n/F1 18 Tf\n72 720 Td\n(Pedoman Etika Kerja KGJ) Tj\nET\nendstream",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $number + 1, $object);
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
