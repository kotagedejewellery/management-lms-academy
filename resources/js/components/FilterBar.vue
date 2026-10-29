<script setup lang="ts">
const props = defineProps<{
    layout?: 'flex' | 'grid';
    layoutClass?: string;
}>();
</script>

<template>
    <form
        :class="[
            props.layout === 'grid'
                ? 'grid items-end gap-3 rounded-2xl border border-border/85 bg-card p-4'
                : 'flex flex-wrap items-end gap-3 rounded-2xl border border-border/85 bg-card p-4',
            props.layoutClass,
        ]"
    >
        <slot />
    </form>
</template>
