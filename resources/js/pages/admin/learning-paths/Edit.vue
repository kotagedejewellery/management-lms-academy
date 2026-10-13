<script setup lang="ts">
import LearningPathForm from './Form.vue';
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Learning paths', href: '/learning-paths' },
            { title: 'Edit learning path', href: '/learning-paths' },
        ],
    },
});
</script>
<template>
    <LearningPathForm title="Ubah learning path" v-bind="$attrs" />
</template>
