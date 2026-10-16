<script setup lang="ts">
import { nextTick, onBeforeUnmount, ref, useId, watch } from 'vue';

const props = defineProps<{
    message?: string;
}>();

const errorElement = ref<HTMLElement | null>(null);
const errorId = `input-error-${useId()}`;
let control: HTMLElement | null = null;
let previousDescribedBy: string | null = null;
let previousInvalid: string | null = null;

function restoreControl(): void {
    if (!control) return;

    if (previousDescribedBy === null)
        control.removeAttribute('aria-describedby');
    else control.setAttribute('aria-describedby', previousDescribedBy);
    if (previousInvalid === null) control.removeAttribute('aria-invalid');
    else control.setAttribute('aria-invalid', previousInvalid);
    control = null;
}

watch(
    () => props.message,
    async (message, previousMessage) => {
        if (!message) {
            restoreControl();

            return;
        }

        await nextTick();
        const nextControl =
            errorElement.value?.parentElement?.parentElement?.querySelector<HTMLElement>(
                'input, select, textarea, [role="combobox"]',
            );
        if (!nextControl) return;

        if (control !== nextControl) {
            restoreControl();
            control = nextControl;
            previousDescribedBy = control.getAttribute('aria-describedby');
            previousInvalid = control.getAttribute('aria-invalid');
        }
        const describedBy = new Set(
            (previousDescribedBy ?? '').split(/\s+/).filter(Boolean),
        );
        describedBy.add(errorId);
        control.setAttribute('aria-describedby', [...describedBy].join(' '));
        control.setAttribute('aria-invalid', 'true');

        if (!previousMessage) {
            requestAnimationFrame(() => {
                const firstInvalid = document.querySelector<HTMLElement>(
                    '[aria-invalid="true"]',
                );

                firstInvalid?.scrollIntoView({
                    block: 'center',
                    behavior: window.matchMedia(
                        '(prefers-reduced-motion: reduce)',
                    ).matches
                        ? 'auto'
                        : 'smooth',
                });
                firstInvalid?.focus({ preventScroll: true });
            });
        }
    },
    { flush: 'post', immediate: true },
);

onBeforeUnmount(restoreControl);
</script>

<template>
    <div v-show="message" aria-live="assertive">
        <p
            :id="errorId"
            ref="errorElement"
            role="alert"
            class="text-sm text-red-600 dark:text-red-500"
        >
            {{ message }}
        </p>
    </div>
</template>
