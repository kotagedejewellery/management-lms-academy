<script setup lang="ts">
import CourseForm from './Form.vue';
type Option = { id: number; name?: string; title?: string };
type Course = {
    id: number;
    code: string;
    title: string;
    description: string | null;
    thumbnail_url: string | null;
    estimated_minutes: number | null;
    is_mandatory: boolean;
    certificate_enabled: boolean;
    passing_grade: number;
    status: 'draft' | 'published' | 'archived';
    category: Option | null;
    division: Option | null;
    position: Option | null;
    prerequisite: Option | null;
    trainer_ids: number[];
};
const props = defineProps<{
    course: Course;
    categories: Option[];
    divisions: Option[];
    positions: Option[];
    prerequisites: Option[];
    trainers: Option[];
    can_assign_trainers: boolean;
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
