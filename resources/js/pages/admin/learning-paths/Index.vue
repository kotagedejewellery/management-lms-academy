<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ListTree, Plus, Search, X } from '@lucide/vue';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import FilterBar from '@/components/FilterBar.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';

type LearningPath = {
    id: number;
    name: string;
    description: string | null;
    status: 'draft' | 'published' | 'archived';
    path_courses_count: number;
};
const props = defineProps<{
    paths: {
        data: LearningPath[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: { status?: string };
}>();
const status = ref(props.filters.status ?? '');
function applyFilters(): void {
    router.get(
        '/learning-paths',
        { status: status.value || undefined },
        { preserveState: true, replace: true },
    );
}
function resetFilters(): void {
    status.value = '';
    applyFilters();
}
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Learning paths', href: '/learning-paths' }],
    },
});
</script>

<template>
    <Head title="Learning paths" />
    <div class="app-page">
        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-end"
        >
            <div class="max-w-2xl">
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    Learning paths
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Susun urutan course untuk kebutuhan onboarding dan
                    pengembangan kompetensi.
                </p>
            </div>
            <Button as-child
                ><Link href="/learning-paths/create"
                    ><Plus /> Tambah learning path</Link
                ></Button
            >
        </div>
        <FilterBar
            layout="grid"
            layout-class="grid-cols-1 sm:grid-cols-[13rem_auto] sm:justify-start"
            @submit.prevent="applyFilters"
        >
            <div class="grid gap-2">
                <label
                    for="path-status"
                    class="text-sm font-medium text-foreground"
                    >Status</label
                ><select
                    id="path-status"
                    v-model="status"
                    class="h-10 rounded-lg border border-input bg-transparent px-3 text-sm"
                >
                    <option value="">Semua status</option>
                    <option value="draft">Draf</option>
                    <option value="published">Dipublikasikan</option>
                    <option value="archived">Diarsipkan</option>
                </select>
            </div>
            <div class="flex w-full gap-2 self-end sm:w-auto">
                <Button class="flex-1 sm:flex-none" type="submit"
                    ><Search /> Terapkan</Button
                >
                <Button
                    class="flex-1 sm:flex-none"
                    type="button"
                    variant="outline"
                    @click="resetFilters"
                    ><X /> Reset</Button
                >
            </div>
        </FilterBar>
        <section
            v-if="paths.data.length"
            class="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border"
        >
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <thead
                        class="border-b border-border bg-muted/40 text-xs text-muted-foreground"
                    >
                        <tr>
                            <th class="px-5 py-3">Learning path</th>
                            <th class="px-5 py-3">Course</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="path in paths.data" :key="path.id">
                            <td class="px-5 py-4">
                                <div class="font-medium text-foreground">
                                    {{ path.name }}
                                </div>
                                <p
                                    v-if="path.description"
                                    class="mt-1 max-w-xl truncate text-xs text-muted-foreground"
                                >
                                    {{ path.description }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-muted-foreground">
                                <span class="tabular-nums">{{
                                    path.path_courses_count
                                }}</span>
                                course
                            </td>
                            <td class="px-5 py-4">
                                <StatusBadge :status="path.status" />
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="`/learning-paths/${path.id}/content`"
                                    class="font-medium underline decoration-border underline-offset-4 hover:decoration-foreground"
                                    >Susun course</Link
                                ><Link
                                    :href="`/learning-paths/${path.id}/edit`"
                                    class="ml-4 font-medium underline decoration-border underline-offset-4 hover:decoration-foreground"
                                    >Edit</Link
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="divide-y divide-border md:hidden">
                <article v-for="path in paths.data" :key="path.id" class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-medium text-foreground">
                                {{ path.name }}
                            </h2>
                            <p
                                v-if="path.description"
                                class="mt-1 text-sm leading-5 text-muted-foreground"
                            >
                                {{ path.description }}
                            </p>
                        </div>
                        <StatusBadge :status="path.status" />
                    </div>
                    <p class="mt-4 text-sm text-muted-foreground">
                        <span class="tabular-nums">{{
                            path.path_courses_count
                        }}</span>
                        course
                    </p>
                    <div class="mt-4 flex gap-4">
                        <Link
                            :href="`/learning-paths/${path.id}/content`"
                            class="text-sm font-medium underline underline-offset-4"
                            >Susun course</Link
                        ><Link
                            :href="`/learning-paths/${path.id}/edit`"
                            class="text-sm font-medium underline underline-offset-4"
                            >Edit</Link
                        >
                    </div>
                </article>
            </div>
            <Pagination
                :from="paths.from"
                :to="paths.to"
                :total="paths.total"
                :previous="paths.prev_page_url"
                :next="paths.next_page_url"
            />
        </section>
        <section v-else class="rounded-xl bg-card shadow-sm ring-1 ring-border">
            <EmptyState
                :icon="ListTree"
                title="Belum ada learning path"
                description="Buat learning path pertama untuk mulai menyusun urutan course."
                ><Button as-child
                    ><Link href="/learning-paths/create"
                        >Tambah learning path</Link
                    ></Button
                ></EmptyState
            >
        </section>
    </div>
</template>
