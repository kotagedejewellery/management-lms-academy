<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
    title: string;
    course?: Course;
    categories: Option[];
    divisions: Option[];
    positions: Option[];
    prerequisites: Option[];
}>();
const form = useForm({
    category_id: props.course?.category?.id?.toString() ?? '',
    division_id: props.course?.division?.id?.toString() ?? '',
    position_id: props.course?.position?.id?.toString() ?? '',
    prerequisite_course_id: props.course?.prerequisite?.id?.toString() ?? '',
    code: props.course?.code ?? '',
    title: props.course?.title ?? '',
    description: props.course?.description ?? '',
    estimated_minutes: props.course?.estimated_minutes?.toString() ?? '',
    is_mandatory: props.course?.is_mandatory ?? true,
    passing_grade: props.course?.passing_grade?.toString() ?? '70',
    status: props.course?.status ?? 'draft',
});
function submit(): void {
    if (props.course) {
        form.put(`/courses/${props.course.id}`);
        return;
    }

    form.post('/courses');
}
</script>

<template>
    <Head :title="title" />
    <div class="flex w-full flex-col gap-8 px-4 py-6 sm:px-6 lg:px-8">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-2xl">
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    {{ title }}
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Atur metadata, audience, urutan belajar, dan status
                    publikasi course.
                </p>
            </div>
            <Button variant="outline" as-child
                ><Link href="/courses">Kembali ke courses</Link></Button
            >
        </header>

        <form class="grid gap-8" @submit.prevent="submit">
            <input v-model="form.passing_grade" type="hidden" />
            <section class="rounded-xl border border-border bg-card p-5 sm:p-6">
                <h2 class="font-semibold text-foreground">Informasi course</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="category_id">Kategori</Label
                        ><select
                            id="category_id"
                            v-model="form.category_id"
                            required
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                        >
                            <option value="">Pilih kategori</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option></select
                        ><InputError :message="form.errors.category_id" /><Link
                            href="/course-categories"
                            class="text-xs underline underline-offset-4"
                            >Kelola kategori</Link
                        >
                    </div>
                    <div class="grid gap-2">
                        <Label for="code">Kode course</Label
                        ><Input
                            id="code"
                            v-model="form.code"
                            required
                            maxlength="32"
                            placeholder="Contoh: ONB-001"
                        /><InputError :message="form.errors.code" />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="title">Judul</Label
                        ><Input
                            id="title"
                            v-model="form.title"
                            required
                            maxlength="255"
                        /><InputError :message="form.errors.title" />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="description">Deskripsi</Label
                        ><textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                        /><InputError :message="form.errors.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="estimated_minutes"
                            >Estimasi durasi (menit)</Label
                        ><Input
                            id="estimated_minutes"
                            v-model="form.estimated_minutes"
                            type="number"
                            min="1"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="is_mandatory">Jenis</Label
                        ><select
                            id="is_mandatory"
                            v-model="form.is_mandatory"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                        >
                            <option :value="true">Wajib</option>
                            <option :value="false">Opsional</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5 sm:p-6">
                <h2 class="font-semibold text-foreground">
                    Audience dan penyelesaian
                </h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="division_id">Divisi</Label
                        ><select
                            id="division_id"
                            v-model="form.division_id"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                        >
                            <option value="">Semua divisi</option>
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
                        <Label for="position_id">Jabatan</Label
                        ><select
                            id="position_id"
                            v-model="form.position_id"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                        >
                            <option value="">Semua jabatan</option>
                            <option
                                v-for="position in positions"
                                :key="position.id"
                                :value="position.id"
                            >
                                {{ position.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="prerequisite_course_id">Prasyarat</Label
                        ><select
                            id="prerequisite_course_id"
                            v-model="form.prerequisite_course_id"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                        >
                            <option value="">Tanpa prasyarat</option>
                            <option
                                v-for="courseOption in prerequisites"
                                :key="courseOption.id"
                                :value="courseOption.id"
                            >
                                {{ courseOption.title }}
                            </option></select
                        ><InputError
                            :message="form.errors.prerequisite_course_id"
                        />
                    </div>
                    <div
                        class="rounded-lg bg-muted/50 p-4 text-sm leading-6 text-muted-foreground"
                    >
                        <p class="font-medium text-foreground">
                            Aturan kelulusan
                        </p>
                        <p class="mt-1">
                            Course dengan Post-Test selesai setelah lulus
                            Post-Test. Course tanpa Post-Test selesai setelah
                            seluruh lesson wajib.
                        </p>
                        <Link
                            v-if="course"
                            :href="`/courses/${course.id}/assessments`"
                            class="mt-2 inline-flex font-medium text-primary underline underline-offset-4"
                            >Atur assessment dan passing grade</Link
                        >
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Status</Label
                        ><select
                            id="status"
                            v-model="form.status"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                        >
                            <option value="draft">Draf</option>
                            <option value="published">Dipublikasikan</option>
                            <option value="archived">Diarsipkan</option>
                        </select>
                        <p class="text-xs leading-5 text-muted-foreground">
                            Course evaluatif memerlukan Post-Test yang telah
                            memiliki soal dan jawaban benar sebelum
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
                    ><Link href="/courses">Batal</Link></Button
                ><Button type="submit" :disabled="form.processing">{{
                    form.processing ? 'Menyimpan…' : 'Simpan course'
                }}</Button>
            </div>
        </form>
    </div>
</template>
