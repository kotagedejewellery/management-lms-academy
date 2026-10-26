<script setup lang="ts">
const props = defineProps<{ layoutClass?: string }>();
</script>

<template>
    <form
        class="grid gap-3 rounded-2xl border border-border/85 bg-card p-4"
        :class="props.layoutClass"
    >
        <slot />
    </form>
</template>
