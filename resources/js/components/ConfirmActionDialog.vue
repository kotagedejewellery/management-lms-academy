<script setup lang="ts">
import { AlertTriangle } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        confirmLabel: string;
        cancelLabel?: string;
        processing?: boolean;
        destructive?: boolean;
    }>(),
    {
        processing: false,
        destructive: false,
        cancelLabel: 'Batal',
    },
);

const emit = defineEmits<{
    'update:open': [open: boolean];
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <Dialog
        :open="open"
        @update:open="!processing && emit('update:open', $event)"
    >
        <DialogContent :show-close-button="!processing" class="sm:max-w-md">
            <DialogHeader>
                <div
                    class="mb-2 flex size-10 items-center justify-center rounded-full bg-muted text-foreground"
                >
                    <AlertTriangle class="size-5" aria-hidden="true" />
                </div>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription class="leading-6">{{
                    description
                }}</DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-2">
                <DialogClose as-child>
                    <Button
                        variant="outline"
                        :disabled="processing"
                        @click="emit('cancel')"
                        >{{ cancelLabel }}</Button
                    >
                </DialogClose>
                <Button
                    :variant="destructive ? 'destructive' : 'default'"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    {{ processing ? 'Memproses…' : confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
