<script setup lang="ts">
import LearningPathForm from './Form.vue';
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Learning paths', href: '/learning-paths' },
            { title: 'Tambah learning path', href: '/learning-paths/create' },
        ],
    },
});
</script>
<template>
    <LearningPathForm title="Tambah learning path" v-bind="$attrs" />
</template>
