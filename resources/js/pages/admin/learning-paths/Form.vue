<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import FormErrorSummary from '@/components/FormErrorSummary.vue';
import InputError from '@/components/InputError.vue';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type LearningPath = {
    id: number;
    name: string;
    description: string | null;
    status: 'draft' | 'published' | 'archived';
};
const props = defineProps<{ title: string; learningPath?: LearningPath }>();
const form = useForm({
    name: props.learningPath?.name ?? '',
    description: props.learningPath?.description ?? '',
    status: props.learningPath?.status ?? 'draft',
});
const leaveGuard = useUnsavedChangesGuard(() => form.isDirty);
function submit(): void {
    if (props.learningPath) {
        form.put(`/learning-paths/${props.learningPath.id}`, {
            onSuccess: () => form.defaults(),
        });
        return;
    }

    form.post('/learning-paths', { onSuccess: () => form.defaults() });
}
</script>

<template>
    <Head :title="title" />
    <div class="app-page mx-auto max-w-3xl gap-8">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-2xl">
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    {{ title }}
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Tentukan nama, penjelasan, dan kesiapan publikasi learning
                    path.
                </p>
            </div>
            <Button variant="outline" as-child
                ><Link href="/learning-paths"
                    >Kembali ke learning paths</Link
                ></Button
            >
        </div>
        <form class="grid gap-6" @submit.prevent="submit">
            <FormErrorSummary :errors="form.errors" />
            <section
                class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border sm:p-6"
            >
                <h2 class="font-semibold text-foreground">
                    Informasi learning path
                </h2>
                <div class="mt-5 grid gap-5">
                    <div class="grid gap-2">
                        <Label for="name">Nama</Label
                        ><Input
                            id="name"
                            v-model="form.name"
                            required
                            maxlength="255"
                        /><InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="description">Deskripsi</Label
                        ><textarea
                            id="description"
                            v-model="form.description"
                            rows="5"
                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                        /><InputError :message="form.errors.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Status</Label
                        ><select
                            v-if="learningPath"
                            id="status"
                            v-model="form.status"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                        >
                            <option value="draft">Draf</option>
                            <option value="published">Dipublikasikan</option>
                            <option value="archived">Diarsipkan</option>
                        </select>
                        <p v-else class="text-sm text-muted-foreground">
                            Learning path baru dibuat sebagai draf. Tambahkan
                            course yang sudah dipublikasikan, kemudian ubah
                            statusnya saat siap digunakan.
                        </p>
                        <p
                            v-if="learningPath"
                            class="text-xs text-muted-foreground"
                        >
                            Learning path hanya dapat dipublikasikan setelah
                            memiliki minimal satu course yang sudah
                            dipublikasikan.
                        </p>
                        <InputError :message="form.errors.status" />
                    </div>
                </div>
            </section>
            <div
                class="flex flex-col-reverse justify-end gap-3 border-t border-border pt-5 sm:flex-row"
            >
                <Button variant="outline" as-child
                    ><Link href="/learning-paths">Batal</Link></Button
                ><Button type="submit" :disabled="form.processing">{{
                    form.processing ? 'Menyimpanâ€¦' : 'Simpan learning path'
                }}</Button>
            </div>
        </form>
        <ConfirmActionDialog
            :open="leaveGuard.open.value"
            title="Keluar tanpa menyimpan?"
            description="Perubahan learning path yang belum disimpan akan hilang."
            confirm-label="Keluar tanpa menyimpan"
            destructive
            @update:open="!$event && leaveGuard.reset()"
            @confirm="leaveGuard.leaveWithoutSaving"
            @cancel="leaveGuard.reset"
        />
    </div>
</template>
