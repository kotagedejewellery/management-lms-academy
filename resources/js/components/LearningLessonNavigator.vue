<script setup lang="ts">
import { CheckCircle2, FileText } from '@lucide/vue';

type Lesson = {
    id: number;
    title: string;
    type: 'article' | 'video' | 'pdf' | 'presentation';
    is_required: boolean;
    sequence: number;
    completed: boolean;
    status: 'not_started' | 'in_progress' | 'completed';
};
type Module = {
    id: number;
    title: string;
    sequence: number;
    status: 'not_started' | 'in_progress' | 'completed';
    lessons: Lesson[];
};

defineProps<{
    modules: Module[];
    activeLessonId: number | null;
}>();

defineEmits<{
    select: [lesson: Lesson, module: Module];
}>();

function statusLabel(status: Lesson['status']): string {
    return {
        not_started: 'Belum dimulai',
        in_progress: 'Sedang dipelajari',
        completed: 'Selesai',
    }[status];
}
</script>

<template>
    <section class="rounded-xl border border-border bg-card p-4">
        <h2 class="flex items-center gap-2 font-semibold text-foreground">
            <FileText class="size-4 text-brand-gold" />Materi course
        </h2>
        <div class="mt-4 space-y-4">
            <section v-for="module in modules" :key="module.id">
                <p
                    class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                >
                    Modul {{ module.sequence }} ·
                    {{ statusLabel(module.status) }}
                </p>
                <h3 class="mt-1 text-sm font-semibold text-foreground">
                    {{ module.title }}
                </h3>
                <div class="mt-2 space-y-1">
                    <button
                        v-for="lesson in module.lessons"
                        :key="lesson.id"
                        type="button"
                        class="flex min-h-11 w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-sm transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        :class="
                            lesson.id === activeLessonId
                                ? 'bg-brand-gold/15 font-medium text-foreground'
                                : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                        "
                        :aria-current="
                            lesson.id === activeLessonId ? 'step' : undefined
                        "
                        @click="$emit('select', lesson, module)"
                    >
                        <CheckCircle2
                            v-if="lesson.completed"
                            class="size-4 shrink-0 text-emerald-700"
                        />
                        <FileText v-else class="size-4 shrink-0" />
                        <span class="min-w-0 flex-1 truncate">{{
                            lesson.title
                        }}</span>
                        <span class="text-xs">{{
                            statusLabel(lesson.status)
                        }}</span>
                    </button>
                </div>
            </section>
        </div>
    </section>
</template>
