<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import FormErrorSummary from '@/components/FormErrorSummary.vue';
import InputError from '@/components/InputError.vue';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Assessment = {
    id: number;
    type: 'pre_test' | 'post_test';
    is_required: boolean;
    passing_grade: number;
    attempt_limit: number;
    time_limit_minutes: number | null;
    questions: {
        id: number;
        prompt: string;
        explanation: string | null;
        points: number;
        sequence: number;
        answers: { id: number; content: string; is_correct: boolean }[];
    }[];
};
const props = defineProps<{
    course: { id: number; code: string; title: string };
    assessments: Record<'pre_test' | 'post_test', Assessment | null>;
}>();
const createForm = useForm({
    type: 'pre_test' as 'pre_test' | 'post_test',
    passing_grade: '0',
    attempt_limit: '3',
    is_required: false,
    time_limit_minutes: '',
});
const settings = Object.fromEntries(
    ['pre_test', 'post_test'].map((type) => {
        const assessment = props.assessments[type as 'pre_test' | 'post_test'];
        return [
            type,
            useForm({
                passing_grade: String(
                    assessment?.passing_grade ?? (type === 'pre_test' ? 0 : 70),
                ),
                attempt_limit: String(assessment?.attempt_limit ?? 3),
                is_required: assessment?.is_required ?? false,
                time_limit_minutes:
                    assessment?.time_limit_minutes?.toString() ?? '',
            }),
        ];
    }),
) as Record<'pre_test' | 'post_test', any>;
const questions = Object.fromEntries(
    ['pre_test', 'post_test'].map((type) => [
        type,
        useForm({
            prompt: '',
            explanation: '',
            points: '1',
            answers: ['', '', '', ''],
            correct_answer: '0',
        }),
    ]),
) as Record<'pre_test' | 'post_test', any>;
const questionToDelete = ref<{
    assessmentId: number;
    questionId: number;
} | null>(null);
const deletingQuestion = ref(false);
const movingQuestionId = ref<number | null>(null);
const leaveGuard = useUnsavedChangesGuard(
    () =>
        createForm.isDirty ||
        Object.values(settings).some((form) => form.isDirty) ||
        Object.values(questions).some((form) => form.isDirty),
);

function create(type: 'pre_test' | 'post_test'): void {
    createForm.type = type;
    createForm.passing_grade = type === 'pre_test' ? '0' : '70';
    createForm.is_required = false;
    createForm.post(`/courses/${props.course.id}/assessments`, {
        onSuccess: () => createForm.reset(),
    });
}
function saveSettings(type: 'pre_test' | 'post_test'): void {
    const assessment = props.assessments[type];
    if (assessment)
        settings[type].patch(
            `/courses/${props.course.id}/assessments/${assessment.id}`,
            { onSuccess: () => settings[type].defaults() },
        );
}
function addQuestion(type: 'pre_test' | 'post_test'): void {
    const assessment = props.assessments[type];
    if (assessment)
        questions[type].post(
            `/courses/${props.course.id}/assessments/${assessment.id}/questions`,
            { onSuccess: () => questions[type].reset() },
        );
}
function deleteQuestion(): void {
    if (questionToDelete.value && !deletingQuestion.value)
        router.delete(
            `/courses/${props.course.id}/assessments/${questionToDelete.value.assessmentId}/questions/${questionToDelete.value.questionId}`,
            {
                onStart: () => (deletingQuestion.value = true),
                onFinish: () => {
                    deletingQuestion.value = false;
                    questionToDelete.value = null;
                },
            },
        );
}
function moveQuestion(
    assessment: Assessment,
    question: Assessment['questions'][number],
    direction: 'up' | 'down',
): void {
    router.patch(
        `/courses/${props.course.id}/assessments/${assessment.id}/questions/${question.id}/move`,
        { direction },
        {
            preserveScroll: true,
            onStart: () => (movingQuestionId.value = question.id),
            onFinish: () => (movingQuestionId.value = null),
        },
    );
}
function title(type: 'pre_test' | 'post_test'): string {
    return type === 'pre_test' ? 'Pre-Test' : 'Post-Test';
}
function description(
    type: 'pre_test' | 'post_test',
    assessment: Assessment | null,
): string {
    return type === 'pre_test'
        ? assessment?.is_required
            ? 'Wajib diselesaikan sebelum learner membuka materi.'
            : 'Baseline pengetahuan; tidak menentukan kelulusan course.'
        : 'Kelulusan course mengikuti passing grade Post-Test ini.';
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: '/courses' },
            { title: 'Assessment', href: '/courses' },
        ],
    },
});
</script>

<template>
    <Head :title="`Assessment — ${course.title}`" />
    <div class="app-page">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-2xl">
                <p class="text-sm text-muted-foreground">{{ course.code }}</p>
                <h1
                    class="mt-1 text-2xl font-semibold tracking-tight text-foreground"
                >
                    Assessment · {{ course.title }}
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Atur baseline, kelulusan, batas percobaan 1–3 kali, dan
                    batas waktu setiap assessment.
                </p>
            </div>
            <Button variant="outline" as-child
                ><Link :href="`/courses/${course.id}/content`"
                    >Materi</Link
                ></Button
            >
        </header>
        <div class="grid gap-6 lg:grid-cols-2">
            <section
                v-for="type in ['pre_test', 'post_test'] as const"
                :key="type"
                class="rounded-xl border border-border bg-card p-5 sm:p-6"
            >
                <div>
                    <h2 class="font-semibold text-foreground">
                        {{ title(type) }}
                    </h2>
                    <p class="mt-1 text-sm leading-6 text-muted-foreground">
                        {{ description(type, assessments[type]) }}
                    </p>
                    <p
                        v-if="assessments[type]"
                        class="mt-2 text-xs text-muted-foreground"
                    >
                        {{ assessments[type]?.questions.length }} soal ·
                        {{ assessments[type]?.attempt_limit }} percobaan{{
                            assessments[type]?.time_limit_minutes
                                ? ` · ${assessments[type]?.time_limit_minutes} menit`
                                : ''
                        }}
                    </p>
                </div>
                <form
                    v-if="!assessments[type]"
                    class="mt-5 grid gap-4 border-t border-border pt-5"
                    @submit.prevent="create(type)"
                >
                    <FormErrorSummary :errors="createForm.errors" />
                    <div class="grid gap-2">
                        <Label :for="`${type}-passing-grade`"
                            >Passing grade</Label
                        ><Input
                            :id="`${type}-passing-grade`"
                            v-model="createForm.passing_grade"
                            type="number"
                            min="0"
                            max="100"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label :for="`${type}-attempt-limit`"
                            >Maksimal percobaan</Label
                        ><Input
                            :id="`${type}-attempt-limit`"
                            v-model="createForm.attempt_limit"
                            type="number"
                            min="1"
                            max="3"
                            required
                        />
                    </div>
                    <label
                        v-if="type === 'pre_test'"
                        class="flex items-start gap-3 rounded-lg border border-border p-3 text-sm"
                        ><input
                            v-model="createForm.is_required"
                            type="checkbox"
                            class="mt-0.5 size-4"
                        /><span
                            ><span class="font-medium text-foreground"
                                >Wajib sebelum materi</span
                            ><span class="mt-1 block text-muted-foreground"
                                >Skor rendah tetap tidak menggagalkan
                                course.</span
                            ></span
                        ></label
                    >
                    <div class="grid gap-2">
                        <Label :for="`${type}-time-limit`"
                            >Batas waktu (menit, opsional)</Label
                        ><Input
                            :id="`${type}-time-limit`"
                            v-model="createForm.time_limit_minutes"
                            type="number"
                            min="1"
                            max="180"
                            placeholder="Tanpa batas waktu"
                        />
                    </div>
                    <InputError
                        :message="
                            createForm.errors.attempt_limit ||
                            createForm.errors.passing_grade
                        "
                    /><Button
                        size="sm"
                        type="submit"
                        :disabled="createForm.processing"
                        ><Plus /> Buat {{ title(type) }}</Button
                    >
                </form>
                <template v-else>
                    <form
                        class="mt-5 grid gap-4 border-t border-border pt-5 sm:grid-cols-2"
                        @submit.prevent="saveSettings(type)"
                    >
                        <FormErrorSummary
                            class="sm:col-span-2"
                            :errors="settings[type].errors"
                        />
                        <div class="grid gap-2">
                            <Label :for="`${type}-edit-passing-grade`"
                                >Passing grade</Label
                            ><Input
                                :id="`${type}-edit-passing-grade`"
                                v-model="settings[type].passing_grade"
                                type="number"
                                min="0"
                                max="100"
                                required
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="`${type}-edit-attempt-limit`"
                                >Maksimal percobaan</Label
                            ><Input
                                :id="`${type}-edit-attempt-limit`"
                                v-model="settings[type].attempt_limit"
                                type="number"
                                min="1"
                                max="3"
                                required
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="`${type}-edit-time-limit`"
                                >Batas waktu (menit)</Label
                            ><Input
                                :id="`${type}-edit-time-limit`"
                                v-model="settings[type].time_limit_minutes"
                                type="number"
                                min="1"
                                max="180"
                                placeholder="Tanpa batas"
                            />
                        </div>
                        <label
                            v-if="type === 'pre_test'"
                            class="flex items-center gap-3 self-end rounded-lg border border-border px-3 py-2 text-sm"
                            ><input
                                v-model="settings[type].is_required"
                                type="checkbox"
                                class="size-4"
                            /><span>Wajib sebelum materi</span></label
                        >
                        <div class="sm:col-span-2">
                            <InputError
                                :message="
                                    settings[type].errors.attempt_limit ||
                                    settings[type].errors.passing_grade
                                "
                            /><Button
                                type="submit"
                                variant="outline"
                                :disabled="settings[type].processing"
                                >Simpan pengaturan</Button
                            >
                        </div>
                    </form>
                    <ol
                        v-if="assessments[type]?.questions.length"
                        class="mt-5 divide-y divide-border border-t border-border"
                    >
                        <li
                            v-for="(question, questionIndex) in assessments[
                                type
                            ]?.questions"
                            :key="question.id"
                            class="py-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-medium text-foreground">
                                    {{ question.sequence }}.
                                    {{ question.prompt }}
                                </p>
                                <div class="flex shrink-0 items-center gap-1">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        :disabled="
                                            movingQuestionId !== null ||
                                            questionIndex === 0
                                        "
                                        :aria-label="`Naikkan soal ${question.sequence}`"
                                        @click="
                                            moveQuestion(
                                                assessments[type]!,
                                                question,
                                                'up',
                                            )
                                        "
                                        ><ChevronUp class="size-4"
                                    /></Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        :disabled="
                                            movingQuestionId !== null ||
                                            questionIndex ===
                                                assessments[type]!.questions
                                                    .length -
                                                    1
                                        "
                                        :aria-label="`Turunkan soal ${question.sequence}`"
                                        @click="
                                            moveQuestion(
                                                assessments[type]!,
                                                question,
                                                'down',
                                            )
                                        "
                                        ><ChevronDown class="size-4"
                                    /></Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        :aria-label="`Hapus soal ${question.sequence}`"
                                        @click="
                                            questionToDelete = {
                                                assessmentId:
                                                    assessments[type]!.id,
                                                questionId: question.id,
                                            }
                                        "
                                        ><Trash2 class="size-4"
                                    /></Button>
                                </div>
                            </div>
                            <p
                                v-for="answer in question.answers"
                                :key="answer.id"
                                :class="
                                    answer.is_correct
                                        ? 'mt-1 text-sm font-medium text-emerald-700'
                                        : 'mt-1 text-sm text-muted-foreground'
                                "
                            >
                                {{ answer.content
                                }}<span v-if="answer.is_correct">
                                    · Jawaban benar</span
                                >
                            </p>
                        </li>
                    </ol>
                    <p
                        v-else
                        class="mt-5 rounded-lg bg-muted/50 p-3 text-sm text-muted-foreground"
                    >
                        Belum ada soal. Course evaluatif tidak dapat
                        dipublikasikan sebelum Post-Test memiliki soal dan
                        jawaban benar.
                    </p>
                    <form
                        class="mt-5 grid gap-3 border-t border-border pt-5"
                        @submit.prevent="addQuestion(type)"
                    >
                        <FormErrorSummary :errors="questions[type].errors" />
                        <div class="grid gap-2">
                            <Label :for="`${type}-prompt`">Soal</Label
                            ><textarea
                                :id="`${type}-prompt`"
                                v-model="questions[type].prompt"
                                required
                                rows="3"
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="`${type}-explanation`"
                                >Pembahasan (opsional)</Label
                            ><textarea
                                :id="`${type}-explanation`"
                                v-model="questions[type].explanation"
                                rows="3"
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="`${type}-points`">Poin</Label
                            ><Input
                                :id="`${type}-points`"
                                v-model="questions[type].points"
                                type="number"
                                min="1"
                                required
                            />
                        </div>
                        <div
                            v-for="(_, index) in questions[type].answers"
                            :key="index"
                            class="grid gap-2"
                        >
                            <Label :for="`${type}-answer-${index}`"
                                >Pilihan {{ index + 1 }}</Label
                            >
                            <div class="flex gap-2">
                                <input
                                    v-model="questions[type].correct_answer"
                                    :value="String(index)"
                                    :aria-label="`Jadikan pilihan ${index + 1} sebagai jawaban benar`"
                                    type="radio"
                                /><Input
                                    :id="`${type}-answer-${index}`"
                                    v-model="questions[type].answers[index]"
                                    required
                                />
                            </div>
                        </div>
                        <Button
                            type="submit"
                            :disabled="questions[type].processing"
                            ><Spinner v-if="questions[type].processing" />
                            <Plus v-else />
                            {{
                                questions[type].processing
                                    ? 'Menambahkan soal…'
                                    : 'Tambah soal'
                            }}</Button
                        >
                    </form>
                </template>
            </section>
        </div>
        <ConfirmActionDialog
            :open="Boolean(questionToDelete)"
            title="Hapus soal?"
            description="Soal dan seluruh pilihannya akan dihapus permanen."
            confirm-label="Hapus soal"
            destructive
            :processing="deletingQuestion"
            @update:open="!$event && (questionToDelete = null)"
            @confirm="deleteQuestion"
        />
        <ConfirmActionDialog
            :open="leaveGuard.open.value"
            title="Keluar tanpa menyimpan?"
            description="Perubahan assessment atau soal yang belum disimpan akan hilang."
            confirm-label="Keluar tanpa menyimpan"
            destructive
            @update:open="!$event && leaveGuard.reset()"
            @confirm="leaveGuard.leaveWithoutSaving"
            @cancel="leaveGuard.reset"
        />
    </div>
</template>
