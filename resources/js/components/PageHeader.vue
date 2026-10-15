<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <header
        class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
    >
        <div class="min-w-0">
            <h1
                class="max-w-3xl text-2xl font-semibold tracking-[-0.025em] text-foreground sm:text-3xl"
            >
                {{ title }}
            </h1>
            <p
                v-if="description"
                class="mt-2 max-w-3xl text-sm leading-6 text-muted-foreground sm:text-base"
            >
                {{ description }}
            </p>
            <span
                class="mt-5 block h-px w-12 bg-brand-gold/75"
                aria-hidden="true"
            />
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 flex-wrap gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
