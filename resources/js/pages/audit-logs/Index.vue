<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';

type Activity = {
    id: number;
    event: string;
    actor: { name: string; employee_id: string | null } | null;
    subject: string | null;
    changes: Record<string, { before: unknown; after: unknown }>;
    created_at: string;
};
const props = defineProps<{
    activities: {
        data: Activity[];
        next_page_url: string | null;
        prev_page_url: string | null;
    };
}>();
const labels: Record<string, string> = {
    'users.store': 'Membuat employee',
    'users.update': 'Memperbarui employee',
    'organization.store': 'Membuat struktur organisasi',
    'organization.update': 'Memperbarui struktur organisasi',
    'courses.store': 'Membuat course',
    'courses.update': 'Memperbarui course',
    'courses.modules.store': 'Menambah modul',
    'courses.modules.update': 'Memperbarui modul',
    'courses.lessons.store': 'Menambah lesson',
    'courses.lessons.update': 'Memperbarui lesson',
    'courses.assessments.store': 'Membuat assessment',
    'courses.assessments.questions.store': 'Menambah soal',
    'learning-paths.store': 'Membuat learning path',
    'learning-paths.update': 'Memperbarui learning path',
    'learning-paths.courses.store': 'Menambah course ke path',
    'learning-paths.courses.update': 'Mengatur urutan path',
    'enrollments.store': 'Menetapkan enrollment',
    'learning-path-assignments.store': 'Menetapkan learning path',
    'user-imports.store': 'Mengunggah impor employee',
    'announcements.store': 'Mengirim pengumuman',
    'reports.exports.store': 'Membuat ekspor laporan',
    'assessment.submitted': 'Mengirim assessment',
};
function label(event: string): string {
    return labels[event] ?? event;
}
function date(value: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
const fieldLabels: Record<string, string> = {
    employee_id: 'Employee ID',
    hired_at: 'Tanggal masuk',
    name: 'Nama',
    email: 'Email',
    phone: 'Telepon',
    division_id: 'Divisi',
    position_id: 'Jabatan',
    branch_id: 'Cabang',
    manager_id: 'Manager',
    status: 'Status',
    code: 'Kode',
    title: 'Judul',
    description: 'Deskripsi',
    is_active: 'Status aktif',
    sequence: 'Urutan',
};
function changeEntries(activity: Activity) {
    return Object.entries(activity.changes);
}
function value(value: unknown): string {
    if (value === null || value === undefined || value === '') return '—';
    if (value === true) return 'Ya';
    if (value === false) return 'Tidak';

    return String(value);
}
defineOptions({
    layout: { breadcrumbs: [{ title: 'Audit activity', href: '/audit-logs' }] },
});
</script>
<template>
    <Head title="Audit activity" />
    <div class="app-page">
        <PageHeader
            title="Aktivitas audit"
            description="Riwayat tindakan administratif dan submission assessment. Data sensitif, jawaban assessment, dan password tidak dicatat."
        />
        <section
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div
                v-if="props.activities.data.length"
                class="divide-y divide-border"
            >
                <article
                    v-for="activity in props.activities.data"
                    :key="activity.id"
                    class="grid gap-1 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:gap-x-8 sm:px-6"
                >
                    <div class="min-w-0">
                        <p class="font-medium text-foreground">
                            {{ label(activity.event) }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ activity.actor?.name ?? 'Akun tidak tersedia'
                            }}<span v-if="activity.actor?.employee_id">
                                · {{ activity.actor.employee_id }}</span
                            ><span v-if="activity.subject">
                                · {{ activity.subject }}</span
                            >
                        </p>
                        <dl
                            v-if="changeEntries(activity).length"
                            class="mt-3 grid gap-2 text-xs sm:grid-cols-2"
                        >
                            <div
                                v-for="[field, change] in changeEntries(
                                    activity,
                                )"
                                :key="field"
                                class="rounded-md border border-border/70 bg-muted/30 px-3 py-2"
                            >
                                <dt class="font-medium text-foreground">
                                    {{ fieldLabels[field] ?? field }}
                                </dt>
                                <dd class="mt-1 text-muted-foreground">
                                    Sebelum: {{ value(change.before) }}
                                </dd>
                                <dd class="text-muted-foreground">
                                    Sesudah: {{ value(change.after) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <time
                        class="text-sm text-muted-foreground sm:text-right"
                        :datetime="activity.created_at"
                        >{{ date(activity.created_at) }}</time
                    >
                </article>
            </div>
            <div v-else class="px-5 py-14 text-center sm:px-6">
                <ShieldCheck
                    class="mx-auto size-5 text-muted-foreground"
                    aria-hidden="true"
                />
                <p class="mt-3 font-medium text-foreground">
                    Belum ada activity tercatat
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Perubahan administratif dan assessment berikutnya akan
                    muncul di sini.
                </p>
            </div>
            <nav
                v-if="
                    props.activities.next_page_url ||
                    props.activities.prev_page_url
                "
                class="flex justify-between border-t border-border px-5 py-3 text-sm"
            >
                <Link
                    v-if="props.activities.prev_page_url"
                    :href="props.activities.prev_page_url"
                    class="text-foreground underline underline-offset-4"
                    >Sebelumnya</Link
                ><span v-else /><Link
                    v-if="props.activities.next_page_url"
                    :href="props.activities.next_page_url"
                    class="text-foreground underline underline-offset-4"
                    >Berikutnya</Link
                >
            </nav>
        </section>
    </div>
</template>
