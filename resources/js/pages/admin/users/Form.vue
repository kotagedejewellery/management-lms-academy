<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import FormErrorSummary from '@/components/FormErrorSummary.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { useUnsavedChangesGuard } from '@/composables/useUnsavedChangesGuard';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Option = {
    id: number;
    name: string;
    slug?: string;
    employee_id?: string | null;
};

type ManagedUser = {
    id: number;
    employee_id: string | null;
    hired_at: string | null;
    name: string;
    email: string;
    phone: string | null;
    is_organization_manager: boolean;
    status: 'active' | 'inactive';
    role: Option | null;
    division: Option | null;
    position: Option | null;
    branch: Option | null;
    manager: Option | null;
};

const props = defineProps<{
    title: string;
    user?: ManagedUser;
    roles: Option[];
    divisions: Option[];
    positions: Option[];
    branches: Option[];
}>();

const form = useForm({
    employee_id: props.user?.employee_id ?? '',
    hired_at: props.user?.hired_at ?? '',
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    phone: props.user?.phone ?? '',
    password: '',
    password_confirmation: '',
    role_id: props.user?.role?.id?.toString() ?? '',
    division_id: props.user?.division?.id?.toString() ?? '',
    position_id: props.user?.position?.id?.toString() ?? '',
    branch_id: props.user?.branch?.id?.toString() ?? '',
    manager_id: props.user?.manager?.id?.toString() ?? '',
    is_organization_manager: props.user?.is_organization_manager ?? false,
    status: props.user?.status ?? 'active',
});
const managers = ref<Option[]>([]);
const managersLoading = ref(false);
const managersError = ref(false);
let managerRequestId = 0;
const leaveGuard = useUnsavedChangesGuard(() => form.isDirty);

async function loadManagers(): Promise<void> {
    const requestId = ++managerRequestId;

    if (!form.division_id) {
        managers.value = [];
        form.manager_id = '';
        managersError.value = false;

        return;
    }

    const params = new URLSearchParams({ division_id: form.division_id });

    if (props.user) {
        params.set('exclude_user_id', props.user.id.toString());
    }

    managersLoading.value = true;
    managersError.value = false;

    try {
        const response = await fetch(`/users/manager-options?${params}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Manager options request failed.');
        }

        const options = (await response.json()) as Option[];
        if (requestId !== managerRequestId) {
            return;
        }

        managers.value = options;
        if (
            !managers.value.some(({ id }) => id.toString() === form.manager_id)
        ) {
            form.manager_id = '';
        }
    } catch {
        if (requestId === managerRequestId) {
            managersError.value = true;
        }
    } finally {
        if (requestId === managerRequestId) {
            managersLoading.value = false;
        }
    }
}

watch(
    () => form.division_id,
    (_, previous) => {
        if (previous !== undefined) {
            form.manager_id = '';
        }

        void loadManagers();
    },
    { immediate: true },
);

function submit(): void {
    if (props.user) {
        form.put(`/users/${props.user.id}`, {
            onSuccess: () => form.defaults(),
        });

        return;
    }

    form.post('/users', { onSuccess: () => form.defaults() });
}
</script>

<template>
    <Head :title="title" />

    <div class="app-page mx-auto max-w-5xl gap-8">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div class="max-w-2xl">
                <h1
                    class="text-2xl font-semibold tracking-tight text-foreground"
                >
                    {{ title }}
                </h1>
                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Tetapkan identitas, struktur organisasi, manager, dan akses
                    akun karyawan.
                </p>
            </div>
            <Button variant="outline" as-child>
                <Link href="/users">Kembali ke users</Link>
            </Button>
        </div>

        <form class="grid gap-8" @submit.prevent="submit">
            <FormErrorSummary :errors="form.errors" />
            <section
                class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border sm:p-6"
            >
                <h2 class="text-base font-semibold text-foreground">
                    Identitas akun
                </h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="employee_id">Employee ID</Label>
                        <Input
                            id="employee_id"
                            v-model="form.employee_id"
                            autocomplete="off"
                            placeholder="Contoh: KGJ-001"
                        />
                        <InputError :message="form.errors.employee_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="hired_at">Tanggal masuk</Label>
                        <Input
                            id="hired_at"
                            v-model="form.hired_at"
                            type="date"
                            :aria-invalid="Boolean(form.errors.hired_at)"
                        />
                        <InputError :message="form.errors.hired_at" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Status akun</Label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                        >
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak aktif</option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="name">Nama lengkap</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            required
                            autocomplete="name"
                            :aria-invalid="Boolean(form.errors.name)"
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Email kerja</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            :aria-invalid="Boolean(form.errors.email)"
                        />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phone">Nomor WhatsApp</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            autocomplete="tel"
                            placeholder="Contoh: 0812 3456 7890"
                            :aria-invalid="Boolean(form.errors.phone)"
                        />
                        <p class="text-xs text-muted-foreground">
                            Digunakan hanya untuk pengumuman WhatsApp.
                        </p>
                        <InputError :message="form.errors.phone" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password">{{
                            user ? 'Password baru' : 'Password'
                        }}</Label>
                        <PasswordInput
                            id="password"
                            v-model="form.password"
                            :required="!user"
                            autocomplete="new-password"
                            :aria-invalid="Boolean(form.errors.password)"
                        />
                        <p v-if="user" class="text-xs text-muted-foreground">
                            Kosongkan jika password tidak diubah.
                        </p>
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Konfirmasi password</Label
                        >
                        <PasswordInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :required="!user && Boolean(form.password)"
                            autocomplete="new-password"
                            :aria-invalid="
                                Boolean(form.errors.password_confirmation)
                            "
                        />
                        <InputError
                            :message="form.errors.password_confirmation"
                        />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="role_id">Role</Label>
                        <select
                            id="role_id"
                            v-model="form.role_id"
                            required
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                        >
                            <option value="" disabled>Pilih role</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.role_id" />
                    </div>
                </div>
            </section>

            <section
                class="rounded-xl bg-card p-5 shadow-sm ring-1 ring-border sm:p-6"
            >
                <h2 class="text-base font-semibold text-foreground">
                    Struktur organisasi
                </h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="division_id">Divisi</Label>
                        <select
                            id="division_id"
                            v-model="form.division_id"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                        >
                            <option value="">Belum ditetapkan</option>
                            <option
                                v-for="division in divisions"
                                :key="division.id"
                                :value="division.id"
                            >
                                {{ division.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.division_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="position_id">Jabatan</Label>
                        <select
                            id="position_id"
                            v-model="form.position_id"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                        >
                            <option value="">Belum ditetapkan</option>
                            <option
                                v-for="position in positions"
                                :key="position.id"
                                :value="position.id"
                            >
                                {{ position.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.position_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="branch_id">Cabang</Label>
                        <select
                            id="branch_id"
                            v-model="form.branch_id"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50"
                        >
                            <option value="">Belum ditetapkan</option>
                            <option
                                v-for="branch in branches"
                                :key="branch.id"
                                :value="branch.id"
                            >
                                {{ branch.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.branch_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="manager_id"
                            >Atasan langsung (Manager)</Label
                        >
                        <select
                            id="manager_id"
                            v-model="form.manager_id"
                            :disabled="!form.division_id || managersLoading"
                            aria-describedby="manager-help"
                            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <option value="">
                                {{
                                    !form.division_id
                                        ? 'Pilih divisi terlebih dahulu'
                                        : managersLoading
                                          ? 'Memuat pilihan...'
                                          : 'Belum ditetapkan'
                                }}
                            </option>
                            <option
                                v-for="manager in managers"
                                :key="manager.id"
                                :value="manager.id"
                            >
                                {{ manager.name
                                }}{{
                                    manager.employee_id
                                        ? ` · ${manager.employee_id}`
                                        : ''
                                }}
                            </option>
                        </select>
                        <p
                            id="manager-help"
                            class="text-xs text-muted-foreground"
                        >
                            Menentukan struktur pelaporan, bukan akses aplikasi.
                            Hanya akun aktif pada divisi yang sama yang dapat
                            dipilih.
                        </p>
                        <p
                            v-if="managersError"
                            class="text-xs text-destructive"
                            role="alert"
                        >
                            Pilihan Manager tidak dapat dimuat.
                            <button
                                type="button"
                                class="underline underline-offset-2"
                                @click="loadManagers"
                            >
                                Coba lagi
                            </button>
                        </p>
                        <InputError :message="form.errors.manager_id" />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="is_organization_manager"
                            >Dapat dipilih sebagai Atasan</Label
                        >
                        <label
                            class="flex min-h-9 w-fit cursor-pointer items-center gap-3 rounded-md px-1 text-sm text-foreground"
                            for="is_organization_manager"
                        >
                            <Checkbox
                                id="is_organization_manager"
                                :model-value="form.is_organization_manager"
                                @update:model-value="
                                    form.is_organization_manager =
                                        $event === true
                                "
                            />
                            Izinkan akun ini dipilih sebagai atasan langsung.
                        </label>
                        <p class="text-xs text-muted-foreground">
                            Hanya akun aktif pada divisi yang sama dan ditandai
                            sebagai Atasan yang muncul pada pilihan atasan.
                        </p>
                        <InputError
                            :message="form.errors.is_organization_manager"
                        />
                    </div>
                </div>
            </section>

            <div
                class="flex flex-col-reverse justify-end gap-3 border-t border-border pt-5 sm:flex-row"
            >
                <Button type="button" variant="outline" as-child
                    ><Link href="/users">Batal</Link></Button
                >
                <Button type="submit" :disabled="form.processing">{{
                    form.processing ? 'Menyimpan…' : 'Simpan user'
                }}</Button>
            </div>
        </form>
        <ConfirmActionDialog
            :open="leaveGuard.open.value"
            title="Keluar tanpa menyimpan?"
            description="Perubahan data karyawan yang belum disimpan akan hilang."
            confirm-label="Keluar tanpa menyimpan"
            destructive
            @update:open="!$event && leaveGuard.reset()"
            @confirm="leaveGuard.leaveWithoutSaving"
            @cancel="leaveGuard.reset"
        />
    </div>
</template>
