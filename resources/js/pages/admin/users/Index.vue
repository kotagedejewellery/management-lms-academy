<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileUp, Plus, Search, UserRound, X } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import FilterBar from '@/components/FilterBar.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Person = {
    id: number;
    employee_id: string | null;
    hired_at: string | null;
    name: string;
    email: string;
    status: 'active' | 'inactive';
    role: { id: number; name: string } | null;
    division: { id: number; name: string } | null;
    position: { id: number; name: string } | null;
    branch: { id: number; name: string } | null;
    manager: { id: number; name: string } | null;
};

type PaginatedUsers = {
    data: Person[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

const props = defineProps<{
    users: PaginatedUsers;
    filters: { search?: string; status?: 'active' | 'inactive' };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Users', href: '/users' }],
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

function applyFilters(): void {
    router.get(
        '/users',
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}
function resetFilters(): void {
    search.value = '';
    status.value = '';
    applyFilters();
}
</script>

<template>
    <Head title="Users" />

    <div class="app-page">
        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-end"
        >
            <div class="max-w-2xl">
                <h1
                    class="text-2xl font-semibold tracking-[-0.025em] text-foreground sm:text-3xl"
                >
                    Users
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Kelola akses karyawan, struktur organisasi, dan relasi
                    manager secara manual.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <Button variant="outline" as-child
                    ><Link href="/user-imports"
                        ><FileUp /> Impor CSV</Link
                    ></Button
                ><Button as-child
                    ><Link href="/users/create"
                        ><Plus /> Tambah user</Link
                    ></Button
                >
            </div>
        </div>

        <FilterBar
            layout="grid"
            layout-class="grid-cols-1 md:grid-cols-[minmax(0,1fr)_13rem_auto]"
            @submit.prevent="applyFilters"
        >
            <div class="grid min-w-0 gap-2">
                <Label for="user-search">Cari</Label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="user-search"
                        v-model="search"
                        class="pl-9"
                        placeholder="Cari nama, email, atau Employee ID"
                    />
                </div>
            </div>
            <div class="grid gap-2">
                <Label for="user-status">Status</Label>
                <select
                    id="user-status"
                    v-model="status"
                    class="h-10 rounded-lg border border-input bg-transparent px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                >
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak aktif</option>
                </select>
            </div>
            <div class="flex w-full gap-2 self-end md:w-auto">
                <Button class="flex-1 md:flex-none" type="submit"
                    ><Search /> Terapkan</Button
                >
                <Button
                    class="flex-1 md:flex-none"
                    type="button"
                    variant="outline"
                    @click="resetFilters"
                    ><X /> Reset</Button
                >
            </div>
        </FilterBar>

        <div
            v-if="users.data.length"
            class="overflow-hidden rounded-2xl border border-border/85 bg-card"
        >
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead
                        class="border-b border-border bg-muted/40 text-xs font-medium text-muted-foreground"
                    >
                        <tr>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Organisasi</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="data-row"
                        >
                            <td class="px-5 py-4">
                                <div class="font-medium text-foreground">
                                    {{ user.name }}
                                </div>
                                <div class="mt-1 text-xs text-muted-foreground">
                                    Tanggal masuk:
                                    <span class="tabular-nums">{{
                                        user.hired_at || 'Belum diisi'
                                    }}</span>
                                </div>
                                <div class="mt-1 text-xs text-muted-foreground">
                                    <span class="tabular-nums">{{
                                        user.employee_id || 'Tanpa ID'
                                    }}</span>
                                    · {{ user.email }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-muted-foreground">
                                {{ user.role?.name || 'Belum ditetapkan' }}
                            </td>
                            <td class="px-5 py-4 text-muted-foreground">
                                {{
                                    [
                                        user.division?.name,
                                        user.position?.name,
                                        user.branch?.name,
                                    ]
                                        .filter(Boolean)
                                        .join(' · ') || 'Belum ditetapkan'
                                }}
                            </td>
                            <td class="px-5 py-4">
                                <StatusBadge :status="user.status" />
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="`/users/${user.id}/edit`"
                                    class="font-medium text-foreground underline decoration-border underline-offset-4 hover:decoration-foreground"
                                    >Edit</Link
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-border md:hidden">
                <article
                    v-for="user in users.data"
                    :key="user.id"
                    class="data-row p-4"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-medium text-foreground">
                                {{ user.name }}
                            </h2>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Tanggal masuk:
                                <span class="tabular-nums">{{
                                    user.hired_at || 'Belum diisi'
                                }}</span>
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ user.employee_id || 'Tanpa ID' }} ·
                                {{ user.email }}
                            </p>
                        </div>
                        <StatusBadge :status="user.status" />
                    </div>
                    <p class="mt-4 text-sm text-muted-foreground">
                        {{ user.role?.name || 'Role belum ditetapkan' }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{
                            [
                                user.division?.name,
                                user.position?.name,
                                user.branch?.name,
                            ]
                                .filter(Boolean)
                                .join(' · ') || 'Organisasi belum ditetapkan'
                        }}
                    </p>
                    <Link
                        :href="`/users/${user.id}/edit`"
                        class="mt-4 inline-flex font-medium text-foreground underline decoration-border underline-offset-4 hover:decoration-foreground"
                        >Edit user</Link
                    >
                </article>
            </div>
        </div>

        <div v-else class="surface">
            <EmptyState
                :icon="UserRound"
                title="Belum ada user yang cocok"
                description="Ubah pencarian atau tambahkan user pertama untuk mulai menetapkan akses belajar."
                ><Button as-child
                    ><Link href="/users/create">Tambah user</Link></Button
                ></EmptyState
            >
        </div>

        <Pagination
            v-if="users.total"
            :from="users.from"
            :to="users.to"
            :total="users.total"
            :previous="users.prev_page_url"
            :next="users.next_page_url"
        />
    </div>
</template>
