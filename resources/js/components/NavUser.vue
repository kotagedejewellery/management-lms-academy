<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';
import { computed, ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { logout } from '@/routes';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { isMobile, state } = useSidebar();
const logoutDialogOpen = ref(false);
const loggingOut = ref(false);

function handleLogout(): void {
    loggingOut.value = true;
    router.flushAll();
    router.post(
        logout().url,
        {},
        {
            onFinish: () => {
                loggingOut.value = false;
            },
        },
    );
}
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu v-if="user">
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        data-test="sidebar-menu-button"
                    >
                        <UserInfo :user="user" />
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'left'
                              : 'bottom'
                    "
                    align="end"
                    :side-offset="4"
                >
                    <UserMenuContent
                        :user="user"
                        @logout="logoutDialogOpen = true"
                    />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
    <ConfirmActionDialog
        v-model:open="logoutDialogOpen"
        title="Keluar dari KGJ Academy?"
        description="Anda perlu masuk kembali untuk melanjutkan pembelajaran atau mengelola data."
        confirm-label="Keluar"
        :processing="loggingOut"
        @confirm="handleLogout"
    />
</template>
