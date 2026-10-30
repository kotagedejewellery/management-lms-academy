<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpenCheck,
    ClipboardCheck,
    ListChecks,
} from '@lucide/vue';
import { computed } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

type Stat = { label: string; value: number };
type Action = { label: string; href: string };
type DashboardItem = {
    id: number;
    title: string;
    description: string;
    meta: string;
    href: string | null;
    locked?: boolean;
    lock_reason?: string | null;
};
type LearningSummary = {
    overall_progress: number;
    pre_test_pending: number;
    post_test_ready: number;
};

const props = defineProps<{
    mode: 'learner' | 'trainer' | 'operations';
    title: string;
    description: string;
    stats: Stat[];
    primary_action: Action | null;
    secondary_action?: Action | null;
    items_title: string;
    empty_title: string;
    empty_description: string;
    items: DashboardItem[];
    learning_summary?: LearningSummary;
}>();

const context = computed(
    () =>
        ({
            learner: {
                label: 'Fokus belajar Anda',
                detail: 'Selesaikan materi yang sedang berjalan dan perhatikan tenggat terdekat.',
                scope: 'Data pembelajaran pribadi Anda.',
                icon: BookOpenCheck,
            },
            trainer: {
                label: 'Kesiapan materi',
                detail: 'Lengkapi course yang belum siap diterbitkan sebelum ditugaskan kepada employee.',
                scope: 'Data course sesuai akses Anda.',
                icon: ClipboardCheck,
            },
            operations: {
                label: 'Operasional pembelajaran',
                detail: 'Pantau assignment terbaru dan lanjutkan pengelolaan program belajar karyawan.',
                scope: 'Data operasional sesuai akses Anda.',
                icon: ClipboardCheck,
            },
        })[props.mode],
);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="app-page flex-1">
        <PageHeader :title="props.title" :description="props.description">
            <template #actions>
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <Button
                        v-if="props.primary_action"
                        as-child
                        class="w-full sm:w-auto"
                    >
                        <Link :href="props.primary_action.href"
                            >{{ props.primary_action.label }} <ArrowRight
                        /></Link>
                    </Button>
                    <Button
                        v-if="props.secondary_action"
                        as-child
                        class="w-full sm:w-auto"
                        variant="outline"
                    >
                        <Link :href="props.secondary_action.href">
                            {{ props.secondary_action.label }}
                        </Link>
                    </Button>
                </div>
            </template>
        </PageHeader>

        <section
            class="grid gap-5 rounded-2xl bg-primary p-6 text-primary-foreground sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:p-7"
        >
            <div class="min-w-0">
                <h2
                    class="text-lg font-semibold tracking-tight text-primary-foreground"
                >
                    {{ context.label }}
                </h2>
                <p
                    class="mt-1 max-w-2xl text-sm leading-6 text-primary-foreground/75"
                >
                    {{ context.detail }}
                </p>
                <p class="mt-3 text-xs font-medium text-primary-foreground/75">
                    {{ context.scope }}
                </p>
            </div>
            <div
                class="grid size-12 place-items-center rounded-full bg-brand-gold/15 text-brand-gold"
            >
                <component
                    :is="context.icon"
                    class="size-6"
                    aria-hidden="true"
                />
            </div>
        </section>

        <dl
            class="grid overflow-hidden rounded-2xl border border-border/85 bg-card sm:grid-cols-2 lg:grid-cols-4"
        >
            <div
                v-for="stat in props.stats"
                :key="stat.label"
                class="relative p-5 last:border-r-0 sm:odd:border-r lg:border-t-0 lg:border-r sm:[&:nth-child(n+3)]:border-t"
            >
                <dt class="text-sm text-muted-foreground">{{ stat.label }}</dt>
                <dd
                    class="mt-2 text-3xl font-semibold tracking-tight text-foreground tabular-nums"
                >
                    {{ stat.value }}
                </dd>
            </div>
        </dl>

        <section
            v-if="props.mode === 'learner' && props.learning_summary"
            class="grid overflow-hidden rounded-2xl border border-border/85 bg-card sm:grid-cols-3"
            aria-label="Ringkasan evaluasi pembelajaran"
        >
            <div class="p-5 sm:border-r">
                <p class="text-sm text-muted-foreground">Progres keseluruhan</p>
                <p
                    class="mt-2 text-3xl font-semibold tracking-tight text-foreground tabular-nums"
                >
                    {{ props.learning_summary.overall_progress }}%
                </p>
            </div>
            <div class="border-t p-5 sm:border-t-0 sm:border-r">
                <p class="text-sm text-muted-foreground">Pre-Test tersedia</p>
                <p
                    class="mt-2 text-3xl font-semibold tracking-tight text-foreground tabular-nums"
                >
                    {{ props.learning_summary.pre_test_pending }}
                </p>
            </div>
            <div class="border-t p-5 sm:border-t-0">
                <p class="text-sm text-muted-foreground">
                    Post-Test siap dikerjakan
                </p>
                <p
                    class="mt-2 text-3xl font-semibold tracking-tight text-foreground tabular-nums"
                >
                    {{ props.learning_summary.post_test_ready }}
                </p>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-2xl border border-border/85 bg-card"
        >
            <div
                class="flex items-center justify-between gap-3 border-b border-border/85 px-5 py-4"
            >
                <h2 class="font-semibold text-foreground">
                    {{ props.items_title }}
                </h2>
                <span class="text-xs font-medium text-muted-foreground">
                    {{ props.items.length }} entri
                </span>
            </div>
            <div v-if="props.items.length" class="divide-y divide-border">
                <component
                    v-for="item in props.items"
                    :key="item.id"
                    :is="item.href ? Link : 'div'"
                    v-bind="item.href ? { href: item.href } : {}"
                    class="data-row flex flex-col gap-1 px-5 py-4 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-inset sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="font-medium text-foreground">
                            {{ item.title }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ item.description }}
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
                    >
                        <StatusBadge v-if="item.locked" status="locked" />
                        <span>{{ item.meta }}</span>
                    </div>
                </component>
            </div>
            <EmptyState
                v-else
                :icon="ListChecks"
                :title="props.empty_title"
                :description="props.empty_description"
            >
                <Button v-if="props.primary_action" as-child>
                    <Link :href="props.primary_action.href">{{
                        props.primary_action.label
                    }}</Link>
                </Button>
            </EmptyState>
        </section>
    </div>
</template>
