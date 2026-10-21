<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Play, RotateCcw } from '@lucide/vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    course: { id: number; title: string };
    assessment: {
        id: number;
        type: 'pre_test' | 'post_test';
        time_limit_minutes: number | null;
    };
}>();

function startAssessment(): void {
    router.post(
        `/my-learning/${props.course.id}/assessments/${props.assessment.id}/start`,
    );
}

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
    <Head title="Mulai Post-Test" />

    <main class="w-full px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <section
            class="mx-auto max-w-2xl rounded-xl border border-border bg-card p-6 sm:p-10"
        >
            <p class="text-sm font-medium text-muted-foreground">
                {{ course.title }}
            </p>
            <h1
                class="mt-2 text-3xl font-semibold tracking-tight text-foreground"
            >
                Kesempatan Post-Test tersedia
            </h1>
            <p class="mt-3 max-w-xl text-sm leading-6 text-muted-foreground">
                Management telah membuka kesempatan untuk mengerjakan Post-Test.
                Percobaan baru dimulai saat Anda menekan tombol di bawah.
                <template v-if="assessment.time_limit_minutes">
                    Batas waktu pengerjaan adalah
                    {{ assessment.time_limit_minutes }} menit.
                </template>
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <Button @click="startAssessment"
                    ><Play /> Mulai Post-Test</Button
                >
                <Button variant="outline" as-child>
                    <Link :href="`/my-learning/${course.id}`">
                        <RotateCcw /> Kembali ke materi
                    </Link>
                </Button>
            </div>
        </section>
    </main>
</template>
