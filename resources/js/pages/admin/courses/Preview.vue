<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Clock3,
    FileText,
    PlayCircle,
    Presentation,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';

type Lesson = {
    id: number;
    title: string;
    type: 'article' | 'video' | 'pdf' | 'presentation';
    is_required: boolean;
    sequence: number;
};
type Course = {
    id: number;
    code: string;
    title: string;
    description: string | null;
    estimated_minutes: number | null;
    status: string;
    thumbnail_url: string | null;
    category: { name: string } | null;
    trainers: { id: number; name: string }[];
    modules: {
        id: number;
        title: string;
        sequence: number;
        lessons: Lesson[];
    }[];
};
const props = defineProps<{ course: Course }>();
function typeLabel(type: Lesson['type']): string {
    return {
        article: 'Artikel',
        video: 'Video',
        pdf: 'PDF',
        presentation: 'Presentasi Canva',
    }[type];
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: '/courses' },
            { title: 'Pratinjau', href: '/courses' },
        ],
    },
});
</script>

<template>
    <Head :title="`Pratinjau · ${course.title}`" />
    <div class="app-page gap-8">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-3xl">
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    Pratinjau course
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Tampilan ini membantu memeriksa informasi dan susunan materi
                    sebelum course dipublikasikan.
                </p>
            </div>
            <Button variant="outline" as-child
                ><Link :href="`/courses/${course.id}/edit`"
                    >Kembali mengedit</Link
                ></Button
            >
        </header>

        <section
            class="overflow-hidden rounded-2xl border border-border bg-card"
        >
            <img
                v-if="course.thumbnail_url"
                :src="course.thumbnail_url"
                :alt="`Thumbnail ${course.title}`"
                class="aspect-[3/1] w-full object-cover"
                width="1200"
                height="400"
            />
            <div class="p-5 sm:p-7">
                <p class="text-sm font-medium text-muted-foreground">
                    {{ course.category?.name ?? 'Course' }} · {{ course.code }}
                </p>
                <h2
                    class="mt-2 text-3xl font-semibold tracking-tight text-foreground"
                >
                    {{ course.title }}
                </h2>
                <p
                    v-if="course.description"
                    class="mt-3 max-w-3xl text-sm leading-6 text-muted-foreground"
                >
                    {{ course.description }}
                </p>
                <div
                    class="mt-6 flex flex-wrap gap-x-6 gap-y-3 text-sm text-muted-foreground"
                >
                    <span class="inline-flex items-center gap-2"
                        ><Clock3 class="size-4" />{{
                            course.estimated_minutes
                                ? `${course.estimated_minutes} menit`
                                : 'Durasi fleksibel'
                        }}</span
                    >
                    <span>{{
                        course.trainers.length
                            ? `Trainer: ${course.trainers.map((trainer) => trainer.name).join(', ')}`
                            : 'Trainer belum ditetapkan'
                    }}</span>
                </div>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-2xl border border-border bg-card"
        >
            <div class="border-b border-border px-5 py-4 sm:px-6">
                <h2 class="font-semibold text-foreground">
                    Materi pembelajaran
                </h2>
            </div>
            <div v-if="course.modules.length" class="divide-y divide-border">
                <article
                    v-for="module in course.modules"
                    :key="module.id"
                    class="px-5 py-5 sm:px-6"
                >
                    <h3 class="font-medium text-foreground">
                        Modul {{ module.sequence }} · {{ module.title }}
                    </h3>
                    <ol class="mt-3 grid gap-2">
                        <li
                            v-for="lesson in module.lessons"
                            :key="lesson.id"
                            class="flex items-center justify-between gap-4 rounded-lg bg-muted/35 px-3 py-2 text-sm"
                        >
                            <span
                                class="inline-flex min-w-0 items-center gap-2 text-foreground"
                                ><component
                                    :is="
                                        lesson.type === 'video'
                                            ? PlayCircle
                                            : lesson.type === 'pdf'
                                              ? FileText
                                              : lesson.type === 'presentation'
                                                ? Presentation
                                                : BookOpen
                                    "
                                    class="size-4 shrink-0 text-muted-foreground"
                                />{{ lesson.sequence }}.
                                {{ lesson.title }}</span
                            >
                            <span class="shrink-0 text-xs text-muted-foreground"
                                >{{ typeLabel(lesson.type)
                                }}{{
                                    lesson.is_required ? ' · Wajib' : ''
                                }}</span
                            >
                        </li>
                    </ol>
                </article>
            </div>
            <p
                v-else
                class="px-5 py-12 text-center text-sm text-muted-foreground"
            >
                Belum ada modul atau lesson untuk dipratinjau.
            </p>
        </section>
    </div>
</template>
