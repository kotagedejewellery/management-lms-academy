<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    course: { id: number; title: string };
    assessment: { id: number; type: 'pre_test' | 'post_test' };
}>();

const title = computed(() =>
    props.assessment.type === 'pre_test'
        ? 'Pre-Test telah dikirim'
        : 'Post-Test telah dikirim',
);
const description = computed(() =>
    props.assessment.type === 'pre_test'
        ? 'Materi yang tersedia dapat Anda lanjutkan.'
        : 'Lanjutkan pembelajaran sesuai materi yang tersedia. Management akan menghubungi Anda bila ada tindak lanjut.',
);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My Learning', href: '/my-learning' },
            { title: 'Assessment', href: '/my-learning' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <main class="w-full px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <section
            class="mx-auto max-w-2xl rounded-xl border border-border bg-card p-6 sm:p-10"
        >
            <div class="text-center">
                <CheckCircle2 class="mx-auto size-10 text-primary" />
                <p class="mt-5 text-sm font-medium text-muted-foreground">
                    {{ course.title }}
                </p>
                <h1
                    class="mt-2 text-3xl font-semibold tracking-tight text-foreground"
                >
                    {{ title }}
                </h1>
                <p
                    class="mx-auto mt-3 max-w-xl text-sm leading-6 text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>

            <div class="mt-8 flex justify-center">
                <Button as-child>
                    <Link :href="`/my-learning/${course.id}`"
                        >Kembali ke materi</Link
                    >
                </Button>
            </div>
        </section>
    </main>
</template>
