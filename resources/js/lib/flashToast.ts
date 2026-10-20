import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

function successMessage(method: string, url: string): string {
    if (url === '/reports/exports') return 'Ekspor laporan sedang disiapkan.';
    if (url === '/user-imports') return 'File diterima dan sedang diproses.';
    if (url === '/announcements') return 'Pengumuman berhasil dikirim.';
    if (url.includes('/assessments/') && url.endsWith('/submit')) {
        return 'Jawaban berhasil dikirim.';
    }
    if (url.endsWith('/move')) return 'Urutan berhasil diperbarui.';
    if (url.includes('/lessons')) {
        return method === 'post'
            ? 'Lesson berhasil ditambahkan.'
            : method === 'delete'
              ? 'Lesson berhasil dihapus.'
              : 'Lesson berhasil diperbarui.';
    }
    if (url.includes('/modules')) {
        return method === 'post'
            ? 'Modul berhasil ditambahkan.'
            : method === 'delete'
              ? 'Modul berhasil dihapus.'
              : 'Modul berhasil diperbarui.';
    }
    if (url.includes('/questions')) {
        return method === 'post'
            ? 'Soal berhasil ditambahkan.'
            : method === 'delete'
              ? 'Soal berhasil dihapus.'
              : 'Soal berhasil diperbarui.';
    }
    if (/\/courses\/\d+\/assessments\/\d+$/.test(url)) {
        return 'Pengaturan assessment berhasil disimpan.';
    }

    return method === 'post'
        ? 'Data berhasil ditambahkan.'
        : method === 'delete'
          ? 'Data berhasil dihapus.'
          : 'Perubahan berhasil disimpan.';
}

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

        const message = successMessage(mutation.method, mutation.url);

        if (
            mutation.url === '/reports/exports' ||
            mutation.url === '/user-imports'
        ) {
            toast.info(message);

            return;
        }

        toast.success(message);
    });

    router.on('error', (event) => {
        if (event.detail.visitId && mutations.delete(event.detail.visitId)) {
            const actionMessage =
                event.detail.errors.action ?? event.detail.errors.status;

            toast.error(
                actionMessage ??
                    'Periksa kembali field yang ditandai sebelum melanjutkan.',
            );
        }
    });

    router.on('networkError', () => {
        toast.error('Koneksi bermasalah. Periksa jaringan lalu coba kembali.');
    });

    router.on('httpException', (event) => {
        if (event.detail.response.status === 403) {
            toast.error(
                'Anda tidak memiliki akses untuk melakukan tindakan ini.',
            );

            return;
        }

        toast.error(
            'Permintaan belum dapat diproses. Coba lagi sebentar atau hubungi LMS Admin bila masalah berlanjut.',
        );
    });
}
