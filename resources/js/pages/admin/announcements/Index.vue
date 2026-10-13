<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Megaphone } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
type Announcement = {
    id: number;
    title: string;
    body: string;
    published_at: string | null;
    author: { name: string } | null;
};
defineProps<{
    announcements: {
        data: Announcement[];
        next_page_url: string | null;
        prev_page_url: string | null;
    };
}>();
const form = useForm({ title: '', body: '' });
const sendDialogOpen = ref(false);
function submit(): void {
    form.post('/announcements', { onSuccess: () => form.reset() });
}
function requestSubmit(): void {
    if (!form.processing && form.title && form.body)
        sendDialogOpen.value = true;
}
function date(value: string | null): string {
    return value
        ? new Intl.DateTimeFormat('id-ID', {
              dateStyle: 'medium',
              timeStyle: 'short',
          }).format(new Date(value))
        : 'Sedang diproses';
}
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Announcements', href: '/announcements' }],
    },
});
</script>
<template>
    <Head title="Announcements" />
    <div class="flex w-full flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <header class="max-w-2xl">
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                Announcements
            </h1>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                Kirim pengumuman ke inbox seluruh employee aktif. WhatsApp
                diproses di background bila nomor dan Fonnte telah
                dikonfigurasi.
            </p>
        </header>
        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_24rem]">
            <section
                class="overflow-hidden rounded-xl border border-border bg-card"
            >
                <div
                    v-if="announcements.data.length"
                    class="divide-y divide-border"
                >
                    <article
                        v-for="announcement in announcements.data"
                        :key="announcement.id"
                        class="px-5 py-5 sm:px-6"
                    >
                        <div class="flex items-start gap-3">
                            <Megaphone
                                class="mt-0.5 size-4 shrink-0 text-amber-700"
                                aria-hidden="true"
                            />
                            <div class="min-w-0">
                                <h2 class="font-semibold text-foreground">
                                    {{ announcement.title }}
                                </h2>
                                <p
                                    class="mt-2 text-sm leading-6 whitespace-pre-line text-muted-foreground"
                                >
                                    {{ announcement.body }}
                                </p>
                                <p class="mt-3 text-xs text-muted-foreground">
                                    {{ announcement.author?.name ?? 'Admin' }} ·
                                    {{ date(announcement.published_at) }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
                <p
                    v-else
                    class="px-5 py-12 text-center text-sm text-muted-foreground"
                >
                    Belum ada pengumuman yang dikirim.
                </p>
                <nav
                    v-if="
                        announcements.next_page_url ||
                        announcements.prev_page_url
                    "
                    class="flex justify-between border-t border-border px-5 py-3 text-sm"
                >
                    <Link
                        v-if="announcements.prev_page_url"
                        :href="announcements.prev_page_url"
                        class="text-foreground underline underline-offset-4"
                        >Sebelumnya</Link
                    ><span v-else /><Link
                        v-if="announcements.next_page_url"
                        :href="announcements.next_page_url"
                        class="text-foreground underline underline-offset-4"
                        >Berikutnya</Link
                    >
                </nav>
            </section>
            <section class="rounded-xl border border-border bg-card p-5 sm:p-6">
                <h2 class="font-semibold text-foreground">Pengumuman baru</h2>
                <p class="mt-1 text-sm leading-6 text-muted-foreground">
                    Satu pengumuman dibuat untuk setiap employee aktif.
                </p>
                <form class="mt-5 grid gap-4" @submit.prevent="requestSubmit">
                    <div class="grid gap-2">
                        <Label for="announcement-title">Judul</Label
                        ><input
                            id="announcement-title"
                            v-model="form.title"
                            required
                            maxlength="160"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                            :aria-invalid="Boolean(form.errors.title)"
                        />
                        <p
                            v-if="form.errors.title"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.title }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="announcement-body">Pesan</Label
                        ><textarea
                            id="announcement-body"
                            v-model="form.body"
                            required
                            maxlength="4000"
                            rows="7"
                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                            :aria-invalid="Boolean(form.errors.body)"
                        />
                        <p
                            v-if="form.errors.body"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.body }}
                        </p>
                    </div>
                    <Button type="submit" :disabled="form.processing">{{
                        form.processing ? 'Mengirim…' : 'Kirim pengumuman'
                    }}</Button>
                </form>
            </section>
        </div>
        <ConfirmActionDialog
            v-model:open="sendDialogOpen"
            title="Kirim pengumuman?"
            description="Pengumuman akan dikirim ke inbox seluruh employee aktif. Notifikasi WhatsApp akan diproses sesuai konfigurasi."
            confirm-label="Kirim pengumuman"
            :processing="form.processing"
            @confirm="submit"
        />
    </div>
</template>
