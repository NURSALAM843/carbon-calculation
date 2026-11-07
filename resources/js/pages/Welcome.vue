<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Layers,
    Lock,
    ShieldCheck,
    Sprout,
    TreePine,
} from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard, login } from '@/routes';

const userRoles = [
    {
        title: 'Super Admin',
        roleKey: 'super_admin',
        email: 'admin@carbon.test',
        responsibilities:
            'Pengaturan organisasi, pengelolaan otorisasi peran, konfigurasi parameter IPCC, dan pemeliharaan master data konsesi.',
        badge: 'Tata Kelola Sistem',
    },
    {
        title: 'ESG & Sustainability Manager',
        roleKey: 'sustainability_manager',
        email: 'manager@carbon.test',
        responsibilities:
            'Inisiasi eksekusi kalkulasi karbon, pemantauan serapan emisi tahunan antar-blok, dan pengajuan audit formal.',
        badge: 'Pengelolaan Portofolio',
    },
    {
        title: 'Field Surveyor',
        roleKey: 'field_surveyor',
        email: 'surveyor@carbon.test',
        responsibilities:
            'Pencatatan sensus tegakan pohon hutan, data tinggi dan pelepah sawit, input uji lab tanah, dan dokumentasi foto geolokasi.',
        badge: 'Akuisisi Data Lapangan',
    },
    {
        title: 'Lead Carbon Auditor',
        roleKey: 'verifier_auditor',
        email: 'auditor@carbon.test',
        responsibilities:
            'Pemeriksaan kepatuhan standar MRV, verifikasi arsip bukti evidence vault, validasi perhitungan, dan penguncian neraca emisi.',
        badge: 'Verifikasi Independen',
    },
];

const methodologyPillars = [
    {
        icon: Layers,
        title: '5 Kolam Karbon Standar IPCC',
        category: 'IPCC Guidelines 2006 / 2019',
        description:
            'Perhitungan serentak pada lima reservoir karbon daratan: Biomassa Atas Permukaan (AGB), Biomassa Bawah Permukaan (BGB), Nekromasa Kayu Mati (Deadwood), Serasah Organik (Litter), dan Karbon Organik Tanah (SOC) pada kedalaman 0-30 cm.',
    },
    {
        icon: Sprout,
        title: 'Matriks Mutasi Fenologi Sawit',
        category: 'SNI 7724:2019 & RSPO PalmGHG',
        description:
            'Kalkulasi bertingkat mengikuti tahapan pertumbuhan perkebunan: Tanaman Belum Menghasilkan (TBM 0-3 tahun), TM Muda (4-8 tahun), TM Prima (9-18 tahun), dan TM Tua (>18 tahun) yang mencakup biomassa batang dan residu pelepah.',
    },
    {
        icon: TreePine,
        title: 'Konsesi Restorasi & Multi-Ekosistem',
        category: 'Model Alometrik Kawasan Tropis',
        description:
            'Dukungan model alometrik spesifik untuk konsesi perkebunan kelapa sawit berkelanjutan, tegakan hutan hujan tropis dataran rendah, kawasan restorasi ekosistem hutan gambut, dan vegetasi mangrove pesisir.',
    },
    {
        icon: ShieldCheck,
        title: 'Jejak Audit MRV Terstruktur',
        category: 'Kepatuhan Regulasi SRN-PPI KLHK',
        description:
            'Siklus penjaminan kualitas berbasis empat status bertahap: Draft, Siap Review, Terverifikasi, dan Terkunci. Dilengkapi tautan bukti geolokasi GPS serta sertifikat hasil uji laboratorium terakreditasi.',
    },
];

const workflowSteps = [
    {
        number: '01',
        title: 'Pengumpulan Data Lapangan',
        description:
            'Sensus diameter setinggi dada (DBH), pencatatan plot vegetasi sawit, dan pengambilan sampel komposit tanah.',
    },
    {
        number: '02',
        title: 'Komputasi Mesin Alometrik',
        description:
            'Penerapan faktor emisi, konversi fraksi karbon (0.47), dan kalkulasi cadangan CO2 equivalen per hektar.',
    },
    {
        number: '03',
        title: 'Pemeriksaan Bukti & Audit',
        description:
            'Verifikasi kesesuaian dokumen laboratorium, audit foto geolokasi, dan validasi kepatuhan metodologi oleh auditor.',
    },
    {
        number: '04',
        title: 'Neraca Karbon Terkunci',
        description:
            'Penerbitan neraca cadangan karbon final yang terkunci dan siap dilaporkan ke otoritas iklim dan standar keberlanjutan.',
    },
];
</script>

<template>
    <div
        class="min-h-screen bg-background text-foreground antialiased selection:bg-emerald-600 selection:text-white"
    >
        <Head
            title="PT Nusantara Carbon & Agroforestry TBK | Platform Akuntansi Karbon Terpadu"
        />

        <!-- Navigation Header -->
        <header
            class="sticky top-0 z-40 border-b border-border/80 bg-background/95 backdrop-blur-sm"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <AppLogoIcon class="size-9 rounded-lg shadow-xs" />
                    <div>
                        <span
                            class="block text-sm font-bold tracking-tight text-foreground sm:text-base"
                        >
                            PT Nusantara Carbon & Agroforestry TBK
                        </span>
                        <span class="block text-[11px] text-muted-foreground">
                            Sistem Akuntansi Karbon & Verifikasi MRV Terpadu
                        </span>
                    </div>
                </div>

                <nav class="hidden items-center gap-6 md:flex">
                    <a
                        href="#metodologi"
                        class="text-xs font-medium text-muted-foreground transition hover:text-foreground"
                    >
                        Metodologi IPCC
                    </a>
                    <a
                        href="#alur-kerja"
                        class="text-xs font-medium text-muted-foreground transition hover:text-foreground"
                    >
                        Alur Kerja MRV
                    </a>
                    <a
                        href="#kredensial"
                        class="text-xs font-medium text-muted-foreground transition hover:text-foreground"
                    >
                        Akun Uji Coba
                    </a>
                </nav>

                <div class="flex items-center gap-3">
                    <Link v-if="$page.props.auth?.user" :href="dashboard()">
                        <Button
                            size="sm"
                            class="cursor-pointer gap-1.5 bg-emerald-600 text-white hover:bg-emerald-700"
                        >
                            Buka Dashboard
                            <ArrowRight class="size-3.5" />
                        </Button>
                    </Link>
                    <Link v-else :href="login()">
                        <Button
                            size="sm"
                            class="cursor-pointer gap-1.5 bg-emerald-600 text-white hover:bg-emerald-700"
                        >
                            Masuk ke Sistem
                            <ArrowRight class="size-3.5" />
                        </Button>
                    </Link>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero Section -->
            <section
                class="border-b border-border/60 bg-muted/20 py-16 sm:py-24"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-800 dark:text-emerald-300"
                        >
                            <span
                                >Sistem Enterprise Inventarisasi Emisi &
                                Cadangan Karbon</span
                            >
                        </div>

                        <h1
                            class="text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl lg:text-5xl lg:leading-tight"
                        >
                            Platform Akuntansi Karbon dan Penjaminan Mutu MRV
                            Multi-Ekosistem
                        </h1>

                        <p
                            class="mt-5 text-sm text-muted-foreground sm:text-base sm:leading-relaxed"
                        >
                            Infrastruktur komputasi cadangan stok karbon
                            berbasis metodologi IPCC 2006/2019 dan SNI
                            7724:2019. Mengakomodasi dinamika fenologi
                            perkebunan kelapa sawit serta konsesi restorasi
                            hutan alam tropis dan mangrove.
                        </p>

                        <div
                            class="mt-8 flex flex-wrap items-center justify-center gap-3"
                        >
                            <Link
                                v-if="$page.props.auth?.user"
                                :href="dashboard()"
                            >
                                <Button
                                    size="lg"
                                    class="cursor-pointer gap-2 bg-emerald-600 px-6 text-white hover:bg-emerald-700"
                                >
                                    Buka Dashboard
                                    <ArrowRight class="size-4" />
                                </Button>
                            </Link>
                            <Link v-else :href="login()">
                                <Button
                                    size="lg"
                                    class="cursor-pointer gap-2 bg-emerald-600 px-6 text-white hover:bg-emerald-700"
                                >
                                    Masuk ke Portal Sistem
                                    <ArrowRight class="size-4" />
                                </Button>
                            </Link>

                            <a href="#kredensial">
                                <Button
                                    variant="outline"
                                    size="lg"
                                    class="cursor-pointer px-5"
                                >
                                    Lihat Akun Uji Coba
                                </Button>
                            </a>
                        </div>

                        <!-- Standards Pill Row -->
                        <div
                            class="mt-10 flex flex-wrap items-center justify-center gap-2 text-xs text-muted-foreground"
                        >
                            <span class="font-medium text-foreground"
                                >Kepatuhan Standar:</span
                            >
                            <span
                                class="rounded border border-border bg-background px-2.5 py-1"
                                >IPCC Guidelines 2006 / 2019</span
                            >
                            <span
                                class="rounded border border-border bg-background px-2.5 py-1"
                                >SNI 7724:2019</span
                            >
                            <span
                                class="rounded border border-border bg-background px-2.5 py-1"
                                >RSPO PalmGHG & ISPO GHG</span
                            >
                            <span
                                class="rounded border border-border bg-background px-2.5 py-1"
                                >SRN-PPI KLHK</span
                            >
                        </div>
                    </div>
                </div>
            </section>

            <!-- Methodology Pillars Section -->
            <section
                id="metodologi"
                class="border-b border-border/60 py-16 sm:py-20"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2
                            class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                        >
                            Arsitektur Penghitungan Karbon & Ekosistem
                        </h2>
                        <p
                            class="mt-2 text-xs text-muted-foreground sm:text-sm"
                        >
                            Dirancang untuk memenuhi kepatuhan audit karbon
                            korporasi kehutanan dan perkebunan berkelanjutan.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 sm:grid-cols-2">
                        <Card
                            v-for="pillar in methodologyPillars"
                            :key="pillar.title"
                            class="border-border/70 transition hover:border-emerald-500/50"
                        >
                            <CardHeader class="pb-3">
                                <div class="flex items-center justify-between">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                    >
                                        <component
                                            :is="pillar.icon"
                                            class="size-5"
                                        />
                                    </div>
                                    <span
                                        class="text-[11px] font-medium text-muted-foreground"
                                    >
                                        {{ pillar.category }}
                                    </span>
                                </div>
                                <CardTitle
                                    class="mt-3 text-base font-semibold text-foreground"
                                >
                                    {{ pillar.title }}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p
                                    class="text-xs leading-relaxed text-muted-foreground"
                                >
                                    {{ pillar.description }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>

            <!-- Workflow Section -->
            <section
                id="alur-kerja"
                class="border-b border-border/60 bg-muted/10 py-16 sm:py-20"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2
                            class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                        >
                            Alur Siklus Penjaminan Kualitas MRV
                        </h2>
                        <p
                            class="mt-2 text-xs text-muted-foreground sm:text-sm"
                        >
                            Tahapan verifikasi berjenjang untuk memastikan
                            integritas data neraca emisi sebelum dilaporkan.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div
                            v-for="step in workflowSteps"
                            :key="step.number"
                            class="rounded-lg border border-border/70 bg-card p-5"
                        >
                            <span
                                class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400"
                            >
                                {{ step.number }}
                            </span>
                            <h3
                                class="mt-2 text-sm font-semibold text-foreground"
                            >
                                {{ step.title }}
                            </h3>
                            <p
                                class="mt-2 text-xs leading-relaxed text-muted-foreground"
                            >
                                {{ step.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Roles & Demo Access Section -->
            <section id="kredensial" class="py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <div
                            class="mb-3 inline-flex items-center gap-1.5 rounded border border-border bg-muted/40 px-3 py-1 text-xs text-muted-foreground"
                        >
                            <Lock
                                class="size-3 text-emerald-600 dark:text-emerald-400"
                            />
                            <span>Kontrol Akses Berbasis Peran (RBAC)</span>
                        </div>
                        <h2
                            class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                        >
                            Peran Pengguna & Kredensial Uji Coba
                        </h2>
                        <p
                            class="mt-2 text-xs text-muted-foreground sm:text-sm"
                        >
                            Kata sandi terpadu untuk semua akun uji coba:
                            <code
                                class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs text-foreground"
                                >password</code
                            >
                        </p>
                    </div>

                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <Card
                            v-for="role in userRoles"
                            :key="role.email"
                            class="flex flex-col justify-between border-border/70 bg-card transition hover:border-emerald-500/50"
                        >
                            <CardHeader class="pb-3">
                                <div
                                    class="mb-2 w-fit rounded border border-border bg-muted/40 px-2 py-0.5 text-[10px] font-medium text-foreground"
                                >
                                    {{ role.badge }}
                                </div>
                                <CardTitle
                                    class="text-base font-bold text-foreground"
                                >
                                    {{ role.title }}
                                </CardTitle>
                                <div
                                    class="mt-1.5 rounded bg-muted/50 p-1.5 font-mono text-xs text-muted-foreground"
                                >
                                    {{ role.email }}
                                </div>
                            </CardHeader>
                            <CardContent class="pt-0">
                                <p
                                    class="mb-4 text-xs leading-relaxed text-muted-foreground"
                                >
                                    {{ role.responsibilities }}
                                </p>
                                <Link :href="login()">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="w-full cursor-pointer text-xs"
                                    >
                                        Masuk dengan Akun Ini
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-border/80 bg-background py-8">
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-2.5">
                    <AppLogoIcon class="size-6 rounded" />
                    <span class="text-xs text-muted-foreground">
                        PT Nusantara Carbon & Agroforestry TBK. Hak cipta
                        dilindungi undang-undang.
                    </span>
                </div>
                <div
                    class="flex items-center gap-4 text-xs text-muted-foreground"
                >
                    <Link :href="login()" class="hover:text-foreground"
                        >Masuk Portal</Link
                    >
                    <a href="#metodologi" class="hover:text-foreground"
                        >Metodologi</a
                    >
                    <a href="#alur-kerja" class="hover:text-foreground"
                        >Alur Kerja MRV</a
                    >
                    <a href="#kredensial" class="hover:text-foreground"
                        >Akun Uji Coba</a
                    >
                </div>
            </div>
        </footer>
    </div>
</template>
