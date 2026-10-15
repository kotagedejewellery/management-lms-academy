<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import AssigneePicker from '@/components/AssigneePicker.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Enrollment = {
    id: number;
    status: string;
    due_at: string | null;
    user: { employee_id: string | null; name: string } | null;
    course: { code: string; title: string } | null;
};
const props = defineProps<{
    enrollments: { data: Enrollment[] };
    courses: { id: number; code: string; title: string }[];
}>();
const dialogOpen = ref(false);
const form = useForm({ user_id: '', course_id: '', due_at: '' });
function openForm(): void {
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}
function submit(): void {
    form.post('/enrollments', {
        onSuccess: () => {
            dialogOpen.value = false;
        },
    });
}
defineOptions({
    layout: { breadcrumbs: [{ title: 'Assignments', href: '/enrollments' }] },
});
</script>

<template>
    <Head title="Assignments" />
    <div class="app-page">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    Assignments
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Tetapkan course yang sudah dipublikasikan kepada employee
                    aktif.
                </p>
            </div>
            <Button @click="openForm"><Plus /> Tetapkan course</Button>
        </header>
        <section
            class="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border"
        >
            <div v-if="enrollments.data.length" class="divide-y divide-border">
                <article
                    v-for="item in enrollments.data"
                    :key="item.id"
                    class="px-5 py-4"
                >
                    <p class="font-medium text-foreground">
                        {{ item.course?.title }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ item.user?.name }} · {{ item.course?.code
                        }}<span v-if="item.due_at">
                            · Tenggat {{ item.due_at }}</span
                        >
                    </p>
                </article>
            </div>
            <p
                v-else
                class="px-5 py-10 text-center text-sm text-muted-foreground"
            >
                Belum ada assignment.
            </p>
        </section>
    </div>
    <Dialog v-model:open="dialogOpen"
        ><DialogContent class="sm:max-w-md"
            ><DialogHeader
                ><DialogTitle>Tetapkan course</DialogTitle
                ><DialogDescription
                    >Employee akan menerima course ini pada daftar
                    pembelajarannya.</DialogDescription
                ></DialogHeader
            >
            <form class="grid gap-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="enrollment-user">Employee</Label
                    ><AssigneePicker
                        id="enrollment-user"
                        v-model="form.user_id"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="enrollment-course">Course</Label
                    ><select
                        id="enrollment-course"
                        v-model="form.course_id"
                        required
                        class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">Pilih course</option>
                        <option
                            v-for="course in courses"
                            :key="course.id"
                            :value="course.id"
                        >
                            {{ course.code }} — {{ course.title }}
                        </option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label for="enrollment-due">Tenggat (opsional)</Label
                    ><Input
                        id="enrollment-due"
                        v-model="form.due_at"
                        type="date"
                    />
                </div>
                <DialogFooter
                    ><Button
                        type="button"
                        variant="outline"
                        @click="dialogOpen = false"
                        >Batal</Button
                    ><Button
                        type="submit"
                        :disabled="form.processing || !form.user_id"
                        >{{
                            form.processing ? 'Menyimpan…' : 'Tetapkan course'
                        }}</Button
                    ></DialogFooter
                >
            </form></DialogContent
        ></Dialog
    >
</template>
