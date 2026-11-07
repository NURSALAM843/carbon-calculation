<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowUpRight,
    Calculator,
    CheckCircle2,
    Clock,
    FileSpreadsheet,
    Flame,
    Layers,
    Leaf,
    MapPin,
    ShieldAlert,
    Sprout,
    TreePine,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Props {
    metrics: {
        total_co2e_stock: number;
        total_carbon_stock: number;
        total_assessed_area_ha: number;
        net_annual_sequestration: number;
        avg_density_co2e_ha: number;
    };
    pools_composition: {
        AGB: number;
        BGB: number;
        DEADWOOD: number;
        LITTER: number;
        SOIL: number;
    };
    ecosystems: Array<{
        ecosystem_type: string;
        block_count: number;
        total_area_ha: number;
        total_co2e_t: number;
    }>;
    palm_stages: Array<{
        block_code: string;
        block_name: string;
        stage: string;
        age_years: number;
        density_ha: number;
        height_m: number;
    }>;
    recent_runs: Array<{
        id: string;
        period_start: string;
        period_end: string;
        ecosystem_type: string;
        status: string;
        land_block?: { name: string; code: string };
        estate?: { name: string };
        methodology_version?: { methodology?: { name: string } };
    }>;
    estates_count: number;
    blocks_count: number;
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
        ],
    },
});

const formatNumber = (num: number) =>
    new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num);

const getEcosystemBadge = (type: string) => {
    switch (type) {
        case 'oil_palm':
            return {
                label: 'Kelapa Sawit',
                class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300',
            };
        case 'natural_forest':
            return {
                label: 'Hutan Alam',
                class: 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300 border-teal-300',
            };
        case 'hti_plantation':
            return {
                label: 'HTI Timber',
                class: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300',
            };
        case 'mangrove':
            return {
                label: 'Mangrove',
                class: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300 border-cyan-300',
            };
        default:
            return { label: type, class: 'bg-gray-100 text-gray-800' };
    }
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'approved':
            return {
                label: 'Disetujui (Locked)',
                class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
            };
        case 'pending_approval':
            return {
                label: 'Pending Review',
                class: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
            };
        case 'rejected':
            return {
                label: 'Perlu Revisi',
                class: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
            };
        default:
            return { label: 'Draft', class: 'bg-muted text-muted-foreground' };
    }
};
</script>

<template>
    <div class="flex flex-col gap-6 p-6">
        <Head title="Dashboard Karbon Multi-Ekosistem" />

        <!-- Header & Action Buttons -->
        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight text-foreground md:text-3xl"
                >
                    Portofolio Karbon & Dekarbonisasi
                </h1>
                <p class="text-sm text-muted-foreground">
                    Sistem akuntansi cadangan dan penyerapan karbon
                    multi-ekosistem dengan modul mutasi sawit.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <Button as-child variant="outline" size="sm">
                    <Link href="/gis">
                        <MapPin class="mr-2 size-4 text-emerald-600" />
                        Peta Spasial GIS
                    </Link>
                </Button>
                <Button as-child size="sm">
                    <Link href="/calculations/create">
                        <Calculator class="mr-2 size-4" />
                        Kalkulasi Baru
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Top 4 KPI Metrics -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Carbon Stock -->
            <Card
                class="border-border/60 shadow-xs transition-colors hover:border-emerald-500/40"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Total Cadangan Karbon</CardTitle
                    >
                    <Leaf class="size-4 text-emerald-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ formatNumber(metrics.total_co2e_stock) }}
                        <span class="text-xs font-normal text-muted-foreground"
                            >t CO₂e</span
                        >
                    </div>
                    <p
                        class="mt-1 flex items-center gap-1 text-xs text-muted-foreground"
                    >
                        <span
                            >{{ formatNumber(metrics.total_carbon_stock) }} ton
                            C</span
                        >
                        <span class="font-semibold text-emerald-600"
                            >• Terverifikasi</span
                        >
                    </p>
                </CardContent>
            </Card>

            <!-- Net Annual Sequestration -->
            <Card
                class="border-border/60 shadow-xs transition-colors hover:border-emerald-500/40"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Serapan Bersih Tahunan</CardTitle
                    >
                    <Activity class="size-4 text-teal-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-emerald-600">
                        +{{ formatNumber(metrics.net_annual_sequestration) }}
                        <span class="text-xs font-normal text-muted-foreground"
                            >t CO₂e/thn</span
                        >
                    </div>
                    <p
                        class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-600"
                    >
                        <ArrowUpRight class="size-3" /> Net Carbon Sink (Laju
                        Akumulasi)
                    </p>
                </CardContent>
            </Card>

            <!-- Average Stock Density -->
            <Card
                class="border-border/60 shadow-xs transition-colors hover:border-emerald-500/40"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Kepadatan Cadangan Rata-rata</CardTitle
                    >
                    <Layers class="size-4 text-amber-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ formatNumber(metrics.avg_density_co2e_ha) }}
                        <span class="text-xs font-normal text-muted-foreground"
                            >t CO₂e/ha</span
                        >
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Luas Terkelola:
                        {{ formatNumber(metrics.total_assessed_area_ha) }}
                        ha
                    </p>
                </CardContent>
            </Card>

            <!-- Estate & Block Coverage -->
            <Card
                class="border-border/60 shadow-xs transition-colors hover:border-emerald-500/40"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium text-muted-foreground"
                        >Cakupan Wilayah</CardTitle
                    >
                    <TreePine class="size-4 text-indigo-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ blocks_count }}
                        <span class="text-xs font-normal text-muted-foreground"
                            >Blok</span
                        >
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Tersebar di {{ estates_count }} Unit Konsesi/Estate
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- 5 Carbon Pools Breakdown & Ecosystem Distribution -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <!-- 5 IPCC Carbon Pools Progress Bars -->
            <Card class="border-border/60 lg:col-span-2">
                <CardHeader>
                    <CardTitle
                        class="flex items-center gap-2 text-lg font-semibold"
                    >
                        <Layers class="size-5 text-emerald-600" />
                        Komposisi 5 Kolam Karbon IPCC (IPCC 5 Carbon Pools)
                    </CardTitle>
                    <CardDescription>
                        Distribusi akumulasi setara emisi tersimpan pada seluruh
                        pool vegetasi dan lapisan tanah.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Soil Pool -->
                    <div>
                        <div
                            class="mb-1 flex justify-between text-sm font-medium"
                        >
                            <span class="flex items-center gap-1.5"
                                ><span
                                    class="size-2.5 rounded-full bg-amber-700"
                                ></span>
                                Karbon Tanah (Soil Organic Carbon - SOC)</span
                            >
                            <span class="font-bold"
                                >{{ formatNumber(pools_composition.SOIL) }} t
                                CO₂e</span
                            >
                        </div>
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-amber-700"
                                :style="{
                                    width: `${Math.min(100, (pools_composition.SOIL / (metrics.total_co2e_stock || 1)) * 100)}%`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- AGB Pool -->
                    <div>
                        <div
                            class="mb-1 flex justify-between text-sm font-medium"
                        >
                            <span class="flex items-center gap-1.5"
                                ><span
                                    class="size-2.5 rounded-full bg-emerald-600"
                                ></span>
                                Biomassa Atas Permukaan (AGB - Batang &
                                Tajuk)</span
                            >
                            <span class="font-bold"
                                >{{ formatNumber(pools_composition.AGB) }} t
                                CO₂e</span
                            >
                        </div>
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-emerald-600"
                                :style="{
                                    width: `${Math.min(100, (pools_composition.AGB / (metrics.total_co2e_stock || 1)) * 100)}%`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- BGB Pool -->
                    <div>
                        <div
                            class="mb-1 flex justify-between text-sm font-medium"
                        >
                            <span class="flex items-center gap-1.5"
                                ><span
                                    class="size-2.5 rounded-full bg-teal-500"
                                ></span>
                                Biomassa Bawah Permukaan (BGB - Perakaran)</span
                            >
                            <span class="font-bold"
                                >{{ formatNumber(pools_composition.BGB) }} t
                                CO₂e</span
                            >
                        </div>
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-teal-500"
                                :style="{
                                    width: `${Math.min(100, (pools_composition.BGB / (metrics.total_co2e_stock || 1)) * 100)}%`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- Litter Pool -->
                    <div>
                        <div
                            class="mb-1 flex justify-between text-sm font-medium"
                        >
                            <span class="flex items-center gap-1.5"
                                ><span
                                    class="size-2.5 rounded-full bg-yellow-600"
                                ></span>
                                Serasah Organik & Pelepah Pangkasan
                                (Litter)</span
                            >
                            <span class="font-bold"
                                >{{ formatNumber(pools_composition.LITTER) }} t
                                CO₂e</span
                            >
                        </div>
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-yellow-600"
                                :style="{
                                    width: `${Math.min(100, (pools_composition.LITTER / (metrics.total_co2e_stock || 1)) * 100)}%`,
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- Deadwood Pool -->
                    <div>
                        <div
                            class="mb-1 flex justify-between text-sm font-medium"
                        >
                            <span class="flex items-center gap-1.5"
                                ><span
                                    class="size-2.5 rounded-full bg-slate-500"
                                ></span>
                                Kayu Mati / Nekromasa (Deadwood)</span
                            >
                            <span class="font-bold"
                                >{{
                                    formatNumber(pools_composition.DEADWOOD)
                                }}
                                t CO₂e</span
                            >
                        </div>
                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-slate-500"
                                :style="{
                                    width: `${Math.min(100, (pools_composition.DEADWOOD / (metrics.total_co2e_stock || 1)) * 100)}%`,
                                }"
                            ></div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Ecosystem Portfolio Breakdown -->
            <Card class="border-border/60">
                <CardHeader>
                    <CardTitle
                        class="flex items-center gap-2 text-lg font-semibold"
                    >
                        <TreePine class="size-5 text-teal-600" />
                        Sebaran Tipe Ekosistem
                    </CardTitle>
                    <CardDescription>
                        Perbandingan cadangan karbon per kategori tutupan lahan.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-if="ecosystems.length === 0"
                        class="py-6 text-center text-xs text-muted-foreground"
                    >
                        Belum ada data tutupan ekosistem yang terdaftar.
                    </div>
                    <div
                        v-else
                        v-for="eco in ecosystems"
                        :key="eco.ecosystem_type"
                        class="rounded-lg border border-border/50 bg-muted/20 p-3"
                    >
                        <div class="flex items-center justify-between">
                            <Badge
                                variant="outline"
                                :class="
                                    getEcosystemBadge(eco.ecosystem_type).class
                                "
                            >
                                {{
                                    getEcosystemBadge(eco.ecosystem_type).label
                                }}
                            </Badge>
                            <span class="text-sm font-bold"
                                >{{ formatNumber(eco.total_co2e_t) }} t
                                CO₂e</span
                            >
                        </div>
                        <div
                            class="mt-2 flex items-center justify-between text-xs text-muted-foreground"
                        >
                            <span>{{ eco.block_count }} Blok</span>
                            <span
                                >{{ formatNumber(eco.total_area_ha) }} ha</span
                            >
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Palm Phenological Growth Mutation Matrix -->
        <Card class="border-border/60">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-lg font-semibold"
                        >
                            <Sprout class="size-5 text-emerald-600" />
                            Matriks Mutasi Pertumbuhan Sawit (Oil Palm
                            Phenological Stages)
                        </CardTitle>
                        <CardDescription>
                            Pelacakan mutasi fase dari TBM (0-3 thn) ke TM Muda,
                            TM Prima, dan TM Tua yang mempengaruhi biomassa
                            batang dan serasah pelepah.
                        </CardDescription>
                    </div>
                    <Button as-child variant="ghost" size="sm">
                        <Link href="/data/palm-stands">
                            Lihat Semua
                            <ArrowUpRight class="ml-1 size-3.5" />
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div
                    v-if="palm_stages.length === 0"
                    class="flex flex-col items-center justify-center py-8 text-center text-muted-foreground"
                >
                    <Sprout class="mb-2 size-8 text-muted-foreground/40" />
                    <p class="text-sm font-medium text-foreground">
                        Belum ada blok tegakan kelapa sawit
                    </p>
                    <p class="text-xs">
                        Data fase fenologi sawit akan muncul setelah blok kebun didaftarkan.
                    </p>
                </div>
                <div
                    v-else
                    class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="palm in palm_stages"
                        :key="palm.block_code"
                        class="rounded-lg border border-border/60 bg-card p-3"
                    >
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-sm font-semibold">{{
                                palm.block_code
                            }}</span>
                            <Badge variant="secondary" class="text-xs">
                                {{ palm.stage }}
                            </Badge>
                        </div>
                        <div class="space-y-1 text-xs text-muted-foreground">
                            <div class="flex justify-between">
                                <span>Umur Tanam:</span>
                                <span class="font-medium text-foreground"
                                    >{{ palm.age_years }} Tahun</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span>Tinggi Batang:</span>
                                <span class="font-medium text-foreground"
                                    >{{ palm.height_m }} m</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span>Kerapatan:</span>
                                <span class="font-medium text-foreground"
                                    >{{ palm.density_ha }} pohon/ha</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Recent Calculations Table -->
        <Card class="border-border/60">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="text-lg font-semibold"
                            >Riwayat Sesi Kalkulasi Terakhir</CardTitle
                        >
                        <CardDescription
                            >Sesi perhitungan stok karbon dan sekuestrasi
                            multi-periode.</CardDescription
                        >
                    </div>
                    <Button as-child variant="outline" size="sm">
                        <Link href="/calculations"> Buka Seluruh Riwayat </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b border-border/60 text-xs text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="px-2 py-3">Blok & Estate</th>
                                <th class="px-2 py-3">Ekosistem</th>
                                <th class="px-2 py-3">Periode</th>
                                <th class="px-2 py-3">Metodologi</th>
                                <th class="px-2 py-3">Status Audit</th>
                                <th class="px-2 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-if="recent_runs.length === 0">
                                <td colspan="6" class="py-10 text-center">
                                    <div class="flex flex-col items-center justify-center text-muted-foreground">
                                        <Calculator class="mb-2 size-8 text-muted-foreground/40" />
                                        <p class="text-sm font-medium text-foreground">
                                            Belum ada riwayat kalkulasi
                                        </p>
                                        <p class="mt-0.5 max-w-sm text-xs text-muted-foreground">
                                            Mulai hitung emisi dan stok karbon dengan memilih blok konsesi pada modul kalkulasi.
                                        </p>
                                        <Button as-child size="sm" class="mt-3 bg-emerald-600 hover:bg-emerald-700 text-white">
                                            <Link href="/calculations/create">
                                                Buat Kalkulasi Baru
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="run in recent_runs"
                                :key="run.id"
                                class="transition-colors hover:bg-muted/30"
                            >
                                <td class="px-2 py-3">
                                    <div class="font-medium text-foreground">
                                        {{ run.land_block?.name }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ run.estate?.name }} ({{
                                            run.land_block?.code
                                        }})
                                    </div>
                                </td>
                                <td class="px-2 py-3">
                                    <Badge
                                        variant="outline"
                                        :class="
                                            getEcosystemBadge(
                                                run.ecosystem_type,
                                            ).class
                                        "
                                    >
                                        {{
                                            getEcosystemBadge(
                                                run.ecosystem_type,
                                            ).label
                                        }}
                                    </Badge>
                                </td>
                                <td
                                    class="px-2 py-3 text-xs text-muted-foreground"
                                >
                                    {{ run.period_start }} s/d
                                    {{ run.period_end }}
                                </td>
                                <td class="px-2 py-3 text-xs">
                                    {{
                                        run.methodology_version?.methodology
                                            ?.name ?? 'Standard'
                                    }}
                                </td>
                                <td class="px-2 py-3">
                                    <Badge
                                        variant="outline"
                                        :class="
                                            getStatusBadge(run.status).class
                                        "
                                    >
                                        {{ getStatusBadge(run.status).label }}
                                    </Badge>
                                </td>
                                <td class="px-2 py-3 text-right">
                                    <Button as-child variant="ghost" size="sm">
                                        <Link :href="`/calculations/${run.id}`">
                                            Detail
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
