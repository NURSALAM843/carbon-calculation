<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Calculator,
    Calendar,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileSpreadsheet,
    Filter,
    Layers,
    Plus,
    RefreshCw,
    Trash2,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface RunItem {
    id: string;
    period_start: string;
    period_end: string;
    ecosystem_type: string;
    area_ha: number;
    calculation_type: string;
    status: 'draft' | 'pending_approval' | 'approved' | 'rejected';
    calculated_at: string;
    land_block?: {
        id: string;
        code: string;
        name: string;
    };
    estate?: {
        id: string;
        code: string;
        name: string;
    };
    methodology_version?: {
        version: string;
        methodology?: {
            name: string;
            code: string;
        };
    };
    result?: {
        total_biomass_t: number;
        total_carbon_t: number;
        total_co2e_t: number;
        carbon_stock_t_ha: number;
        co2e_stock_t_ha: number;
        sequestration_t_co2e_year: number | null;
    };
}

interface Props {
    runs: {
        data: RunItem[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    estates: Array<{ id: string; name: string; code: string }>;
    filters: {
        status?: string;
        ecosystem_type?: string;
        estate_id?: string;
    };
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Calculation Runs', href: '/calculations' },
        ],
    },
});

const filterStatus = ref(props.filters.status || '');
const filterEcosystem = ref(props.filters.ecosystem_type || '');
const filterEstate = ref(props.filters.estate_id || '');

const applyFilters = () => {
    router.get(
        '/calculations',
        {
            status: filterStatus.value || undefined,
            ecosystem_type: filterEcosystem.value || undefined,
            estate_id: filterEstate.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const resetFilters = () => {
    filterStatus.value = '';
    filterEcosystem.value = '';
    filterEstate.value = '';
    router.get('/calculations');
};

const deleteDraft = (id: string) => {
    if (
        confirm(
            'Yakin ingin menghapus draf kalkulasi ini? Data yang terhapus tidak dapat dikembalikan.',
        )
    ) {
        router.delete(`/calculations/${id}`);
    }
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'approved':
            return {
                label: 'Disetujui (Approved)',
                class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
            };
        case 'pending_approval':
            return {
                label: 'Menunggu Verifikasi',
                class: 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border-amber-500/30',
            };
        case 'rejected':
            return {
                label: 'Perlu Revisi',
                class: 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-500/30',
            };
        default:
            return {
                label: 'Draf (Draft)',
                class: 'bg-neutral-500/15 text-neutral-700 dark:text-neutral-400 border-neutral-500/30',
            };
    }
};

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
        case 'peat_swamp_forest':
            return {
                label: 'Gambut / PSF',
                class: 'bg-amber-600/15 text-amber-800 dark:text-amber-400 border-amber-600/30',
            };
        default:
            return { label: type, class: 'bg-neutral-500/15 text-neutral-600' };
    }
};
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Riwayat Kalkulasi Karbon" />
        <!-- Header Banner -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <Calculator
                        class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
                    />
                    Riwayat & Sesi Kalkulasi Karbon
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Daftar seluruh perhitungan cadangan karbon (Carbon Stock)
                    dan laju penyerapan bersih (Sequestration) lintas ekosistem.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Link href="/reports">
                    <Button variant="outline" class="cursor-pointer gap-2">
                        <FileSpreadsheet class="h-4 w-4 text-emerald-600" />
                        Laporan & Ekspor
                    </Button>
                </Link>
                <Link href="/calculations/create">
                    <Button class="gap-2">
                        <Plus class="h-4 w-4" />
                        Kalkulasi Baru (Wizard)
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Filters Bar -->
        <Card
            class="border border-neutral-200 bg-white/70 backdrop-blur-sm dark:border-neutral-800 dark:bg-neutral-900/70"
        >
            <CardContent class="p-4">
                <div class="grid grid-cols-1 items-center gap-3 sm:grid-cols-4">
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                            >Status Persetujuan</label
                        >
                        <select
                            v-model="filterStatus"
                            @change="applyFilters"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100"
                        >
                            <option value="">Semua Status</option>
                            <option value="draft">Draf (Draft)</option>
                            <option value="pending_approval">
                                Menunggu Verifikasi
                            </option>
                            <option value="approved">
                                Disetujui (Approved)
                            </option>
                            <option value="rejected">
                                Perlu Revisi (Rejected)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                            >Tipe Ekosistem</label
                        >
                        <select
                            v-model="filterEcosystem"
                            @change="applyFilters"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100"
                        >
                            <option value="">Semua Ekosistem</option>
                            <option value="oil_palm">
                                Kelapa Sawit (Palm Mutation)
                            </option>
                            <option value="natural_forest">
                                Hutan Alam (Chave/SNI)
                            </option>
                            <option value="hti_plantation">
                                HTI Kayu Budidaya
                            </option>
                            <option value="mangrove">
                                Mangrove / Lahan Basah
                            </option>
                            <option value="peat_swamp_forest">
                                Hutan Rawa Gambut
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                            >Unit Estate / Konsesi</label
                        >
                        <select
                            v-model="filterEstate"
                            @change="applyFilters"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100"
                        >
                            <option value="">Semua Estate</option>
                            <option
                                v-for="estate in estates"
                                :key="estate.id"
                                :value="estate.id"
                            >
                                {{ estate.name }} ({{ estate.code }})
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2 pt-2 sm:pt-4">
                        <Button @click="applyFilters" class="flex-1 gap-2">
                            <Filter class="h-4 w-4" />
                            Terapkan
                        </Button>
                        <Button
                            @click="resetFilters"
                            variant="outline"
                            title="Reset Filter"
                            class="cursor-pointer"
                        >
                            <RefreshCw class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Table Card -->
        <Card
            class="overflow-hidden border border-neutral-200 shadow-sm dark:border-neutral-800"
        >
            <div class="overflow-x-auto">
                <table
                    class="w-full text-left text-sm text-neutral-600 dark:text-neutral-300"
                >
                    <thead
                        class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold text-neutral-500 uppercase dark:border-neutral-700 dark:bg-neutral-800/80 dark:text-neutral-400"
                    >
                        <tr>
                            <th class="px-5 py-3.5">Blok Lahan & Estate</th>
                            <th class="px-4 py-3.5">Ekosistem</th>
                            <th class="px-4 py-3.5">Periode & Tipe</th>
                            <th class="px-4 py-3.5">Metodologi</th>
                            <th class="px-4 py-3.5 text-right">
                                Cadangan (t C)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Total (t CO2e)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Kerapatan (t CO2e/ha)
                            </th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-100 font-medium dark:divide-neutral-800"
                    >
                        <tr
                            v-for="run in runs.data"
                            :key="run.id"
                            class="transition-colors hover:bg-neutral-50/80 dark:hover:bg-neutral-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-semibold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ run.land_block?.name || 'Blok' }}
                                </div>
                                <div
                                    class="mt-0.5 flex items-center gap-1.5 text-xs text-neutral-500"
                                >
                                    <span class="font-mono">{{
                                        run.land_block?.code
                                    }}</span>
                                    <span>•</span>
                                    <span>{{ run.estate?.name }}</span>
                                    <span>•</span>
                                    <span>{{ run.area_ha }} ha</span>
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
                            <td class="px-4 py-4">
                                <div
                                    class="flex items-center gap-1 text-xs text-neutral-800 dark:text-neutral-200"
                                >
                                    <Calendar
                                        class="h-3.5 w-3.5 text-neutral-400"
                                    />
                                    {{ run.period_start }} s/d
                                    {{ run.period_end }}
                                </div>
                                <span
                                    class="mt-0.5 block font-mono text-[11px] text-neutral-400"
                                >
                                    {{
                                        run.calculation_type === 'sequestration'
                                            ? 'Laju Sekuestrasi'
                                            : 'Stok Karbon'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div
                                    class="text-xs font-medium text-neutral-900 dark:text-neutral-100"
                                >
                                    {{
                                        run.methodology_version?.methodology
                                            ?.name || 'Metodologi'
                                    }}
                                </div>
                                <div class="text-[11px] text-neutral-400">
                                    Ver:
                                    {{
                                        run.methodology_version?.version ||
                                        'v1.0'
                                    }}
                                </div>
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-neutral-900 dark:text-neutral-100"
                            >
                                {{
                                    Number(
                                        run.result?.total_carbon_t || 0,
                                    ).toLocaleString('id-ID', {
                                        minimumFractionDigits: 1,
                                        maximumFractionDigits: 1,
                                    })
                                }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400"
                            >
                                {{
                                    Number(
                                        run.result?.total_co2e_t || 0,
                                    ).toLocaleString('id-ID', {
                                        minimumFractionDigits: 1,
                                        maximumFractionDigits: 1,
                                    })
                                }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-neutral-700 dark:text-neutral-300"
                            >
                                {{
                                    Number(
                                        run.result?.co2e_stock_t_ha || 0,
                                    ).toFixed(2)
                                }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <Badge
                                    :class="getStatusBadge(run.status).class"
                                    variant="outline"
                                    class="text-xs font-normal"
                                >
                                    {{ getStatusBadge(run.status).label }}
                                </Badge>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link :href="`/calculations/${run.id}`">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 cursor-pointer gap-1.5 px-2.5 text-xs text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/50"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                            Detail
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="run.status === 'draft'"
                                        @click="deleteDraft(run.id)"
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 cursor-pointer px-2 text-xs text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50"
                                        title="Hapus Draf"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="runs.data.length === 0">
                            <td
                                colspan="9"
                                class="px-6 py-12 text-center text-neutral-400"
                            >
                                <Layers
                                    class="mx-auto mb-2 h-10 w-10 text-neutral-300 dark:text-neutral-600"
                                />
                                <p
                                    class="font-medium text-neutral-600 dark:text-neutral-400"
                                >
                                    Tidak ada sesi kalkulasi yang cocok dengan
                                    filter.
                                </p>
                                <p class="mt-1 text-xs text-neutral-400">
                                    Gunakan wizard kalkulasi untuk memulai
                                    perhitungan baru.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="runs.total > 0"
                class="flex items-center justify-between border-t border-neutral-200 px-6 py-4 text-xs text-neutral-500 dark:border-neutral-800"
            >
                <div>
                    Menampilkan
                    <span
                        class="font-semibold text-neutral-900 dark:text-neutral-100"
                        >{{ runs.from || 0 }}</span
                    >
                    s/d
                    <span
                        class="font-semibold text-neutral-900 dark:text-neutral-100"
                        >{{ runs.to || 0 }}</span
                    >
                    dari total
                    <span
                        class="font-semibold text-neutral-900 dark:text-neutral-100"
                        >{{ runs.total }}</span
                    >
                    sesi
                </div>
                <div class="flex items-center gap-1">
                    <Link
                        v-if="runs.prev_page_url"
                        :href="runs.prev_page_url"
                        class="flex items-center gap-1 rounded border border-neutral-200 px-3 py-1.5 hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800"
                    >
                        <ChevronLeft class="h-3.5 w-3.5" /> Sebelumnya
                    </Link>
                    <span
                        v-else
                        class="flex cursor-not-allowed items-center gap-1 rounded border border-neutral-100 px-3 py-1.5 opacity-40 dark:border-neutral-800"
                    >
                        <ChevronLeft class="h-3.5 w-3.5" /> Sebelumnya
                    </span>

                    <span
                        class="px-3 py-1.5 font-semibold text-neutral-800 dark:text-neutral-200"
                    >
                        Hal. {{ runs.current_page }} dari
                        {{ runs.last_page }}
                    </span>

                    <Link
                        v-if="runs.next_page_url"
                        :href="runs.next_page_url"
                        class="flex items-center gap-1 rounded border border-neutral-200 px-3 py-1.5 hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800"
                    >
                        Berikutnya <ChevronRight class="h-3.5 w-3.5" />
                    </Link>
                    <span
                        v-else
                        class="flex cursor-not-allowed items-center gap-1 rounded border border-neutral-100 px-3 py-1.5 opacity-40 dark:border-neutral-800"
                    >
                        Berikutnya <ChevronRight class="h-3.5 w-3.5" />
                    </span>
                </div>
            </div>
        </Card>
    </div>
</template>
