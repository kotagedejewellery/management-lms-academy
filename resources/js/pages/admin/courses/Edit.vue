<script setup lang="ts">
import CourseForm from './Form.vue';
type Option = { id: number; name?: string; title?: string };
type Course = {
    id: number;
    code: string;
    title: string;
    description: string | null;
    estimated_minutes: number | null;
    is_mandatory: boolean;
    passing_grade: number;
    status: 'draft' | 'published' | 'archived';
    category: Option | null;
    division: Option | null;
    position: Option | null;
    prerequisite: Option | null;
};
const props = defineProps<{
    course: Course;
    categories: Option[];
    divisions: Option[];
    positions: Option[];
    prerequisites: Option[];
}>();
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: '/courses' },
            { title: 'Edit course', href: '/courses' },
        ],
    },
});
</script>
<template><CourseForm title="Ubah course" v-bind="props" /></template>
