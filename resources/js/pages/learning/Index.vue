<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
type Enrollment = {
    id: number;
    status: 'active' | 'completed' | 'not_passed';
    due_at: string | null;
    completed_lessons_count: number;
    lessons_count: number;
    course: {
        id: number;
        code: string;
        title: string;
        description: string | null;
        estimated_minutes: number | null;
    } | null;
};
defineProps<{ enrollments: { data: Enrollment[] } }>();
function statusLabel(status: Enrollment['status']): string {
    return { active: 'Aktif', completed: 'Selesai', not_passed: 'Tidak lulus' }[
        status
    ];
}
defineOptions({
    layout: { breadcrumbs: [{ title: 'My Learning', href: '/my-learning' }] },
});
</script>
<template>
    <Head title="My Learning" />
    <div class="flex w-full flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                My Learning
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                Lanjutkan course yang telah ditugaskan kepada Anda.
            </p>
        </div>
        <section
            v-if="enrollments.data.length"
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            <article
                v-for="item in enrollments.data"
                :key="item.id"
                class="flex flex-col rounded-xl border border-border bg-card p-5"
            >
                <div class="flex items-start justify-between gap-3">
                    <h2 class="font-semibold text-foreground">
                        {{ item.course?.title }}
                    </h2>
                    <span
                        class="shrink-0 text-xs font-medium text-muted-foreground"
                        >{{ statusLabel(item.status) }}</span
                    >
                </div>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    {{ item.course?.description || item.course?.code }}
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
                <Button class="mt-5" as-child
                    ><Link :href="`/my-learning/${item.course?.id}`">{{
                        item.status === 'not_passed'
                            ? 'Lihat hasil'
                            : 'Buka course'
                    }}</Link></Button
                >
            </article>
        </section>
        <section v-else class="rounded-xl border border-border bg-card">
            <EmptyState
                :icon="BookOpen"
                title="Belum ada course yang ditugaskan"
                description="Assignment akan muncul di sini setelah LMS Admin menetapkannya."
            />
        </section>
    </div>
</template>
