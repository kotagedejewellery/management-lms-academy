<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { BookOpenCheck, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import InputError from '@/components/InputError.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Resource = {
    id: number;
    original_name: string;
    mime_type: string;
    size: number;
};
type Lesson = {
    id: number;
    title: string;
    type: 'article' | 'video' | 'pdf';
    resources: Resource[];
    is_required: boolean;
    sequence: number;
};
type Module = {
    id: number;
    title: string;
    sequence: number;
    lessons: Lesson[];
};
const props = defineProps<{
    course: {
        id: number;
        code: string;
        title: string;
        status: 'draft' | 'published' | 'archived';
        modules: Module[];
    };
}>();
const moduleForm = useForm({
    title: '',
    sequence: String(props.course.modules.length + 1),
});
const lessonForm = useForm({
    module_id: props.course.modules[0]?.id?.toString() ?? '',
    title: '',
    type: 'article',
    content: '',
    transcript: '',
    resource_url: '',
    resource: null as File | null,
    is_required: true,
    sequence: '1',
});
const deletion = ref<{ label: string; url: string } | null>(null);
const deleting = ref(false);
const leaveGuard = useUnsavedChangesGuard(
    () => moduleForm.isDirty || lessonForm.isDirty,
);

function addModule(): void {
    moduleForm.post(`/courses/${props.course.id}/modules`, {
        onSuccess: () => moduleForm.reset('title'),
    });
}
function addLesson(): void {
    lessonForm.post(
        `/courses/${props.course.id}/modules/${lessonForm.module_id}/lessons`,
        {
            onSuccess: () =>
                lessonForm.reset(
                    'title',
                    'content',
                    'transcript',
                    'resource_url',
                    'resource',
                ),
        },
    );
}
function chooseResource(event: Event): void {
    lessonForm.resource = (event.target as HTMLInputElement).files?.[0] ?? null;
}
function confirmDelete(): void {
    if (deletion.value && !deleting.value)
        router.delete(deletion.value.url, {
            onStart: () => (deleting.value = true),
            onFinish: () => {
                deleting.value = false;
                deletion.value = null;
            },
        });
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Courses', href: '/courses' },
            { title: 'Materi', href: '/courses' },
        ],
    },
});
</script>

<template>
    <Head :title="`Materi — ${course.title}`" />
    <div class="app-page">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-2xl">
                <div class="flex items-center gap-3">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-foreground"
                    >
                        {{ course.title }}
                    </h1>
                    <StatusBadge :status="course.status" />
                </div>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    {{ course.code }} · Susun modul dan lesson dalam urutan
                    pembelajaran yang akan diterima learner.
                </p>
            </div>
            <div class="flex gap-3">
                <Button variant="outline" as-child
                    ><Link :href="`/courses/${course.id}/assessments`"
                        >Assessment</Link
                    ></Button
                ><Button variant="outline" as-child
                    ><Link :href="`/courses/${course.id}/edit`"
                        >Pengaturan course</Link
                    ></Button
                >
            </div>
        </header>
        <div
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-start"
        >
            <section
                class="overflow-hidden rounded-xl bg-card shadow-sm ring-1 ring-border"
            >
                <div
                    v-if="course.modules.length"
                    class="divide-y divide-border"
                >
                    <article
                        v-for="module in course.modules"
                        :key="module.id"
                        class="px-5 py-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="font-semibold text-foreground">
                                    {{ module.sequence }}. {{ module.title }}
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ module.lessons.length }} lesson
                                </p>
                            </div>
                            <Button
                                v-if="course.status === 'draft'"
                                type="button"
                                variant="ghost"
                                size="icon"
                                :aria-label="`Hapus modul ${module.title}`"
                                @click="
                                    deletion = {
                                        label: `modul ${module.title}`,
                                        url: `/courses/${course.id}/modules/${module.id}`,
                                    }
                                "
                                ><Trash2 class="size-4"
                            /></Button>
                        </div>
                        <ol
                            v-if="module.lessons.length"
                            class="mt-4 divide-y divide-border rounded-lg border border-border"
                        >
                            <li
                                v-for="lesson in module.lessons"
                                :key="lesson.id"
                                class="flex items-center justify-between gap-3 px-4 py-3"
                            >
                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-sm font-medium text-foreground"
                                    >
                                        {{ lesson.sequence }}.
                                        {{ lesson.title }}
                                    </h3>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{
                                            lesson.type === 'article'
                                                ? 'Artikel'
                                                : lesson.type === 'video'
                                                  ? 'Video'
                                                  : 'PDF'
                                        }}
                                        ·
                                        {{
                                            lesson.is_required
                                                ? 'Wajib'
                                                : 'Opsional'
                                        }}
                                    </p>
                                    <ul
                                        v-if="lesson.resources.length"
                                        class="mt-2 space-y-1"
                                    >
                                        <li
                                            v-for="resource in lesson.resources"
                                            :key="resource.id"
                                            class="flex items-center gap-2 text-xs text-muted-foreground"
                                        >
                                            <span class="truncate">{{
                                                resource.original_name
                                            }}</span
                                            ><button
                                                v-if="course.status === 'draft'"
                                                type="button"
                                                class="text-destructive hover:underline"
                                                @click="
                                                    deletion = {
                                                        label: `file ${resource.original_name}`,
                                                        url: `/courses/${course.id}/modules/${module.id}/lessons/${lesson.id}/resources/${resource.id}`,
                                                    }
                                                "
                                            >
                                                Hapus
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <Button
                                    v-if="course.status === 'draft'"
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    :aria-label="`Hapus lesson ${lesson.title}`"
                                    @click="
                                        deletion = {
                                            label: `lesson ${lesson.title}`,
                                            url: `/courses/${course.id}/modules/${module.id}/lessons/${lesson.id}`,
                                        }
                                    "
                                    ><Trash2 class="size-4"
                                /></Button>
                            </li>
                        </ol>
                        <p v-else class="mt-4 text-sm text-muted-foreground">
                            Belum ada lesson pada modul ini.
                        </p>
                    </article>
                </div>
                <EmptyState
                    v-else
                    :icon="BookOpenCheck"
                    title="Belum ada modul"
                    description="Tambahkan modul pertama melalui formulir di samping."
                />
            </section>
            <div class="grid gap-6">
                <section
                    class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border"
                >
                    <h2 class="font-semibold text-foreground">Tambah modul</h2>
                    <form class="mt-5 grid gap-4" @submit.prevent="addModule">
                        <div class="grid gap-2">
                            <Label for="module-title">Judul modul</Label
                            ><Input
                                id="module-title"
                                v-model="moduleForm.title"
                                required
                            /><InputError :message="moduleForm.errors.title" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="module-sequence">Urutan</Label
                            ><Input
                                id="module-sequence"
                                v-model="moduleForm.sequence"
                                type="number"
                                min="1"
                                required
                            /><InputError
                                :message="moduleForm.errors.sequence"
                            />
                        </div>
                        <Button type="submit" :disabled="moduleForm.processing"
                            ><Plus /> Tambah modul</Button
                        >
                    </form>
                </section>
                <section
                    class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border"
                >
                    <h2 class="font-semibold text-foreground">Tambah lesson</h2>
                    <form class="mt-5 grid gap-4" @submit.prevent="addLesson">
                        <div class="grid gap-2">
                            <Label for="lesson-module">Modul</Label
                            ><select
                                id="lesson-module"
                                v-model="lessonForm.module_id"
                                required
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option value="">Pilih modul</option>
                                <option
                                    v-for="module in course.modules"
                                    :key="module.id"
                                    :value="module.id"
                                >
                                    {{ module.sequence }}. {{ module.title }}
                                </option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <Label for="lesson-title">Judul lesson</Label
                            ><Input
                                id="lesson-title"
                                v-model="lessonForm.title"
                                required
                            /><InputError :message="lessonForm.errors.title" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="lesson-type">Tipe materi</Label
                            ><select
                                id="lesson-type"
                                v-model="lessonForm.type"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option value="article">Artikel</option>
                                <option value="video">Video</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                        <div
                            v-if="lessonForm.type === 'article'"
                            class="grid gap-2"
                        >
                            <Label for="lesson-content">Isi artikel</Label
                            ><textarea
                                id="lesson-content"
                                v-model="lessonForm.content"
                                rows="4"
                                class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            /><InputError
                                :message="lessonForm.errors.content"
                            />
                        </div>
                        <template v-else
                            ><div class="grid gap-2">
                                <Label for="resource-url"
                                    >URL resource (opsional)</Label
                                ><Input
                                    id="resource-url"
                                    v-model="lessonForm.resource_url"
                                    type="url"
                                    placeholder="https://"
                                /><InputError
                                    :message="lessonForm.errors.resource_url"
                                />
                            </div>
                            <div
                                v-if="lessonForm.type === 'video'"
                                class="grid gap-2"
                            >
                                <Label for="lesson-transcript"
                                    >Transkrip video (opsional)</Label
                                ><textarea
                                    id="lesson-transcript"
                                    v-model="lessonForm.transcript"
                                    rows="4"
                                    class="rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                                />
                                <p
                                    class="text-xs leading-5 text-muted-foreground"
                                >
                                    Teks ini dapat dibuka learner sebagai
                                    alternatif aksesibilitas video.
                                </p>
                                <InputError
                                    :message="lessonForm.errors.transcript"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="lesson-resource"
                                    >Upload resource privat</Label
                                ><Input
                                    id="lesson-resource"
                                    type="file"
                                    accept="application/pdf,video/mp4,video/webm"
                                    @change="chooseResource"
                                />
                                <p
                                    class="text-xs leading-5 text-muted-foreground"
                                >
                                    PDF, MP4, atau WebM hingga 50 MB. File
                                    diakses melalui izin aplikasi.
                                </p>
                                <InputError
                                    :message="lessonForm.errors.resource"
                                /></div
                        ></template>
                        <div class="grid gap-2">
                            <Label for="lesson-sequence">Urutan</Label
                            ><Input
                                id="lesson-sequence"
                                v-model="lessonForm.sequence"
                                type="number"
                                min="1"
                                required
                            /><InputError
                                :message="lessonForm.errors.sequence"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="lesson-required">Kewajiban</Label
                            ><select
                                id="lesson-required"
                                v-model="lessonForm.is_required"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option :value="true">Wajib</option>
                                <option :value="false">Opsional</option>
                            </select>
                        </div>
                        <Button type="submit" :disabled="lessonForm.processing"
                            ><Plus /> Tambah lesson</Button
                        >
                    </form>
                </section>
            </div>
        </div>
        <ConfirmActionDialog
            :open="Boolean(deletion)"
            title="Hapus konten?"
            :description="`Tindakan ini menghapus ${deletion?.label ?? 'konten'} secara permanen.`"
            confirm-label="Hapus"
            destructive
            :processing="deleting"
            @update:open="!$event && (deletion = null)"
            @confirm="confirmDelete"
        />
        <ConfirmActionDialog
            :open="leaveGuard.open.value"
            title="Keluar tanpa menyimpan?"
            description="Perubahan modul atau lesson yang belum disimpan akan hilang."
            confirm-label="Keluar tanpa menyimpan"
            destructive
            @update:open="!$event && leaveGuard.reset()"
            @confirm="leaveGuard.leaveWithoutSaving"
            @cancel="leaveGuard.reset"
        />
    </div>
</template>
