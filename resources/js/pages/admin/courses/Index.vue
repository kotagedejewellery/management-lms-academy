<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { BookOpen, Plus, Search } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Course = {
    id: number;
    code: string;
    title: string;
    estimated_minutes: number | null;
    is_mandatory: boolean;
    status: 'draft' | 'published' | 'archived';
    category: { name: string } | null;
    division: { name: string } | null;
    modules_count: number;
};
const props = defineProps<{
    courses: {
        data: Course[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: { search?: string; status?: string };
    can_create_course: boolean;
    is_trainer: boolean;
}>();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
function applyFilters(): void {
    router.get(
        '/courses',
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}
defineOptions({
    layout: { breadcrumbs: [{ title: 'Courses', href: '/courses' }] },
});
</script>

<template>
    <Head title="Courses" />
    <div class="app-page">
        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-end"
        >
            <div class="max-w-2xl">
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    {{ is_trainer ? 'Course binaan' : 'Courses' }}
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    {{
                        is_trainer
                            ? 'Kelola materi dan assessment pada course yang ditugaskan kepada Anda.'
                            : 'Bangun materi, modul, dan lesson sebelum menerbitkan course untuk learner.'
                    }}
                </p>
            </div>
            <Button v-if="can_create_course" as-child
                ><Link href="/courses/create"
                    ><Plus /> Tambah course</Link
                ></Button
            >
        </div>
        <form
            class="grid gap-3 rounded-xl bg-card p-4 shadow-sm ring-1 ring-border sm:grid-cols-[minmax(0,1fr)_11rem_auto]"
            @submit.prevent="applyFilters"
        >
            <div class="relative">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                /><Input
                    v-model="search"
                    class="pl-9"
                    aria-label="Cari course"
                    placeholder="Cari judul atau course code"
                />
            </div>
            <select
                v-model="status"
                aria-label="Filter status course"
                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
            >
                <option value="">Semua status</option>
                <option value="draft">Draf</option>
                <option value="published">Dipublikasikan</option>
                <option value="archived">Diarsipkan</option></select
            ><Button type="submit">Terapkan</Button>
        </form>
        <section
            v-if="courses.data.length"
            class="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border"
        >
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead
                        class="border-b border-border bg-muted/40 text-xs text-muted-foreground"
                    >
                        <tr>
                            <th class="px-5 py-3">Course</th>
                            <th class="px-5 py-3">Audience</th>
                            <th class="px-5 py-3">Materi</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="course in courses.data" :key="course.id">
                            <td class="px-5 py-4">
                                <div class="font-medium text-foreground">
                                    {{ course.title }}
                                </div>
                                <div class="mt-1 text-xs text-muted-foreground">
                                    <span class="tabular-nums">{{
                                        course.code
                                    }}</span>
                                    ·
                                    {{
                                        course.category?.name ||
                                        'Tanpa kategori'
                                    }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-muted-foreground">
                                {{ course.division?.name || 'Semua divisi' }}
                            </td>
                            <td class="px-5 py-4 text-muted-foreground">
                                {{ course.modules_count }} modul ·
                                {{
                                    course.estimated_minutes
                                        ? `${course.estimated_minutes} menit`
                                        : 'Durasi belum diatur'
                                }}
                            </td>
                            <td class="px-5 py-4">
                                <StatusBadge :status="course.status" />
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="`/courses/${course.id}/content`"
                                    class="font-medium underline decoration-border underline-offset-4 hover:decoration-foreground"
                                    >Materi</Link
                                ><Link
                                    :href="`/courses/${course.id}/edit`"
                                    class="ml-4 font-medium underline decoration-border underline-offset-4 hover:decoration-foreground"
                                    >Edit</Link
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="divide-y divide-border md:hidden">
                <article
                    v-for="course in courses.data"
                    :key="course.id"
                    class="p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-medium text-foreground">
                                {{ course.title }}
                            </h2>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ course.code }} ·
                                {{ course.category?.name || 'Tanpa kategori' }}
                            </p>
                        </div>
                        <StatusBadge :status="course.status" />
                    </div>
                    <p class="mt-4 text-sm text-muted-foreground">
                        {{ course.modules_count }} modul ·
                        {{ course.is_mandatory ? 'Wajib' : 'Opsional' }}
                    </p>
                    <div class="mt-4 flex gap-4">
                        <Link
                            :href="`/courses/${course.id}/content`"
                            class="text-sm font-medium underline underline-offset-4"
                            >Materi</Link
                        ><Link
                            :href="`/courses/${course.id}/edit`"
                            class="text-sm font-medium underline underline-offset-4"
                            >Edit</Link
                        >
                    </div>
                </article>
            </div>
            <Pagination
                v-if="courses.total"
                :from="courses.from"
                :to="courses.to"
                :total="courses.total"
                :previous="courses.prev_page_url"
                :next="courses.next_page_url"
            />
        </section>
        <section v-else class="rounded-xl bg-card shadow-sm ring-1 ring-border">
            <EmptyState
                :icon="BookOpen"
                title="Belum ada course yang cocok"
                :description="
                    can_create_course
                        ? 'Tambahkan course pertama atau ubah filter pencarian.'
                        : is_trainer
                          ? 'Belum ada course binaan. Hubungi LMS Admin untuk menetapkan Anda sebagai Trainer / Mentor.'
                          : 'Course yang ditugaskan kepada Anda akan muncul di sini.'
                "
                ><Button v-if="can_create_course" as-child
                    ><Link href="/courses/create">Tambah course</Link></Button
                ></EmptyState
            >
        </section>
    </div>
</template>
