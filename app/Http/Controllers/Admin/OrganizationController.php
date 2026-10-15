<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(Request $request, string $type): Response
    {
        $this->authorizeOrganization($request);
        [$model, $label] = $this->entity($type);

        $items = $model::query()
            ->when($type === 'positions', fn ($query) => $query->with('division:id,name'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Model $item) => $this->itemData($type, $item));

        $record = $request->integer('edit')
            ? $model::query()->find($request->integer('edit'))
            : null;

        return Inertia::render('admin/organization/Index', [
            'type' => $type,
            'label' => $label,
            'items' => $items,
            'record' => $record ? $this->itemData($type, $record) : null,
            'divisions' => Division::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $this->authorizeOrganization($request);
        [$model] = $this->entity($type);
        $model::query()->create($this->validatedData($request, $type));

        return to_route('organization.index', ['type' => $type]);
    }

    public function update(Request $request, string $type, int $record): RedirectResponse
    {
        $this->authorizeOrganization($request);
        [$model] = $this->entity($type);
        $item = $model::query()->findOrFail($record);
        $item->update($this->validatedData($request, $type, $item));

        return to_route('organization.index', ['type' => $type]);
    }

    private function authorizeOrganization(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-organization'), 403);
    }

    /** @return array{class-string<Branch|Division|Position>, string} */
    private function entity(string $type): array
    {
        return match ($type) {
            'divisions' => [Division::class, 'Divisi'],
            'positions' => [Position::class, 'Jabatan'],
            'branches' => [Branch::class, 'Cabang'],
            default => abort(404),
        };
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, string $type, ?Model $item = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['required', 'boolean'],
        ];

        if ($type === 'positions') {
            $rules['division_id'] = ['required', 'integer', Rule::exists('divisions', 'id')];

            return $request->validate($rules);
        }

        $table = $type;
        $rules['code'] = ['required', 'string', 'max:32', Rule::unique($table, 'code')->ignore($item)];

        return $request->validate($rules);
    }

    /** @return array<string, mixed> */
    private function itemData(string $type, Model $item): array
    {
        $position = $item instanceof Position ? $item : null;

        return [
            'id' => (int) $item->getKey(),
            'code' => $position ? null : $item->getAttribute('code'),
            'name' => (string) $item->getAttribute('name'),
            'division_id' => $position?->division_id,
            'division' => $position?->division?->only(['id', 'name']),
            'is_active' => (bool) $item->getAttribute('is_active'),
        ];
    }
}
