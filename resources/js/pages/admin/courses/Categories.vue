<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { FolderPlus, Pencil, Plus } from '@lucide/vue';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import InputError from '@/components/InputError.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
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

type Category = {
    id: number;
    name: string;
    is_active: boolean;
    courses_count: number;
};
type EditableCategory = Omit<Category, 'courses_count'>;

const props = defineProps<{
    categories: {
        data: Category[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    record: EditableCategory | null;
}>();
const record = ref<EditableCategory | null>(props.record);
const dialogOpen = ref(Boolean(props.record));
const form = useForm({
    name: props.record?.name ?? '',
    is_active: props.record?.is_active ?? true,
});

function openForm(category: EditableCategory | null = null): void {
    record.value = category;
    form.defaults({
        name: category?.name ?? '',
        is_active: category?.is_active ?? true,
    }).reset();
    form.clearErrors();
    dialogOpen.value = true;
}

function submit(): void {
    const options = {
        onSuccess: () => {
            dialogOpen.value = false;
        },
    };
    if (record.value) {
        form.put(`/course-categories/${record.value.id}`, options);
        return;
    }

    form.post('/course-categories', options);
}
</script>

<template>
    <Head title="Kategori course" />
    <div class="app-page">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-2xl">
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    Kategori course
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Kelompokkan materi agar katalog mudah ditemukan dan
                    dikelola.
                </p>
            </div>
            <div class="flex gap-3">
                <Button variant="outline" as-child
                    ><Link href="/courses">Kembali ke courses</Link></Button
                ><Button @click="openForm()"><Plus /> Tambah kategori</Button>
            </div>
        </header>
        <section
            class="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border"
        >
            <div v-if="categories.data.length" class="divide-y divide-border">
                <article
                    v-for="category in categories.data"
                    :key="category.id"
                    class="flex items-center justify-between gap-4 px-5 py-4"
                >
                    <div>
                        <h2 class="font-medium text-foreground">
                            {{ category.name }}
                        </h2>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ category.courses_count }} course
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <StatusBadge
                            :status="category.is_active ? 'active' : 'inactive'"
                        /><Button
                            variant="ghost"
                            size="sm"
                            @click="openForm(category)"
                            ><Pencil /> Edit</Button
                        >
                    </div>
                </article>
            </div>
            <EmptyState
                v-else
                :icon="FolderPlus"
                title="Belum ada kategori"
                description="Buat kategori pertama sebelum menambahkan course."
            />
            <Pagination
                v-if="categories.total"
                :from="categories.from"
                :to="categories.to"
                :total="categories.total"
                :previous="categories.prev_page_url"
                :next="categories.next_page_url"
            />
        </section>
    </div>
    <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-md"
            ><DialogHeader
                ><DialogTitle>{{
                    record ? 'Ubah kategori' : 'Tambah kategori'
                }}</DialogTitle
                ><DialogDescription
                    >Nama kategori dipakai untuk membantu employee menemukan
                    materi.</DialogDescription
                ></DialogHeader
            >
            <form class="grid gap-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="category-name">Nama kategori</Label
                    ><Input
                        id="category-name"
                        v-model="form.name"
                        required
                        maxlength="120"
                    /><InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="category-status">Status</Label
                    ><select
                        id="category-status"
                        v-model="form.is_active"
                        class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option :value="true">Aktif</option>
                        <option :value="false">Tidak aktif</option>
                    </select>
                </div>
                <DialogFooter
                    ><Button
                        type="button"
                        variant="outline"
                        @click="dialogOpen = false"
                        >Batal</Button
                    ><Button type="submit" :disabled="form.processing">{{
                        form.processing ? 'Menyimpan…' : 'Simpan'
                    }}</Button></DialogFooter
                >
            </form>
        </DialogContent>
    </Dialog>
</template>
