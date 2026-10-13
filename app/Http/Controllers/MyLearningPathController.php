<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LearningPathAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyLearningPathController extends Controller
{
    public function index(Request $request): Response
    {
        $assignments = LearningPathAssignment::query()->where('user_id', $request->user()->id)->with('learningPath.pathCourses.course:id,code,title')->latest()->paginate(12);
        $courseIds = $assignments->getCollection()->flatMap(fn ($a) => $a->learningPath->pathCourses->pluck('course_id'))->unique();
        $enrollments = Enrollment::query()->where('user_id', $request->user()->id)->whereIn('course_id', $courseIds)->whereIn('status', ['active', 'completed', 'not_passed'])->get()->keyBy('course_id');
        $assignments->getCollection()->transform(fn ($a) => ['id' => $a->id, 'name' => $a->learningPath->name, 'description' => $a->learningPath->description, 'courses' => $a->learningPath->pathCourses->map(fn ($pc) => ['id' => $pc->course_id, 'sequence' => $pc->sequence, 'code' => $pc->course?->code, 'title' => $pc->course?->title, 'enrollment_status' => $enrollments->get($pc->course_id)?->status])]);

        return Inertia::render('learning/Paths', ['paths' => $assignments]);
    }
}
