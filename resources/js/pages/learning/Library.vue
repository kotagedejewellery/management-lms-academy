<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpen, Clock3, Search, X } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Course = {
    id: number;
    code: string;
    title: string;
    description: string | null;
    estimated_minutes: number | null;
    is_mandatory: boolean;
    is_assigned: boolean;
    thumbnail_url: string | null;
    category: { name: string } | null;
};

const props = defineProps<{
    courses: {
        data: Course[];
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: { search: string };
}>();
const filters = { search: props.filters.search };
function applySearch(): void {
    router.get('/course-library', filters, {
        preserveState: true,
        replace: true,
    });
}
function clearSearch(): void {
    filters.search = '';
    applySearch();
}
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Course Library', href: '/course-library' }],
    },
});
</script>

<template>
    <Head title="Course Library" />

    <div class="app-page">
        <PageHeader
            title="Katalog course"
            description="Jelajahi course yang relevan dengan divisi dan posisi Anda. Materi tersedia setelah course ditetapkan."
        />

        <form class="flex max-w-xl gap-2" @submit.prevent="applySearch">
            <div class="grid flex-1 gap-2">
                <Label class="sr-only" for="library-search">Cari course</Label
                ><Input
                    id="library-search"
                    v-model="filters.search"
                    placeholder="Cari judul atau kode course"
                />
            </div>
            <Button type="submit" variant="outline"
                ><Search /><span class="sr-only sm:not-sr-only"
                    >Cari</span
                ></Button
            ><Button
                v-if="filters.search"
                type="button"
                variant="outline"
                @click="clearSearch"
                ><X /><span class="sr-only sm:not-sr-only">Reset</span></Button
            >
        </form>

        <section>
            <div
                v-if="courses.data.length"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <article
                    v-for="course in courses.data"
                    :key="course.id"
                    class="flex min-h-56 flex-col rounded-2xl border border-border/85 bg-card p-5 transition-[border-color,transform] duration-200 hover:-translate-y-0.5 hover:border-brand-gold/45"
                >
                    <img
                        v-if="course.thumbnail_url"
                        :src="course.thumbnail_url"
                        :alt="`Thumbnail ${course.title}`"
                        class="mb-4 aspect-[16/7] w-full rounded-lg object-cover"
                        loading="lazy"
                        width="640"
                        height="280"
                    />
                    <p class="text-sm text-muted-foreground">
                        {{ course.category?.name ?? 'Course' }}
                    </p>
                    <h2 class="mt-2 font-semibold text-foreground">
                        {{ course.title }}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ course.code }}
                    </p>
                    <p
                        v-if="course.description"
                        class="mt-4 line-clamp-3 text-sm leading-6 text-muted-foreground"
                    >
                        {{ course.description }}
                    </p>
                    <div class="mt-auto pt-5">
                        <p
                            v-if="course.estimated_minutes"
                            class="flex items-center gap-1 text-sm text-muted-foreground"
                        >
                            <Clock3 class="size-4" />{{
                                course.estimated_minutes
                            }}
                            menit
                        </p>
                        <Button
                            v-if="course.is_assigned"
                            class="mt-4 w-full"
                            size="sm"
                            as-child
                            ><Link :href="`/my-learning/${course.id}`"
                                >Buka pembelajaran</Link
                            ></Button
                        >
                        <p
                            v-else
                            class="mt-4 text-sm font-medium text-muted-foreground"
                        >
                            Belum ditetapkan kepada Anda
                        </p>
                    </div>
                </article>
            </div>
            <EmptyState
                v-else
                :icon="BookOpen"
                title="Course tidak ditemukan"
                description="Coba ubah kata kunci pencarian atau hubungi Admin untuk assignment course."
            />
        </section>

        <Pagination
            v-if="courses.data.length"
            :from="courses.from"
            :to="courses.to"
            :total="courses.total"
            :previous="courses.prev_page_url"
            :next="courses.next_page_url"
        />
    </div>
</template>
