<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Award, Download } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';

type Certificate = {
    id: number;
    number: string;
    issued_at: string | null;
    recipient: { name: string; employee_id: string | null } | null;
    course: { code: string; title: string } | null;
    download_url: string | null;
};

defineProps<{
    certificates: {
        data: Certificate[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    can_manage_certificates: boolean;
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Sertifikat', href: '/certificates' }] },
});
</script>

<template>
    <Head title="Sertifikat" />
    <main class="app-page">
        <PageHeader
            :title="
                can_manage_certificates
                    ? 'Sertifikat diterbitkan'
                    : 'Sertifikat saya'
            "
            description="Sertifikat tersedia setelah course yang mengaktifkannya selesai sesuai aturan kelulusan."
        />

        <section
            v-if="certificates.data.length"
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div
                v-for="certificate in certificates.data"
                :key="certificate.id"
                class="flex flex-col gap-4 border-b border-border p-5 last:border-b-0 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="font-medium text-foreground">
                        {{ certificate.course?.title }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ certificate.course?.code }} ·
                        {{ certificate.number }}
                    </p>
                    <p
                        v-if="can_manage_certificates"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ certificate.recipient?.name }}
                        <span v-if="certificate.recipient?.employee_id"
                            >· {{ certificate.recipient.employee_id }}</span
                        >
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground tabular-nums">
                        Diterbitkan {{ certificate.issued_at }}
                    </p>
                </div>
                <Button
                    v-if="certificate.download_url"
                    as-child
                    class="shrink-0"
                >
                    <a :href="certificate.download_url">
                        <Download class="size-4" /> Unduh PDF
                    </a>
                </Button>
            </div>
            <div class="border-t border-border p-4">
                <Pagination
                    :from="certificates.from"
                    :to="certificates.to"
                    :total="certificates.total"
                    :previous="certificates.prev_page_url"
                    :next="certificates.next_page_url"
                />
            </div>
        </section>

        <section v-else class="rounded-xl border border-border bg-card">
            <EmptyState
                :icon="Award"
                title="Belum ada sertifikat"
                description="Sertifikat akan muncul di sini setelah course yang memenuhi syarat selesai."
            />
        </section>
    </main>
</template>
