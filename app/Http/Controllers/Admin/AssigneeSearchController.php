<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssigneeSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('manage-enrollments'), 403);
        $data = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);

        return response()->json(User::query()
            ->where('status', 'active')
            ->where(fn ($query) => $query
                ->where('name', 'ilike', '%'.$data['q'].'%')
                ->orWhere('employee_id', 'ilike', '%'.$data['q'].'%'))
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'employee_id', 'name']));
    }
}
