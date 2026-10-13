<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BookOpen, Users } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

type Stat = { label: string; value: number };
type Course = {
    id: number;
    code: string;
    title: string;
    due_at: string | null;
};
type Enrollment = {
    id: number;
    employee: string;
    course: string;
    status: string;
    due_at: string | null;
};

defineProps<{
    mode: 'learner' | 'management';
    stats: Stat[];
    continue_course: Course | null;
    recent_enrollments: Enrollment[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex w-full flex-1 flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <header>
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                Dashboard
            </h1>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                {{
                    mode === 'management'
                        ? 'Pantau assignment dan penyelesaian course terbaru.'
                        : 'Lanjutkan pembelajaran dan pantau pencapaian Anda.'
                }}
            </p>
        </header>

        <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-xl border border-border bg-card p-5"
            >
                <dt class="text-sm text-muted-foreground">{{ stat.label }}</dt>
                <dd
                    class="mt-2 text-2xl font-semibold text-foreground tabular-nums"
                >
                    {{ stat.value }}
                </dd>
            </div>
        </dl>

        <section
            v-if="mode === 'learner' && continue_course"
            class="rounded-xl border border-border bg-card p-5"
        >
            <p class="text-sm text-muted-foreground">Lanjutkan pembelajaran</p>
            <div
                class="mt-2 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h2 class="font-semibold text-foreground">
                        {{ continue_course.title }}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ continue_course.code
                        }}<span v-if="continue_course.due_at">
                            · Tenggat {{ continue_course.due_at }}</span
                        >
                    </p>
                </div>
                <Button as-child
                    ><Link :href="`/my-learning/${continue_course.id}`"
                        >Lanjutkan <ArrowRight /></Link
                ></Button>
            </div>
        </section>

        <section
            v-else-if="mode === 'learner'"
            class="rounded-xl bg-card shadow-sm ring-1 ring-border"
        >
            <EmptyState
                :icon="BookOpen"
                title="Belum ada pembelajaran aktif"
                description="Course yang ditetapkan untuk Anda akan muncul di sini."
            >
                <Button as-child variant="outline"
                    ><Link href="/my-learning">Lihat My Learning</Link></Button
                >
            </EmptyState>
        </section>

        <section
            v-else
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div class="border-b border-border px-5 py-4">
                <h2 class="font-semibold text-foreground">
                    Assignment terbaru
                </h2>
            </div>
            <div
                v-if="recent_enrollments.length"
                class="divide-y divide-border"
            >
                <div
                    v-for="enrollment in recent_enrollments"
                    :key="enrollment.id"
                    class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="font-medium text-foreground">
                            {{ enrollment.employee }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ enrollment.course }}
                        </p>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{
                            enrollment.status === 'completed'
                                ? 'Selesai'
                                : 'Aktif'
                        }}<span v-if="enrollment.due_at">
                            · {{ enrollment.due_at }}</span
                        >
                    </p>
                </div>
            </div>
            <EmptyState
                v-else
                :icon="Users"
                title="Belum ada assignment"
                description="Tetapkan course kepada karyawan untuk mulai memantau progres."
                ><Button as-child
                    ><Link href="/enrollments">Kelola assignment</Link></Button
                ></EmptyState
            >
        </section>
    </div>
</template>
