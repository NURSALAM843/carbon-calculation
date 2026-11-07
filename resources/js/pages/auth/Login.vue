<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { KeyRound, Check } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Masuk ke Portal MRV',
        description:
            'Masukkan kredensial akun untuk mengakses platform akuntansi karbon',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const demoAccounts = [
    {
        label: 'Super Admin',
        email: 'admin@carbon.test',
        role: 'Super Admin',
        badge: 'Konfigurasi & Akses',
    },
    {
        label: 'ESG Manager',
        email: 'manager@carbon.test',
        role: 'Sustainability Manager',
        badge: 'Simulasi & Portofolio',
    },
    {
        label: 'Field Surveyor',
        email: 'surveyor@carbon.test',
        role: 'Field Surveyor',
        badge: 'Sensus & Sampel Lab',
    },
    {
        label: 'Lead Auditor',
        email: 'auditor@carbon.test',
        role: 'Lead Auditor',
        badge: 'Verifikasi & Kunci',
    },
];

const selectedDemo = ref<string | null>(null);

const fillAccount = (email: string) => {
    selectedDemo.value = email;
    const emailEl = document.getElementById('email') as HTMLInputElement | null;
    const passwordEl = document.getElementById(
        'password',
    ) as HTMLInputElement | null;
    if (emailEl) {
        emailEl.value = email;
        emailEl.dispatchEvent(new Event('input', { bubbles: true }));
        emailEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (passwordEl) {
        passwordEl.value = 'password';
        passwordEl.dispatchEvent(new Event('input', { bubbles: true }));
        passwordEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
};
</script>

<template>
    <Head title="Masuk | Nusantara Carbon" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400"
    >
        {{ status }}
    </div>

    <!-- Demo Accounts Quick-Select Card -->
    <div class="rounded-lg border border-border bg-muted/40 p-3.5">
        <div class="mb-2.5 flex items-center justify-between">
            <span
                class="flex items-center gap-1.5 text-xs font-semibold text-foreground"
            >
                <KeyRound
                    class="size-3.5 text-emerald-600 dark:text-emerald-400"
                />
                Pilih Akun Demo (Uji Coba)
            </span>
            <span
                class="rounded bg-muted px-1.5 py-0.5 font-mono text-[10px] text-muted-foreground"
            >
                password: password
            </span>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <button
                v-for="acc in demoAccounts"
                :key="acc.email"
                type="button"
                @click="fillAccount(acc.email)"
                :class="[
                    'flex cursor-pointer flex-col items-start rounded-md border p-2 text-left transition',
                    selectedDemo === acc.email
                        ? 'border-emerald-600 bg-emerald-50 text-emerald-950 dark:border-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-100'
                        : 'border-border/80 bg-background text-foreground hover:border-border hover:bg-muted/60',
                ]"
            >
                <div class="flex w-full items-center justify-between">
                    <span class="text-xs leading-none font-semibold">{{
                        acc.label
                    }}</span>
                    <Check
                        v-if="selectedDemo === acc.email"
                        class="size-3 text-emerald-600 dark:text-emerald-400"
                    />
                </div>
                <span
                    class="mt-1 font-mono text-[10px] text-muted-foreground"
                    >{{ acc.email }}</span
                >
                <span class="mt-1 text-[10px] text-muted-foreground/80">{{
                    acc.badge
                }}</span>
            </button>
        </div>
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-4"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="email">Alamat Email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    v-focus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="nama@nusantaracarbon.co.id"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Kata Sandi</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs"
                        :tabindex="5"
                    >
                        Lupa kata sandi?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label
                    for="remember"
                    class="flex items-center space-x-2 text-xs text-muted-foreground"
                >
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Ingat saya di perangkat ini</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-1 w-full cursor-pointer bg-emerald-600 text-white hover:bg-emerald-700"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Masuk ke Sistem
            </Button>
        </div>

        <div
            class="rounded-md border border-border/60 bg-muted/20 p-2.5 text-center text-xs text-muted-foreground"
        >
            Akses sistem dibatasi untuk personel terotorisasi PT Nusantara
            Carbon & Agroforestry TBK.
        </div>
    </Form>
</template>
