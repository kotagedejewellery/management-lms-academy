<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon: Component;
    title: string;
    description: string;
}>();
</script>

<template>
    <div class="px-6 py-12 text-center">
        <component :is="icon" class="mx-auto size-6 text-muted-foreground" />
        <h2 class="mt-4 font-semibold text-foreground">{{ title }}</h2>
        <p
            class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted-foreground"
        >
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-5"><slot /></div>
    </div>
</template>
