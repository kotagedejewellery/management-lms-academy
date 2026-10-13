<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    from: number | null;
    to: number | null;
    total: number;
    previous: string | null;
    next: string | null;
}>();
</script>

<template>
    <nav
        aria-label="Pagination"
        class="flex flex-col justify-between gap-3 border-t border-border px-5 py-4 text-sm text-muted-foreground sm:flex-row sm:items-center"
    >
        <p>Menampilkan {{ from }}–{{ to }} dari {{ total }}</p>
        <div class="flex gap-2">
            <Button v-if="previous" variant="outline" size="sm" as-child
                ><Link :href="previous">Sebelumnya</Link></Button
            ><Button v-if="next" variant="outline" size="sm" as-child
                ><Link :href="next">Berikutnya</Link></Button
            >
        </div>
    </nav>
</template>
