<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessUserImport;
use App\Models\UserImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserImportController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeUsers($request);

        return Inertia::render('admin/users/Import', [
            'imports' => $request->user()->userImports()->latest()->paginate(10)->through(fn (UserImport $import) => [
                'id' => $import->id, 'original_name' => $import->original_name, 'status' => $import->status, 'total_rows' => $import->total_rows, 'imported_rows' => $import->imported_rows, 'skipped_rows' => $import->skipped_rows, 'errors' => $import->errors ?? [], 'completed_at' => $import->completed_at?->toIso8601String(), 'created_at' => $import->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUsers($request);
        $file = $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']])['file'];
        $import = $request->user()->userImports()->create(['path' => $file->store('user-imports', 'local'), 'original_name' => $file->getClientOriginalName(), 'status' => 'queued']);
        ProcessUserImport::dispatch($import->id)->afterCommit();

        return to_route('user-imports.index');
    }

    private function authorizeUsers(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-users'), 403);
    }
}
