<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ListTree } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
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
    }[];
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
    <div
        class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8"
    >
        <header>
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                Learning Paths
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                Ikuti urutan course yang ditetapkan untuk pengembangan Anda.
            </p>
        </header>
        <section v-if="paths.data.length" class="grid gap-4">
            <article
                v-for="path in paths.data"
                :key="path.id"
                class="rounded-xl border border-border bg-card p-5"
            >
                <h2 class="font-semibold text-foreground">{{ path.name }}</h2>
                <p
                    v-if="path.description"
                    class="mt-2 text-sm text-muted-foreground"
                >
                    {{ path.description }}
                </p>
                <ol class="mt-5 divide-y divide-border">
                    <li
                        v-for="course in path.courses"
                        :key="course.id"
                        class="flex items-center justify-between gap-3 py-3"
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
                            v-if="course.enrollment_status === 'active'"
                            size="sm"
                            variant="outline"
                            as-child
                            ><Link :href="`/my-learning/${course.id}`"
                                >Buka</Link
                            ></Button
                        >
                        <p v-else class="text-sm text-muted-foreground">
                            {{
                                course.enrollment_status === 'completed'
                                    ? 'Selesai'
                                    : 'Belum ditetapkan'
                            }}
                        </p>
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
                ><Link href="/my-learning">My Learning</Link></Button
            ></EmptyState
        >
    </div>
</template>
