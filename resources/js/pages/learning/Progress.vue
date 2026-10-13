<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, ChevronRight } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';

type Enrollment = {
    id: number;
    status: 'active' | 'completed';
    completed_at: string | null;
    best_post_score: number | null;
    completed_lessons_count: number;
    lessons_count: number;
    progress: number;
    course: { id: number; code: string; title: string } | null;
};

defineProps<{
    stats: { active: number; completed: number };
    enrollments: {
        data: Enrollment[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}>();

function score(value: number | null): string {
    return value === null ? 'Belum ada' : value.toFixed(2);
}
defineOptions({
    layout: { breadcrumbs: [{ title: 'My Progress', href: '/my-progress' }] },
});
</script>

<template>
    <Head title="My Progress" />

    <div
        class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8"
    >
        <header>
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                My Progress
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                Lihat penyelesaian lesson dan hasil Post-Test terbaik Anda.
            </p>
        </header>

        <dl class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-border bg-card p-5">
                <dt class="text-sm text-muted-foreground">Sedang dipelajari</dt>
                <dd
                    class="mt-2 text-2xl font-semibold text-foreground tabular-nums"
                >
                    {{ stats.active }}
                </dd>
            </div>
            <div class="rounded-xl border border-border bg-card p-5">
                <dt class="text-sm text-muted-foreground">Course selesai</dt>
                <dd
                    class="mt-2 text-2xl font-semibold text-foreground tabular-nums"
                >
                    {{ stats.completed }}
                </dd>
            </div>
        </dl>

        <section
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div v-if="enrollments.data.length" class="divide-y divide-border">
                <article
                    v-for="item in enrollments.data"
                    :key="item.id"
                    class="px-5 py-5"
                >
                    <div
                        class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
                    >
                        <div class="min-w-0">
                            <p class="text-sm text-muted-foreground">
                                {{ item.course?.code }}
                            </p>
                            <h2 class="mt-1 font-semibold text-foreground">
                                {{ item.course?.title }}
                            </h2>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{
                                    item.status === 'completed'
                                        ? `Selesai${item.completed_at ? ` · ${item.completed_at}` : ''}`
                                        : 'Sedang dipelajari'
                                }}
                                · Post-Test terbaik:
                                {{ score(item.best_post_score) }}
                            </p>
                        </div>
                        <Button
                            v-if="item.course && item.status === 'active'"
                            size="sm"
                            variant="outline"
                            as-child
                            ><Link :href="`/my-learning/${item.course.id}`"
                                >Lanjutkan <ChevronRight /></Link
                        ></Button>
                    </div>
                    <div class="mt-4">
                        <div
                            class="flex justify-between text-sm text-muted-foreground"
                        >
                            <span>Lesson selesai</span
                            ><span class="tabular-nums"
                                >{{ item.completed_lessons_count }}/{{
                                    item.lessons_count
                                }}
                                · {{ item.progress }}%</span
                            >
                        </div>
                        <div
                            class="mt-2 h-2 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-foreground transition-[width] duration-300"
                                :style="{ width: `${item.progress}%` }"
                            />
                        </div>
                    </div>
                </article>
            </div>
            <EmptyState
                v-else
                :icon="BookOpen"
                title="Belum ada progres pembelajaran"
                description="Course yang ditetapkan kepada Anda akan tampil di sini."
                ><Button as-child
                    ><Link href="/my-learning">Lihat My Learning</Link></Button
                ></EmptyState
            >
            <Pagination
                v-if="enrollments.data.length"
                :from="enrollments.from"
                :to="enrollments.to"
                :total="enrollments.total"
                :previous="enrollments.prev_page_url"
                :next="enrollments.next_page_url"
            />
        </section>
    </div>
</template>
