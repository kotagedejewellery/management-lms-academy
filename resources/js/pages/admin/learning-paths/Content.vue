<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ListOrdered, Plus, Trash2 } from '@lucide/vue';
import { reactive, ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import InputError from '@/components/InputError.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Course = { id: number; code: string; title: string; status?: 'published' };
type PathCourse = { id: number; sequence: number; course: Course };
const props = defineProps<{
    learningPath: {
        id: number;
        name: string;
        description: string | null;
        status: 'draft' | 'published' | 'archived';
        courses: PathCourse[];
    };
    courses: Course[];
}>();
const form = useForm({
    course_id: '',
    sequence: String(props.learningPath.courses.length + 1),
});
const sequences = reactive<Record<number, string>>(
    Object.fromEntries(
        props.learningPath.courses.map((pathCourse) => [
            pathCourse.id,
            String(pathCourse.sequence),
        ]),
    ),
);
const pathCourseToDelete = ref<PathCourse | null>(null);

function addCourse(): void {
    form.post(`/learning-paths/${props.learningPath.id}/courses`, {
        onSuccess: () => form.reset('course_id'),
    });
}
function updateSequence(pathCourse: PathCourse): void {
    router.put(
        `/learning-paths/${props.learningPath.id}/courses/${pathCourse.id}`,
        { course_id: pathCourse.course.id, sequence: sequences[pathCourse.id] },
    );
}
function deleteCourse(): void {
    if (pathCourseToDelete.value)
        router.delete(
            `/learning-paths/${props.learningPath.id}/courses/${pathCourseToDelete.value.id}`,
            { onFinish: () => (pathCourseToDelete.value = null) },
        );
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Learning paths', href: '/learning-paths' },
            { title: 'Susun course', href: '/learning-paths' },
        ],
    },
});
</script>

<template>
    <Head :title="`Susun course — ${learningPath.name}`" />
    <div class="flex w-full flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-2xl">
                <div class="flex flex-wrap items-center gap-3">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-foreground"
                    >
                        {{ learningPath.name }}
                    </h1>
                    <StatusBadge :status="learningPath.status" />
                </div>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Susun urutan course yang akan diikuti learner. Hanya course
                    berstatus dipublikasikan yang dapat ditambahkan.
                </p>
            </div>
            <Button variant="outline" as-child
                ><Link :href="`/learning-paths/${learningPath.id}/edit`"
                    >Pengaturan path</Link
                ></Button
            >
        </header>
        <div
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-start"
        >
            <section
                class="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border"
            >
                <div
                    v-if="learningPath.courses.length"
                    class="divide-y divide-border"
                >
                    <article
                        v-for="pathCourse in learningPath.courses"
                        :key="pathCourse.id"
                        class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Course {{ pathCourse.sequence }}
                            </p>
                            <h2
                                class="mt-1 truncate font-semibold text-foreground"
                            >
                                {{ pathCourse.course.title }}
                            </h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                <span class="tabular-nums">{{
                                    pathCourse.course.code
                                }}</span>
                                · Dipublikasikan
                            </p>
                        </div>
                        <div class="flex items-end gap-2">
                            <form
                                class="flex items-end gap-2"
                                @submit.prevent="updateSequence(pathCourse)"
                            >
                                <div class="grid gap-2">
                                    <Label
                                        :for="`sequence-${pathCourse.id}`"
                                        class="text-xs"
                                        >Urutan</Label
                                    ><Input
                                        :id="`sequence-${pathCourse.id}`"
                                        v-model="sequences[pathCourse.id]"
                                        type="number"
                                        min="1"
                                        class="w-20"
                                        required
                                    />
                                </div>
                                <Button
                                    type="submit"
                                    variant="outline"
                                    size="sm"
                                    >Simpan</Button
                                >
                            </form>
                            <Button
                                v-if="learningPath.status === 'draft'"
                                type="button"
                                variant="ghost"
                                size="icon"
                                :aria-label="`Hapus ${pathCourse.course.title} dari path`"
                                @click="pathCourseToDelete = pathCourse"
                                ><Trash2 class="size-4"
                            /></Button>
                        </div>
                    </article>
                </div>
                <EmptyState
                    v-else
                    :icon="ListOrdered"
                    title="Belum ada course"
                    description="Tambahkan course yang sudah dipublikasikan melalui formulir di samping."
                />
            </section>
            <section
                class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border"
            >
                <h2 class="font-semibold text-foreground">Tambah course</h2>
                <form class="mt-5 grid gap-4" @submit.prevent="addCourse">
                    <div class="grid gap-2">
                        <Label for="course-id">Course</Label
                        ><select
                            id="course-id"
                            v-model="form.course_id"
                            required
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                        >
                            <option value="">Pilih course</option>
                            <option
                                v-for="course in courses"
                                :key="course.id"
                                :value="course.id"
                            >
                                {{ course.code }} — {{ course.title }}
                            </option></select
                        ><InputError :message="form.errors.course_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="sequence">Urutan</Label
                        ><Input
                            id="sequence"
                            v-model="form.sequence"
                            type="number"
                            min="1"
                            required
                        /><InputError :message="form.errors.sequence" />
                    </div>
                    <Button type="submit" :disabled="form.processing"
                        ><Plus /> Tambah course</Button
                    >
                </form>
            </section>
        </div>
        <ConfirmActionDialog
            :open="Boolean(pathCourseToDelete)"
            title="Hapus course dari path?"
            :description="`${pathCourseToDelete?.course.title ?? 'Course'} akan dihapus dari learning path ini.`"
            confirm-label="Hapus course"
            destructive
            @update:open="!$event && (pathCourseToDelete = null)"
            @confirm="deleteCourse"
        />
    </div>
</template>
