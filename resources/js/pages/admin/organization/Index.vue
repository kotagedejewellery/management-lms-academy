<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Building2, Pencil, Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
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

type OrganizationItem = {
    id: number;
    code: string | null;
    name: string;
    division_id: number | null;
    division: { id: number; name: string } | null;
    is_active: boolean;
};
const props = defineProps<{
    type: 'divisions' | 'positions' | 'branches';
    label: string;
    items: {
        data: OrganizationItem[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    record: OrganizationItem | null;
    divisions: { id: number; name: string }[];
}>();
const record = ref<OrganizationItem | null>(props.record);
const dialogOpen = ref(Boolean(props.record));
const needsCode = computed(() => props.type !== 'positions');
const values = (item: OrganizationItem | null) => ({
    code: item?.code ?? '',
    name: item?.name ?? '',
    division_id: item?.division_id ? String(item.division_id) : '',
    is_active: item?.is_active ?? true,
});
const form = useForm(values(props.record));
const sections = [
    { type: 'divisions', label: 'Divisi' },
    { type: 'positions', label: 'Jabatan' },
    { type: 'branches', label: 'Cabang' },
] as const;
function openForm(item: OrganizationItem | null = null): void {
    record.value = item;
    form.defaults(values(item)).reset();
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
        form.put(`/organization/${props.type}/${record.value.id}`, options);
        return;
    }

    form.post(`/organization/${props.type}`, options);
}
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Organisasi', href: '/organization/divisions' }],
    },
});
</script>

<template>
    <Head :title="label" />
    <div class="app-page">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-2xl">
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    Organisasi
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Atur divisi, jabatan, dan cabang agar data karyawan serta
                    pembelajaran dapat ditetapkan secara konsisten.
                </p>
            </div>
            <Button @click="openForm()"
                ><Plus /> Tambah {{ label.toLowerCase() }}</Button
            >
        </header>
        <nav aria-label="Master organisasi" class="flex flex-wrap gap-2">
            <Button
                v-for="section in sections"
                :key="section.type"
                :variant="type === section.type ? 'default' : 'outline'"
                size="sm"
                as-child
                ><Link :href="`/organization/${section.type}`">{{
                    section.label
                }}</Link></Button
            >
        </nav>
        <section
            class="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border"
        >
            <div v-if="items.data.length" class="divide-y divide-border">
                <article
                    v-for="item in items.data"
                    :key="item.id"
                    class="flex items-center justify-between gap-4 px-5 py-4"
                >
                    <div class="min-w-0">
                        <h2 class="truncate font-medium text-foreground">
                            {{ item.name }}
                        </h2>
                        <p class="mt-1 text-xs text-muted-foreground">
                            <span v-if="item.code" class="tabular-nums"
                                >{{ item.code }} · </span
                            >{{ item.division?.name || 'Semua divisi' }}
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <StatusBadge
                            :status="item.is_active ? 'active' : 'inactive'"
                        /><Button
                            variant="ghost"
                            size="sm"
                            @click="openForm(item)"
                            ><Pencil /> Edit</Button
                        >
                    </div>
                </article>
            </div>
            <EmptyState
                v-else
                :icon="Building2"
                :title="`Belum ada ${label.toLowerCase()}`"
                description="Tambahkan data pertama untuk mulai mengelola struktur organisasi."
            /><Pagination
                v-if="items.total"
                :from="items.from"
                :to="items.to"
                :total="items.total"
                :previous="items.prev_page_url"
                :next="items.next_page_url"
            />
        </section>
    </div>
    <Dialog v-model:open="dialogOpen"
        ><DialogContent class="sm:max-w-md"
            ><DialogHeader
                ><DialogTitle>{{
                    record
                        ? `Ubah ${label.toLowerCase()}`
                        : `Tambah ${label.toLowerCase()}`
                }}</DialogTitle
                ><DialogDescription
                    >Record tidak dihapus agar riwayat organisasi pada user
                    tetap aman.</DialogDescription
                ></DialogHeader
            >
            <form class="grid gap-4" @submit.prevent="submit">
                <div v-if="needsCode" class="grid gap-2">
                    <Label for="organization-code">Kode</Label
                    ><Input
                        id="organization-code"
                        v-model="form.code"
                        required
                        maxlength="32"
                        placeholder="Contoh: OPS"
                    /><InputError :message="form.errors.code" />
                </div>
                <div class="grid gap-2">
                    <Label for="organization-name">Nama {{ label }}</Label
                    ><Input
                        id="organization-name"
                        v-model="form.name"
                        required
                        maxlength="120"
                    /><InputError :message="form.errors.name" />
                </div>
                <div v-if="type === 'positions'" class="grid gap-2">
                    <Label for="organization-division">Divisi</Label
                    ><select
                        id="organization-division"
                        v-model="form.division_id"
                        required
                        class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">Pilih divisi</option>
                        <option
                            v-for="division in divisions"
                            :key="division.id"
                            :value="division.id"
                        >
                            {{ division.name }}
                        </option></select
                    ><InputError :message="form.errors.division_id" />
                </div>
                <div class="grid gap-2">
                    <Label for="organization-status">Status</Label
                    ><select
                        id="organization-status"
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
            </form></DialogContent
        ></Dialog
    >
</template>
