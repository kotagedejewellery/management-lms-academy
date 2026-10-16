<script setup lang="ts">
import { KeyRound, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import { Button } from '@/components/ui/button';
import type { Passkey } from '@/types/auth';

const props = defineProps<{
    passkey: Passkey;
}>();

const emit = defineEmits<{
    remove: [id: number, onFinish: () => void];
}>();

const isDeleting = ref(false);
const confirmOpen = ref(false);

function handleDelete(): void {
    isDeleting.value = true;
    emit('remove', props.passkey.id, () => {
        isDeleting.value = false;
    });
}
</script>

<template>
    <div class="flex items-center justify-between border-b p-4 last:border-b-0">
        <div class="flex items-center gap-4">
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted"
            >
                <KeyRound class="h-5 w-5 text-muted-foreground" />
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <p class="font-medium tracking-tight">{{ passkey.name }}</p>
                    <span
                        v-if="passkey.authenticator"
                        class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-[11px] font-medium tracking-wide text-muted-foreground uppercase ring-1 ring-border ring-inset"
                    >
                        {{ passkey.authenticator }}
                    </span>
                </div>
                <p class="text-sm text-muted-foreground">
                    Ditambahkan {{ passkey.created_at_diff }}
                    <template v-if="passkey.last_used_at_diff">
                        <span class="mx-1 text-muted-foreground/50">/</span>
                        Terakhir digunakan {{ passkey.last_used_at_diff }}
                    </template>
                </p>
            </div>
        </div>

        <Button
            variant="ghost"
            size="sm"
            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
            @click="confirmOpen = true"
        >
            <Trash2 class="h-4 w-4" />
            <span class="sr-only">Hapus passkey</span>
        </Button>
        <ConfirmActionDialog
            v-model:open="confirmOpen"
            title="Hapus passkey?"
            :description="`Passkey ${passkey.name} tidak dapat lagi digunakan untuk masuk ke akun Anda.`"
            confirm-label="Hapus passkey"
            :processing="isDeleting"
            destructive
            @confirm="handleDelete"
        />
    </div>
</template>
