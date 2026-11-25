<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ManagerRelationshipService
{
    /** @return EloquentCollection<int, User> */
    public function options(int $divisionId, ?User $exclude = null): EloquentCollection
    {
        return User::query()
            ->where('status', 'active')
            ->where('division_id', $divisionId)
            ->where('is_organization_manager', true)
            ->when($exclude, fn ($query) => $query->whereNotIn('id', $this->exclusionIds($exclude)))
            ->orderBy('name')
            ->get(['id', 'employee_id', 'name']);
    }

    public function validationMessage(?int $managerId, ?int $divisionId, ?User $user = null): ?string
    {
        if (! $managerId) {
            return null;
        }

        if (! $divisionId) {
            return 'Pilih divisi sebelum menetapkan manager.';
        }

        $manager = User::query()->find($managerId);

        if (! $manager || $manager->status !== 'active' || $manager->division_id !== $divisionId) {
            return 'Manager harus aktif dan berada pada divisi yang sama.';
        }

        if (! $manager->is_organization_manager) {
            return 'Akun yang dipilih belum ditetapkan sebagai Atasan.';
        }

        if (! $user) {
            return null;
        }

        if ($manager->is($user)) {
            return 'Manager tidak boleh merujuk ke akun yang sama.';
        }

        if (in_array($manager->id, $this->exclusionIds($user), true)) {
            return 'Manager tidak boleh merupakan bawahan dari akun ini.';
        }

        return null;
    }

    /** @return array<int, int> */
    private function exclusionIds(User $user): array
    {
        $excluded = [$user->id];
        $directReportIds = [$user->id];

        while ($directReportIds !== []) {
            $directReportIds = User::query()
                ->whereIn('manager_id', $directReportIds)
                ->pluck('id')
                ->all();
            $directReportIds = array_values(array_diff($directReportIds, $excluded));
            $excluded = [...$excluded, ...$directReportIds];
        }

        return $excluded;
    }
}
