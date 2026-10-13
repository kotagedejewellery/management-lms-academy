<script setup lang="ts">
import CourseForm from './Form.vue';
type Option = { id: number; name?: string; title?: string };
const props = defineProps<{
    categories: Option[];
    divisions: Option[];
    positions: Option[];
    prerequisites: Option[];
}>();
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: '/courses' },
            { title: 'Tambah course', href: '/courses/create' },
        ],
    },
});
</script>
<template><CourseForm title="Tambah course" v-bind="props" /></template>
