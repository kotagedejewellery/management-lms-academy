<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, Check } from '@lucide/vue';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import PageHeader from '@/components/PageHeader.vue';
type Notification = {
    id: number;
    title: string;
    body: string;
    read_at: string | null;
    created_at: string;
};
const props = defineProps<{
    notifications: {
        data: Notification[];
        next_page_url: string | null;
        prev_page_url: string | null;
    };
}>();
const markingId = ref<number | null>(null);
function markRead(notification: Notification): void {
    if (!notification.read_at && markingId.value === null) {
        router.post(
            `/notifications/${notification.id}/read`,
            {},
            {
                preserveScroll: true,
                onStart: () => (markingId.value = notification.id),
                onSuccess: () =>
                    toast.success('Notifikasi ditandai sudah dibaca.'),
                onFinish: () => (markingId.value = null),
            },
        );
    }
}
function date(value: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Notifications', href: '/notifications' }],
    },
});
</script>
<template>
    <Head title="Notifications" />
    <div class="app-page">
        <PageHeader
            title="Notifikasi"
            description="Pengumuman dari KGJ Academy tersimpan di sini agar dapat dibaca kembali."
        />
        <section
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div
                v-if="props.notifications.data.length"
                class="divide-y divide-border"
            >
                <button
                    v-for="notification in props.notifications.data"
                    :key="notification.id"
                    type="button"
                    :disabled="markingId === notification.id"
                    class="flex w-full gap-3 px-5 py-5 text-left transition-colors hover:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none focus-visible:ring-inset sm:px-6"
                    :class="
                        notification.read_at
                            ? ''
                            : 'bg-amber-50/60 dark:bg-amber-950/10'
                    "
                    @click="markRead(notification)"
                >
                    <span
                        class="mt-0.5 grid size-7 shrink-0 place-items-center rounded-full"
                        :class="
                            notification.read_at
                                ? 'bg-muted text-muted-foreground'
                                : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'
                        "
                        aria-hidden="true"
                        ><Check
                            v-if="notification.read_at"
                            class="size-4" /><Bell
                            v-else
                            class="size-4" /></span
                    ><span class="min-w-0"
                        ><span
                            class="flex flex-wrap items-center gap-x-2 gap-y-1"
                            ><span class="font-semibold text-foreground">{{
                                notification.title
                            }}</span
                            ><span
                                v-if="!notification.read_at"
                                class="text-xs font-medium text-amber-800 dark:text-amber-300"
                                >Baru</span
                            ></span
                        ><span
                            class="mt-2 block text-sm leading-6 whitespace-pre-line text-muted-foreground"
                            >{{ notification.body }}</span
                        ><span
                            class="mt-3 block text-xs text-muted-foreground"
                            >{{ date(notification.created_at) }}</span
                        ></span
                    >
                </button>
            </div>
            <div v-else class="px-5 py-14 text-center sm:px-6">
                <Bell
                    class="mx-auto size-5 text-muted-foreground"
                    aria-hidden="true"
                />
                <p class="mt-3 font-medium text-foreground">
                    Belum ada notifikasi
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Pengumuman baru akan muncul di halaman ini.
                </p>
            </div>
            <nav
                v-if="
                    props.notifications.next_page_url ||
                    props.notifications.prev_page_url
                "
                class="flex justify-between border-t border-border px-5 py-3 text-sm"
            >
                <Link
                    v-if="props.notifications.prev_page_url"
                    :href="props.notifications.prev_page_url"
                    class="text-foreground underline underline-offset-4"
                    >Sebelumnya</Link
                ><span v-else /><Link
                    v-if="props.notifications.next_page_url"
                    :href="props.notifications.next_page_url"
                    class="text-foreground underline underline-offset-4"
                    >Berikutnya</Link
                >
            </nav>
        </section>
    </div>
</template>
