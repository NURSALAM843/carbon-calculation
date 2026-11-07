<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { login } from '@/routes';

defineOptions({
    layout: {
        title: 'Registrasi Ditutup',
        description:
            'Pendaftaran akun baru dikelola langsung oleh administrator sistem.',
    },
});
</script>

<template>
    <Head title="Registrasi Ditutup" />

    <div class="flex flex-col gap-6 text-center">
        <div class="rounded-lg border border-border bg-muted/30 p-6">
            <h2 class="text-base font-semibold text-foreground">
                Akses Mandiri Ditutup
            </h2>
            <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
                Pendaftaran akun sistem PT Nusantara Carbon & Agroforestry TBK
                dilakukan secara terpusat oleh Administrator. Silakan hubungi
                tim IT atau masuk menggunakan kredensial yang telah diberikan.
            </p>
        </div>

        <Link :href="login()">
            <Button
                class="w-full cursor-pointer bg-emerald-600 text-white hover:bg-emerald-700"
            >
                Kembali ke Halaman Masuk
            </Button>
        </Link>
    </div>
</template>
