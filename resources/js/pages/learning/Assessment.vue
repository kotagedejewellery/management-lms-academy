<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Clock3,
    RotateCcw,
    Save,
    Send,
} from '@lucide/vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import { Button } from '@/components/ui/button';

type Question = {
    id: number;
    prompt: string;
    points: number;
    sequence: number;
    answers: { id: number; content: string; sequence: number }[];
};
type DraftStatus = 'saved' | 'saving' | 'error';

const props = defineProps<{
    course: { id: number; title: string };
    assessment: {
        id: number;
        type: 'pre_test' | 'post_test';
        attempt_limit: number;
        attempt_number: number;
        expires_at: string | null;
        draft_answers: Record<string, number | string>;
        questions: Question[];
    };
}>();

const form = useForm({
    answers: Object.fromEntries(
        props.assessment.questions.map((question) => [
            question.id,
            String(props.assessment.draft_answers[String(question.id)] ?? ''),
        ]),
    ) as Record<number, string>,
});
const currentQuestionIndex = ref(0);
const remainingSeconds = ref(0);
const timeExpired = ref(false);
const submitDialogOpen = ref(false);
const leaveDialogOpen = ref(false);
const answerError = ref('');
const submitError = ref('');
const draftStatus = ref<DraftStatus>('saved');
let timer: ReturnType<typeof setInterval> | undefined;
let draftTimer: ReturnType<typeof setTimeout> | undefined;

const currentQuestion = computed(
    () => props.assessment.questions[currentQuestionIndex.value],
);
const isFirstQuestion = computed(() => currentQuestionIndex.value === 0);
const isLastQuestion = computed(
    () => currentQuestionIndex.value === props.assessment.questions.length - 1,
);
const answeredCount = computed(
    () =>
        props.assessment.questions.filter(
            (question) => form.answers[question.id],
        ).length,
);
const completionPercentage = computed(() =>
    props.assessment.questions.length === 0
        ? 0
        : Math.round(
              (answeredCount.value / props.assessment.questions.length) * 100,
          ),
);
const assessmentTitle = computed(() =>
    props.assessment.type === 'pre_test' ? 'Pre-Test' : 'Post-Test',
);
const assessmentDescription = computed(() =>
    props.assessment.type === 'pre_test'
        ? 'Ukur pemahaman awal Anda sebelum atau saat mempelajari materi. Hasil ini tidak menentukan kelulusan course.'
        : 'Selesaikan evaluasi akhir untuk menyelesaikan course.',
);
const formattedTime = computed(
    () =>
        `${String(Math.floor(remainingSeconds.value / 60)).padStart(2, '0')}:${String(remainingSeconds.value % 60).padStart(2, '0')}`,
);

function csrfToken(): string {
    return (
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.content ?? ''
    );
}
async function saveDraft(): Promise<boolean> {
    if (timeExpired.value) return false;
    if (draftTimer) clearTimeout(draftTimer);
    draftStatus.value = 'saving';
    try {
        const response = await fetch(
            `/my-learning/${props.course.id}/assessments/${props.assessment.id}/draft`,
            {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ answers: form.answers }),
            },
        );
        if (!response.ok) throw new Error();
        draftStatus.value = 'saved';
        return true;
    } catch {
        draftStatus.value = 'error';
        return false;
    }
}
function scheduleDraft(): void {
    if (draftTimer) clearTimeout(draftTimer);
    draftTimer = setTimeout(() => {
        void saveDraft();
    }, 600);
}
async function submit(): Promise<void> {
    if (form.processing) return;
    if (!timeExpired.value) await saveDraft();
    form.post(
        `/my-learning/${props.course.id}/assessments/${props.assessment.id}/submit`,
        {
            onError: (errors) => {
                submitError.value =
                    errors.assessment ??
                    'Jawaban belum dapat dikirim. Periksa koneksi lalu coba lagi.';
            },
        },
    );
}
function moveToNextQuestion(): void {
    if (!currentQuestion.value || form.answers[currentQuestion.value.id]) {
        answerError.value = '';
        currentQuestionIndex.value += 1;
        return;
    }
    answerError.value = 'Pilih satu jawaban sebelum melanjutkan.';
}
function requestSubmit(): void {
    if (form.processing || timeExpired.value) return;
    const unansweredIndex = props.assessment.questions.findIndex(
        (question) => !form.answers[question.id],
    );
    if (unansweredIndex !== -1) {
        currentQuestionIndex.value = unansweredIndex;
        answerError.value = 'Masih ada jawaban yang belum dipilih.';
        return;
    }
    answerError.value = '';
    submitDialogOpen.value = true;
}
function leaveAssessment(): void {
    leaveDialogOpen.value = false;
    router.visit(`/my-learning/${props.course.id}`);
}
function updateTimer(): void {
    if (!props.assessment.expires_at) return;
    remainingSeconds.value = Math.max(
        0,
        Math.ceil(
            (new Date(props.assessment.expires_at).getTime() - Date.now()) /
                1000,
        ),
    );
    if (remainingSeconds.value === 0) {
        timeExpired.value = true;
        if (timer) clearInterval(timer);
        void submit();
    }
}
function warnBeforeUnload(event: BeforeUnloadEvent): void {
    if (draftStatus.value === 'saving') {
        event.preventDefault();
        event.returnValue = '';
    }
}
onMounted(() => {
    window.addEventListener('beforeunload', warnBeforeUnload);
    if (!props.assessment.expires_at) return;
    updateTimer();
    if (!timeExpired.value) timer = setInterval(updateTimer, 1000);
});
onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
    if (draftTimer) clearTimeout(draftTimer);
    window.removeEventListener('beforeunload', warnBeforeUnload);
});
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My Learning', href: '/my-learning' },
            { title: 'Assessment', href: '/my-learning' },
        ],
    },
});
</script>

<template>
    <Head :title="assessmentTitle" />
    <main class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 lg:py-8">
        <header class="border-b border-border pb-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <p class="text-sm font-medium text-muted-foreground">
                        {{ course.title }}
                    </p>
                    <h1
                        class="mt-2 text-3xl font-semibold tracking-tight text-foreground"
                    >
                        {{ assessmentTitle }}
                    </h1>
                    <p
                        class="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground"
                    >
                        {{ assessmentDescription }}
                    </p>
                </div>
                <Button variant="outline" @click="leaveDialogOpen = true"
                    >Kembali ke course</Button
                >
            </div>
            <div
                class="mt-6 grid gap-4 border-t border-border pt-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end"
            >
                <div>
                    <div
                        class="flex items-center justify-between gap-4 text-sm"
                    >
                        <span class="font-medium text-foreground"
                            >{{ answeredCount }}/{{
                                assessment.questions.length
                            }}
                            jawaban terisi</span
                        ><span class="text-muted-foreground tabular-nums"
                            >{{ completionPercentage }}%</span
                        >
                    </div>
                    <div
                        class="mt-2 h-2 overflow-hidden rounded-full bg-muted"
                        role="progressbar"
                        :aria-valuenow="completionPercentage"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-label="Jawaban terisi"
                    >
                        <div
                            class="h-full rounded-full bg-primary transition-[width] duration-300"
                            :style="{ width: `${completionPercentage}%` }"
                        />
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Percobaan {{ assessment.attempt_number }} dari
                    {{ assessment.attempt_limit }}
                </p>
            </div>
            <div
                class="mt-3 flex items-center gap-2 text-sm"
                aria-live="polite"
            >
                <Save class="size-4 text-muted-foreground" /><span
                    v-if="draftStatus === 'saved'"
                    class="text-muted-foreground"
                    >Jawaban tersimpan sebagai draft</span
                ><span
                    v-else-if="draftStatus === 'saving'"
                    class="text-muted-foreground"
                    >Menyimpan draft…</span
                ><template v-else
                    ><span class="text-destructive">Draft belum tersimpan.</span
                    ><Button size="sm" variant="outline" @click="saveDraft"
                        ><RotateCcw /> Coba lagi</Button
                    ></template
                >
            </div>
        </header>
        <section
            v-if="assessment.expires_at"
            class="mt-6 flex items-center justify-between rounded-xl border border-border bg-muted/40 px-4 py-3"
            aria-live="polite"
        >
            <span
                class="flex items-center gap-2 text-sm font-medium text-foreground"
                ><Clock3 class="size-4 text-primary" />{{
                    timeExpired
                        ? 'Waktu habis, jawaban sedang dikirim.'
                        : 'Waktu tersisa'
                }}</span
            ><strong class="font-mono text-lg text-foreground tabular-nums">{{
                formattedTime
            }}</strong>
        </section>
        <form
            v-if="currentQuestion"
            class="mt-6"
            @submit.prevent="requestSubmit"
        >
            <article class="rounded-xl border border-border bg-card p-5 sm:p-8">
                <p class="text-sm font-medium text-muted-foreground">
                    Pertanyaan {{ currentQuestionIndex + 1 }} dari
                    {{ assessment.questions.length }}
                </p>
                <h2
                    class="mt-3 text-xl leading-8 font-semibold text-foreground sm:text-2xl"
                >
                    {{ currentQuestion.prompt }}
                </h2>
                <fieldset
                    class="mt-7 grid gap-3"
                    :aria-describedby="answerError ? 'answer-error' : undefined"
                >
                    <legend class="sr-only">
                        Pilih jawaban untuk pertanyaan
                        {{ currentQuestionIndex + 1 }}
                    </legend>
                    <label
                        v-for="answer in currentQuestion.answers"
                        :key="answer.id"
                        class="flex cursor-pointer items-start gap-3 rounded-lg border border-input px-4 py-4 text-sm leading-6 transition-colors hover:bg-muted/50 has-[:checked]:border-primary has-[:checked]:bg-muted/60"
                        ><input
                            v-model="form.answers[currentQuestion.id]"
                            :value="String(answer.id)"
                            type="radio"
                            class="mt-1"
                            @change="
                                answerError = '';
                                scheduleDraft();
                            "
                        /><span>{{ answer.content }}</span></label
                    >
                </fieldset>
                <p
                    v-if="answerError"
                    id="answer-error"
                    class="mt-4 text-sm font-medium text-destructive"
                    role="alert"
                >
                    {{ answerError }}
                </p>
            </article>
            <footer
                class="sticky bottom-0 mt-6 flex items-center justify-between gap-3 border-t border-border bg-background/95 py-4 backdrop-blur sm:static sm:bg-transparent sm:py-0"
            >
                <Button
                    type="button"
                    variant="outline"
                    :disabled="isFirstQuestion"
                    @click="currentQuestionIndex -= 1"
                    ><ChevronLeft /> Sebelumnya</Button
                ><Button
                    v-if="!isLastQuestion"
                    type="button"
                    @click="moveToNextQuestion"
                    >Berikutnya <ChevronRight /></Button
                ><Button
                    v-else
                    type="submit"
                    :disabled="form.processing || timeExpired"
                    ><Send />
                    {{
                        form.processing
                            ? 'Mengirim jawaban...'
                            : 'Kirim jawaban'
                    }}</Button
                >
            </footer>
        </form>
        <p
            v-if="submitError"
            class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive"
            role="alert"
        >
            <span>{{ submitError }}</span
            ><Button size="sm" variant="outline" @click="submit"
                ><RotateCcw /> Coba lagi</Button
            >
        </p>
        <section
            v-else-if="!currentQuestion"
            class="mt-6 rounded-xl border border-border bg-card p-8 text-center"
        >
            <h2 class="text-lg font-semibold text-foreground">
                Belum ada soal
            </h2>
            <p class="mt-2 text-sm text-muted-foreground">
                Assessment ini belum dapat dikerjakan. Hubungi LMS Admin.
            </p>
        </section>
        <ConfirmActionDialog
            v-model:open="submitDialogOpen"
            :title="`Kirim ${assessmentTitle}?`"
            description="Jawaban pada percobaan ini akan dinilai dan tidak dapat diubah setelah dikirim."
            confirm-label="Kirim jawaban"
            :processing="form.processing"
            @confirm="submit"
        />
        <ConfirmActionDialog
            v-model:open="leaveDialogOpen"
            title="Keluar dari assessment?"
            description="Jawaban yang belum dikirim tersimpan sebagai draft dan dapat dilanjutkan kembali."
            confirm-label="Keluar"
            @confirm="leaveAssessment"
        />
    </main>
</template>
