<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ListTree } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
type P = {
    id: number;
    name: string;
    description: string | null;
    courses: {
        id: number;
        sequence: number;
        code: string | null;
        title: string | null;
        enrollment_status: string | null;
        locked: boolean;
        lock_reason: string | null;
    }[];
    progress: { completed: number; total: number };
};
defineProps<{ paths: { data: P[] } }>();
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Learning Paths', href: '/my-learning-paths' }],
    },
});
</script>
<template>
    <Head title="Learning Paths" />
    <div class="app-page">
        <PageHeader
            title="Jalur belajar saya"
            description="Ikuti urutan course yang ditetapkan untuk pengembangan kompetensi Anda."
        />
        <section v-if="paths.data.length" class="grid gap-4">
            <article
                v-for="path in paths.data"
                :key="path.id"
                class="rounded-2xl border border-border/85 bg-card p-5"
            >
                <h2 class="font-semibold text-foreground">{{ path.name }}</h2>
                <p
                    v-if="path.description"
                    class="mt-2 text-sm text-muted-foreground"
                >
                    {{ path.description }}
                </p>
                <p class="mt-3 text-sm font-medium text-muted-foreground">
                    {{ path.progress.completed }}/{{ path.progress.total }}
                    course selesai
                </p>
                <ol class="mt-5 divide-y divide-border">
                    <li
                        v-for="course in path.courses"
                        :key="course.id"
                        class="data-row flex items-center justify-between gap-3 py-3"
                    >
                        <div>
                            <p class="text-sm text-muted-foreground">
                                {{ course.sequence }}. {{ course.code }}
                            </p>
                            <p class="font-medium text-foreground">
                                {{ course.title }}
                            </p>
                        </div>
                        <Button
                            v-if="
                                course.enrollment_status === 'active' &&
                                !course.locked
                            "
                            size="sm"
                            variant="outline"
                            as-child
                            ><Link :href="`/my-learning/${course.id}`"
                                >Buka</Link
                            ></Button
                        >
                        <StatusBadge
                            v-else-if="course.enrollment_status === 'completed'"
                            status="completed"
                        />
                        <div
                            v-else-if="
                                course.enrollment_status === 'pending_review'
                            "
                            class="text-right"
                        >
                            <StatusBadge status="pending_review" />
                            <p
                                class="mt-1 max-w-48 text-xs text-muted-foreground"
                            >
                                Tim pembelajaran sedang melakukan evaluasi
                                akhir.
                            </p>
                        </div>
                        <div
                            v-else-if="
                                course.enrollment_status === 'needs_guidance'
                            "
                            class="text-right"
                        >
                            <StatusBadge status="needs_guidance" />
                            <p
                                class="mt-1 max-w-48 text-xs text-muted-foreground"
                            >
                                Management akan memberikan arahan tindak lanjut.
                            </p>
                        </div>
                        <div v-else class="text-right">
                            <StatusBadge status="locked" />
                            <p
                                v-if="course.lock_reason"
                                class="mt-1 max-w-48 text-xs text-muted-foreground"
                            >
                                {{ course.lock_reason }}
                            </p>
                        </div>
                    </li>
                </ol>
            </article>
        </section>
        <EmptyState
            v-else
            :icon="ListTree"
            title="Belum ada learning path"
            description="Learning path yang ditetapkan akan muncul di sini."
            ><Button as-child
                ><Link href="/my-learning">Pembelajaran saya</Link></Button
            ></EmptyState
        >
    </div>
</template>
