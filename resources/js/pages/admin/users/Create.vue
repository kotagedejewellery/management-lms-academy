<script setup lang="ts">
import UserForm from './Form.vue';
type Option = {
    id: number;
    name: string;
    slug?: string;
    employee_id?: string | null;
};
const props = defineProps<{
    roles: Option[];
    divisions: Option[];
    positions: Option[];
    branches: Option[];
    managers: Option[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: '/users' },
            { title: 'Tambah user', href: '/users/create' },
        ],
    },
});
</script>

<template>
    <UserForm title="Tambah user" v-bind="props" />
</template>
