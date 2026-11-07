<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowRight,
    Calculator,
    CheckCircle2,
    Database,
    FileCheck,
    FileSpreadsheet,
    Layers,
    Leaf,
    Lock,
    MapPin,
    ShieldCheck,
    Sprout,
    TestTube,
    TreePine,
} from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard, login } from '@/routes';

const userRoles = [
    {
        title: 'Super Admin',
        role: 'super_admin',
        email: 'admin@carbon.test',
        description: 'Pengaturan organisasi, pengelolaan hak akses peran, serta konfigurasi metodologi dan parameter IPCC.',
        badge: 'Tata Kelola & Konfigurasi',
        badgeClass: 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200',
    },
    {
        title: 'ESG & Sustainability Manager',
        role: 'sustainability_manager',
        email: 'manager@carbon.test',
        description: 'Inisiasi dan simulasi kalkulasi karbon, pemantauan serapan tahunan, serta pengajuan persetujuan audit.',
        badge: 'Pengelolaan Portofolio',
        badgeClass: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    },
    {
        title: 'Field Surveyor',
        role: 'field_surveyor',
        email: 'surveyor@carbon.test',
        description: 'Pencatatan sensus tegakan pohon hutan, survei pelepah dan tinggi sawit, sampel lab tanah, dan unggah bukti lapangan.',
        badge: 'Pengumpulan Data Lapangan',
        badgeClass: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    },
    {
        title: 'Lead Carbon Auditor',
        role: 'verifier_auditor',
        email: 'auditor@carbon.test',
        description: 'Pemeriksaan kepatuhan standar MRV, telaah arsip bukti (evidence vault), verifikasi dan penguncian neraca karbon.',
        badge: 'Verifikasi Independen',
        badgeClass: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300',
    },
];

const methodologyPillars = [
    {
        icon: Layers,
        title: '5 Kolam Karbon Standar IPCC',
        description:
            'Menghitung Above Ground Biomass (AGB), Below Ground Biomass (BGB), Nekromasa Kayu Mati (Deadwood), Serasah Organik (Litter), dan Karbon Organik Tanah (SOC) secara simultan.',
    },
    {
        icon: Sprout,
        title: 'Matriks Mutasi Fenologi Sawit',
        description:
            'Dinamika alometrik bertingkat mengikuti fase pertumbuhan: Tanaman Belum Menghasilkan (TBM), TM Muda, TM Prima, dan TM Tua yang mempengaruhi biomassa batang dan residu pelepah.',
    },
    {
        icon: TreePine,
        title: 'Cakupan Multi-Ekosistem',
        description:
            'Formula allometrik polimorfik terkalibrasi untuk perkebunan kelapa sawit, tegakan hutan hujan tropis dataran rendah, konsesi restorasi ekosistem, hutan tanaman industri, dan mangrove.',
    },
    {
        icon: ShieldCheck,
        title: 'Jejak Audit & Validasi MRV',
        description:
            'Siklus hidup kalkulasi tertata: status Draft, Pengajuan Review, hingga Persetujuan Terkunci (Locked) dengan tautan langsung ke foto geolokasi dan sertifikasi laboratorium.',
    },
    {
        icon: MapPin,
        title: 'Pemetaan Spasial GIS Terintegrasi',
        description:
            'Visualisasi interaktif batas blok konsesi lahan, sebaran cadangan t CO2e per hektar, dan zonasi ekosistem berbasis koordinat spasial poligon.',
    },
    {
        icon: FileSpreadsheet,
        title: 'Laporan Siap Audit & Ekspor Data',
        description:
            'Ringkasan neraca emisi terstruktur, perbandingan antar-sesi pengukuran, serta fasilitas ekspor berkas audit dalam format tabular CSV yang kompatibel dengan standar SRN-PPI.',
    },
];
</script>

<template>
    <div class="min-h-screen bg-background text-foreground antialiased selection:bg-emerald-500 selection:text-white">
        <Head title="Nusantara Carbon | Platform Akuntansi Karbon Multi-Ekosistem" />

        <!-- Navigation Header -->
        <header class="sticky top-0 z-40 border-b border-border/80 bg-background/95 backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <AppLogoIcon class="size-9 rounded-lg shadow-xs" />
                    <div>
                        <span class="block text-sm font-bold tracking-tight text-foreground sm:text-base">
                            PT Nusantara Carbon & Agroforestry TBK
                        </span>
                        <span class="block text-[11px] text-muted-foreground">
                            Sistem Akuntansi Karbon & Inventarisasi MRV
                        </span>
                    </div>
                </div>

                <nav class="hidden items-center gap-6 md:flex">
                    <a href="#fitur" class="text-xs font-medium text-muted-foreground transition hover:text-foreground">
                        Fitur Utama
                    </a>
                    <a href="#metodologi" class="text-xs font-medium text-muted-foreground transition hover:text-foreground">
                        Metodologi IPCC
                    </a>
                    <a href="#roles" class="text-xs font-medium text-muted-foreground transition hover:text-foreground">
                        Akun Uji Coba
                    </a>
                </nav>

                <div class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="dashboard()"
                    >
                        <Button size="sm" class="gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white">
                            Masuk ke Dashboard
                            <ArrowRight class="size-3.5" />
                        </Button>
                    </Link>
                    <Link
                        v-else
                        :href="login()"
                    >
                        <Button size="sm" class="gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white">
                            Masuk ke Sistem
                            <ArrowRight class="size-3.5" />
                        </Button>
                    </Link>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero Section -->
            <section class="relative border-b border-border/60 bg-gradient-to-b from-emerald-500/5 via-background to-background py-16 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                            <Leaf class="size-3.5 text-emerald-600 dark:text-emerald-400" />
                            <span>MRV Karbon Kompatibel IPCC 2006/2019 & SRN-PPI KLHK</span>
                        </div>

                        <h1 class="text-3xl font-extrabold tracking-tight text-foreground sm:text-5xl sm:leading-tight">
                            Platform Akuntansi Karbon & Verifikasi Multi-Ekosistem
                        </h1>

                        <p class="mt-5 text-base text-muted-foreground sm:text-lg sm:leading-relaxed">
                            Aplikasi komputasi cadangan stok karbon berbasis 5 kolam karbon IPCC, mengakomodasi dinamika fenologi perkebunan kelapa sawit berkelanjutan serta konsesi restorasi hutan tropis dan mangrove.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                            <Link v-if="$page.props.auth?.user" :href="dashboard()">
                                <Button size="lg" class="gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6">
                                    Buka Dashboard
                                    <ArrowRight class="size-4" />
                                </Button>
                            </Link>
                            <Link v-else :href="login()">
                                <Button size="lg" class="gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6">
                                    Masuk ke Akun
                                    <ArrowRight class="size-4" />
                                </Button>
                            </Link>

                            <a href="#roles">
                                <Button variant="outline" size="lg" class="px-5">
                                    Lihat Akun Uji Coba
                                </Button>
                            </a>
                        </div>

                        <!-- Standards Pill Row -->
                        <div class="mt-10 flex flex-wrap items-center justify-center gap-2 text-xs text-muted-foreground">
                            <span class="font-medium text-foreground">Standar Acuan:</span>
                            <span class="rounded-md border border-border bg-muted/40 px-2 py-0.5">IPCC Guidelines 2006/2019</span>
                            <span class="rounded-md border border-border bg-muted/40 px-2 py-0.5">SNI 7724:2019</span>
                            <span class="rounded-md border border-border bg-muted/40 px-2 py-0.5">ISPO GHG & RSPO PalmGHG</span>
                            <span class="rounded-md border border-border bg-muted/40 px-2 py-0.5">SRN-PPI KLHK</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Methodology Pillars Section -->
            <section id="metodologi" class="border-b border-border/60 py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Arsitektur Penghitungan Karbon & Ekosistem
                        </h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Dirancang khusus untuk mendukung akuntabilitas korporasi kehutanan dan perkebunan berkelanjutan.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Card
                            v-for="pillar in methodologyPillars"
                            :key="pillar.title"
                            class="border-border/60 transition-all hover:border-emerald-500/50 hover:shadow-xs"
                        >
                            <CardHeader class="pb-3">
                                <div class="mb-3 flex size-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <component :is="pillar.icon" class="size-5" />
                                </div>
                                <CardTitle class="text-base font-semibold leading-snug">
                                    {{ pillar.title }}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-xs leading-relaxed text-muted-foreground">
                                    {{ pillar.description }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>

            <!-- Roles & Demo Access Section -->
            <section id="roles" class="py-16 sm:py-20 bg-muted/20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <div class="inline-flex items-center gap-1.5 rounded-full border border-border bg-card px-3 py-1 text-xs text-muted-foreground mb-3">
                            <Lock class="size-3 text-emerald-600" />
                            <span>Role-Based Access Control (RBAC)</span>
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Peran Pengguna & Kredensial Uji Coba
                        </h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Password default untuk seluruh akun demo di bawah ini adalah: <code class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs text-foreground">password</code>
                        </p>
                    </div>

                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <Card
                            v-for="role in userRoles"
                            :key="role.email"
                            class="flex flex-col justify-between border-border/70 bg-card transition hover:border-emerald-500/50"
                        >
                            <CardHeader class="pb-3">
                                <Badge variant="outline" :class="['w-fit text-[11px] mb-2', role.badgeClass]">
                                    {{ role.badge }}
                                </Badge>
                                <CardTitle class="text-base font-bold text-foreground">
                                    {{ role.title }}
                                </CardTitle>
                                <div class="mt-1 text-xs text-muted-foreground font-mono bg-muted/50 rounded p-1">
                                    {{ role.email }}
                                </div>
                            </CardHeader>
                            <CardContent class="pt-0">
                                <p class="text-xs leading-relaxed text-muted-foreground mb-4">
                                    {{ role.description }}
                                </p>
                                <Link :href="login()">
                                    <Button variant="outline" size="sm" class="w-full text-xs">
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
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8">
                <div class="flex items-center gap-2.5">
                    <AppLogoIcon class="size-6 rounded" />
                    <span class="text-xs text-muted-foreground">
                        PT Nusantara Carbon & Agroforestry TBK. Hak cipta dilindungi undang-undang.
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-muted-foreground">
                    <Link :href="login()" class="hover:text-foreground">Masuk</Link>
                    <a href="#metodologi" class="hover:text-foreground">Metodologi</a>
                    <a href="#roles" class="hover:text-foreground">Akun Uji Coba</a>
                </div>
            </div>
        </footer>
    </div>
</template>
