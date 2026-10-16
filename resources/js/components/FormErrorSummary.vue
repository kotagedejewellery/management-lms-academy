<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        errors: Record<string, string | undefined>;
        title?: string;
    }>(),
    {
        title: 'Periksa kembali isian Anda',
    },
);

const messages = computed(() => [
    ...new Set(Object.values(props.errors).filter(Boolean) as string[]),
]);
</script>

<template>
    <div
        v-if="messages.length"
        role="alert"
        class="rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-foreground"
    >
        <p class="font-medium">{{ title }}</p>
        <p class="mt-1 leading-5 text-muted-foreground">
            Perbaiki bagian yang ditandai, lalu kirim formulir kembali.
        </p>
        <ul v-if="messages.length > 1" class="mt-2 list-disc space-y-1 pl-5">
            <li v-for="message in messages" :key="message">{{ message }}</li>
        </ul>
    </div>
</template>
