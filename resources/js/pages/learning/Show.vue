<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Check,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Download,
    FileText,
    LockKeyhole,
    Play,
    PlayCircle,
    RotateCcw,
    Trophy,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';

type Lesson = {
    id: number;
    title: string;
    type: 'article' | 'video' | 'pdf';
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
type LessonEntry = Lesson & {
    module_id: number;
    module_title: string;
    module_sequence: number;
};
type SelectedLesson = LessonEntry & {
    content: string | null;
    transcript: string | null;
    resource_url: string | null;
    resources: { id: number; original_name: string; mime_type: string }[];
};
type Assessment = {
    id: number;
    type: 'pre_test' | 'post_test';
    is_required: boolean;
    ready: boolean;
    attempt_limit: number;
    attempts_used: number;
    latest_attempt: {
        attempt_number: number;
        score: string;
        passed: boolean | null;
    } | null;
};
type CourseAction =
    | { kind: 'link'; href: string; label: string; description: string }
    | {
          kind: 'lesson';
          lesson: LessonEntry;
          label: string;
          description: string;
      }
    | { kind: 'none'; label: string; description: string };

const props = defineProps<{
    enrollment: {
        id: number;
        status: 'active' | 'completed' | 'not_passed';
        due_at: string | null;
    };
    course: {
        id: number;
        code: string;
        title: string;
        description: string | null;
        estimated_minutes: number | null;
        pre_test_required: boolean;
        pre_test_completed: boolean;
        metadata: { category: string | null; target_audience: string };
        progress: {
            completed_lessons: number;
            total_lessons: number;
            completed_required_lessons: number;
            required_lessons: number;
        };
        assessments: Assessment[];
        modules: Module[];
    };
    selected_lesson: SelectedLesson | null;
}>();

const allLessons = computed<LessonEntry[]>(() =>
    props.course.modules.flatMap((module) =>
        module.lessons.map((lesson) => ({
            ...lesson,
            module_id: module.id,
            module_title: module.title,
            module_sequence: module.sequence,
        })),
    ),
);
const activeLesson = computed(() => props.selected_lesson);
const firstIncompleteLesson = computed(() =>
    allLessons.value.find((lesson) => !lesson.completed),
);
const activeLessonIndex = computed(() =>
    allLessons.value.findIndex(
        (lesson) => lesson.id === activeLesson.value?.id,
    ),
);
const previousLesson = computed(() =>
    activeLessonIndex.value > 0
        ? allLessons.value[activeLessonIndex.value - 1]
        : null,
);
const nextLesson = computed(() =>
    activeLessonIndex.value >= 0 &&
    activeLessonIndex.value < allLessons.value.length - 1
        ? allLessons.value[activeLessonIndex.value + 1]
        : null,
);
const preTest = computed(() =>
    props.course.assessments.find(
        (assessment) => assessment.type === 'pre_test',
    ),
);
const postTest = computed(() =>
    props.course.assessments.find(
        (assessment) => assessment.type === 'post_test',
    ),
);
const completingLessonId = ref<number | null>(null);
const lessonError = ref('');
const progressPercentage = computed(() =>
    props.course.progress.total_lessons === 0
        ? 0
        : Math.round(
              (props.course.progress.completed_lessons /
                  props.course.progress.total_lessons) *
                  100,
          ),
);
const requiredProgressLabel = computed(() =>
    props.course.progress.required_lessons === 0
        ? 'Tidak ada lesson wajib'
        : `${props.course.progress.completed_required_lessons}/${props.course.progress.required_lessons} lesson wajib selesai`,
);
const estimatedDuration = computed(() =>
    props.course.estimated_minutes
        ? `${props.course.estimated_minutes} menit`
        : 'Durasi fleksibel',
);
const pdfPreviewUrl = computed(() => {
    if (!activeLesson.value || activeLesson.value.type !== 'pdf') return null;
    const resource = activeLesson.value.resources.find(
        (item) => item.mime_type === 'application/pdf',
    );
    return (
        activeLesson.value.resource_url ??
        (resource ? resourcePreviewHref(resource) : null)
    );
});
const courseAction = computed<CourseAction>(() => {
    if (
        props.enrollment.status === 'completed' &&
        postTest.value?.latest_attempt
    )
        return {
            kind: 'link',
            href: resultHref(postTest.value),
            label: 'Lihat hasil Post-Test',
            description: 'Course ini telah selesai.',
        };
    if (
        props.enrollment.status === 'not_passed' &&
        postTest.value?.latest_attempt
    )
        return {
            kind: 'link',
            href: resultHref(postTest.value),
            label: 'Lihat hasil Post-Test',
            description: 'Batas percobaan Post-Test telah tercapai.',
        };
    if (
        props.course.pre_test_required &&
        !props.course.pre_test_completed &&
        preTest.value
    ) {
        const action = assessmentAction(preTest.value);
        if (action)
            return {
                kind: 'link',
                ...action,
                description:
                    'Pre-Test wajib diselesaikan sebelum materi dapat dibuka.',
            };
    }
    if (props.enrollment.status === 'completed')
        return {
            kind: 'none',
            label: 'Course selesai',
            description: 'Seluruh persyaratan course telah terpenuhi.',
        };
    if (firstIncompleteLesson.value)
        return {
            kind: 'lesson',
            lesson: firstIncompleteLesson.value,
            label:
                props.course.progress.completed_lessons === 0
                    ? 'Mulai belajar'
                    : 'Lanjutkan materi',
            description: requiredProgressLabel.value,
        };
    if (postTest.value) {
        const action = assessmentAction(postTest.value);
        if (action)
            return {
                kind: 'link',
                ...action,
                description: 'Seluruh lesson wajib telah selesai.',
            };
    }
    return {
        kind: 'none',
        label: 'Materi belum tersedia',
        description:
            'Hubungi LMS Admin bila course ini seharusnya sudah memiliki materi.',
    };
});

function assessmentTitle(assessment: Assessment): string {
    return assessment.type === 'pre_test' ? 'Pre-Test' : 'Post-Test';
}
function assessmentDescription(assessment: Assessment): string {
    return assessment.type === 'pre_test'
        ? assessment.is_required
            ? 'Tes baseline wajib sebelum materi dapat dibuka.'
            : 'Tes baseline opsional sebelum atau saat belajar.'
        : assessment.ready
          ? 'Evaluasi akhir setelah materi wajib selesai.'
          : 'Terkunci sampai materi wajib selesai.';
}
function lessonTypeLabel(type: Lesson['type']): string {
    return { article: 'Artikel', video: 'Video', pdf: 'PDF' }[type];
}
function progressStatusLabel(
    status: Lesson['status'] | Module['status'],
): string {
    return {
        not_started: 'Belum dimulai',
        in_progress: 'Sedang dipelajari',
        completed: 'Selesai',
    }[status];
}
function assessmentHref(assessment: Assessment): string {
    return `/my-learning/${props.course.id}/assessments/${assessment.id}`;
}
function resultHref(assessment: Assessment): string {
    return `${assessmentHref(assessment)}/result`;
}
function resourceDownloadHref(resource: { id: number }): string {
    return activeLesson.value
        ? `/courses/${props.course.id}/modules/${activeLesson.value.module_id}/lessons/${activeLesson.value.id}/resources/${resource.id}`
        : '#';
}
function resourcePreviewHref(resource: { id: number }): string {
    return `${resourceDownloadHref(resource)}/preview`;
}
function assessmentAction(
    assessment: Assessment,
): { href: string; label: string } | null {
    if (
        props.enrollment.status === 'active' &&
        assessment.ready &&
        assessment.attempts_used < assessment.attempt_limit
    )
        return {
            href: assessmentHref(assessment),
            label:
                assessment.attempts_used > 0
                    ? `Ulangi ${assessmentTitle(assessment)}`
                    : `Mulai ${assessmentTitle(assessment)}`,
        };
    return assessment.latest_attempt
        ? {
              href: resultHref(assessment),
              label: `Lihat hasil ${assessmentTitle(assessment)}`,
          }
        : null;
}
function selectLesson(lesson: LessonEntry, scrollToPlayer = false): void {
    lessonError.value = '';
    if (props.course.pre_test_required && !props.course.pre_test_completed) {
        lessonError.value = 'Selesaikan Pre-Test wajib sebelum membuka materi.';
        return;
    }
    if (lesson.id === activeLesson.value?.id) {
        if (scrollToPlayer)
            document
                .getElementById('lesson-player')
                ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }
    router.get(
        `/my-learning/${props.course.id}`,
        { lesson: lesson.id },
        {
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                if (scrollToPlayer)
                    document.getElementById('lesson-player')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
            },
            onError: () => {
                lessonError.value =
                    'Materi tidak dapat dimuat. Periksa koneksi lalu coba lagi.';
            },
        },
    );
}
function complete(lesson: SelectedLesson): void {
    if (completingLessonId.value !== null) return;
    lessonError.value = '';
    completingLessonId.value = lesson.id;
    router.post(
        `/my-learning/${props.course.id}/lessons/${lesson.id}/complete`,
        {},
        {
            preserveScroll: true,
            onError: () => {
                lessonError.value =
                    'Lesson belum tersimpan sebagai selesai. Periksa koneksi lalu coba lagi.';
            },
            onFinish: () => {
                completingLessonId.value = null;
            },
        },
    );
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My Learning', href: '/my-learning' },
            { title: 'Course', href: '/my-learning' },
        ],
    },
});
</script>

<template>
    <Head :title="course.title" />
    <main class="w-full px-4 py-6 sm:px-6 lg:px-8">
        <section class="border-b border-border pb-6">
            <div
                class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between"
            >
                <div class="max-w-3xl">
                    <p class="text-sm font-medium text-muted-foreground">
                        {{ course.code }}
                    </p>
                    <h1
                        class="mt-2 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl"
                    >
                        {{ course.title }}
                    </h1>
                    <p
                        v-if="course.description"
                        class="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground sm:text-base"
                    >
                        {{ course.description }}
                    </p>
                </div>
                <div class="w-full xl:max-w-sm">
                    <Button
                        v-if="courseAction.kind === 'link'"
                        class="w-full"
                        as-child
                        ><Link :href="courseAction.href"
                            ><Play class="size-4" />{{
                                courseAction.label
                            }}</Link
                        ></Button
                    ><Button
                        v-else-if="courseAction.kind === 'lesson'"
                        class="w-full"
                        @click="selectLesson(courseAction.lesson, true)"
                        ><Play class="size-4" />{{ courseAction.label }}</Button
                    >
                    <p v-else class="text-sm font-medium text-muted-foreground">
                        {{ courseAction.label }}
                    </p>
                    <p class="mt-2 text-sm leading-5 text-muted-foreground">
                        {{ courseAction.description }}
                    </p>
                </div>
            </div>
            <dl
                class="mt-6 grid gap-4 border-t border-border pt-5 sm:grid-cols-2 xl:grid-cols-4"
            >
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Progress materi
                    </dt>
                    <dd
                        class="mt-1 text-lg font-semibold text-foreground tabular-nums"
                    >
                        {{ course.progress.completed_lessons }}/{{
                            course.progress.total_lessons
                        }}
                        lesson
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Estimasi waktu
                    </dt>
                    <dd
                        class="mt-1 flex items-center gap-2 text-lg font-semibold text-foreground"
                    >
                        <Clock3 class="size-4 text-muted-foreground" />{{
                            estimatedDuration
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Kategori</dt>
                    <dd class="mt-1 text-lg font-semibold text-foreground">
                        {{ course.metadata.category ?? 'Umum' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Status assignment
                    </dt>
                    <dd class="mt-1 text-lg font-semibold text-foreground">
                        {{
                            enrollment.status === 'completed'
                                ? 'Selesai'
                                : enrollment.status === 'not_passed'
                                  ? 'Tidak lulus'
                                  : 'Aktif'
                        }}
                    </dd>
                </div>
            </dl>
            <div class="mt-5 max-w-3xl">
                <div
                    class="mb-2 flex items-center justify-between gap-4 text-sm"
                >
                    <span class="font-medium text-foreground">{{
                        requiredProgressLabel
                    }}</span
                    ><span class="text-muted-foreground tabular-nums"
                        >{{ progressPercentage }}%</span
                    >
                </div>
                <div
                    class="h-2 overflow-hidden rounded-full bg-muted"
                    role="progressbar"
                    :aria-valuenow="progressPercentage"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-label="Progress materi"
                >
                    <div
                        class="h-full rounded-full bg-primary transition-[width] duration-300"
                        :style="{ width: `${progressPercentage}%` }"
                    />
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[18rem_minmax(0,1fr)]">
            <aside class="space-y-5 xl:sticky xl:top-6 xl:self-start">
                <section class="rounded-xl border border-border bg-card p-4">
                    <h2
                        class="flex items-center gap-2 font-semibold text-foreground"
                    >
                        <Trophy class="size-4 text-primary" />Evaluasi course
                    </h2>
                    <div class="mt-3 space-y-2">
                        <article
                            v-for="assessment in course.assessments"
                            :key="assessment.id"
                            class="rounded-lg border border-border p-3"
                        >
                            <div class="flex items-start gap-3">
                                <CheckCircle2
                                    v-if="
                                        assessment.latest_attempt?.passed ===
                                        true
                                    "
                                    class="mt-0.5 size-4 shrink-0 text-emerald-700"
                                /><LockKeyhole
                                    v-else-if="!assessment.ready"
                                    class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                                /><PlayCircle
                                    v-else
                                    class="mt-0.5 size-4 shrink-0 text-primary"
                                />
                                <div>
                                    <h3 class="font-medium text-foreground">
                                        {{ assessmentTitle(assessment) }}
                                    </h3>
                                    <p
                                        class="mt-1 text-xs leading-5 text-muted-foreground"
                                    >
                                        {{ assessmentDescription(assessment) }}
                                    </p>
                                    <p
                                        v-if="assessment.attempts_used"
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ assessment.attempts_used }}/{{
                                            assessment.attempt_limit
                                        }}
                                        percobaan digunakan
                                    </p>
                                    <Link
                                        v-if="assessmentAction(assessment)"
                                        :href="
                                            assessmentAction(assessment)
                                                ?.href ?? '#'
                                        "
                                        class="mt-2 inline-flex text-xs font-semibold text-primary underline underline-offset-4"
                                        >{{
                                            assessmentAction(assessment)?.label
                                        }}</Link
                                    >
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
                <details
                    class="rounded-xl border border-border bg-card p-4"
                    open
                >
                    <summary
                        class="flex cursor-pointer list-none items-center gap-2 font-semibold text-foreground"
                    >
                        <BookOpen class="size-4 text-primary" />Materi course
                    </summary>
                    <div class="mt-4 space-y-4">
                        <section
                            v-for="module in course.modules"
                            :key="module.id"
                        >
                            <p
                                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Modul {{ module.sequence }} ·
                                {{ progressStatusLabel(module.status) }}
                            </p>
                            <h3
                                class="mt-1 text-sm font-semibold text-foreground"
                            >
                                {{ module.title }}
                            </h3>
                            <div class="mt-2 space-y-1">
                                <button
                                    v-for="lesson in module.lessons"
                                    :key="lesson.id"
                                    type="button"
                                    class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-sm transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    :class="
                                        lesson.id === activeLesson?.id
                                            ? 'bg-muted font-medium text-foreground'
                                            : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                                    "
                                    :aria-current="
                                        lesson.id === activeLesson?.id
                                            ? 'step'
                                            : undefined
                                    "
                                    @click="
                                        selectLesson({
                                            ...lesson,
                                            module_id: module.id,
                                            module_title: module.title,
                                            module_sequence: module.sequence,
                                        })
                                    "
                                >
                                    <CheckCircle2
                                        v-if="lesson.completed"
                                        class="size-4 shrink-0 text-emerald-700"
                                    /><FileText
                                        v-else
                                        class="size-4 shrink-0"
                                    /><span class="min-w-0 flex-1 truncate">{{
                                        lesson.title
                                    }}</span
                                    ><span class="text-xs">{{
                                        progressStatusLabel(lesson.status)
                                    }}</span>
                                </button>
                            </div>
                        </section>
                    </div>
                </details>
            </aside>
            <section
                id="lesson-player"
                class="scroll-mt-6 rounded-xl border border-border bg-card"
            >
                <template v-if="activeLesson"
                    ><div
                        class="flex flex-col gap-4 border-b border-border p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6"
                    >
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Modul {{ activeLesson.module_sequence }} ·
                                {{ activeLesson.module_title }}
                            </p>
                            <h2
                                class="mt-1 text-2xl font-semibold tracking-tight text-foreground"
                            >
                                {{ activeLesson.title }}
                            </h2>
                        </div>
                        <span
                            class="inline-flex w-fit items-center rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground"
                            >{{ lessonTypeLabel(activeLesson.type) }}</span
                        >
                    </div>
                    <div class="p-5 sm:p-6">
                        <video
                            v-if="
                                activeLesson.type === 'video' &&
                                activeLesson.resource_url
                            "
                            class="aspect-video w-full rounded-lg bg-black"
                            controls
                            preload="metadata"
                        >
                            <source :src="activeLesson.resource_url" />
                            Browser Anda tidak mendukung pemutar video.
                        </video>
                        <div
                            v-else-if="
                                activeLesson.type === 'pdf' && pdfPreviewUrl
                            "
                            class="overflow-hidden rounded-lg border border-border"
                        >
                            <iframe
                                :src="pdfPreviewUrl"
                                :title="`Preview ${activeLesson.title}`"
                                class="h-[70vh] min-h-[32rem] w-full bg-muted"
                            />
                        </div>
                        <div
                            v-else-if="activeLesson.type === 'pdf'"
                            class="rounded-lg border border-border bg-muted/40 p-6"
                        >
                            <FileText class="size-8 text-primary" />
                            <h3 class="mt-4 font-semibold text-foreground">
                                Materi PDF
                            </h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                PDF belum memiliki sumber yang dapat
                                ditampilkan.
                            </p>
                        </div>
                        <article v-else class="max-w-3xl">
                            <p
                                v-if="activeLesson.content"
                                class="text-sm leading-7 whitespace-pre-line text-foreground sm:text-base"
                            >
                                {{ activeLesson.content }}
                            </p>
                            <p v-else class="text-sm text-muted-foreground">
                                Materi artikel ini belum memiliki isi.
                            </p>
                        </article>
                        <details
                            v-if="activeLesson.transcript"
                            class="mt-6 max-w-3xl border-t border-border pt-5"
                        >
                            <summary
                                class="cursor-pointer text-sm font-semibold text-foreground"
                            >
                                Baca transkrip video
                            </summary>
                            <p
                                class="mt-3 text-sm leading-7 whitespace-pre-line text-muted-foreground"
                            >
                                {{ activeLesson.transcript }}
                            </p>
                        </details>
                        <section
                            v-if="
                                activeLesson.resources.length ||
                                activeLesson.resource_url
                            "
                            class="mt-8 border-t border-border pt-5"
                        >
                            <h3 class="font-semibold text-foreground">
                                Resource materi
                            </h3>
                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-3">
                                <a
                                    v-if="
                                        activeLesson.resource_url &&
                                        activeLesson.type !== 'video'
                                    "
                                    :href="activeLesson.resource_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-2 text-sm font-medium text-primary underline underline-offset-4"
                                    ><Download class="size-4" />Buka resource</a
                                ><a
                                    v-for="resource in activeLesson.resources"
                                    :key="resource.id"
                                    :href="resourceDownloadHref(resource)"
                                    class="inline-flex items-center gap-2 text-sm font-medium text-primary underline underline-offset-4"
                                    ><Download class="size-4" />{{
                                        resource.original_name
                                    }}</a
                                >
                            </div>
                        </section>
                    </div>
                    <div
                        class="flex flex-col gap-3 border-t border-border bg-muted/30 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5"
                    >
                        <Button
                            variant="outline"
                            :disabled="!previousLesson"
                            @click="
                                previousLesson && selectLesson(previousLesson)
                            "
                            ><ChevronLeft />Sebelumnya</Button
                        >
                        <p
                            v-if="activeLesson.completed"
                            class="inline-flex items-center gap-2 text-sm font-medium text-emerald-700"
                        >
                            <Check class="size-4" />Lesson selesai
                        </p>
                        <Button
                            v-else-if="enrollment.status === 'active'"
                            :disabled="completingLessonId !== null"
                            @click="complete(activeLesson)"
                            ><Check />{{
                                completingLessonId === activeLesson.id
                                    ? 'Menyimpan...'
                                    : 'Tandai selesai'
                            }}</Button
                        ><span
                            v-else
                            class="text-sm font-medium text-muted-foreground"
                            >Course selesai</span
                        ><Button
                            variant="outline"
                            :disabled="!nextLesson"
                            @click="nextLesson && selectLesson(nextLesson)"
                            >Berikutnya<ChevronRight
                        /></Button>
                    </div>
                    <div
                        v-if="lessonError"
                        class="flex flex-wrap items-center justify-between gap-3 border-t border-destructive/30 bg-destructive/5 px-5 py-3 text-sm text-destructive"
                        role="alert"
                    >
                        <span>{{ lessonError }}</span
                        ><Button
                            variant="outline"
                            size="sm"
                            @click="activeLesson && complete(activeLesson)"
                            ><RotateCcw />Coba lagi</Button
                        >
                    </div></template
                >
                <div v-else class="p-8 text-center sm:p-12">
                    <LockKeyhole
                        v-if="
                            course.pre_test_required &&
                            !course.pre_test_completed
                        "
                        class="mx-auto size-8 text-muted-foreground"
                    /><BookOpen
                        v-else
                        class="mx-auto size-8 text-muted-foreground"
                    />
                    <h2 class="mt-4 text-lg font-semibold text-foreground">
                        {{
                            course.pre_test_required &&
                            !course.pre_test_completed
                                ? 'Materi masih terkunci'
                                : 'Belum ada materi'
                        }}
                    </h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{
                            course.pre_test_required &&
                            !course.pre_test_completed
                                ? 'Selesaikan Pre-Test wajib untuk membuka materi course.'
                                : 'Course ini belum memiliki lesson yang dapat dipelajari.'
                        }}
                    </p>
                </div>
            </section>
        </section>
    </main>
</template>
