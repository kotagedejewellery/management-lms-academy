<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
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
import { Label } from '@/components/ui/label';

type Assignment = {
    id: number;
    user: { name: string; employee_id: string | null } | null;
    path: { name: string } | null;
};
type Rule = {
    id: number;
    path: { name: string } | null;
    division: { name: string } | null;
    position: { name: string } | null;
    branch: { name: string } | null;
};
type Division = { id: number; name: string };
type Position = { id: number; division_id: number; name: string };
const props = defineProps<{
    assignments: { data: Assignment[] };
    rules: Rule[];
    paths: { id: number; name: string }[];
    divisions: Division[];
    positions: Position[];
    branches: { id: number; name: string }[];
}>();
const dialogOpen = ref(false);
const ruleDialogOpen = ref(false);
const form = useForm({ user_id: '', learning_path_id: '' });
const ruleForm = useForm({
    learning_path_id: '',
    division_id: '',
    position_id: '',
    branch_id: '',
});
const availablePositions = computed(() =>
    props.positions.filter(
        (position) =>
            !ruleForm.division_id ||
            position.division_id === Number(ruleForm.division_id),
    ),
);
function openForm(): void {
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}
function openRuleForm(): void {
    ruleForm.reset();
    ruleForm.clearErrors();
    ruleDialogOpen.value = true;
}
function submit(): void {
    form.post('/learning-path-assignments', {
        onSuccess: () => {
            dialogOpen.value = false;
        },
    });
}
function submitRule(): void {
    ruleForm.post('/learning-path-assignment-rules', {
        onSuccess: () => {
            ruleDialogOpen.value = false;
        },
    });
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Path assignments', href: '/learning-path-assignments' },
        ],
    },
});
</script>

<template>
    <Head title="Path assignments" />
    <div class="flex w-full flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    Path assignments
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Tetapkan learning path secara manual atau otomatis. Course
                    terbit dalam path akan ikut tersedia untuk employee.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" @click="openRuleForm"
                    ><Plus /> Aturan otomatis</Button
                ><Button @click="openForm"><Plus /> Tetapkan path</Button>
            </div>
        </header>
        <section
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div v-if="assignments.data.length" class="divide-y divide-border">
                <article
                    v-for="item in assignments.data"
                    :key="item.id"
                    class="px-5 py-4"
                >
                    <p class="font-medium text-foreground">
                        {{ item.path?.name }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ item.user?.name
                        }}<span v-if="item.user?.employee_id">
                            - {{ item.user.employee_id }}</span
                        >
                    </p>
                </article>
            </div>
            <p
                v-else
                class="px-5 py-10 text-center text-sm text-muted-foreground"
            >
                Belum ada path assignment.
            </p>
        </section>
        <section
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div class="border-b border-border px-5 py-4">
                <h2 class="font-semibold text-foreground">
                    Aturan assignment otomatis
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Berlaku saat employee aktif dibuat, diperbarui, atau diimpor
                    dengan kombinasi organisasi yang sama.
                </p>
            </div>
            <div v-if="rules.length" class="divide-y divide-border">
                <article v-for="rule in rules" :key="rule.id" class="px-5 py-4">
                    <p class="font-medium text-foreground">
                        {{ rule.path?.name }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ rule.division?.name }} - {{ rule.position?.name }} -
                        {{ rule.branch?.name }}
                    </p>
                </article>
            </div>
            <p
                v-else
                class="px-5 py-10 text-center text-sm text-muted-foreground"
            >
                Belum ada aturan otomatis.
            </p>
        </section>
    </div>
    <Dialog v-model:open="dialogOpen"
        ><DialogContent class="sm:max-w-md"
            ><DialogHeader
                ><DialogTitle>Tetapkan learning path</DialogTitle
                ><DialogDescription
                    >Path membantu employee melihat urutan pembelajaran yang
                    direkomendasikan.</DialogDescription
                ></DialogHeader
            >
            <form class="grid gap-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="path-user">Employee</Label
                    ><AssigneePicker id="path-user" v-model="form.user_id" />
                </div>
                <div class="grid gap-2">
                    <Label for="path">Learning path</Label
                    ><select
                        id="path"
                        v-model="form.learning_path_id"
                        required
                        class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">Pilih learning path</option>
                        <option
                            v-for="path in paths"
                            :key="path.id"
                            :value="path.id"
                        >
                            {{ path.name }}
                        </option>
                    </select>
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
                            form.processing ? 'Menyimpan...' : 'Tetapkan path'
                        }}</Button
                    ></DialogFooter
                >
            </form></DialogContent
        ></Dialog
    >
    <Dialog v-model:open="ruleDialogOpen"
        ><DialogContent class="sm:max-w-md"
            ><DialogHeader
                ><DialogTitle>Aturan assignment otomatis</DialogTitle
                ><DialogDescription
                    >Employee harus cocok pada divisi, jabatan, dan cabang untuk
                    menerima path ini.</DialogDescription
                ></DialogHeader
            >
            <form class="grid gap-4" @submit.prevent="submitRule">
                <div class="grid gap-2">
                    <Label for="rule-path">Learning path</Label
                    ><select
                        id="rule-path"
                        v-model="ruleForm.learning_path_id"
                        required
                        class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">Pilih learning path</option>
                        <option
                            v-for="path in paths"
                            :key="path.id"
                            :value="path.id"
                        >
                            {{ path.name }}
                        </option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label for="rule-division">Divisi</Label
                    ><select
                        id="rule-division"
                        v-model="ruleForm.division_id"
                        required
                        class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"
                        @change="ruleForm.position_id = ''"
                    >
                        <option value="">Pilih divisi</option>
                        <option
                            v-for="division in divisions"
                            :key="division.id"
                            :value="division.id"
                        >
                            {{ division.name }}
                        </option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label for="rule-position">Jabatan</Label
                    ><select
                        id="rule-position"
                        v-model="ruleForm.position_id"
                        required
                        :disabled="!ruleForm.division_id"
                        class="h-10 rounded-md border border-input bg-transparent px-3 text-sm disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <option value="">Pilih jabatan</option>
                        <option
                            v-for="position in availablePositions"
                            :key="position.id"
                            :value="position.id"
                        >
                            {{ position.name }}
                        </option>
                    </select>
                </div>
                <div class="grid gap-2">
                    <Label for="rule-branch">Cabang</Label
                    ><select
                        id="rule-branch"
                        v-model="ruleForm.branch_id"
                        required
                        class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">Pilih cabang</option>
                        <option
                            v-for="branch in branches"
                            :key="branch.id"
                            :value="branch.id"
                        >
                            {{ branch.name }}
                        </option>
                    </select>
                </div>
                <DialogFooter
                    ><Button
                        type="button"
                        variant="outline"
                        @click="ruleDialogOpen = false"
                        >Batal</Button
                    ><Button type="submit" :disabled="ruleForm.processing">{{
                        ruleForm.processing ? 'Menyimpan...' : 'Simpan aturan'
                    }}</Button></DialogFooter
                >
            </form></DialogContent
        ></Dialog
    >
</template>
