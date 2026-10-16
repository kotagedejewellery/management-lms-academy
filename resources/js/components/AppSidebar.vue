<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { buildSidebarNavigation } from '@/navigation/sidebar';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';

const page = usePage();

const mainNavGroups = computed(() =>
    buildSidebarNavigation({
        ...page.props.auth.can,
        isSuperAdmin: page.props.auth.isSuperAdmin,
    }),
);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader
            class="border-b border-sidebar-border px-3 py-4 group-data-[collapsible=icon]:p-2"
        >
            <Link
                :href="dashboard()"
                class="flex h-14 items-center justify-center rounded-lg px-2 transition-colors group-data-[collapsible=icon]:size-8 group-data-[collapsible=icon]:p-0 hover:bg-sidebar-accent focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none"
            >
                <AppLogo class="group-data-[collapsible=icon]:hidden" />
                <AppLogoIcon
                    class="hidden size-8 rounded-lg object-contain group-data-[collapsible=icon]:block"
                />
            </Link>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border p-2">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
