<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { FileUp } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import { Button } from '@/components/ui/button';

type Import = {
    id: number;
    original_name: string;
    status: string;
    total_rows: number;
    imported_rows: number;
    skipped_rows: number;
    errors: { line: number; message: string }[];
    completed_at: string | null;
    created_at: string;
};
defineProps<{
    imports: {
        data: Import[];
        next_page_url: string | null;
        prev_page_url: string | null;
    };
}>();
const form = useForm<{ file: File | null }>({ file: null });
const importDialogOpen = ref(false);
function submit(): void {
    form.post('/user-imports', {
        forceFormData: true,
        onSuccess: () => form.reset(),
    });
}
function choose(event: Event): void {
    form.file = (event.target as HTMLInputElement).files?.[0] ?? null;
}
function requestSubmit(): void {
    if (!form.processing && form.file) importDialogOpen.value = true;
}
function date(value: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: '/users' },
            { title: 'Impor CSV', href: '/user-imports' },
        ],
    },
});
</script>
<template>
    <Head title="Impor employee CSV" />
    <div class="flex w-full flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <header class="max-w-3xl">
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                Impor employee CSV
            </h1>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                Baris dengan Employee ID atau email yang sudah ada akan dilewati
                dan dilaporkan. Password awal diambil dari konfigurasi server,
                bukan dari file.
            </p>
        </header>
        <section class="rounded-xl border border-border bg-card p-5 sm:p-6">
            <h2 class="font-semibold text-foreground">Unggah file</h2>
            <p class="mt-1 text-sm leading-6 text-muted-foreground">
                Header wajib:
                <span class="font-medium text-foreground"
                    >employee_id, name, email</span
                >. Header opsional: phone, division_code, position_name,
                branch_code, manager_employee_id, status.
            </p>
            <form
                class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end"
                @submit.prevent="requestSubmit"
            >
                <label
                    class="grid min-w-0 flex-1 gap-2 text-sm font-medium text-foreground"
                    >File CSV<input
                        type="file"
                        accept=".csv,text/csv"
                        required
                        class="block w-full rounded-md border border-input bg-transparent p-2 text-sm file:mr-3 file:border-0 file:bg-muted file:px-2 file:py-1 file:text-sm file:font-medium"
                        @change="choose" /></label
                ><Button type="submit" :disabled="form.processing || !form.file"
                    ><FileUp />{{
                        form.processing ? 'Mengunggah…' : 'Mulai impor'
                    }}</Button
                >
            </form>
            <p v-if="form.errors.file" class="mt-3 text-sm text-destructive">
                {{ form.errors.file }}
            </p>
        </section>
        <section
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div v-if="imports.data.length" class="divide-y divide-border">
                <article
                    v-for="item in imports.data"
                    :key="item.id"
                    class="px-5 py-5 sm:px-6"
                >
                    <div
                        class="flex flex-col justify-between gap-2 sm:flex-row"
                    >
                        <div>
                            <h2 class="font-medium text-foreground">
                                {{ item.original_name }}
                            </h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{
                                    item.status === 'completed'
                                        ? `${item.imported_rows} diimpor · ${item.skipped_rows} dilewati dari ${item.total_rows} baris`
                                        : item.status === 'failed'
                                          ? 'Impor gagal'
                                          : 'Sedang diproses'
                                }}
                            </p>
                        </div>
                        <time
                            class="text-sm text-muted-foreground"
                            :datetime="item.created_at"
                            >{{
                                date(item.completed_at ?? item.created_at)
                            }}</time
                        >
                    </div>
                    <ul
                        v-if="item.errors.length"
                        class="mt-3 grid gap-1 border-t border-border pt-3 text-sm text-muted-foreground"
                    >
                        <li
                            v-for="error in item.errors"
                            :key="`${item.id}-${error.line}-${error.message}`"
                        >
                            Baris {{ error.line || 'file' }}:
                            {{ error.message }}
                        </li>
                    </ul>
                </article>
            </div>
            <div v-else class="px-5 py-14 text-center sm:px-6">
                <FileUp
                    class="mx-auto size-5 text-muted-foreground"
                    aria-hidden="true"
                />
                <p class="mt-3 font-medium text-foreground">Belum ada impor</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Unggah CSV pertama untuk menambahkan employee secara massal.
                </p>
            </div>
            <nav
                v-if="imports.next_page_url || imports.prev_page_url"
                class="flex justify-between border-t border-border px-5 py-3 text-sm"
            >
                <Link
                    v-if="imports.prev_page_url"
                    :href="imports.prev_page_url"
                    class="text-foreground underline underline-offset-4"
                    >Sebelumnya</Link
                ><span v-else /><Link
                    v-if="imports.next_page_url"
                    :href="imports.next_page_url"
                    class="text-foreground underline underline-offset-4"
                    >Berikutnya</Link
                >
            </nav>
        </section>
        <ConfirmActionDialog
            v-model:open="importDialogOpen"
            title="Mulai impor employee?"
            :description="`File ${form.file?.name ?? 'CSV'} akan diproses di background. Baris duplikat akan dilewati dan hasilnya dapat dilihat pada daftar impor.`"
            confirm-label="Mulai impor"
            :processing="form.processing"
            @confirm="submit"
        />
    </div>
</template>
