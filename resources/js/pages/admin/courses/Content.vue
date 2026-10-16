<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    BookOpenCheck,
    ChevronDown,
    ChevronUp,
    Pencil,
    Plus,
    Trash2,
    X,
} from '@lucide/vue';
import { ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import FormErrorSummary from '@/components/FormErrorSummary.vue';
import InputError from '@/components/InputError.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Resource = {
    id: number;
    original_name: string;
    mime_type: string;
    size: number;
};
type Lesson = {
    id: number;
    title: string;
    type: 'article' | 'video' | 'pdf' | 'presentation';
    content: string | null;
    transcript: string | null;
    resource_url: string | null;
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
});
const lessonForm = useForm({
    module_id: props.course.modules[0]?.id?.toString() ?? '',
    title: '',
    type: 'article',
    content: '',
    transcript: '',
    resource_url: '',
    resource: null as File | null,
    replace_resource: false,
    is_required: true,
});
const deletion = ref<{ label: string; url: string } | null>(null);
const deleting = ref(false);
const editingLesson = ref<Lesson | null>(null);
const resourceSource = ref<'file' | 'url'>('file');
const resourceInputKey = ref(0);
const movingItem = ref<string | null>(null);
const leaveGuard = useUnsavedChangesGuard(
    () => moduleForm.isDirty || lessonForm.isDirty,
);

function addModule(): void {
    moduleForm.post(`/courses/${props.course.id}/modules`, {
        onSuccess: () => moduleForm.reset('title'),
    });
}
function addLesson(): void {
    const options = { onSuccess: resetLessonForm };
    if (editingLesson.value) {
        lessonForm.put(
            `/courses/${props.course.id}/modules/${lessonForm.module_id}/lessons/${editingLesson.value.id}`,
            options,
        );

        return;
    }

    lessonForm.post(
        `/courses/${props.course.id}/modules/${lessonForm.module_id}/lessons`,
        options,
    );
}
function editLesson(module: Module, lesson: Lesson): void {
    editingLesson.value = lesson;
    lessonForm.module_id = module.id.toString();
    lessonForm.title = lesson.title;
    lessonForm.type = lesson.type;
    lessonForm.content = lesson.content ?? '';
    lessonForm.transcript = lesson.transcript ?? '';
    lessonForm.resource_url = lesson.resource_url ?? '';
    lessonForm.resource = null;
    lessonForm.replace_resource = false;
    lessonForm.is_required = lesson.is_required;
    resourceSource.value = lesson.resource_url ? 'url' : 'file';
    resourceInputKey.value += 1;
}
function resetLessonForm(): void {
    editingLesson.value = null;
    lessonForm.reset();
    lessonForm.module_id = props.course.modules[0]?.id?.toString() ?? '';
    lessonForm.type = 'article';
    lessonForm.is_required = true;
    lessonForm.replace_resource = false;
    resourceSource.value = 'file';
    resourceInputKey.value += 1;
}
function chooseResourceSource(source: 'file' | 'url'): void {
    resourceSource.value = source;
    if (source === 'file') {
        lessonForm.resource_url = '';

        return;
    }

    lessonForm.resource = null;
    lessonForm.replace_resource = false;
    resourceInputKey.value += 1;
}
function chooseResource(event: Event): void {
    lessonForm.resource = (event.target as HTMLInputElement).files?.[0] ?? null;
    lessonForm.replace_resource = Boolean(
        editingLesson.value && lessonForm.resource,
    );
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
function moveModule(module: Module, direction: 'up' | 'down'): void {
    router.patch(
        `/courses/${props.course.id}/modules/${module.id}/move`,
        { direction },
        {
            preserveScroll: true,
            onStart: () => (movingItem.value = `module-${module.id}`),
            onFinish: () => (movingItem.value = null),
        },
    );
}
function moveLesson(
    module: Module,
    lesson: Lesson,
    direction: 'up' | 'down',
): void {
    router.patch(
        `/courses/${props.course.id}/modules/${module.id}/lessons/${lesson.id}/move`,
        { direction },
        {
            preserveScroll: true,
            onStart: () => (movingItem.value = `lesson-${lesson.id}`),
            onFinish: () => (movingItem.value = null),
        },
    );
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
                        v-for="(module, moduleIndex) in course.modules"
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
                            <div
                                v-if="course.status === 'draft'"
                                class="flex shrink-0 items-center gap-1"
                            >
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    :disabled="
                                        movingItem !== null || moduleIndex === 0
                                    "
                                    :aria-label="`Naikkan modul ${module.title}`"
                                    @click="moveModule(module, 'up')"
                                    ><ChevronUp class="size-4"
                                /></Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    :disabled="
                                        movingItem !== null ||
                                        moduleIndex ===
                                            course.modules.length - 1
                                    "
                                    :aria-label="`Turunkan modul ${module.title}`"
                                    @click="moveModule(module, 'down')"
                                    ><ChevronDown class="size-4"
                                /></Button>
                                <Button
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
                        </div>
                        <ol
                            v-if="module.lessons.length"
                            class="mt-4 divide-y divide-border rounded-lg border border-border"
                        >
                            <li
                                v-for="(lesson, lessonIndex) in module.lessons"
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
                                                  : lesson.type === 'pdf'
                                                    ? 'PDF'
                                                    : 'Presentasi Canva'
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
                                <div
                                    v-if="course.status === 'draft'"
                                    class="flex shrink-0 items-center gap-1"
                                >
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        :disabled="
                                            movingItem !== null ||
                                            lessonIndex === 0
                                        "
                                        :aria-label="`Naikkan lesson ${lesson.title}`"
                                        @click="
                                            moveLesson(module, lesson, 'up')
                                        "
                                        ><ChevronUp class="size-4"
                                    /></Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        :disabled="
                                            movingItem !== null ||
                                            lessonIndex ===
                                                module.lessons.length - 1
                                        "
                                        :aria-label="`Turunkan lesson ${lesson.title}`"
                                        @click="
                                            moveLesson(module, lesson, 'down')
                                        "
                                        ><ChevronDown class="size-4"
                                    /></Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        :aria-label="`Ubah lesson ${lesson.title}`"
                                        @click="editLesson(module, lesson)"
                                        ><Pencil class="size-4"
                                    /></Button>
                                    <Button
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
                                </div>
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
            <div v-if="course.status === 'draft'" class="grid gap-6">
                <section
                    class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border"
                >
                    <h2 class="font-semibold text-foreground">Tambah modul</h2>
                    <form class="mt-5 grid gap-4" @submit.prevent="addModule">
                        <FormErrorSummary :errors="moduleForm.errors" />
                        <div class="grid gap-2">
                            <Label for="module-title">Judul modul</Label
                            ><Input
                                id="module-title"
                                v-model="moduleForm.title"
                                required
                            /><InputError :message="moduleForm.errors.title" />
                        </div>
                        <Button type="submit" :disabled="moduleForm.processing"
                            ><Spinner v-if="moduleForm.processing" />
                            <Plus v-else />
                            {{
                                moduleForm.processing
                                    ? 'Menyimpan modul…'
                                    : 'Tambah modul'
                            }}</Button
                        >
                    </form>
                </section>
                <section
                    class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border"
                >
                    <h2 class="font-semibold text-foreground">
                        {{ editingLesson ? 'Ubah lesson' : 'Tambah lesson' }}
                    </h2>
                    <p class="mt-1 text-sm leading-6 text-muted-foreground">
                        {{
                            editingLesson
                                ? 'Perbarui isi atau sumber materi. File lama dipertahankan sampai Anda memilih file pengganti.'
                                : 'Pilih jenis materi lalu lengkapi sumber yang akan dibuka learner.'
                        }}
                    </p>
                    <form class="mt-5 grid gap-4" @submit.prevent="addLesson">
                        <FormErrorSummary :errors="lessonForm.errors" />
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
                            <InputError
                                :message="lessonForm.errors.module_id"
                            />
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
                                :disabled="Boolean(editingLesson)"
                                class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                            >
                                <option value="article">Artikel</option>
                                <option value="video">Video</option>
                                <option value="pdf">PDF</option>
                                <option value="presentation">
                                    Presentasi Canva
                                </option>
                            </select>
                            <p
                                v-if="editingLesson"
                                class="text-xs leading-5 text-muted-foreground"
                            >
                                Tipe materi tidak dapat diubah setelah lesson
                                dibuat.
                            </p>
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
                        <template
                            v-else-if="lessonForm.type === 'presentation'"
                        >
                            <div class="grid gap-2">
                                <Label for="resource-url">URL Canva</Label>
                                <Input
                                    id="resource-url"
                                    v-model="lessonForm.resource_url"
                                    type="url"
                                    placeholder="https://www.canva.com/design/.../view"
                                    required
                                />
                                <p
                                    class="text-xs leading-5 text-muted-foreground"
                                >
                                    Dari Canva, pilih Share lalu atur akses ke
                                    <strong>Can view</strong>. Gunakan tautan
                                    view, embed, atau canva.link; tautan editor
                                    tidak dapat digunakan.
                                </p>
                                <InputError
                                    :message="lessonForm.errors.resource_url"
                                />
                            </div>
                        </template>
                        <template v-else>
                            <div class="grid gap-2">
                                <Label>Sumber materi</Label>
                                <div class="grid grid-cols-2 gap-2">
                                    <Button
                                        type="button"
                                        :variant="
                                            resourceSource === 'file'
                                                ? 'default'
                                                : 'outline'
                                        "
                                        :aria-pressed="
                                            resourceSource === 'file'
                                        "
                                        :disabled="lessonForm.processing"
                                        @click="chooseResourceSource('file')"
                                        >Unggah file</Button
                                    >
                                    <Button
                                        type="button"
                                        :variant="
                                            resourceSource === 'url'
                                                ? 'default'
                                                : 'outline'
                                        "
                                        :aria-pressed="resourceSource === 'url'"
                                        :disabled="lessonForm.processing"
                                        @click="chooseResourceSource('url')"
                                        >Gunakan URL</Button
                                    >
                                </div>
                            </div>
                            <div
                                v-if="resourceSource === 'url'"
                                class="grid gap-2"
                            >
                                <Label for="resource-url">
                                    URL
                                    {{
                                        lessonForm.type === 'video'
                                            ? 'video'
                                            : 'PDF'
                                    }}
                                </Label>
                                <Input
                                    id="resource-url"
                                    v-model="lessonForm.resource_url"
                                    type="url"
                                    placeholder="https://"
                                    required
                                />
                                <p
                                    class="text-xs leading-5 text-muted-foreground"
                                >
                                    Video mendukung URL file MP4/WebM langsung,
                                    atau tautan YouTube dan Vimeo.
                                </p>
                                <InputError
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
                            <div
                                v-if="resourceSource === 'file'"
                                class="grid gap-2"
                            >
                                <Label for="lesson-resource">{{
                                    editingLesson
                                        ? 'Ganti file privat'
                                        : 'Upload file privat'
                                }}</Label
                                ><Input
                                    :key="resourceInputKey"
                                    id="lesson-resource"
                                    type="file"
                                    :accept="
                                        lessonForm.type === 'pdf'
                                            ? 'application/pdf'
                                            : 'video/mp4,video/webm'
                                    "
                                    :required="!editingLesson"
                                    :disabled="lessonForm.processing"
                                    @change="chooseResource"
                                />
                                <p
                                    class="text-xs leading-5 text-muted-foreground"
                                >
                                    {{
                                        lessonForm.type === 'pdf'
                                            ? 'PDF'
                                            : 'MP4 atau WebM'
                                    }}
                                    hingga 50 MB. File diakses melalui izin
                                    aplikasi.
                                    <template v-if="editingLesson">
                                        Kosongkan bila file saat ini tetap
                                        digunakan.
                                    </template>
                                </p>
                                <p
                                    v-if="lessonForm.resource"
                                    class="text-xs font-medium text-foreground"
                                >
                                    File dipilih: {{ lessonForm.resource.name }}
                                </p>
                                <div
                                    v-if="lessonForm.processing"
                                    class="grid gap-2"
                                    role="status"
                                    aria-live="polite"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        {{
                                            lessonForm.progress
                                                ? 'Mengunggah file ' +
                                                  lessonForm.progress
                                                      .percentage +
                                                  '%'
                                                : 'Menyiapkan lesson…'
                                        }}
                                    </p>
                                    <div
                                        class="h-2 overflow-hidden rounded-full bg-muted"
                                        role="progressbar"
                                        aria-label="Progres unggah file lesson"
                                        :aria-valuenow="
                                            lessonForm.progress?.percentage ?? 0
                                        "
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    >
                                        <div
                                            class="h-full bg-primary transition-[width] duration-200"
                                            :style="{
                                                width:
                                                    (lessonForm.progress
                                                        ?.percentage ?? 0) +
                                                    '%',
                                            }"
                                        />
                                    </div>
                                </div>
                                <InputError
                                    :message="lessonForm.errors.resource"
                                />
                            </div>
                        </template>
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
                        <div class="flex flex-wrap gap-3">
                            <Button
                                type="submit"
                                :disabled="lessonForm.processing"
                            >
                                <Spinner v-if="lessonForm.processing" />
                                <Pencil v-else-if="editingLesson" />
                                <Plus v-else />
                                {{
                                    lessonForm.processing
                                        ? editingLesson
                                            ? 'Menyimpan lesson…'
                                            : 'Menambahkan lesson…'
                                        : editingLesson
                                          ? 'Simpan perubahan'
                                          : 'Tambah lesson'
                                }}
                            </Button>
                            <Button
                                v-if="editingLesson"
                                type="button"
                                variant="outline"
                                :disabled="lessonForm.processing"
                                @click="resetLessonForm"
                                ><X /> Batal</Button
                            >
                        </div>
                    </form>
                </section>
            </div>
            <section
                v-else
                class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border"
            >
                <h2 class="font-semibold text-foreground">Materi terkunci</h2>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Course tidak dalam status Draft. Ubah status course menjadi
                    Draft melalui Pengaturan course sebelum mengubah modul,
                    lesson, atau sumber materi.
                </p>
            </section>
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
