<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Activity,
    Award,
    CheckCircle2,
    Download,
    FileSpreadsheet,
    FileText,
    Layers,
    MapPin,
    Printer,
    Search,
    ShieldCheck,
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

interface ApprovedRunItem {
    id: string;
    period_start: string;
    period_end: string;
    ecosystem_type: string;
    area_ha: number;
    calculated_at: string;
    estate?: { name: string; code: string };
    land_block?: { name: string; code: string };
    methodology_version?: {
        version: string;
        methodology?: { name: string };
    };
    result?: {
        total_biomass_t: number;
        total_carbon_t: number;
        total_co2e_t: number;
        carbon_stock_t_ha: number;
        co2e_stock_t_ha: number;
        sequestration_t_co2e_year: number | null;
        pool_results?: Array<{
            carbon_t: number;
            co2e_t: number;
            carbon_pool?: { code: string };
        }>;
    };
}

interface Props {
    approvedRuns: ApprovedRunItem[];
    totalRuns: number;
    approvedCount: number;
}

const props = defineProps<Props>();

const searchQuery = ref('');

const filteredRuns = computed(() => {
    if (!searchQuery.value.trim()) return props.approvedRuns;
    const q = searchQuery.value.toLowerCase();
    return props.approvedRuns.filter(
        (r) =>
            r.land_block?.name.toLowerCase().includes(q) ||
            r.land_block?.code.toLowerCase().includes(q) ||
            r.estate?.name.toLowerCase().includes(q) ||
            r.ecosystem_type.toLowerCase().includes(q),
    );
});

const totalVerifiedCo2e = computed(() => {
    return props.approvedRuns.reduce(
        (sum, r) => sum + Number(r.result?.total_co2e_t || 0),
        0,
    );
});

const totalVerifiedCarbon = computed(() => {
    return props.approvedRuns.reduce(
        (sum, r) => sum + Number(r.result?.total_carbon_t || 0),
        0,
    );
});

const totalVerifiedArea = computed(() => {
    return props.approvedRuns.reduce(
        (sum, r) => sum + Number(r.area_ha || 0),
        0,
    );
});

const getEcosystemBadge = (type: string) => {
    switch (type) {
        case 'oil_palm':
            return {
                label: 'Kelapa Sawit',
                class: 'bg-lime-500/15 text-lime-700 dark:text-lime-400 border-lime-500/30',
            };
        case 'natural_forest':
            return {
                label: 'Hutan Alam',
                class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
            };
        case 'hti_plantation':
            return {
                label: 'HTI Kayu',
                class: 'bg-teal-500/15 text-teal-700 dark:text-teal-400 border-teal-500/30',
            };
        case 'mangrove':
            return {
                label: 'Mangrove',
                class: 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-400 border-cyan-500/30',
            };
        default:
            return { label: type, class: 'bg-neutral-500/15 text-neutral-600' };
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Reports & Audits', href: '/reports' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Laporan & Neraca Karbon Terverifikasi" />
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <FileSpreadsheet
                        class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
                    />
                    Laporan Neraca Karbon &amp; Kepatuhan Audit
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Dokumentasi resmi stok cadangan karbon dan laju penyerapan
                    yang telah diverifikasi auditor untuk sertifikasi
                    keberlanjutan.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="/reports/download" target="_blank">
                    <Button class="gap-2">
                        <Download class="h-4 w-4" />
                        Unduh CSV Lengkap (Audit Ready)
                    </Button>
                </a>
            </div>
        </div>

        <!-- Compliance Standards Badges Ribbon -->
        <div
            class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-neutral-800 bg-neutral-900 p-4 text-neutral-200"
        >
            <div class="flex items-center gap-3">
                <Award class="h-6 w-6 shrink-0 text-amber-400" />
                <div>
                    <div class="text-sm font-bold text-white">
                        Standar Metodologi &amp; Regulasi Kepatuhan Karbon
                    </div>
                    <p class="mt-0.5 text-xs text-neutral-400">
                        Formula perhitungan telah disesuaikan dengan parameter
                        standar internasional dan nasional:
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Badge
                    variant="outline"
                    class="border-emerald-500/40 bg-neutral-800 font-mono text-[11px] text-emerald-400"
                >
                    IPCC AFOLU 2019 (Tier 2)
                </Badge>
                <Badge
                    variant="outline"
                    class="border-cyan-500/40 bg-neutral-800 font-mono text-[11px] text-cyan-400"
                >
                    SNI 7724 / Chave 2014
                </Badge>
                <Badge
                    variant="outline"
                    class="border-lime-500/40 bg-neutral-800 font-mono text-[11px] text-lime-400"
                >
                    ISPO GHG &amp; RSPO PalmGHG
                </Badge>
                <Badge
                    variant="outline"
                    class="border-amber-500/40 bg-neutral-800 text-[11px] text-amber-400"
                >
                    SRN-PPI KLHK
                </Badge>
            </div>
        </div>

        <!-- Executive KPI Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <Card
                class="border border-neutral-200 bg-white/70 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <CardHeader class="pb-2">
                    <CardDescription class="text-xs"
                        >Total Stok Diserap</CardDescription
                    >
                    <CardTitle
                        class="tabular-nums text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400"
                    >
                        {{
                            Number(totalVerifiedCo2e).toLocaleString('id-ID', {
                                maximumFractionDigits: 1,
                            })
                        }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-xs text-neutral-400"
                    >t CO2e emisi terkunci</CardContent
                >
            </Card>

            <Card
                class="border border-neutral-200 bg-white/70 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <CardHeader class="pb-2">
                    <CardDescription class="text-xs"
                        >Cadangan Karbon Murni (C)</CardDescription
                    >
                    <CardTitle
                        class="tabular-nums text-2xl font-bold tracking-tight text-teal-600 dark:text-teal-400"
                    >
                        {{
                            Number(totalVerifiedCarbon).toLocaleString(
                                'id-ID',
                                { maximumFractionDigits: 1 },
                            )
                        }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-xs text-neutral-400"
                    >ton C murni (5 pools)</CardContent
                >
            </Card>

            <Card
                class="border border-neutral-200 bg-white/70 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <CardHeader class="pb-2">
                    <CardDescription class="text-xs"
                        >Luas Lahan Terverifikasi</CardDescription
                    >
                    <CardTitle
                        class="tabular-nums text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                    >
                        {{
                            Number(totalVerifiedArea).toLocaleString('id-ID', {
                                maximumFractionDigits: 1,
                            })
                        }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-xs text-neutral-400"
                    >hektar tutupan diaudit</CardContent
                >
            </Card>

            <Card
                class="border border-neutral-200 bg-white/70 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <CardHeader class="pb-2">
                    <CardDescription class="text-xs"
                        >Kepatuhan Audit</CardDescription
                    >
                    <CardTitle
                        class="tabular-nums text-2xl font-bold tracking-tight text-emerald-600"
                    >
                        {{ approvedCount }} / {{ totalRuns }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-xs text-neutral-400"
                    >sesi kalkulasi disetujui</CardContent
                >
            </Card>
        </div>

        <!-- Verified Reports Table -->
        <Card
            class="overflow-hidden border border-neutral-200 shadow-sm dark:border-neutral-800"
        >
            <CardHeader
                class="flex flex-col gap-3 border-b bg-neutral-50/50 sm:flex-row sm:items-center sm:justify-between dark:bg-neutral-900/50"
            >
                <CardTitle class="flex items-center gap-2 text-base">
                    <ShieldCheck class="h-4 w-4 text-emerald-600" />
                    Daftar Berkas Neraca Karbon Terverifikasi
                </CardTitle>
                <div class="relative w-full sm:w-64">
                    <Search
                        class="absolute top-2.5 left-3 h-3.5 w-3.5 text-neutral-400"
                    />
                    <input
                        v-model="searchQuery"
                        placeholder="Cari blok, estate, ekosistem..."
                        class="w-full rounded-lg border border-neutral-300 bg-white py-1.5 pr-3 pl-8 text-xs focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900"
                    />
                </div>
            </CardHeader>
            <div class="overflow-x-auto">
                <table
                    class="w-full text-left text-sm text-neutral-600 dark:text-neutral-300"
                >
                    <thead
                        class="border-b bg-neutral-50 text-xs font-semibold text-neutral-500 uppercase dark:bg-neutral-800/80 dark:text-neutral-400"
                    >
                        <tr>
                            <th class="px-5 py-3.5">Blok Lahan &amp; Estate</th>
                            <th class="px-4 py-3.5">Ekosistem</th>
                            <th class="px-4 py-3.5">Periode Audit</th>
                            <th class="px-4 py-3.5">Metodologi</th>
                            <th class="px-4 py-3.5 text-right">
                                Cadangan (t C)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Total Stok (t CO2e)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Kerapatan (t/ha)
                            </th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-100 font-medium dark:divide-neutral-800"
                    >
                        <tr
                            v-for="run in filteredRuns"
                            :key="run.id"
                            class="hover:bg-neutral-50/80 dark:hover:bg-neutral-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-bold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ run.land_block?.name }}
                                </div>
                                <div
                                    class="mt-0.5 font-mono text-xs text-neutral-400"
                                >
                                    {{ run.land_block?.code }} •
                                    {{ run.estate?.name }} ({{ run.area_ha }}
                                    ha)
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <Badge
                                    :class="
                                        getEcosystemBadge(run.ecosystem_type)
                                            .class
                                    "
                                    variant="outline"
                                    class="text-xs font-normal"
                                >
                                    {{
                                        getEcosystemBadge(run.ecosystem_type)
                                            .label
                                    }}
                                </Badge>
                            </td>
                            <td class="px-4 py-4 font-mono text-xs">
                                {{ run.period_start }} s/d
                                {{ run.period_end }}
                            </td>
                            <td class="px-4 py-4 text-xs">
                                {{ run.methodology_version?.methodology?.name }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-neutral-900 dark:text-neutral-100"
                            >
                                {{
                                    Number(
                                        run.result?.total_carbon_t || 0,
                                    ).toLocaleString('id-ID', {
                                        maximumFractionDigits: 1,
                                    })
                                }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400"
                            >
                                {{
                                    Number(
                                        run.result?.total_co2e_t || 0,
                                    ).toLocaleString('id-ID', {
                                        maximumFractionDigits: 1,
                                    })
                                }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-neutral-800 dark:text-neutral-200"
                            >
                                {{
                                    Number(
                                        run.result?.co2e_stock_t_ha || 0,
                                    ).toFixed(2)
                                }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link :href="`/calculations/${run.id}`">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 px-2 text-xs text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/50"
                                    >
                                        Lihat Berkas
                                    </Button>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="filteredRuns.length === 0">
                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-neutral-400"
                            >
                                <ShieldCheck
                                    class="mx-auto mb-2 h-10 w-10 text-neutral-400"
                                />
                                <p
                                    class="font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Belum ada berkas neraca karbon yang
                                    berstatus disetujui (Approved).
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>
