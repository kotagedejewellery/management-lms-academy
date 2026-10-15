<script setup lang="ts">
import UserForm from './Form.vue';
type Option = {
    id: number;
    name: string;
    slug?: string;
    employee_id?: string | null;
};
type ManagedUser = {
    id: number;
    employee_id: string | null;
    hired_at: string | null;
    name: string;
    email: string;
    phone: string | null;
    status: 'active' | 'inactive';
    role: Option | null;
    division: Option | null;
    position: Option | null;
    branch: Option | null;
    manager: Option | null;
};
const props = defineProps<{
    user: ManagedUser;
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
            { title: 'Edit user', href: '/users' },
        ],
    },
});
</script>

<template>
    <UserForm title="Edit user" v-bind="props" />
</template>
