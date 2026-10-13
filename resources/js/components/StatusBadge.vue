<script setup lang="ts">
import { computed } from 'vue';
import {
    CircleCheck,
    CirclePause,
    FilePenLine,
    LockKeyhole,
    Send,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';

type Status =
    | 'active'
    | 'inactive'
    | 'draft'
    | 'published'
    | 'archived'
    | 'locked';

const props = defineProps<{ status: Status }>();

const presentation = computed(
    () =>
        ({
            active: {
                label: 'Aktif',
                icon: CircleCheck,
                class: 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300',
            },
            inactive: {
                label: 'Tidak aktif',
                icon: CirclePause,
                class: 'border-border bg-muted text-muted-foreground',
            },
            draft: {
                label: 'Draf',
                icon: FilePenLine,
                class: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300',
            },
            published: {
                label: 'Dipublikasikan',
                icon: Send,
                class: 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-300',
            },
            archived: {
                label: 'Diarsipkan',
                icon: LockKeyhole,
                class: 'border-border bg-muted text-muted-foreground',
            },
            locked: {
                label: 'Terkunci',
                icon: LockKeyhole,
                class: 'border-border bg-muted text-muted-foreground',
            },
        })[props.status],
);
</script>

<template>
    <Badge variant="outline" :class="presentation.class"
        ><component :is="presentation.icon" />{{ presentation.label }}</Badge
    >
</template>
