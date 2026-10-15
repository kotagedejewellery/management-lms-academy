<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ClipboardList, Download, FileDown, Search, X } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Enrollment = {
    id: number;
    status: 'active' | 'completed' | 'not_passed';
    due_at: string | null;
    completed_at: string | null;
    pre_test_score: number | null;
    best_post_score: number | null;
    improvement: number | null;
    post_test_attempts: number;
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
    applyFilters();
}
function searchCourses(): void {
    filters.course_id = '';
    courseOptions.value = [];
    courseSearching.value = false;
    courseSearchError.value = '';
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
    return { active: 'Aktif', completed: 'Selesai', not_passed: 'Tidak lulus' }[
        status
    ];
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
                    Pantau baseline, skor terbaik, improvement, attempt, dan
                    status course per karyawan.
                </p>
            </div>
            <Button :disabled="exporting" @click="exportCsv"
                ><FileDown />
                {{ exporting ? 'Menyiapkan ekspor' : 'Ekspor CSV' }}</Button
            >
        </header>
        <form
            class="grid gap-3 rounded-2xl border border-border/85 bg-card p-4 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_11rem_15rem_13rem_auto_auto]"
            @submit.prevent="applyFilters"
        >
            <div class="grid gap-2">
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
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                >
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="completed">Selesai</option>
                    <option value="not_passed">Tidak lulus</option>
                </select>
            </div>
            <div class="grid gap-2">
                <Label for="report-course-search">Course</Label>
                <Input
                    id="report-course-search"
                    v-model="courseQuery"
                    placeholder="Cari nama atau kode course"
                    autocomplete="off"
                    @input="searchCourses"
                />
                <select
                    id="report-course"
                    v-model="filters.course_id"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                >
                    <option value="">Semua course</option>
                    <option
                        v-for="course in courseOptions"
                        :key="course.id"
                        :value="course.id"
                    >
                        {{ course.code }} - {{ course.title }}
                    </option>
                </select>
                <p class="text-xs text-muted-foreground" aria-live="polite">
                    {{
                        courseSearching
                            ? 'Mencari course...'
                            : courseSearchError ||
                              'Ketik minimal 2 karakter, lalu pilih course.'
                    }}
                </p>
                <Button
                    v-if="courseSearchError"
                    type="button"
                    size="sm"
                    variant="outline"
                    @click="retryCourseSearch"
                    >Coba lagi</Button
                >
            </div>
            <div class="grid gap-2">
                <Label for="report-division">Divisi</Label
                ><select
                    id="report-division"
                    v-model="filters.division_id"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
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
            <Button class="self-end" type="submit"><Search /> Terapkan</Button
            ><Button
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
                            Pre {{ score(item.pre_test_score) }} · Post terbaik
                            {{ score(item.best_post_score) }} ·
                            {{
                                item.improvement === null
                                    ? 'Improvement —'
                                    : `Improvement ${item.improvement > 0 ? '+' : ''}${item.improvement}`
                            }}
                        </p>
                    </div>
                    <p class="text-sm font-medium text-foreground">
                        {{ statusLabel(item.status) }} ·
                        {{ item.post_test_attempts }} attempt Post-Test
                    </p>
                </article>
            </div>
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[920px] text-left text-sm">
                    <thead
                        class="border-b border-border bg-muted/40 text-muted-foreground"
                    >
                        <tr>
                            <th class="px-5 py-3 font-medium">Karyawan</th>
                            <th class="px-5 py-3 font-medium">Course</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">
                                Pre-Test
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Post terbaik
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Improvement
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Attempt
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
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{ score(item.pre_test_score) }}
                            </td>
                            <td
                                class="px-5 py-4 text-right font-medium text-foreground tabular-nums"
                            >
                                {{ score(item.best_post_score) }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{
                                    item.improvement === null
                                        ? '—'
                                        : `${item.improvement > 0 ? '+' : ''}${item.improvement}`
                                }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums">
                                {{ item.post_test_attempts }}
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
