<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Circle,
    FileText,
    LockKeyhole,
    PlayCircle,
} from '@lucide/vue';

type Lesson = {
    id: number;
    title: string;
    type: 'article' | 'video' | 'pdf' | 'presentation';
    is_required: boolean;
    sequence: number;
    completed: boolean;
    locked: boolean;
    status: 'not_started' | 'in_progress' | 'completed' | 'locked';
};
type Assessment = {
    id: number;
    type: 'pre_test' | 'post_test';
    submitted: boolean;
    can_start: boolean;
    ready: boolean;
};
type Module = {
    id: number;
    title: string;
    sequence: number;
    status: 'not_started' | 'in_progress' | 'completed' | 'locked';
    locked: boolean;
    lock_reason: string;
    assessments: Assessment[];
    lessons: Lesson[];
};

const props = defineProps<{
    courseId: number;
    courseAssessments: Assessment[];
    modules: Module[];
    activeLessonId: number | null;
}>();

defineEmits<{
    select: [lesson: Lesson, module: Module];
}>();

function assessmentLabel(assessment: Assessment): string {
    return assessment.type === 'pre_test' ? 'Pre-Test' : 'Post-Test';
}

function assessmentStatus(assessment: Assessment): string {
    if (assessment.submitted) return 'Selesai';
    if (assessment.can_start) return 'Siap dikerjakan';

    return assessment.ready ? 'Menunggu' : 'Terkunci';
}

function lessonStatus(lesson: Lesson): string {
    return {
        not_started: 'Belum dimulai',
        in_progress: 'Sedang dipelajari',
        completed: 'Selesai',
        locked: 'Terkunci',
    }[lesson.status];
}

function moduleStatus(module: Module): string {
    return {
        not_started: 'Belum dimulai',
        in_progress: 'Sedang dipelajari',
        completed: 'Selesai',
        locked: 'Terkunci',
    }[module.status];
}

function materialProgress(module: Module): string {
    const completed = module.lessons.filter(
        (lesson) => lesson.completed,
    ).length;
    const optional = module.lessons.filter(
        (lesson) => !lesson.is_required,
    ).length;

    return `${completed}/${module.lessons.length} materi${optional ? ' termasuk opsional' : ''}`;
}

function assessmentMarker(
    module: Module,
    type: Assessment['type'],
): string | null {
    const assessment = module.assessments.find((item) => item.type === type);
    if (!assessment) return null;

    return `${type === 'pre_test' ? 'Pre' : 'Post'} ${assessment.submitted ? 'selesai' : assessment.can_start ? 'siap' : assessmentStatus(assessment).toLowerCase()}`;
}

function moduleStages(module: Module): string {
    return [
        assessmentMarker(module, 'pre_test'),
        materialProgress(module),
        assessmentMarker(module, 'post_test'),
    ]
        .filter(Boolean)
        .join(' · ');
}

function assessmentHref(assessment: Assessment): string {
    return `/my-learning/${props.courseId}/assessments/${assessment.id}`;
}

function isCurrentModule(module: Module): boolean {
    return (
        module.status === 'in_progress' ||
        module.assessments.some((assessment) => assessment.can_start) ||
        module.lessons.some((lesson) => lesson.id === props.activeLessonId)
    );
}

const coursePreTests = computed(() =>
    props.courseAssessments.filter(
        (assessment) => assessment.type === 'pre_test',
    ),
);
const coursePostTests = computed(() =>
    props.courseAssessments.filter(
        (assessment) => assessment.type === 'post_test',
    ),
);
</script>

<template>
    <section class="rounded-xl border border-border bg-card p-4">
        <h2 class="flex items-center gap-2 font-semibold text-foreground">
            <FileText class="size-4 text-brand-gold" />Rencana pembelajaran
        </h2>
        <p class="mt-1 text-xs leading-5 text-muted-foreground">
            Ikuti tahap yang ditandai untuk melanjutkan pembelajaran.
        </p>

        <div v-if="coursePreTests.length" class="mt-4 space-y-2">
            <p
                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Tahap awal course
            </p>
            <div
                v-for="assessment in coursePreTests"
                :key="assessment.id"
                class="flex items-center justify-between gap-3 rounded-lg bg-muted/40 px-3 py-2.5 text-sm"
            >
                <span
                    class="flex min-w-0 items-center gap-2 font-medium text-foreground"
                >
                    <CheckCircle2
                        v-if="assessment.submitted"
                        class="size-4 shrink-0 text-emerald-700"
                    />
                    <LockKeyhole
                        v-else-if="!assessment.can_start"
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                    <PlayCircle v-else class="size-4 shrink-0 text-primary" />
                    {{ assessmentLabel(assessment) }}
                </span>
                <Link
                    v-if="assessment.can_start"
                    :href="assessmentHref(assessment)"
                    class="shrink-0 text-xs font-semibold text-primary underline underline-offset-4"
                    >Buka</Link
                >
                <span v-else class="shrink-0 text-xs text-muted-foreground">{{
                    assessmentStatus(assessment)
                }}</span>
            </div>
        </div>

        <div class="mt-4 space-y-2">
            <details
                v-for="module in modules"
                :key="module.id"
                :open="isCurrentModule(module)"
                class="rounded-lg border border-border"
            >
                <summary
                    class="cursor-pointer list-none px-3 py-3 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <div class="flex items-start gap-2">
                        <CheckCircle2
                            v-if="module.status === 'completed'"
                            class="mt-0.5 size-4 shrink-0 text-emerald-700"
                        />
                        <LockKeyhole
                            v-else-if="module.locked"
                            class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                        />
                        <PlayCircle
                            v-else-if="module.status === 'in_progress'"
                            class="mt-0.5 size-4 shrink-0 text-primary"
                        />
                        <Circle
                            v-else
                            class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                        />
                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-sm font-semibold text-foreground"
                            >
                                Modul {{ module.sequence }} · {{ module.title }}
                            </span>
                            <span
                                class="mt-1 block text-xs text-muted-foreground"
                            >
                                {{ moduleStatus(module) }} ·
                                {{ moduleStages(module) }}
                            </span>
                        </span>
                    </div>
                </summary>

                <div class="space-y-2 border-t border-border px-3 py-3">
                    <p
                        v-if="module.locked"
                        class="text-xs leading-5 text-muted-foreground"
                    >
                        {{ module.lock_reason }}
                    </p>
                    <div
                        v-for="assessment in module.assessments"
                        :key="assessment.id"
                        class="flex items-center justify-between gap-3 rounded-md bg-muted/40 px-2.5 py-2 text-sm"
                    >
                        <span
                            class="flex min-w-0 items-center gap-2 text-foreground"
                        >
                            <CheckCircle2
                                v-if="assessment.submitted"
                                class="size-4 shrink-0 text-emerald-700"
                            />
                            <LockKeyhole
                                v-else-if="!assessment.can_start"
                                class="size-4 shrink-0 text-muted-foreground"
                            />
                            <PlayCircle
                                v-else
                                class="size-4 shrink-0 text-primary"
                            />
                            {{ assessmentLabel(assessment) }}
                        </span>
                        <Link
                            v-if="assessment.can_start"
                            :href="assessmentHref(assessment)"
                            class="shrink-0 text-xs font-semibold text-primary underline underline-offset-4"
                            >Buka</Link
                        >
                        <span
                            v-else
                            class="shrink-0 text-xs text-muted-foreground"
                            >{{ assessmentStatus(assessment) }}</span
                        >
                    </div>
                    <button
                        v-for="lesson in module.lessons"
                        :key="lesson.id"
                        type="button"
                        class="flex min-h-11 w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        :disabled="lesson.locked"
                        :class="
                            lesson.id === activeLessonId
                                ? 'bg-brand-gold/15 font-medium text-foreground'
                                : lesson.locked
                                  ? 'cursor-not-allowed text-muted-foreground/70'
                                  : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                        "
                        :aria-current="
                            lesson.id === activeLessonId ? 'step' : undefined
                        "
                        @click="$emit('select', lesson, module)"
                    >
                        <LockKeyhole
                            v-if="lesson.locked"
                            class="size-4 shrink-0"
                        />
                        <CheckCircle2
                            v-else-if="lesson.completed"
                            class="size-4 shrink-0 text-emerald-700"
                        />
                        <FileText v-else class="size-4 shrink-0" />
                        <span class="min-w-0 flex-1 truncate">{{
                            lesson.title
                        }}</span>
                        <span class="text-xs">{{ lessonStatus(lesson) }}</span>
                    </button>
                </div>
            </details>
        </div>

        <div v-if="coursePostTests.length" class="mt-4 space-y-2">
            <p
                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Evaluasi akhir course
            </p>
            <div
                v-for="assessment in coursePostTests"
                :key="assessment.id"
                class="flex items-center justify-between gap-3 rounded-lg bg-muted/40 px-3 py-2.5 text-sm"
            >
                <span
                    class="flex min-w-0 items-center gap-2 font-medium text-foreground"
                >
                    <CheckCircle2
                        v-if="assessment.submitted"
                        class="size-4 shrink-0 text-emerald-700"
                    />
                    <LockKeyhole
                        v-else-if="!assessment.can_start"
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                    <PlayCircle v-else class="size-4 shrink-0 text-primary" />
                    {{ assessmentLabel(assessment) }}
                </span>
                <Link
                    v-if="assessment.can_start"
                    :href="assessmentHref(assessment)"
                    class="shrink-0 text-xs font-semibold text-primary underline underline-offset-4"
                    >Buka</Link
                >
                <span v-else class="shrink-0 text-xs text-muted-foreground">{{
                    assessmentStatus(assessment)
                }}</span>
            </div>
        </div>
    </section>
</template>
