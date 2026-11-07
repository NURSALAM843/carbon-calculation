<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { KeyRound, ShieldAlert, UserCheck } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import PasskeyVerify from '@/components/PasskeyVerify.vue';

defineOptions({
    layout: {
        title: 'Masuk ke Akun Anda',
        description: 'Masukkan email dan kata sandi untuk mengakses portofolio karbon',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const demoAccounts = [
    { label: 'Super Admin', email: 'admin@carbon.test', role: 'Super Admin' },
    { label: 'ESG Manager', email: 'manager@carbon.test', role: 'Sustainability Manager' },
    { label: 'Surveyor', email: 'surveyor@carbon.test', role: 'Field Surveyor' },
    { label: 'Auditor', email: 'auditor@carbon.test', role: 'Lead Auditor' },
];

const selectedDemo = ref<string | null>(null);

const fillAccount = (email: string) => {
    selectedDemo.value = email;
    const emailEl = document.getElementById('email') as HTMLInputElement | null;
    const passwordEl = document.getElementById('password') as HTMLInputElement | null;
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
        class="mb-4 text-center text-sm font-medium text-emerald-600"
    >
        {{ status }}
    </div>

    <PasskeyVerify />

    <!-- Demo Accounts Quick-Select Card -->
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-50/50 p-3.5 dark:bg-emerald-950/20">
        <div class="mb-2 flex items-center justify-between text-xs font-semibold text-emerald-900 dark:text-emerald-200">
            <span class="flex items-center gap-1.5">
                <KeyRound class="size-3.5 text-emerald-600 dark:text-emerald-400" />
                Pilih Akun Demo (Password: password)
            </span>
        </div>
        <div class="grid grid-cols-2 gap-1.5">
            <button
                v-for="acc in demoAccounts"
                :key="acc.email"
                type="button"
                @click="fillAccount(acc.email)"
                :class="[
                    'flex flex-col items-start rounded border px-2 py-1.5 text-left text-xs transition cursor-pointer',
                    selectedDemo === acc.email
                        ? 'border-emerald-600 bg-emerald-100/80 font-medium text-emerald-950 dark:border-emerald-500 dark:bg-emerald-900/60 dark:text-emerald-100'
                        : 'border-border/60 bg-background text-foreground hover:border-emerald-500/40 hover:bg-muted/40'
                ]"
            >
                <span class="font-semibold text-[11px]">{{ acc.label }}</span>
                <span class="truncate text-[10px] text-muted-foreground">{{ acc.email }}</span>
            </button>
        </div>
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-5">
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
                    placeholder="email@example.com"
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
                <Label for="remember" class="flex items-center space-x-2 text-xs">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Ingat saya di perangkat ini</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Masuk ke Aplikasi
            </Button>
        </div>

        <div class="text-center text-xs text-muted-foreground">
            Belum memiliki akses akun?
            <TextLink :href="register()" :tabindex="5">Daftarkan akun baru</TextLink>
        </div>
    </Form>
</template>
