<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon: Component;
    title: string;
    description: string;
}>();
</script>

<template>
    <div class="px-6 py-12 text-center sm:py-16">
        <div
            class="mx-auto grid size-11 place-items-center rounded-full bg-brand-gold/12 text-brand-gold"
        >
            <component :is="icon" class="size-5" />
        </div>
        <h2 class="mt-5 font-semibold tracking-tight text-foreground">
            {{ title }}
        </h2>
        <p
            class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted-foreground"
        >
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-5"><slot /></div>
    </div>
</template>
