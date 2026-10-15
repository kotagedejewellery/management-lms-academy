<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
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

        <section class="rounded-xl border border-border bg-card p-5 sm:p-6">
            <h2 class="font-semibold text-foreground">Identitas organisasi</h2>
            <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">Employee ID</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.employee_id ?? 'Belum ditetapkan'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Divisi</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.division?.name ??
                            'Belum ditetapkan'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Jabatan</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.position?.name ??
                            'Belum ditetapkan'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Cabang</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.branch?.name ??
                            'Belum ditetapkan'
                        }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Manager</dt>
                    <dd class="mt-1 font-medium text-foreground">
                        {{
                            props.organization.manager?.name ??
                            'Belum ditetapkan'
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
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="email@perusahaan.com"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Email kerja Anda belum terverifikasi.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Kirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    Tautan verifikasi baru telah dikirim ke email kerja Anda.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Simpan perubahan</Button
                >
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>
