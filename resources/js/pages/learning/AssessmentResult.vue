<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, CircleAlert, RotateCcw, Trophy } from '@lucide/vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    course: { id: number; title: string };
    enrollment_status: 'active' | 'completed' | 'not_passed';
    assessment: {
        id: number;
        type: 'pre_test' | 'post_test';
        passing_grade: number;
        attempt_limit: number;
    };
    latest: {
        attempt_number: number;
        score: string;
        passed: boolean | null;
        duration_seconds: number | null;
    };
    best_score: string;
    attempts_used: number;
    pre_test_score: string | null;
    improvement: number | null;
    review: {
        question: string;
        selected_answer: string | null;
        correct_answer: string | null;
        is_correct: boolean;
        explanation: string | null;
    }[];
}>();
const isPostTest = computed(() => props.assessment.type === 'post_test');
const passed = computed(() => props.latest.passed === true);
const terminalFailure = computed(
    () => isPostTest.value && props.enrollment_status === 'not_passed',
);
const canRetry = computed(
    () =>
        props.enrollment_status === 'active' &&
        props.attempts_used < props.assessment.attempt_limit &&
        !passed.value,
);
const remainingAttempts = computed(() =>
    Math.max(0, props.assessment.attempt_limit - props.attempts_used),
);
const title = computed(() =>
    isPostTest.value
        ? passed.value
            ? 'Post-Test selesai'
            : terminalFailure.value
              ? 'Post-Test tidak lulus'
              : 'Post-Test belum lulus'
        : 'Pre-Test selesai',
);
const description = computed(() =>
    isPostTest.value
        ? passed.value
            ? 'Anda telah menyelesaikan course ini.'
            : terminalFailure.value
              ? 'Batas percobaan telah tercapai. Hubungi LMS Admin bila membutuhkan pembukaan ulang.'
              : 'Anda dapat mempelajari materi kembali sebelum mencoba lagi.'
        : 'Gunakan hasil ini sebagai gambaran awal sebelum memulai materi.',
);
const duration = computed(() =>
    props.latest.duration_seconds === null
        ? null
        : `${Math.floor(props.latest.duration_seconds / 60)} menit ${props.latest.duration_seconds % 60} detik`,
);
const courseStatus = computed(() =>
    props.enrollment_status === 'completed'
        ? 'Selesai'
        : props.enrollment_status === 'not_passed'
          ? 'Tidak lulus'
          : 'Berjalan',
);
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My Learning', href: '/my-learning' },
            { title: 'Hasil Assessment', href: '/my-learning' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />
    <main class="w-full px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <section
            class="mx-auto max-w-4xl rounded-xl border border-border bg-card p-6 sm:p-10"
        >
            <div class="mx-auto max-w-2xl text-center">
                <CheckCircle2
                    v-if="passed"
                    class="mx-auto size-10 text-emerald-700"
                /><Trophy
                    v-else-if="!isPostTest"
                    class="mx-auto size-10 text-primary"
                /><CircleAlert v-else class="mx-auto size-10 text-amber-700" />
                <p class="mt-5 text-sm font-medium text-muted-foreground">
                    {{ course.title }}
                </p>
                <h1
                    class="mt-2 text-3xl font-semibold tracking-tight text-foreground"
                >
                    {{ title }}
                </h1>
                <p
                    class="mx-auto mt-3 max-w-xl text-sm leading-6 text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
            <div
                class="mx-auto mt-8 max-w-md rounded-xl bg-muted/50 px-6 py-7 text-center"
            >
                <p class="text-sm text-muted-foreground">Skor percobaan ini</p>
                <p
                    class="mt-2 text-5xl font-semibold tracking-tight text-foreground tabular-nums"
                >
                    {{ latest.score
                    }}<span class="text-2xl text-muted-foreground">/100</span>
                </p>
                <p
                    v-if="isPostTest"
                    class="mt-3 text-sm font-medium"
                    :class="passed ? 'text-emerald-700' : 'text-amber-700'"
                >
                    {{ passed ? 'Lulus' : 'Belum lulus' }} · Batas lulus
                    {{ assessment.passing_grade }}
                </p>
            </div>
            <dl class="mx-auto mt-6 grid max-w-3xl gap-3 sm:grid-cols-4">
                <div class="rounded-lg border border-border p-4">
                    <dt class="text-sm text-muted-foreground">Skor terbaik</dt>
                    <dd
                        class="mt-1 text-xl font-semibold text-foreground tabular-nums"
                    >
                        {{ best_score }}
                    </dd>
                </div>
                <div class="rounded-lg border border-border p-4">
                    <dt class="text-sm text-muted-foreground">Percobaan</dt>
                    <dd
                        class="mt-1 text-xl font-semibold text-foreground tabular-nums"
                    >
                        {{ latest.attempt_number }}/{{
                            assessment.attempt_limit
                        }}
                    </dd>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Sisa {{ remainingAttempts }}
                    </p>
                </div>
                <div class="rounded-lg border border-border p-4">
                    <dt class="text-sm text-muted-foreground">
                        Waktu pengerjaan
                    </dt>
                    <dd class="mt-1 text-base font-semibold text-foreground">
                        {{ duration ?? 'Tidak tercatat' }}
                    </dd>
                </div>
                <div class="rounded-lg border border-border p-4">
                    <dt class="text-sm text-muted-foreground">Status course</dt>
                    <dd class="mt-1 text-xl font-semibold text-foreground">
                        {{ courseStatus }}
                    </dd>
                </div>
            </dl>
            <p
                v-if="improvement !== null"
                class="mx-auto mt-6 max-w-xl text-center text-sm leading-6 text-muted-foreground"
            >
                Perbandingan Post-Test terbaik dengan Pre-Test pertama:
                <span class="font-semibold text-foreground"
                    >{{ improvement > 0 ? '+' : ''
                    }}{{ improvement }} poin</span
                >.
            </p>
            <section
                v-if="review.length"
                class="mx-auto mt-8 max-w-3xl border-t border-border pt-6"
            >
                <h2
                    class="text-xl font-semibold tracking-tight text-foreground"
                >
                    Tinjau jawaban
                </h2>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Jawaban benar dan pembahasan ditampilkan setelah assessment
                    dikirim.
                </p>
                <ol
                    class="mt-5 divide-y divide-border rounded-xl border border-border"
                >
                    <li
                        v-for="(item, index) in review"
                        :key="index"
                        class="p-5"
                    >
                        <div class="flex items-start gap-3">
                            <CheckCircle2
                                v-if="item.is_correct"
                                class="mt-0.5 size-5 shrink-0 text-emerald-700"
                            /><CircleAlert
                                v-else
                                class="mt-0.5 size-5 shrink-0 text-amber-700"
                            />
                            <div>
                                <h3 class="font-medium text-foreground">
                                    {{ index + 1 }}. {{ item.question }}
                                </h3>
                                <p class="mt-3 text-sm text-muted-foreground">
                                    Jawaban Anda:
                                    <span class="font-medium text-foreground">{{
                                        item.selected_answer ?? 'Tidak dijawab'
                                    }}</span>
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Jawaban benar:
                                    <span class="font-medium text-foreground">{{
                                        item.correct_answer ?? 'Tidak tersedia'
                                    }}</span>
                                </p>
                                <p
                                    v-if="item.explanation"
                                    class="mt-3 text-sm leading-6 text-muted-foreground"
                                >
                                    {{ item.explanation }}
                                </p>
                            </div>
                        </div>
                    </li>
                </ol>
            </section>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <Button v-if="canRetry" as-child
                    ><Link
                        :href="`/my-learning/${course.id}/assessments/${assessment.id}`"
                        ><RotateCcw />
                        {{
                            isPostTest ? 'Ulangi Post-Test' : 'Ulangi Pre-Test'
                        }}</Link
                    ></Button
                ><Button :variant="canRetry ? 'outline' : 'default'" as-child
                    ><Link :href="`/my-learning/${course.id}`"
                        >Kembali ke course</Link
                    ></Button
                >
            </div>
        </section>
    </main>
</template>
