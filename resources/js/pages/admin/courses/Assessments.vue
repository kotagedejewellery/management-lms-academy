<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
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
    attempts_count: number;
    editable: boolean;
    lock_reason: string | null;
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
    course: {
        id: number;
        code: string;
        title: string;
        status: 'draft' | 'published' | 'archived';
    };
    modules: { id: number; title: string; sequence: number }[];
    selected_module: { id: number; title: string } | null;
    selected_question_id: number | null;
    assessments: Record<'pre_test' | 'post_test', Assessment | null>;
}>();
const selectedModuleId = ref(props.selected_module?.id.toString() ?? '');
const moduleScope = computed(() => props.selected_module !== null);
const createForm = useForm({
    type: 'pre_test' as 'pre_test' | 'post_test',
    passing_grade: '0',
    attempt_limit: '3',
    is_required: false,
    time_limit_minutes: '',
    module_id: props.selected_module?.id.toString() ?? '',
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
const expandedQuestionIds = ref<number[]>(
    props.selected_question_id ? [props.selected_question_id] : [],
);
const editingQuestion = ref<{
    assessmentId: number;
    questionId: number;
} | null>(null);
const editQuestionForm = useForm({
    prompt: '',
    explanation: '',
    points: '1',
    answers: [] as string[],
    correct_answer: '0',
});
const leaveGuard = useUnsavedChangesGuard(
    () =>
        createForm.isDirty ||
        Object.values(settings).some((form) => form.isDirty) ||
        Object.values(questions).some((form) => form.isDirty),
);

function create(type: 'pre_test' | 'post_test'): void {
    createForm.type = type;
    createForm.passing_grade = type === 'pre_test' ? '0' : '70';
    createForm.attempt_limit = type === 'pre_test' ? '1' : '3';
    createForm.is_required = moduleScope.value;
    createForm.module_id = selectedModuleId.value;
    createForm.post(`/courses/${props.course.id}/assessments`, {
        onSuccess: () => createForm.reset(),
    });
}
function toggleQuestion(questionId: number): void {
    expandedQuestionIds.value = expandedQuestionIds.value.includes(questionId)
        ? expandedQuestionIds.value.filter((id) => id !== questionId)
        : [...expandedQuestionIds.value, questionId];
}
function hasValidAnswerKey(question: Assessment['questions'][number]): boolean {
    return question.answers.filter((answer) => answer.is_correct).length === 1;
}
function startEditingQuestion(
    assessment: Assessment,
    question: Assessment['questions'][number],
): void {
    editingQuestion.value = {
        assessmentId: assessment.id,
        questionId: question.id,
    };
    editQuestionForm.prompt = question.prompt;
    editQuestionForm.explanation = question.explanation ?? '';
    editQuestionForm.points = String(question.points);
    editQuestionForm.answers = question.answers.map((answer) => answer.content);
    editQuestionForm.correct_answer = String(
        question.answers.findIndex((answer) => answer.is_correct),
    );
    editQuestionForm.clearErrors();
    if (!expandedQuestionIds.value.includes(question.id)) {
        expandedQuestionIds.value.push(question.id);
    }
}
function cancelEditingQuestion(): void {
    editingQuestion.value = null;
    editQuestionForm.reset();
    editQuestionForm.clearErrors();
}
function saveQuestion(): void {
    if (!editingQuestion.value) return;

    editQuestionForm.put(
        `/courses/${props.course.id}/assessments/${editingQuestion.value.assessmentId}/questions/${editingQuestion.value.questionId}`,
        { onSuccess: cancelEditingQuestion },
    );
}
function changeScope(): void {
    router.get(
        `/courses/${props.course.id}/assessments`,
        selectedModuleId.value ? { module: selectedModuleId.value } : {},
    );
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
    if (moduleScope.value)
        return type === 'pre_test'
            ? 'Wajib diselesaikan sebelum materi modul dapat dibuka.'
            : 'Harus lulus sebelum learner dapat melanjutkan ke modul berikutnya.';

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
                <p class="mt-3 text-sm font-medium text-foreground">
                    Pre-Test → Materi wajib → Post-Test → Modul berikutnya
                </p>
            </div>
            <Button variant="outline" as-child
                ><Link :href="`/courses/${course.id}/content`"
                    >Materi</Link
                ></Button
            >
        </header>
        <div class="max-w-md">
            <Label for="assessment-module">Ruang lingkup evaluasi</Label>
            <select
                id="assessment-module"
                v-model="selectedModuleId"
                class="mt-2 flex h-10 w-full rounded-md border border-input bg-background px-3 text-sm"
                @change="changeScope"
            >
                <option value="">Evaluasi course</option>
                <option
                    v-for="module in modules"
                    :key="module.id"
                    :value="String(module.id)"
                >
                    Modul {{ module.sequence }} · {{ module.title }}
                </option>
            </select>
        </div>
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
                    v-if="!assessments[type] && course.status === 'draft'"
                    class="mt-5 grid gap-4 border-t border-border pt-5"
                    @submit.prevent="create(type)"
                >
                    <FormErrorSummary :errors="createForm.errors" />
                    <div v-if="type === 'post_test'" class="grid gap-2">
                        <Label :for="`${type}-passing-grade`"
                            >Nilai kelulusan</Label
                        ><Input
                            :id="`${type}-passing-grade`"
                            v-model="createForm.passing_grade"
                            type="number"
                            min="0"
                            max="100"
                            required
                        />
                    </div>
                    <div v-if="type === 'post_test'" class="grid gap-2">
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
                    <p
                        v-else
                        class="rounded-lg border border-border bg-muted/40 p-3 text-sm text-muted-foreground"
                    >
                        Pre-Test mencatat baseline pengetahuan. Nilai tidak
                        menentukan kelulusan dan hanya dapat dikerjakan satu
                        kali.
                    </p>
                    <label
                        v-if="type === 'pre_test' && !moduleScope"
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
                <p
                    v-else-if="!assessments[type]"
                    class="mt-5 rounded-lg border border-dashed border-border bg-muted/40 p-3 text-sm text-muted-foreground"
                >
                    Assessment baru hanya dapat dibuat saat course masih
                    berstatus Draf.
                </p>
                <template v-else>
                    <form
                        v-if="assessments[type]?.editable"
                        class="mt-5 grid gap-4 border-t border-border pt-5 sm:grid-cols-2"
                        @submit.prevent="saveSettings(type)"
                    >
                        <FormErrorSummary
                            class="sm:col-span-2"
                            :errors="settings[type].errors"
                        />
                        <p
                            v-if="type === 'pre_test'"
                            class="rounded-lg border border-border bg-muted/40 p-3 text-sm text-muted-foreground sm:col-span-2"
                        >
                            Pre-Test mencatat baseline pengetahuan dan hanya
                            dapat dikerjakan satu kali.
                        </p>
                        <div v-if="type === 'post_test'" class="grid gap-2">
                            <Label :for="`${type}-edit-passing-grade`"
                                >Nilai kelulusan</Label
                            ><Input
                                :id="`${type}-edit-passing-grade`"
                                v-model="settings[type].passing_grade"
                                type="number"
                                min="0"
                                max="100"
                                required
                            />
                        </div>
                        <div v-if="type === 'post_test'" class="grid gap-2">
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
                            v-if="type === 'pre_test' && !moduleScope"
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
                    <p
                        v-if="!assessments[type]?.editable"
                        class="mt-5 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-950"
                    >
                        {{ assessments[type]?.lock_reason }}
                    </p>
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
                                <div class="min-w-0">
                                    <p class="font-medium text-foreground">
                                        {{ question.sequence }}.
                                        {{ question.prompt }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ question.points }} poin ·
                                        {{
                                            hasValidAnswerKey(question)
                                                ? 'Kunci jawaban siap'
                                                : 'Kunci jawaban belum valid'
                                        }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <Button
                                        v-if="assessments[type]?.editable"
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        :aria-label="`Edit soal ${question.sequence}`"
                                        @click="
                                            startEditingQuestion(
                                                assessments[type]!,
                                                question,
                                            )
                                        "
                                        ><Pencil class="size-4"
                                    /></Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        :aria-expanded="
                                            expandedQuestionIds.includes(
                                                question.id,
                                            )
                                        "
                                        @click="toggleQuestion(question.id)"
                                        >{{
                                            expandedQuestionIds.includes(
                                                question.id,
                                            )
                                                ? 'Sembunyikan'
                                                : 'Detail'
                                        }}</Button
                                    >
                                    <Button
                                        v-if="assessments[type]?.editable"
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
                                        v-if="assessments[type]?.editable"
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
                                        v-if="assessments[type]?.editable"
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
                            <form
                                v-if="
                                    editingQuestion?.assessmentId ===
                                        assessments[type]?.id &&
                                    editingQuestion?.questionId === question.id
                                "
                                class="mt-4 grid gap-3 rounded-lg border border-border bg-muted/30 p-4"
                                @submit.prevent="saveQuestion"
                            >
                                <FormErrorSummary
                                    :errors="editQuestionForm.errors"
                                />
                                <div class="grid gap-2">
                                    <Label :for="`edit-question-${question.id}`"
                                        >Soal</Label
                                    ><textarea
                                        :id="`edit-question-${question.id}`"
                                        v-model="editQuestionForm.prompt"
                                        required
                                        rows="3"
                                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label
                                        :for="`edit-explanation-${question.id}`"
                                        >Pembahasan (opsional)</Label
                                    ><textarea
                                        :id="`edit-explanation-${question.id}`"
                                        v-model="editQuestionForm.explanation"
                                        rows="3"
                                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label :for="`edit-points-${question.id}`"
                                        >Poin</Label
                                    ><Input
                                        :id="`edit-points-${question.id}`"
                                        v-model="editQuestionForm.points"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                                <fieldset
                                    class="grid gap-3"
                                    :aria-describedby="`edit-answer-help-${question.id}`"
                                >
                                    <legend class="font-medium text-foreground">
                                        Pilihan jawaban dan kunci
                                    </legend>
                                    <p
                                        :id="`edit-answer-help-${question.id}`"
                                        class="text-sm text-muted-foreground"
                                    >
                                        Pilih tepat satu jawaban benar.
                                    </p>
                                    <div
                                        v-for="(
                                            _, index
                                        ) in editQuestionForm.answers"
                                        :key="index"
                                        class="flex items-center gap-2"
                                    >
                                        <input
                                            v-model="
                                                editQuestionForm.correct_answer
                                            "
                                            :value="String(index)"
                                            :aria-label="`Jadikan pilihan ${index + 1} sebagai jawaban benar`"
                                            type="radio"
                                        /><Input
                                            v-model="
                                                editQuestionForm.answers[index]
                                            "
                                            :aria-label="`Pilihan ${index + 1}`"
                                            required
                                        />
                                    </div>
                                </fieldset>
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        type="submit"
                                        :disabled="editQuestionForm.processing"
                                        >Simpan soal</Button
                                    ><Button
                                        type="button"
                                        variant="outline"
                                        :disabled="editQuestionForm.processing"
                                        @click="cancelEditingQuestion"
                                        >Batal</Button
                                    >
                                </div>
                            </form>
                            <div
                                v-else-if="
                                    expandedQuestionIds.includes(question.id)
                                "
                                class="mt-3"
                            >
                                <p
                                    v-if="question.explanation"
                                    class="mb-3 text-sm text-muted-foreground"
                                >
                                    Pembahasan: {{ question.explanation }}
                                </p>
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
                            </div>
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
                        v-if="assessments[type]?.editable"
                        class="mt-5 grid gap-3 border-t border-border pt-5"
                        @submit.prevent="addQuestion(type)"
                    >
                        <FormErrorSummary :errors="questions[type].errors" />
                        <div>
                            <h3 class="font-medium text-foreground">
                                Tambah soal {{ title(type) }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Pilih tepat satu jawaban benar. Soal tanpa kunci
                                jawaban tidak dapat dipublikasikan.
                            </p>
                        </div>
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
                        <fieldset
                            class="grid gap-3"
                            :aria-describedby="`${type}-answer-help`"
                        >
                            <legend class="font-medium text-foreground">
                                Pilihan jawaban dan kunci
                            </legend>
                            <p
                                :id="`${type}-answer-help`"
                                class="text-sm text-muted-foreground"
                            >
                                Pilih tepat satu jawaban benar.
                            </p>
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
                        </fieldset>
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
