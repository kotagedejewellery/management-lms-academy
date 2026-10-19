<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerServiceDealmakerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn (): Course => $this->seedCourse());
    }

    public function seedCourse(): Course
    {
        $category = CourseCategory::query()->firstOrCreate(
            ['name' => 'Layanan Pelanggan'],
            ['is_active' => true],
        );

        $course = Course::query()->firstOrCreate(
            ['code' => 'CS-DEAL-001'],
            [
                'category_id' => $category->id,
                'title' => 'Customer Service Dealmaker Mastery',
                'description' => 'Program penguatan kemampuan customer service, product knowledge, dan strategi penjualan perhiasan.',
                'estimated_minutes' => 300,
                'is_mandatory' => false,
                'requires_post_test' => false,
                'certificate_enabled' => false,
                'passing_grade' => 70,
                'status' => 'published',
                'published_at' => now(),
            ],
        );

        foreach ($this->modules() as $moduleSequence => $moduleData) {
            $module = CourseModule::query()->firstOrCreate(
                ['course_id' => $course->id, 'sequence' => $moduleSequence + 1],
                ['title' => $moduleData['title']],
            );

            Lesson::query()->firstOrCreate(
                ['module_id' => $module->id, 'sequence' => 1],
                [
                    'title' => 'Presentasi '.$moduleData['title'],
                    'type' => 'presentation',
                    'content' => null,
                    'transcript' => null,
                    'resource_url' => $moduleData['presentation_url'],
                    'is_required' => true,
                ],
            );
            Lesson::query()->firstOrCreate(
                ['module_id' => $module->id, 'sequence' => 2],
                [
                    'title' => 'Video '.$moduleData['title'],
                    'type' => 'video',
                    'content' => null,
                    'transcript' => null,
                    'resource_url' => $moduleData['video_url'],
                    'is_required' => true,
                ],
            );
        }

        return $course;
    }

    /** @return array<int, array{title: string, presentation_url: string, video_url: string}> */
    private function modules(): array
    {
        return [
            ['title' => 'CS Dealmaker Mastery', 'presentation_url' => 'https://www.canva.com/design/DAHJVwit-10/Z7OevY3UT_3y3nbIKMstkw/view', 'video_url' => 'https://youtu.be/DphVgtOniXc'],
            ['title' => 'Product Knowledge', 'presentation_url' => 'https://www.canva.com/design/DAHJbhbdWnk/xmzjGgpsZE98qm7zYwKcLA/view', 'video_url' => 'https://youtu.be/nf8GqkgCKlk'],
            ['title' => 'Work Environment & Customer Character Analysis', 'presentation_url' => 'https://www.canva.com/design/DAHJss7IOao/y2hsFGtxoh80O0MognzZ2Q/view', 'video_url' => 'https://youtu.be/W7hghh3cunI'],
            ['title' => 'High-End Product Sales Strategy', 'presentation_url' => 'https://www.canva.com/design/DAHJuExbLC4/--KSSFG9JHoifWumQMosUg/view', 'video_url' => 'https://youtu.be/Bb3olSJnsP0'],
            ['title' => 'High Convertion Strategy', 'presentation_url' => 'https://www.canva.com/design/DAHJ_JGW1F4/atk4tzQ1hxGlCB2g_PLjnA/view', 'video_url' => 'https://youtu.be/NrBOCXLlBXE'],
            ['title' => 'Customer Service Career Path', 'presentation_url' => 'https://canva.link/89g2ztdcfb7og9q', 'video_url' => 'https://youtu.be/mN7Zb-KDSKE'],
            ['title' => 'Mastering Detailed Oriented & Budget Conscious', 'presentation_url' => 'https://canva.link/wqzlyop3l4h4dfx', 'video_url' => 'https://youtu.be/vvE0wckeMxQ'],
            ['title' => 'Excellent Problem Solving & ComPlaint Handling', 'presentation_url' => 'https://canva.link/l4k0jufis0p773v', 'video_url' => 'https://youtu.be/i9g6XEZUFLc'],
            ['title' => 'The Jewellery Warior', 'presentation_url' => 'https://canva.link/ipamd5sqj5naqt0', 'video_url' => 'https://youtu.be/Rz9Es3qUU6k'],
            ['title' => 'Sales Excellent & Winning Spirit', 'presentation_url' => 'https://canva.link/zfxyuysode30fp3', 'video_url' => 'https://youtu.be/k1FO1xfwqHI'],
        ];
    }
}
