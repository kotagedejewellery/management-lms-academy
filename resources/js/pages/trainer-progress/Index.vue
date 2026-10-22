<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ClipboardList, RotateCcw, Search, X } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Enrollment = {
    id: number;
    status: 'active' | 'pending_review' | 'completed' | 'not_passed';
    pre_test_score: number | null;
    best_post_score: number | null;
    improvement: number | null;
    post_test_attempts: number;
    module_assessments: ModuleAssessment[];
    retake_assessments: RetakeAssessment[];
    can_approve: boolean;
    user: { employee_id: string | null; name: string } | null;
    course: { code: string; title: string } | null;
};
type Course = { id: number; code: string; title: string };
type ModuleAssessment = {
    module: { sequence: number; title: string };
    pre_test: { score: number } | null;
    post_test: { best_score: number; attempts: number; passed: boolean } | null;
};
type RetakeAssessment = {
    id: number;
    label: string;
    status: 'available' | 'pending' | 'exhausted';
};

const props = defineProps<{
    enrollments: {
        data: Enrollment[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    courses: Course[];
    filters: { search: string; status: string; course_id: number | null };
}>();

const filters = {
    search: props.filters.search,
    status: props.filters.status,
    course_id: props.filters.course_id?.toString() ?? '',
};
const authorizingRetake = ref<string | null>(null);
const cancellingRetake = ref<string | null>(null);
const approvingEnrollment = ref<number | null>(null);

function applyFilters(): void {
    router.get('/trainer-progress', filters, {
        preserveState: true,
        replace: true,
    });
}

function clearFilters(): void {
    filters.search = '';
    filters.status = '';
    filters.course_id = '';
    applyFilters();
}

function authorizeRetake(
    enrollmentId: number,
    assessment: RetakeAssessment,
): void {
    const key = `${enrollmentId}:${assessment.id}`;
    router.post(
        `/enrollments/${enrollmentId}/assessments/${assessment.id}/retakes`,
        {},
        {
            preserveScroll: true,
            onStart: () => (authorizingRetake.value = key),
            onFinish: () => (authorizingRetake.value = null),
        },
    );
}

function cancelRetake(
    enrollmentId: number,
    assessment: RetakeAssessment,
): void {
    const key = `${enrollmentId}:${assessment.id}`;
    router.delete(
        `/enrollments/${enrollmentId}/assessments/${assessment.id}/retakes`,
        {
            preserveScroll: true,
            onStart: () => (cancellingRetake.value = key),
            onFinish: () => (cancellingRetake.value = null),
        },
    );
}

function approve(enrollmentId: number): void {
    router.post(
        `/trainer-progress/${enrollmentId}/approve`,
        {},
        {
            preserveScroll: true,
            onStart: () => (approvingEnrollment.value = enrollmentId),
            onFinish: () => (approvingEnrollment.value = null),
        },
    );
}

function retakeStatusLabel(status: RetakeAssessment['status']): string {
    return {
        available: 'Menunggu persetujuan internal',
        pending: 'Kesempatan ulang sudah dibuka',
        exhausted: 'Batas percobaan telah tercapai',
    }[status];
}

function score(value: number | null): string {
    return value === null ? '—' : value.toFixed(2);
}

function statusLabel(status: Enrollment['status']): string {
    return {
        active: 'Aktif',
        pending_review: 'Menunggu evaluasi akhir',
        completed: 'Selesai',
        not_passed: 'Tidak lulus',
    }[status];
}

function postTestStatusLabel(postTest: ModuleAssessment['post_test']): string {
    if (postTest === null) {
        return 'Belum dikerjakan';
    }

    return postTest.passed ? 'Lulus' : 'Belum lulus';
}

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Progres course', href: '/trainer-progress' }],
    },
});
</script>

<template>
    <Head title="Progres course" />

    <div class="app-page">
        <PageHeader
            title="Progres course"
            description="Pantau progres dan hasil assessment pada course yang ditugaskan kepada Anda."
        />

        <form
            class="grid gap-3 rounded-2xl border border-border/85 bg-card p-4 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_11rem_15rem_auto_auto]"
            @submit.prevent="applyFilters"
        >
            <div class="grid gap-2">
                <Label for="trainer-progress-search">Cari</Label>
                <Input
                    id="trainer-progress-search"
                    v-model="filters.search"
                    placeholder="Nama atau ID karyawan"
                />
            </div>
            <div class="grid gap-2">
                <Label for="trainer-progress-status">Status</Label>
                <select
                    id="trainer-progress-status"
                    v-model="filters.status"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                >
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="pending_review">
                        Menunggu evaluasi akhir
                    </option>
                    <option value="completed">Selesai</option>
                    <option value="not_passed">Tidak lulus</option>
                </select>
            </div>
            <div class="grid gap-2">
                <Label for="trainer-progress-course">Course</Label>
                <select
                    id="trainer-progress-course"
                    v-model="filters.course_id"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                >
                    <option value="">Semua course yang ditugaskan</option>
                    <option
                        v-for="course in courses"
                        :key="course.id"
                        :value="course.id"
                    >
                        {{ course.code }} - {{ course.title }}
                    </option>
                </select>
            </div>
            <Button class="self-end" type="submit"><Search /> Terapkan</Button>
            <Button
                class="self-end"
                type="button"
                variant="outline"
                @click="clearFilters"
                ><X /> Reset</Button
            >
        </form>

        <section
            class="overflow-hidden rounded-2xl border border-border/85 bg-card"
        >
            <div v-if="enrollments.data.length" class="divide-y divide-border">
                <article
                    v-for="item in enrollments.data"
                    :key="item.id"
                    class="data-row px-5 py-5"
                >
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start"
                    >
                        <div class="min-w-0">
                            <p class="font-semibold text-foreground">
                                {{ item.user?.name }}
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{
                                    item.user?.employee_id ??
                                    'Tanpa ID karyawan'
                                }}
                                · {{ item.course?.code }} ·
                                {{ item.course?.title }}
                            </p>
                        </div>
                        <p class="text-sm font-medium text-foreground">
                            {{ statusLabel(item.status) }}
                        </p>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-4">
                        <div>
                            <dt class="text-muted-foreground">Pre-Test</dt>
                            <dd class="mt-1 font-medium text-foreground">
                                {{ score(item.pre_test_score) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">
                                Post-Test terbaik
                            </dt>
                            <dd class="mt-1 font-medium text-foreground">
                                {{ score(item.best_post_score) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Peningkatan</dt>
                            <dd class="mt-1 font-medium text-foreground">
                                {{
                                    item.improvement === null
                                        ? '—'
                                        : `${item.improvement > 0 ? '+' : ''}${item.improvement}`
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">
                                Attempt Post-Test
                            </dt>
                            <dd class="mt-1 font-medium text-foreground">
                                {{ item.post_test_attempts }}
                            </dd>
                        </div>
                    </dl>
                    <section
                        v-if="item.can_approve"
                        class="mt-5 rounded-lg border border-border bg-muted/30 p-4"
                    >
                        <h3 class="font-medium text-foreground">
                            Evaluasi akhir
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Seluruh tahapan telah dikirim. Tinjau hasil
                            assessment sebelum menyetujui kelulusan.
                        </p>
                        <Button
                            class="mt-3"
                            size="sm"
                            :disabled="approvingEnrollment !== null"
                            @click="approve(item.id)"
                        >
                            {{
                                approvingEnrollment === item.id
                                    ? 'Menyetujui...'
                                    : 'Setujui kelulusan'
                            }}
                        </Button>
                    </section>
                    <section
                        v-if="item.module_assessments.length"
                        class="mt-5 border-t border-border pt-4"
                    >
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between"
                        >
                            <h3 class="font-medium text-foreground">
                                Evaluasi per modul
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                Pre-Test pertama dan skor terbaik Post-Test
                            </p>
                        </div>
                        <dl class="mt-3 divide-y divide-border text-sm">
                            <div
                                v-for="assessment in item.module_assessments"
                                :key="assessment.module.sequence"
                                class="grid gap-3 py-3 first:pt-0 sm:grid-cols-[minmax(12rem,1fr)_10rem_14rem] sm:items-center"
                            >
                                <div>
                                    <dt class="font-medium text-foreground">
                                        Modul {{ assessment.module.sequence }}:
                                        {{ assessment.module.title }}
                                    </dt>
                                </div>
                                <div v-if="assessment.pre_test">
                                    <dt class="text-muted-foreground">
                                        Pre-Test
                                    </dt>
                                    <dd
                                        class="mt-1 font-medium text-foreground"
                                    >
                                        {{ score(assessment.pre_test.score) }}
                                    </dd>
                                </div>
                                <div v-else>
                                    <dt class="text-muted-foreground">
                                        Pre-Test
                                    </dt>
                                    <dd
                                        class="mt-1 font-medium text-muted-foreground"
                                    >
                                        Belum dikerjakan
                                    </dd>
                                </div>
                                <div v-if="assessment.post_test">
                                    <dt class="text-muted-foreground">
                                        Post-Test
                                    </dt>
                                    <dd
                                        class="mt-1 font-medium text-foreground"
                                    >
                                        {{
                                            postTestStatusLabel(
                                                assessment.post_test,
                                            )
                                        }}
                                        · terbaik
                                        {{
                                            score(
                                                assessment.post_test.best_score,
                                            )
                                        }}
                                        · {{ assessment.post_test.attempts }}x
                                    </dd>
                                </div>
                                <div v-else>
                                    <dt class="text-muted-foreground">
                                        Post-Test
                                    </dt>
                                    <dd
                                        class="mt-1 font-medium text-muted-foreground"
                                    >
                                        Belum dikerjakan
                                    </dd>
                                </div>
                            </div>
                        </dl>
                    </section>
                    <section
                        v-if="item.retake_assessments.length"
                        class="mt-5 border-t border-border pt-4"
                    >
                        <h3 class="font-medium text-foreground">
                            Tindak lanjut assessment
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Buka kesempatan ulang hanya setelah memberikan
                            arahan kepada peserta.
                        </p>
                        <div class="mt-3 grid gap-3">
                            <div
                                v-for="assessment in item.retake_assessments"
                                :key="assessment.id"
                                class="flex flex-col gap-3 rounded-lg border border-border p-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-foreground"
                                    >
                                        {{ assessment.label }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{
                                            retakeStatusLabel(assessment.status)
                                        }}
                                    </p>
                                </div>
                                <Button
                                    v-if="assessment.status === 'available'"
                                    size="sm"
                                    :disabled="authorizingRetake !== null"
                                    @click="
                                        authorizeRetake(item.id, assessment)
                                    "
                                >
                                    <RotateCcw />
                                    {{
                                        authorizingRetake ===
                                        `${item.id}:${assessment.id}`
                                            ? 'Membuka...'
                                            : 'Buka kesempatan ulang'
                                    }}
                                </Button>
                                <Button
                                    v-else-if="assessment.status === 'pending'"
                                    size="sm"
                                    variant="outline"
                                    :disabled="cancellingRetake !== null"
                                    @click="cancelRetake(item.id, assessment)"
                                >
                                    <RotateCcw />
                                    {{
                                        cancellingRetake ===
                                        `${item.id}:${assessment.id}`
                                            ? 'Membatalkan...'
                                            : 'Batalkan kesempatan ulang'
                                    }}
                                </Button>
                            </div>
                        </div>
                    </section>
                </article>
            </div>
            <EmptyState
                v-else
                :icon="ClipboardList"
                title="Belum ada progres pada course Anda"
                description="Data akan muncul ketika course yang ditugaskan kepada Anda memiliki peserta."
            />
            <Pagination
                v-if="enrollments.data.length"
                :from="enrollments.from"
                :to="enrollments.to"
                :total="enrollments.total"
                :previous="enrollments.prev_page_url"
                :next="enrollments.next_page_url"
            />
        </section>
    </div>
</template>
