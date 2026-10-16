<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';

// oxfmt-ignore
type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Keamanan akun',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Keamanan akun" />

    <h1 class="sr-only">Keamanan akun</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Ubah kata sandi"
            description="Gunakan kata sandi panjang dan unik untuk menjaga keamanan akun."
        />

        <section class="rounded-xl border border-border bg-card p-5 sm:p-6">
            <h2 class="font-semibold text-foreground">Status keamanan</h2>
            <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">
                        Autentikasi dua faktor
                    </dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            !props.canManageTwoFactor
                                ? 'Belum tersedia'
                                : props.twoFactorEnabled
                                  ? 'Aktif'
                                  : 'Belum aktif'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Passkey</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            !props.canManagePasskeys
                                ? 'Belum tersedia'
                                : `${props.passkeys?.length ?? 0} terdaftar`
                        }}
                    </dd>
                </div>
            </dl>
        </section>

        <Form
            v-bind="SecurityController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="current_password">Kata sandi saat ini</Label>
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi saat ini"
                />
                <InputError :message="errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Kata sandi baru</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="Masukkan kata sandi baru"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation"
                    >Konfirmasi kata sandi baru</Label
                >
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="Ulangi kata sandi baru"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="processing"
                    data-test="update-password-button"
                >
                    Simpan kata sandi
                </Button>
            </div>
        </Form>
    </div>

    <ManageTwoFactor
        :canManageTwoFactor="canManageTwoFactor"
        :requiresConfirmation="requiresConfirmation"
        :twoFactorEnabled="twoFactorEnabled"
    />

    <ManagePasskeys
        :canManagePasskeys="canManagePasskeys"
        :passkeys="passkeys"
    />
</template>
