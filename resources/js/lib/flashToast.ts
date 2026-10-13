import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    const mutations = new Map<string, { method: string; url: string }>();

    router.on('before', (event) => {
        const { method, url, id } = event.detail.visit;

        if (['post', 'put', 'patch', 'delete'].includes(method)) {
            mutations.set(id, { method, url: url.pathname });
        }
    });

    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (!data) {
            return;
        }

        toast[data.type](data.message);
    });

    router.on('success', (event) => {
        const mutation = event.detail.visitId
            ? mutations.get(event.detail.visitId)
            : undefined;

        if (!event.detail.visitId || !mutation) {
            return;
        }

        mutations.delete(event.detail.visitId);

        if (
            ['/login', '/logout'].includes(mutation.url) ||
            mutation.url.startsWith('/notifications/')
        ) {
            return;
        }

        const flash = event.detail.page.flash as
            | { toast?: FlashToast }
            | undefined;
        if (flash?.toast) {
            return;
        }

        if (mutation.url === '/reports/exports') {
            toast.info('Ekspor laporan sedang disiapkan.');
            return;
        }

        if (mutation.url === '/user-imports') {
            toast.info('File diterima dan sedang diproses.');
            return;
        }

        if (mutation.url === '/announcements') {
            toast.success('Pengumuman berhasil dikirim.');
            return;
        }

        if (
            mutation.url.includes('/assessments/') &&
            mutation.url.endsWith('/submit')
        ) {
            toast.success('Jawaban berhasil dikirim.');
            return;
        }

        toast.success(
            mutation.method === 'post'
                ? 'Data berhasil ditambahkan.'
                : mutation.method === 'delete'
                  ? 'Data berhasil dihapus.'
                  : 'Perubahan berhasil disimpan.',
        );
    });

    router.on('error', (event) => {
        if (event.detail.visitId && mutations.delete(event.detail.visitId)) {
            toast.error(
                'Periksa kembali field yang ditandai sebelum melanjutkan.',
            );
        }
    });

    router.on('networkError', () => {
        toast.error('Koneksi bermasalah. Periksa jaringan lalu coba kembali.');
    });

    router.on('httpException', () => {
        toast.error(
            'Sistem belum dapat memproses permintaan ini. Coba lagi sebentar.',
        );
    });
}
