<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { MailCheck } from '@lucide/vue';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengaturan profil',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isEmailVerified = computed(() => Boolean(user.value?.email_verified_at));
const email = ref(user.value?.email ?? '');
const isEmailChanged = computed(
    () => email.value.trim().toLowerCase() !== user.value?.email.toLowerCase(),
);
const props = defineProps<{
    organization: {
        employee_id: string | null;
        division: { name: string } | null;
        position: { name: string } | null;
        branch: { name: string } | null;
        manager: { name: string } | null;
    };
}>();
</script>

<template>
    <Head title="Profil" />

    <h1 class="sr-only">Profil</h1>

    <div v-if="user" class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profil saya"
            description="Perbarui nama dan email kerja Anda. Data organisasi dikelola oleh LMS Admin."
        />

        <Alert
            v-if="page.props.mustVerifyEmail && !isEmailVerified"
            class="items-start"
        >
            <MailCheck class="mt-0.5 size-4" />
            <AlertTitle>Verifikasi email diperlukan</AlertTitle>
            <AlertDescription>
                Verifikasi <strong>{{ user.email }}</strong> untuk membuka
                pengaturan keamanan dan tampilan akun.
                <Link
                    :href="send()"
                    as="button"
                    class="ml-1 font-medium text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors hover:decoration-current dark:decoration-neutral-500"
                >
                    Kirim ulang tautan verifikasi.
                </Link>
                <p
                    v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 font-medium text-foreground"
                >
                    Tautan verifikasi baru telah dikirim.
                </p>
            </AlertDescription>
        </Alert>

        <section class="rounded-xl border border-border bg-card p-5 sm:p-6">
            <h2 class="font-semibold text-foreground">Data organisasi</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Hubungi LMS Admin bila data berikut perlu diperbarui.
            </p>
            <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">ID Karyawan</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.employee_id ??
                            'Belum diatur oleh LMS Admin'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Divisi</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.division?.name ??
                            'Belum diatur oleh LMS Admin'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Jabatan</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.position?.name ??
                            'Belum diatur oleh LMS Admin'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Cabang</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.branch?.name ??
                            'Belum diatur oleh LMS Admin'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Manager</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.manager?.name ??
                            'Belum diatur oleh LMS Admin'
                        }}
                    </dd>
                </div>
            </dl>
        </section>

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nama lengkap</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Nama lengkap"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email kerja</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    v-model="email"
                    required
                    autocomplete="username"
                    placeholder="email@perusahaan.com"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <p
                v-if="page.props.mustVerifyEmail && isEmailChanged"
                class="-mt-4 text-sm text-muted-foreground"
            >
                Email baru harus diverifikasi sebelum pengaturan keamanan dan
                tampilan dapat digunakan.
            </p>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Simpan perubahan</Button
                >
            </div>
        </Form>
    </div>
</template>
