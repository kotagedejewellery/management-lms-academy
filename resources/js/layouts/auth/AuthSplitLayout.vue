<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-dvh bg-stone-50 lg:grid-cols-2 dark:bg-zinc-950">
        <aside
            class="relative hidden min-h-dvh flex-col bg-zinc-950 p-12 text-stone-50 lg:flex"
        >
            <Link
                :href="home()"
                class="flex items-center gap-3 self-start focus-visible:ring-2 focus-visible:ring-amber-300 focus-visible:ring-offset-4 focus-visible:ring-offset-zinc-950 focus-visible:outline-none"
            >
                <img
                    src="/images/kgj-logo.png?v=2"
                    alt="Kotagede Jewellery"
                    class="h-16 w-28 object-contain"
                />
                <span>
                    <span class="block text-base font-semibold tracking-tight"
                        >KGJ Academy</span
                    >
                    <span
                        class="mt-0.5 block text-xs tracking-wide text-stone-400"
                        >Pembelajaran internal</span
                    >
                </span>
            </Link>

            <div class="my-auto max-w-2xl">
                <p
                    class="max-w-2xl text-3xl leading-tight font-semibold tracking-tight text-balance text-stone-50 xl:text-4xl 2xl:text-5xl"
                >
                    <span class="block">Belajar dengan arah.</span>
                    <span class="block">Bertumbuh dengan standar.</span>
                </p>
                <p class="mt-6 max-w-md text-base leading-7 text-stone-300">
                    Satu ruang pembelajaran untuk materi kerja, evaluasi
                    kompetensi, dan perkembangan Anda di KGJ.
                </p>
            </div>

            <p class="text-sm text-stone-500">
                KGJ Academy · Pembelajaran internal
            </p>
        </aside>

        <main
            class="flex min-h-dvh items-center justify-center px-6 py-10 sm:px-10 lg:px-16"
        >
            <div class="w-full max-w-sm">
                <Link
                    :href="home()"
                    class="mb-12 flex items-center gap-3 focus-visible:ring-2 focus-visible:ring-amber-700 focus-visible:ring-offset-4 focus-visible:outline-none lg:hidden"
                >
                    <img
                        src="/images/kgj-logo.png?v=2"
                        alt="Kotagede Jewellery"
                        class="h-12 w-20 object-contain"
                    />
                    <span>
                        <span
                            class="block font-semibold tracking-tight text-foreground"
                            >KGJ Academy</span
                        >
                        <span class="block text-xs text-muted-foreground"
                            >Pembelajaran internal</span
                        >
                    </span>
                </Link>

                <header class="mb-8">
                    <h1
                        v-if="title"
                        class="text-3xl font-semibold tracking-tight text-foreground"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-3 text-sm leading-6 text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                </header>

                <slot />
            </div>
        </main>
    </div>
</template>
