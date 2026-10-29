<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ClipboardList, Download, FileDown, Search, X } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import FilterBar from '@/components/FilterBar.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Enrollment = {
    id: number;
    status: 'active' | 'pending_review' | 'completed' | 'not_passed';
    due_at: string | null;
    completed_at: string | null;
    pre_test_score: number | null;
    best_post_score: number | null;
    improvement: number | null;
    post_test_attempts: number;
    has_course_assessment: boolean;
    assessment_summary: {
        score: number | null;
        completed_modules: number;
        total_modules: number;
        is_final: boolean;
    };
    user: { employee_id: string | null; name: string } | null;
    course: { code: string; title: string } | null;
};
type Filters = {
    search: string;
    status: string;
    course_id: number | null;
    division_id: number | null;
};
type Export = {
    id: number;
    status: 'queued' | 'processing' | 'ready' | 'failed';
    created_at: string;
    completed_at: string | null;
    download_url: string | null;
};
type Division = { id: number; name: string };
type Course = { id: number; code: string; title: string };
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
    divisions: Division[];
    filters: Filters;
    exports: Export[];
}>();
const filters = {
    search: props.filters.search,
    status: props.filters.status,
    course_id: props.filters.course_id?.toString() ?? '',
    division_id: props.filters.division_id?.toString() ?? '',
};
const exporting = ref(false);
const courseOptions = ref<Course[]>(props.courses);
const courseQuery = ref(
    props.courses[0]
        ? `${props.courses[0].code} - ${props.courses[0].title}`
        : '',
);
const courseSearching = ref(false);
const courseSearchError = ref('');
const courseSelectionOpen = ref(false);
const activeCourseIndex = ref(-1);
let courseSearchTimer: ReturnType<typeof setTimeout> | undefined;
let courseSearchRequest = 0;
let exportPollTimer: ReturnType<typeof setInterval> | undefined;
const hasPendingExport = computed(() =>
    props.exports.some(
        (item) => item.status === 'queued' || item.status === 'processing',
    ),
);
function applyFilters(): void {
    router.get('/reports', filters, { preserveState: true, replace: true });
}
function clearFilters(): void {
    filters.search = '';
    filters.status = '';
    filters.course_id = '';
    filters.division_id = '';
    courseQuery.value = '';
    courseOptions.value = [];
    courseSelectionOpen.value = false;
    activeCourseIndex.value = -1;
    applyFilters();
}
function searchCourses(): void {
    filters.course_id = '';
    courseOptions.value = [];
    courseSearching.value = false;
    courseSearchError.value = '';
    courseSelectionOpen.value = true;
    activeCourseIndex.value = -1;
    clearTimeout(courseSearchTimer);

    const query = courseQuery.value.trim();
    if (query.length < 2) return;

    const request = ++courseSearchRequest;
    courseSearching.value = true;
    courseSearchTimer = setTimeout(async () => {
        try {
            const response = await fetch(
                `/reports/courses?q=${encodeURIComponent(query)}`,
                { headers: { Accept: 'application/json' } },
            );
            if (!response.ok) throw new Error('Course tidak dapat dimuat.');
            const courses = (await response.json()) as Course[];
            if (request === courseSearchRequest) courseOptions.value = courses;
        } catch {
            if (request === courseSearchRequest) {
                courseSearchError.value =
                    'Course tidak dapat dimuat. Periksa koneksi lalu coba lagi.';
            }
        } finally {
            if (request === courseSearchRequest) courseSearching.value = false;
        }
    }, 250);
}
function selectCourse(course: Course): void {
    filters.course_id = course.id.toString();
    courseQuery.value = `${course.code} - ${course.title}`;
    courseOptions.value = [course];
    courseSearchError.value = '';
    courseSelectionOpen.value = false;
    activeCourseIndex.value = -1;
}
function clearCourse(): void {
    filters.course_id = '';
    courseQuery.value = '';
    courseOptions.value = [];
    courseSearchError.value = '';
    courseSelectionOpen.value = false;
    activeCourseIndex.value = -1;
}
function moveCourseSelection(direction: 1 | -1): void {
    if (!courseOptions.value.length) return;

    courseSelectionOpen.value = true;
    activeCourseIndex.value =
        (activeCourseIndex.value + direction + courseOptions.value.length) %
        courseOptions.value.length;
}
function selectActiveCourse(event: KeyboardEvent): void {
    const course =
        courseOptions.value[activeCourseIndex.value] ?? courseOptions.value[0];
    if (!course) return;

    event.preventDefault();
    selectCourse(course);
}
function closeCourseSelection(): void {
    courseSelectionOpen.value = false;
    activeCourseIndex.value = -1;
}
function retryCourseSearch(): void {
    searchCourses();
}
function exportCsv(): void {
    router.post('/reports/exports', filters, {
        preserveScroll: true,
        onStart: () => (exporting.value = true),
        onFinish: () => (exporting.value = false),
    });
}
function score(value: number | null): string {
    return value === null ? '—' : value.toFixed(2);
}
function courseAssessmentValue(item: Enrollment, value: number | null): string {
    return item.has_course_assessment ? score(value) : 'Tidak digunakan';
}
function courseImprovement(item: Enrollment): string {
    if (!item.has_course_assessment) return 'Tidak digunakan';

    return item.improvement === null
        ? '—'
        : `${item.improvement > 0 ? '+' : ''}${item.improvement}`;
}
function moduleCourseScore(item: Enrollment): string {
    return item.assessment_summary.total_modules === 0
        ? 'Tidak digunakan'
        : score(item.assessment_summary.score);
}
function moduleCourseScoreLabel(item: Enrollment): string {
    if (item.assessment_summary.total_modules === 0) {
        return 'Tidak digunakan';
    }

    return item.assessment_summary.is_final
        ? 'Nilai akhir'
        : 'Rata-rata berjalan';
}
function moduleCompletion(item: Enrollment): string {
    if (item.assessment_summary.total_modules === 0) {
        return 'Tidak digunakan';
    }

    return `${item.assessment_summary.completed_modules} dari ${item.assessment_summary.total_modules}`;
}
function startExportPolling(): void {
    if (exportPollTimer || !hasPendingExport.value) return;

    exportPollTimer = setInterval(() => {
        router.reload({ only: ['exports'] });
    }, 5000);
}
function stopExportPolling(): void {
    if (!exportPollTimer) return;

    clearInterval(exportPollTimer);
    exportPollTimer = undefined;
}
watch(hasPendingExport, (pending) => {
    if (pending) startExportPolling();
    else stopExportPolling();
});
onMounted(startExportPolling);
onBeforeUnmount(stopExportPolling);
function statusLabel(status: Enrollment['status']): string {
    return {
        active: 'Aktif',
        pending_review: 'Menunggu evaluasi akhir',
        completed: 'Selesai',
        not_passed: 'Tidak lulus',
    }[status];
}
defineOptions({
    layout: { breadcrumbs: [{ title: 'Reports', href: '/reports' }] },
});
</script>

<template>
    <Head title="Reports" />
    <div class="app-page">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div>
                <h1
                    class="text-2xl font-semibold tracking-[-0.025em] text-foreground sm:text-3xl"
                >
                    Laporan pembelajaran
                </h1>
                <p
                    class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground"
                >
                    Pantau nilai course per modul, baseline, skor terbaik,
                    improvement, attempt, dan status course per karyawan.
                </p>
            </div>
            <Button :disabled="exporting" @click="exportCsv"
                ><FileDown />
                {{ exporting ? 'Menyiapkan ekspor' : 'Ekspor CSV' }}</Button
            >
        </header>
        <FilterBar
            layout="grid"
            layout-class="grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(0,1.4fr)_13rem_minmax(18rem,1.1fr)_13rem_auto]"
            @submit.prevent="applyFilters"
        >
            <div class="grid min-w-0 gap-2">
                <Label for="report-search">Cari</Label
                ><Input
                    id="report-search"
                    v-model="filters.search"
                    placeholder="Nama, ID, atau course"
                />
            </div>
            <div class="grid gap-2">
                <Label for="report-status">Status</Label
                ><select
                    id="report-status"
                    v-model="filters.status"
                    class="h-10 rounded-lg border border-input bg-transparent px-3 text-sm"
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
            <div class="grid min-w-0 gap-2">
                <Label for="report-course-search">Course</Label>
                <div class="relative">
                    <Input
                        id="report-course-search"
                        v-model="courseQuery"
                        class="pr-10"
                        placeholder="Cari course (min. 2 karakter)"
                        autocomplete="off"
                        role="combobox"
                        aria-autocomplete="list"
                        aria-controls="report-course-options"
                        :aria-activedescendant="
                            activeCourseIndex >= 0
                                ? `report-course-option-${courseOptions[activeCourseIndex]?.id}`
                                : undefined
                        "
                        :aria-expanded="courseSelectionOpen"
                        @focus="courseSelectionOpen = true"
                        @input="searchCourses"
                        @keydown.down.prevent="moveCourseSelection(1)"
                        @keydown.up.prevent="moveCourseSelection(-1)"
                        @keydown.enter="selectActiveCourse"
                        @keydown.esc="closeCourseSelection"
                    />
                    <button
                        v-if="filters.course_id"
                        class="absolute top-1/2 right-2 grid size-7 -translate-y-1/2 place-items-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        type="button"
                        aria-label="Hapus filter course"
                        @click="clearCourse"
                    >
                        <X class="size-4" />
                    </button>
                    <div
                        v-if="
                            courseSelectionOpen &&
                            courseQuery.trim().length >= 2
                        "
                        id="report-course-options"
                        class="absolute z-20 mt-2 w-full overflow-hidden rounded-lg border border-border bg-popover shadow-lg"
                    >
                        <p
                            v-if="courseSearching"
                            class="px-3 py-2 text-sm text-muted-foreground"
                        >
                            Mencari course...
                        </p>
                        <div
                            v-else-if="courseSearchError"
                            class="flex items-center justify-between gap-2 px-3 py-2"
                        >
                            <p class="text-sm text-destructive">
                                {{ courseSearchError }}
                            </p>
                            <Button
                                size="sm"
                                type="button"
                                variant="outline"
                                @click="retryCourseSearch"
                                >Coba lagi</Button
                            >
                        </div>
                        <ul
                            v-else-if="courseOptions.length"
                            class="max-h-52 overflow-y-auto py-1"
                            role="listbox"
                        >
                            <li
                                v-for="(course, index) in courseOptions"
                                :key="course.id"
                                :id="`report-course-option-${course.id}`"
                                role="option"
                                :aria-selected="
                                    filters.course_id === course.id.toString()
                                "
                            >
                                <button
                                    class="w-full px-3 py-2 text-left text-sm hover:bg-muted focus-visible:bg-muted focus-visible:outline-none"
                                    :class="{
                                        'bg-muted': activeCourseIndex === index,
                                    }"
                                    type="button"
                                    @click="selectCourse(course)"
                                >
                                    <span class="font-medium text-foreground">
                                        {{ course.title }}
                                    </span>
                                    <span
                                        class="ml-2 text-xs text-muted-foreground"
                                    >
                                        {{ course.code }}
                                    </span>
                                </button>
                            </li>
                        </ul>
                        <p
                            v-else
                            class="px-3 py-2 text-sm text-muted-foreground"
                        >
                            Course tidak ditemukan.
                        </p>
                    </div>
                </div>
                <p class="sr-only" aria-live="polite">
                    {{
                        courseSearching
                            ? 'Mencari course...'
                            : courseSearchError ||
                              (filters.course_id
                                  ? 'Course dipilih untuk filter laporan.'
                                  : 'Ketik minimal 2 karakter, lalu pilih course.')
                    }}
                </p>
            </div>
            <div class="grid gap-2">
                <Label for="report-division">Divisi</Label
                ><select
                    id="report-division"
                    v-model="filters.division_id"
                    class="h-10 rounded-lg border border-input bg-transparent px-3 text-sm"
                >
                    <option value="">Semua divisi</option>
                    <option
                        v-for="division in divisions"
                        :key="division.id"
                        :value="division.id"
                    >
                        {{ division.name }}
                    </option>
                </select>
            </div>
            <div
                class="flex w-full gap-2 self-end md:col-span-2 xl:col-span-1 xl:w-auto"
            >
                <Button class="flex-1 xl:flex-none" type="submit"
                    ><Search /> Terapkan</Button
                >
                <Button
                    class="flex-1 xl:flex-none"
                    type="button"
                    variant="outline"
                    @click="clearFilters"
                    ><X /> Reset</Button
                >
            </div>
        </FilterBar>
        <section
            class="overflow-hidden rounded-2xl border border-border/85 bg-card"
        >
            <div
                v-if="enrollments.data.length"
                class="divide-y divide-border md:hidden"
            >
                <article
                    v-for="item in enrollments.data"
                    :key="item.id"
                    class="space-y-3 px-5 py-4"
                >
                    <div>
                        <h2 class="font-medium text-foreground">
                            {{ item.user?.name }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ item.user?.employee_id ?? 'Tanpa ID karyawan' }}
                        </p>
                    </div>
                    <div>
                        <p class="font-medium text-foreground">
                            {{ item.course?.title }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ moduleCourseScoreLabel(item) }}:
                            {{ moduleCourseScore(item) }}
                            <template
                                v-if="item.assessment_summary.total_modules > 0"
                            >
                                · {{ moduleCompletion(item) }} modul evaluatif
                                selesai
                            </template>
                        </p>
                        <p
                            v-if="item.has_course_assessment"
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Pre-Test course
                            {{ score(item.pre_test_score) }} · Post-Test course
                            terbaik {{ score(item.best_post_score) }} ·
                            Peningkatan course {{ courseImprovement(item) }}
                        </p>
                    </div>
                    <p class="text-sm font-medium text-foreground">
                        {{ statusLabel(item.status) }}
                        <template v-if="item.has_course_assessment">
                            · {{ item.post_test_attempts }} percobaan Post-Test
                            course
                        </template>
                    </p>
                </article>
            </div>
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[1160px] text-left text-sm">
                    <thead
                        class="border-b border-border bg-muted/40 text-muted-foreground"
                    >
                        <tr>
                            <th class="px-5 py-3 font-medium">Karyawan</th>
                            <th class="px-5 py-3 font-medium">Course</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">
                                Nilai course
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Modul evaluatif selesai
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Pre-Test course
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Post-Test course terbaik
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Peningkatan course
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Percobaan Post-Test course
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="item in enrollments.data" :key="item.id">
                            <td class="px-5 py-4">
                                <p class="font-medium text-foreground">
                                    {{ item.user?.name }}
                                </p>
                                <p class="mt-1 text-muted-foreground">
                                    {{
                                        item.user?.employee_id ??
                                        'Tanpa ID karyawan'
                                    }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-foreground">
                                    {{ item.course?.title }}
                                </p>
                                <p class="mt-1 text-muted-foreground">
                                    {{ item.course?.code }}
                                </p>
                            </td>
                            <td class="px-5 py-4 font-medium text-foreground">
                                {{ statusLabel(item.status) }}
                            </td>
                            <td
                                class="px-5 py-4 text-right font-medium text-foreground tabular-nums"
                            >
                                {{ moduleCourseScore(item) }}
                                <span
                                    v-if="
                                        item.assessment_summary.total_modules >
                                        0
                                    "
                                    class="mt-1 block text-xs font-normal text-muted-foreground"
                                >
                                    {{ moduleCourseScoreLabel(item) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{ moduleCompletion(item) }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{
                                    courseAssessmentValue(
                                        item,
                                        item.pre_test_score,
                                    )
                                }}
                            </td>
                            <td
                                class="px-5 py-4 text-right font-medium text-foreground tabular-nums"
                            >
                                {{
                                    courseAssessmentValue(
                                        item,
                                        item.best_post_score,
                                    )
                                }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{ courseImprovement(item) }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{
                                    item.has_course_assessment
                                        ? item.post_test_attempts
                                        : 'Tidak digunakan'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <EmptyState
                v-if="!enrollments.data.length"
                :icon="ClipboardList"
                title="Tidak ada data laporan"
                description="Ubah filter atau buat assignment baru untuk mulai memantau pembelajaran."
            /><Pagination
                v-else
                :from="enrollments.from"
                :to="enrollments.to"
                :total="enrollments.total"
                :previous="enrollments.prev_page_url"
                :next="enrollments.next_page_url"
            />
        </section>
        <section
            v-if="exports.length"
            class="overflow-hidden rounded-2xl border border-border/85 bg-card"
        >
            <div class="border-b border-border px-5 py-4">
                <h2 class="font-semibold text-foreground">Ekspor terbaru</h2>
            </div>
            <div class="divide-y divide-border">
                <div
                    v-for="item in exports"
                    :key="item.id"
                    class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="font-medium text-foreground">Laporan CSV</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{
                                item.status === 'ready'
                                    ? `Siap${item.completed_at ? ` - ${item.completed_at}` : ''}`
                                    : item.status === 'failed'
                                      ? 'Gagal diproses'
                                      : 'Sedang diproses'
                            }}
                        </p>
                    </div>
                    <Button v-if="item.download_url" size="sm" as-child
                        ><a :href="item.download_url"
                            ><Download /> Unduh</a
                        ></Button
                    >
                    <p v-else class="text-sm text-muted-foreground">
                        {{
                            item.status === 'failed'
                                ? 'Buat ekspor baru untuk mencoba kembali.'
                                : 'Status akan diperbarui otomatis selama halaman ini terbuka.'
                        }}
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>
