<script setup lang="ts">
import { Form, router } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import { onUnmounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { disable, enable } from '@/routes/two-factor';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);
const disableDialogOpen = ref(false);
const disabling = ref(false);

function disableTwoFactor(): void {
    disabling.value = true;
    router.delete(disable.url(), {
        preserveScroll: true,
        onFinish: () => {
            disabling.value = false;
        },
    });
}

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <div v-if="canManageTwoFactor" class="space-y-6">
        <Heading
            variant="small"
            title="Autentikasi dua faktor"
            description="Tambahkan kode dari aplikasi autentikator saat masuk."
        />

        <div
            v-if="!twoFactorEnabled"
            class="flex flex-col items-start justify-start space-y-4"
        >
            <p class="text-sm text-muted-foreground">
                Saat diaktifkan, Anda memasukkan kode dari aplikasi autentikator
                di ponsel setiap kali masuk.
            </p>

            <div>
                <Button v-if="hasSetupData" @click="showSetupModal = true">
                    <ShieldCheck />Lanjutkan pengaturan
                </Button>
                <Form
                    v-else
                    v-bind="enable.form()"
                    @success="showSetupModal = true"
                    #default="{ processing }"
                >
                    <Button type="submit" :disabled="processing">
                        Aktifkan 2FA
                    </Button>
                </Form>
            </div>
        </div>

        <div v-else class="flex flex-col items-start justify-start space-y-4">
            <p class="text-sm text-muted-foreground">
                Saat masuk, Anda akan diminta kode dari aplikasi autentikator di
                ponsel.
            </p>

            <Button variant="destructive" @click="disableDialogOpen = true">
                Nonaktifkan 2FA
            </Button>

            <TwoFactorRecoveryCodes />
        </div>

        <TwoFactorSetupModal
            v-model:isOpen="showSetupModal"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />
        <ConfirmActionDialog
            v-model:open="disableDialogOpen"
            title="Nonaktifkan autentikasi dua faktor?"
            description="Akun Anda tidak lagi meminta kode tambahan saat masuk. Anda dapat mengaktifkannya kembali kapan saja."
            confirm-label="Nonaktifkan 2FA"
            :processing="disabling"
            destructive
            @confirm="disableTwoFactor"
        />
    </div>
</template>
