import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

export function useUnsavedChangesGuard(hasChanges: () => boolean) {
    const open = ref(false);
    const pendingUrl = ref<string | null>(null);
    let bypass = false;
    let removeVisitListener: (() => void) | undefined;

    function reset(): void {
        open.value = false;
        pendingUrl.value = null;
    }

    function leaveWithoutSaving(): void {
        const url = pendingUrl.value;
        reset();

        if (!url) return;

        bypass = true;
        router.visit(url, {
            onFinish: () => {
                bypass = false;
            },
        });
    }

    function warnBeforeUnload(event: BeforeUnloadEvent): void {
        if (!hasChanges()) return;

        event.preventDefault();
        event.returnValue = '';
    }

    onMounted(() => {
        removeVisitListener = router.on('before', (event) => {
            if (
                bypass ||
                event.detail.visit.method.toLowerCase() !== 'get' ||
                !hasChanges()
            ) {
                return;
            }

            pendingUrl.value = event.detail.visit.url.href;
            open.value = true;

            return false;
        });
        window.addEventListener('beforeunload', warnBeforeUnload);
    });

    onBeforeUnmount(() => {
        removeVisitListener?.();
        window.removeEventListener('beforeunload', warnBeforeUnload);
    });

    return { open, reset, leaveWithoutSaving };
}
