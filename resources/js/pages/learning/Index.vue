<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpen } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import FilterBar from '@/components/FilterBar.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
type Enrollment = {
    id: number;
    status: 'active' | 'pending_review' | 'completed' | 'needs_guidance';
    due_at: string | null;
    completed_lessons_count: number;
    lessons_count: number;
    locked: boolean;
    lock_reason: string | null;
    course: {
        id: number;
        code: string;
        title: string;
        description: string | null;
        estimated_minutes: number | null;
        is_mandatory: boolean;
    } | null;
};
const props = defineProps<{
    enrollments: {
        data: Enrollment[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: { state?: string; mandatory?: string };
}>();
const filters = {
    state: props.filters.state ?? '',
    mandatory: props.filters.mandatory ?? '',
};
function applyFilters(): void {
    router.get('/my-learning', filters, { preserveState: true, replace: true });
}
function progress(item: Enrollment): number {
    return item.lessons_count === 0
        ? 0
        : Math.round((item.completed_lessons_count / item.lessons_count) * 100);
}
defineOptions({
    layout: { breadcrumbs: [{ title: 'My Learning', href: '/my-learning' }] },
});
</script>
<template>
    <Head title="My Learning" />
    <div class="app-page">
        <PageHeader
            title="Pembelajaran saya"
            description="Lanjutkan course yang telah ditugaskan dan pantau progres Anda."
        />
        <FilterBar
            layout="grid"
            layout-class="grid-cols-1 sm:max-w-md sm:grid-cols-2"
            @change="applyFilters"
        >
            <label class="grid gap-1 text-sm font-medium text-foreground">
                Status
                <select
                    v-model="filters.state"
                    class="h-10 rounded-lg border border-input bg-background px-3 text-sm"
                >
                    <option value="">Semua status</option>
                    <option value="not_started">Belum mulai</option>
                    <option value="in_progress">Sedang berjalan</option>
                    <option value="completed">Selesai</option>
                </select>
            </label>
            <label class="grid gap-1 text-sm font-medium text-foreground">
                Jenis
                <select
                    v-model="filters.mandatory"
                    class="h-10 rounded-lg border border-input bg-background px-3 text-sm"
                >
                    <option value="">Wajib dan opsional</option>
                    <option value="mandatory">Wajib</option>
                    <option value="optional">Opsional</option>
                </select>
            </label>
        </FilterBar>
        <section
            v-if="enrollments.data.length"
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            <article
                v-for="item in enrollments.data"
                :key="item.id"
                class="flex flex-col rounded-2xl border border-border/85 bg-card p-5 transition-[border-color,transform] duration-200 hover:-translate-y-0.5 hover:border-brand-gold/45"
            >
                <div class="flex items-start justify-between gap-3">
                    <h2 class="font-semibold text-foreground">
                        {{ item.course?.title }}
                    </h2>
                    <StatusBadge
                        class="shrink-0"
                        :status="
                            item.locked
                                ? 'locked'
                                : item.status === 'active'
                                  ? 'in_progress'
                                  : item.status
                        "
                    />
                </div>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    {{ item.course?.description || item.course?.code }}
                </p>
                <p class="mt-3 text-xs font-medium text-muted-foreground">
                    {{
                        item.course?.is_mandatory
                            ? 'Course wajib'
                            : 'Course opsional'
                    }}
                </p>
                <p class="mt-5 text-sm text-muted-foreground">
                    <span class="tabular-nums"
                        >{{ item.completed_lessons_count }}/{{
                            item.lessons_count
                        }}</span
                    >
                    lesson selesai<span v-if="item.due_at">
                        · Tenggat {{ item.due_at }}</span
                    >
                </p>
                <div
                    class="mt-3 h-2 overflow-hidden rounded-full bg-muted"
                    role="progressbar"
                    :aria-valuenow="progress(item)"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :aria-label="`Progres ${item.course?.title ?? 'course'}`"
                >
                    <div
                        class="h-full rounded-full bg-brand-gold transition-[width] duration-300"
                        :style="{ width: `${progress(item)}%` }"
                    />
                </div>
                <p
                    v-if="item.locked"
                    class="mt-5 text-sm font-medium text-muted-foreground"
                >
                    {{ item.lock_reason }}
                </p>
                <Button v-else class="mt-5" as-child
                    ><Link :href="`/my-learning/${item.course?.id}`">{{
                        'Buka course'
                    }}</Link></Button
                >
            </article>
        </section>
        <section v-else class="surface">
            <EmptyState
                :icon="BookOpen"
                title="Belum ada course yang ditugaskan"
                description="Assignment akan muncul di sini setelah LMS Admin menetapkannya."
            />
        </section>
        <Pagination
            v-if="enrollments.data.length"
            :from="enrollments.from"
            :to="enrollments.to"
            :total="enrollments.total"
            :previous="enrollments.prev_page_url"
            :next="enrollments.next_page_url"
        />
    </div>
</template>
