<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, UsersRound, X } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Enrollment = {
    id: number;
    status: 'active' | 'completed' | 'not_passed';
    due_at: string | null;
    completed_at: string | null;
    pre_test_score: number | null;
    best_post_score: number | null;
    improvement: number | null;
    post_test_attempts: number;
    user: { employee_id: string | null; name: string } | null;
    course: { code: string; title: string } | null;
};
type Filters = { search: string; status: string };
const props = defineProps<{
    enrollments: {
        data: Enrollment[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: Filters;
}>();
const filters = { search: props.filters.search, status: props.filters.status };
function applyFilters(): void {
    router.get('/team-progress', filters, {
        preserveState: true,
        replace: true,
    });
}
function resetFilters(): void {
    filters.search = '';
    filters.status = '';
    applyFilters();
}
function score(value: number | null): string {
    return value === null ? '—' : value.toFixed(2);
}
function statusLabel(status: Enrollment['status']): string {
    return { active: 'Aktif', completed: 'Selesai', not_passed: 'Tidak lulus' }[
        status
    ];
}
defineOptions({
    layout: { breadcrumbs: [{ title: 'Progres Tim', href: '/team-progress' }] },
});
</script>

<template>
    <Head title="Progres Tim" />
    <div class="flex w-full flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <header>
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                Progres tim
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                Pantau baseline, hasil evaluasi, dan improvement direct report
                Anda tanpa melihat tim lain.
            </p>
        </header>
        <form
            class="grid gap-3 rounded-xl border border-border bg-card p-4 sm:grid-cols-[minmax(0,1fr)_11rem_auto_auto]"
            @submit.prevent="applyFilters"
        >
            <div class="grid gap-2">
                <Label for="team-search">Cari</Label
                ><Input
                    id="team-search"
                    v-model="filters.search"
                    placeholder="Nama, ID, atau course"
                />
            </div>
            <div class="grid gap-2">
                <Label for="team-status">Status</Label
                ><select
                    id="team-status"
                    v-model="filters.status"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                >
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="completed">Selesai</option>
                    <option value="not_passed">Tidak lulus</option>
                </select>
            </div>
            <Button class="self-end" type="submit"><Search /> Terapkan</Button
            ><Button
                class="self-end"
                type="button"
                variant="outline"
                @click="resetFilters"
                ><X /> Reset</Button
            >
        </form>
        <section
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div
                v-if="enrollments.data.length"
                class="divide-y divide-border md:hidden"
            >
                <article
                    v-for="item in enrollments.data"
                    :key="item.id"
                    class="space-y-3 px-5 py-4"
                >
                    <div>
                        <h2 class="font-medium text-foreground">
                            {{ item.user?.name }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ item.user?.employee_id ?? 'Tanpa ID karyawan' }}
                        </p>
                    </div>
                    <div>
                        <p class="font-medium text-foreground">
                            {{ item.course?.title }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Pre {{ score(item.pre_test_score) }} · Post
                            {{ score(item.best_post_score) }} ·
                            {{
                                item.improvement === null
                                    ? 'Improvement —'
                                    : `Improvement ${item.improvement > 0 ? '+' : ''}${item.improvement}`
                            }}
                        </p>
                    </div>
                    <p class="text-sm font-medium text-foreground">
                        {{ statusLabel(item.status) }} ·
                        {{ item.post_test_attempts }} attempt
                    </p>
                </article>
            </div>
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-border bg-muted/40 text-muted-foreground"
                    >
                        <tr>
                            <th class="px-5 py-3 font-medium">Karyawan</th>
                            <th class="px-5 py-3 font-medium">Course</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">
                                Pre
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Post terbaik
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Improvement
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Attempt
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="item in enrollments.data" :key="item.id">
                            <td class="px-5 py-4">
                                <p class="font-medium text-foreground">
                                    {{ item.user?.name }}
                                </p>
                                <p class="mt-1 text-muted-foreground">
                                    {{
                                        item.user?.employee_id ??
                                        'Tanpa ID karyawan'
                                    }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-foreground">
                                    {{ item.course?.title }}
                                </p>
                                <p class="mt-1 text-muted-foreground">
                                    {{ item.course?.code }}
                                </p>
                            </td>
                            <td class="px-5 py-4 font-medium text-foreground">
                                {{ statusLabel(item.status) }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{ score(item.pre_test_score) }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{ score(item.best_post_score) }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{
                                    item.improvement === null
                                        ? '—'
                                        : `${item.improvement > 0 ? '+' : ''}${item.improvement}`
                                }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{ item.post_test_attempts }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <EmptyState
                v-if="!enrollments.data.length"
                :icon="UsersRound"
                title="Belum ada data tim"
                description="Direct report yang memiliki assignment akan tampil di sini."
            /><Pagination
                v-else
                :from="enrollments.from"
                :to="enrollments.to"
                :total="enrollments.total"
                :previous="enrollments.prev_page_url"
                :next="enrollments.next_page_url"
            />
        </section>
    </div>
</template>
