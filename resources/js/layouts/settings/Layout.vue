<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

type SettingsNavItem = NavItem & { requiresVerification?: boolean };

const sidebarNavItems: SettingsNavItem[] = [
    {
        title: 'Profil',
        href: editProfile(),
    },
    {
        title: 'Keamanan',
        href: editSecurity(),
        requiresVerification: true,
    },
    {
        title: 'Tampilan',
        href: editAppearance(),
        requiresVerification: true,
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
const page = usePage();
const isEmailVerified = computed(() =>
    Boolean(page.props.auth.user?.email_verified_at),
);

function canOpen(item: SettingsNavItem): boolean {
    return !item.requiresVerification || isEmailVerified.value;
}
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 xl:px-10">
        <Heading
            title="Pengaturan akun"
            description="Kelola profil, keamanan, dan tampilan akun Anda."
        />

        <div class="flex flex-col lg:flex-row lg:gap-12">
            <aside class="w-full max-w-2xl lg:w-52">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Pengaturan akun"
                >
                    <template
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                    >
                        <Button
                            v-if="canOpen(item)"
                            variant="ghost"
                            :class="[
                                'w-full justify-start',
                                {
                                    'bg-muted': isCurrentOrParentUrl(item.href),
                                },
                            ]"
                            as-child
                        >
                            <Link :href="item.href">
                                {{ item.title }}
                            </Link>
                        </Button>
                        <div
                            v-else
                            class="flex min-h-9 items-center justify-between rounded-md px-3 py-2 text-sm text-muted-foreground"
                            aria-disabled="true"
                        >
                            <span>{{ item.title }}</span>
                            <Badge variant="secondary" class="text-[10px]">
                                Perlu verifikasi
                            </Badge>
                        </div>
                    </template>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 lg:max-w-3xl">
                <section class="max-w-2xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
