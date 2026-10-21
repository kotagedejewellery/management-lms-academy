<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
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
    ListTree,
    LockKeyhole,
    Play,
    PlayCircle,
    RotateCcw,
    Trophy,
} from '@lucide/vue';
import LearningLessonNavigator from '@/components/LearningLessonNavigator.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';

type Lesson = {
    id: number;
    title: string;
    type: 'article' | 'video' | 'pdf' | 'presentation';
    is_required: boolean;
    sequence: number;
    completed: boolean;
    status: 'not_started' | 'in_progress' | 'completed' | 'locked';
    locked: boolean;
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
type LessonEntry = Lesson & {
    module_id: number;
    module_title: string;
    module_sequence: number;
};
type SelectedLesson = LessonEntry & {
    content: string | null;
    transcript: string | null;
    resource_url: string | null;
    playback_seconds: number;
    resources: { id: number; original_name: string; mime_type: string }[];
};
type Assessment = {
    id: number;
    module_id: number | null;
    type: 'pre_test' | 'post_test';
    is_required: boolean;
    ready: boolean;
    submitted: boolean;
    can_start: boolean;
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
        course_completed: boolean;
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
        mentors: { id: number; name: string }[];
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
    allLessons.value.find((lesson) => !lesson.completed && !lesson.locked),
);
const nextModuleAssessment = computed(() =>
    props.course.modules
        .flatMap((module) => module.assessments)
        .find((assessment) => assessment.can_start && !assessment.submitted),
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
const lessonNavigatorOpen = ref(false);
const videoPlayer = ref<HTMLVideoElement | null>(null);
const lastPlaybackSaved = ref(0);
const playbackSaving = ref(false);
const playbackQueued = ref(false);
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
function videoEmbedUrl(resourceUrl: string | null): string | null {
    if (!resourceUrl) return null;

    try {
        const url = new URL(resourceUrl);
        const host = url.hostname.replace(/^www\./, '').toLowerCase();
        if (host === 'youtu.be') {
            const videoId = url.pathname.split('/').filter(Boolean)[0];

            return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
        }
        if (host === 'youtube.com' || host === 'm.youtube.com') {
            const videoId =
                url.searchParams.get('v') ??
                url.pathname.match(/^\/(?:embed|shorts)\/([^/]+)/)?.[1];

            return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
        }
        if (host === 'vimeo.com') {
            const videoId = url.pathname.match(/^\/(\d+)/)?.[1];

            return videoId ? `https://player.vimeo.com/video/${videoId}` : null;
        }
    } catch {
        return null;
    }

    return null;
}
const embeddedVideoUrl = computed(() =>
    activeLesson.value?.type === 'video'
        ? videoEmbedUrl(activeLesson.value.resource_url)
        : null,
);
const courseAction = computed<CourseAction>(() => {
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
    if (props.enrollment.course_completed)
        return {
            kind: 'none',
            label: 'Course selesai',
            description:
                'Periksa sertifikat Anda bila diterbitkan untuk course ini.',
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
    if (nextModuleAssessment.value) {
        const action = assessmentAction(nextModuleAssessment.value);
        if (action)
            return {
                kind: 'link',
                ...action,
                description: assessmentDescription(nextModuleAssessment.value),
            };
    }
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
    if (assessment.module_id !== null)
        return assessment.type === 'pre_test'
            ? 'Wajib diselesaikan sebelum materi modul dapat dibuka.'
            : assessment.ready
              ? 'Lulus Post-Test untuk membuka modul berikutnya.'
              : 'Selesaikan lesson wajib modul untuk membuka Post-Test.';

    return assessment.type === 'pre_test'
        ? assessment.is_required
            ? 'Tes baseline wajib sebelum materi dapat dibuka.'
            : 'Tes baseline opsional sebelum atau saat belajar.'
        : assessment.ready
          ? 'Evaluasi akhir setelah materi wajib selesai.'
          : 'Terkunci sampai materi wajib selesai.';
}
function lessonTypeLabel(type: Lesson['type']): string {
    return {
        article: 'Artikel',
        video: 'Video',
        pdf: 'PDF',
        presentation: 'Presentasi Canva',
    }[type];
}
function assessmentHref(assessment: Assessment): string {
    return `/my-learning/${props.course.id}/assessments/${assessment.id}`;
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
    if (!props.enrollment.course_completed && assessment.can_start)
        return {
            href: assessmentHref(assessment),
            label: `Buka ${assessmentTitle(assessment)}`,
        };
    return null;
}
function assessmentStatusDescription(assessment: Assessment): string {
    if (assessment.submitted && !assessment.can_start) {
        return 'Assessment telah dikirim. Management akan memberikan arahan bila diperlukan.';
    }

    return assessmentDescription(assessment);
}
function selectLesson(lesson: LessonEntry, scrollToPlayer = false): void {
    lessonError.value = '';
    if (lesson.locked) {
        lessonError.value =
            'Materi ini masih terkunci. Selesaikan evaluasi yang diwajibkan terlebih dahulu.';
        return;
    }
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
    persistPlayback();
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
function selectLessonFromNavigator(
    lesson: Lesson,
    module: Pick<Module, 'id' | 'title' | 'sequence'>,
): void {
    lessonNavigatorOpen.value = false;
    selectLesson({
        ...lesson,
        module_id: module.id,
        module_title: module.title,
        module_sequence: module.sequence,
    });
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
function restorePlayback(event: Event): void {
    const video = event.target as HTMLVideoElement;
    const seconds = activeLesson.value?.playback_seconds ?? 0;
    if (
        seconds > 0 &&
        Number.isFinite(video.duration) &&
        seconds < video.duration - 1
    ) {
        video.currentTime = seconds;
        lastPlaybackSaved.value = seconds;
    }
}
function persistPlayback(): void {
    const lesson = activeLesson.value;
    const video = videoPlayer.value;
    if (!lesson || !video || lesson.type !== 'video') return;
    if (playbackSaving.value) {
        playbackQueued.value = true;

        return;
    }

    const seconds = Math.floor(video.currentTime);
    if (seconds <= 0 || seconds === lastPlaybackSaved.value) return;
    playbackSaving.value = true;
    fetch(`/my-learning/${props.course.id}/lessons/${lesson.id}/playback`, {
        method: 'PUT',
        keepalive: true,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN':
                document.querySelector<HTMLMetaElement>(
                    'meta[name="csrf-token"]',
                )?.content ?? '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ seconds }),
    })
        .then((response) => {
            if (!response.ok) throw new Error();
            lastPlaybackSaved.value = seconds;
        })
        .catch(() => {
            lessonError.value =
                'Posisi video belum tersimpan. Periksa koneksi lalu lanjutkan belajar.';
        })
        .finally(() => {
            playbackSaving.value = false;
            if (playbackQueued.value) {
                playbackQueued.value = false;
                persistPlayback();
            }
        });
}
function queuePlayback(): void {
    const video = videoPlayer.value;
    if (video && Math.floor(video.currentTime) - lastPlaybackSaved.value >= 15)
        persistPlayback();
}
function savePlaybackWhenPageIsHidden(): void {
    if (document.visibilityState === 'hidden') persistPlayback();
}
onMounted(() => {
    document.addEventListener('visibilitychange', savePlaybackWhenPageIsHidden);
});
onBeforeUnmount(() => {
    document.removeEventListener(
        'visibilitychange',
        savePlaybackWhenPageIsHidden,
    );
    persistPlayback();
});
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
    <main class="app-page">
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
                        Trainer / Mentor
                    </dt>
                    <dd class="mt-1 text-lg font-semibold text-foreground">
                        {{
                            course.mentors.length
                                ? course.mentors
                                      .map((mentor) => mentor.name)
                                      .join(', ')
                                : 'Belum ditetapkan'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Status assignment
                    </dt>
                    <dd class="mt-1 text-lg font-semibold text-foreground">
                        {{ enrollment.course_completed ? 'Selesai' : 'Aktif' }}
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
                                    v-if="assessment.submitted"
                                    class="mt-0.5 size-4 shrink-0 text-primary"
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
                                        {{
                                            assessmentStatusDescription(
                                                assessment,
                                            )
                                        }}
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
                <section
                    v-if="
                        course.modules.some(
                            (module) => module.assessments.length,
                        )
                    "
                    class="rounded-xl border border-border bg-card p-4"
                >
                    <h2
                        class="flex items-center gap-2 font-semibold text-foreground"
                    >
                        <Trophy class="size-4 text-primary" />Evaluasi modul
                    </h2>
                    <div class="mt-3 space-y-3">
                        <div
                            v-for="module in course.modules.filter(
                                (item) => item.assessments.length,
                            )"
                            :key="module.id"
                            class="rounded-lg border border-border p-3"
                        >
                            <h3 class="text-sm font-medium text-foreground">
                                Modul {{ module.sequence }} · {{ module.title }}
                            </h3>
                            <div class="mt-2 space-y-2">
                                <article
                                    v-for="assessment in module.assessments"
                                    :key="assessment.id"
                                    class="rounded-md bg-muted/40 p-2.5"
                                >
                                    <p
                                        class="text-sm font-medium text-foreground"
                                    >
                                        {{ assessmentTitle(assessment) }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs leading-5 text-muted-foreground"
                                    >
                                        {{
                                            assessmentStatusDescription(
                                                assessment,
                                            )
                                        }}
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
                                </article>
                            </div>
                        </div>
                    </div>
                </section>
                <LearningLessonNavigator
                    class="hidden xl:block"
                    :modules="course.modules"
                    :active-lesson-id="activeLesson?.id ?? null"
                    @select="selectLessonFromNavigator"
                />
            </aside>
            <div class="xl:hidden">
                <Sheet v-model:open="lessonNavigatorOpen">
                    <SheetTrigger as-child>
                        <Button variant="outline" class="w-full">
                            <ListTree />Daftar materi
                        </Button>
                    </SheetTrigger>
                    <SheetContent
                        side="left"
                        class="w-full max-w-sm overflow-y-auto p-4"
                    >
                        <SheetHeader class="mb-4 text-left">
                            <SheetTitle>Daftar materi</SheetTitle>
                        </SheetHeader>
                        <LearningLessonNavigator
                            :modules="course.modules"
                            :active-lesson-id="activeLesson?.id ?? null"
                            @select="selectLessonFromNavigator"
                        />
                    </SheetContent>
                </Sheet>
            </div>
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
                        <iframe
                            v-if="
                                activeLesson.type === 'video' &&
                                embeddedVideoUrl
                            "
                            :src="embeddedVideoUrl"
                            :title="`Video ${activeLesson.title}`"
                            class="aspect-video w-full rounded-lg border border-border bg-black"
                            allow="
                                accelerometer;
                                autoplay;
                                clipboard-write;
                                encrypted-media;
                                gyroscope;
                                picture-in-picture;
                                web-share;
                            "
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                        />
                        <video
                            v-else-if="
                                activeLesson.type === 'video' &&
                                activeLesson.resource_url
                            "
                            class="aspect-video w-full rounded-lg bg-black"
                            controls
                            preload="metadata"
                            ref="videoPlayer"
                            @loadedmetadata="restorePlayback"
                            @timeupdate="queuePlayback"
                            @pause="persistPlayback"
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
                        <div
                            v-else-if="
                                activeLesson.type === 'presentation' &&
                                activeLesson.resource_url
                            "
                        >
                            <iframe
                                :src="activeLesson.resource_url"
                                :title="`Presentasi ${activeLesson.title}`"
                                class="aspect-video w-full rounded-lg border border-border bg-muted"
                                allowfullscreen
                                referrerpolicy="strict-origin-when-cross-origin"
                            />
                            <a
                                :href="activeLesson.resource_url"
                                target="_blank"
                                rel="noopener"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-primary underline underline-offset-4"
                                ><Download class="size-4" />Buka presentasi di
                                Canva</a
                            >
                        </div>
                        <div
                            v-else-if="activeLesson.type === 'presentation'"
                            class="rounded-lg border border-border bg-muted/40 p-6"
                        >
                            <FileText class="size-8 text-primary" />
                            <h3 class="mt-4 font-semibold text-foreground">
                                Presentasi Canva
                            </h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Presentasi belum memiliki tautan yang dapat
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
                                        activeLesson.type !== 'video' &&
                                        activeLesson.type !== 'presentation'
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
                            v-else-if="!enrollment.course_completed"
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
                    <template v-if="courseAction.kind === 'link'">
                        <LockKeyhole
                            class="mx-auto size-8 text-muted-foreground"
                        />
                        <h2 class="mt-4 text-lg font-semibold text-foreground">
                            Evaluasi perlu diselesaikan
                        </h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ courseAction.description }}
                        </p>
                        <Button class="mt-5" as-child
                            ><Link :href="courseAction.href">{{
                                courseAction.label
                            }}</Link></Button
                        >
                    </template>
                    <template v-else>
                        <BookOpen
                            class="mx-auto size-8 text-muted-foreground"
                        />
                        <h2 class="mt-4 text-lg font-semibold text-foreground">
                            Belum ada materi
                        </h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Course ini belum memiliki lesson yang dapat
                            dipelajari.
                        </p>
                    </template>
                </div>
            </section>
        </section>
    </main>
</template>
