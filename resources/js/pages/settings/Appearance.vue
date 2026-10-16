<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tampilan',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Tampilan" />

    <h1 class="sr-only">Tampilan</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Tampilan"
            description="Pilih tampilan yang nyaman untuk browser di perangkat ini."
        />
        <AppearanceTabs />
    </div>
</template>
