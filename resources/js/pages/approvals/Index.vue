<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    Eye,
    FileCheck2,
    History,
    ShieldCheck,
    XCircle,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface PendingRun {
    id: string;
    period_start: string;
    period_end: string;
    ecosystem_type: string;
    area_ha: number;
    status: string;
    calculated_at: string;
    land_block?: {
        name: string;
        code: string;
        estate?: { name: string };
    };
    methodology_version?: {
        version: string;
        methodology?: { name: string };
    };
    executor?: {
        name: string;
        email: string;
    };
    result?: {
        total_biomass_t: number;
        total_carbon_t: number;
        total_co2e_t: number;
        co2e_stock_t_ha: number;
    };
}

interface RecentApproval {
    id: string;
    status: string;
    notes: string | null;
    reviewed_at: string;
    reviewer?: { name: string; email: string };
    calculation_run?: {
        id: string;
        ecosystem_type: string;
        land_block?: { name: string; code: string };
    };
}

interface Props {
    pendingRuns: {
        data: PendingRun[];
        total: number;
        current_page: number;
        last_page: number;
    };
    recentApprovals: RecentApproval[];
}

const props = defineProps<Props>();

// Modal states
const activeRun = ref<PendingRun | null>(null);
const modalType = ref<'approve' | 'reject' | null>(null);
const auditNotes = ref('');

const openModal = (run: PendingRun, type: 'approve' | 'reject') => {
    activeRun.value = run;
    modalType.value = type;
    auditNotes.value = '';
};

const closeModal = () => {
    activeRun.value = null;
    modalType.value = null;
    auditNotes.value = '';
};

const submitApprovalAction = () => {
    if (!activeRun.value || !modalType.value) return;

    if (modalType.value === 'reject' && !auditNotes.value.trim()) {
        alert('Harap isi alasan revisi untuk staf pembuat kalkulasi.');
        return;
    }

    const endpoint = `/calculations/${activeRun.value.id}/${modalType.value}`;
    router.post(
        endpoint,
        { notes: auditNotes.value },
        {
            onSuccess: () => {
                closeModal();
            },
        },
    );
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
        default:
            return { label: type, class: 'bg-neutral-500/15 text-neutral-600' };
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Approvals Queue', href: '/approvals' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Antrean Persetujuan Auditor" />
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <FileCheck2
                        class="h-7 w-7 text-amber-600 dark:text-amber-400"
                    />
                    Antrean Verifikasi &amp; Persetujuan Auditor
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Tinjau kalkulasi karbon yang diajukan oleh surveyor lapangan
                    sebelum dikunci permanen untuk kepatuhan ESG &amp; SRN-PPI.
                </p>
            </div>
            <Badge
                variant="outline"
                class="self-start border-amber-500/40 bg-amber-50 px-3 py-1 font-mono text-xs text-amber-700 sm:self-auto dark:bg-amber-950/40"
            >
                {{ pendingRuns.total }} Menunggu Review
            </Badge>
        </div>

        <!-- Pending Runs Table -->
        <Card
            class="overflow-hidden border border-neutral-200 shadow-sm dark:border-neutral-800"
        >
            <CardHeader
                class="border-b border-neutral-200 bg-neutral-50/50 dark:border-neutral-800 dark:bg-neutral-900/50"
            >
                <CardTitle class="flex items-center gap-2 text-base">
                    <Clock class="h-4 w-4 text-amber-600" />
                    Daftar Kalkulasi Menunggu Persetujuan
                </CardTitle>
            </CardHeader>
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
                            <th class="px-4 py-3.5">Periode Penilaian</th>
                            <th class="px-4 py-3.5 text-right">
                                Total Diserap (t CO2e)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Kerapatan (t/ha)
                            </th>
                            <th class="px-4 py-3.5">Pengusul</th>
                            <th class="px-5 py-3.5 text-right">
                                Keputusan Auditor
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-100 font-medium dark:divide-neutral-800"
                    >
                        <tr
                            v-for="run in pendingRuns.data"
                            :key="run.id"
                            class="transition-colors hover:bg-neutral-50/80 dark:hover:bg-neutral-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-semibold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ run.land_block?.name }}
                                </div>
                                <div
                                    class="mt-0.5 flex items-center gap-1.5 text-xs text-neutral-500"
                                >
                                    <span class="font-mono">{{
                                        run.land_block?.code
                                    }}</span>
                                    <span>•</span>
                                    <span>{{
                                        run.land_block?.estate?.name
                                    }}</span>
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
                            <td class="px-4 py-4 font-mono text-xs">
                                {{ run.period_start }} s/d
                                {{ run.period_end }}
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
                                class="px-4 py-4 text-right font-mono text-neutral-700 dark:text-neutral-300"
                            >
                                {{
                                    Number(
                                        run.result?.co2e_stock_t_ha || 0,
                                    ).toFixed(2)
                                }}
                            </td>
                            <td
                                class="px-4 py-4 text-xs text-neutral-600 dark:text-neutral-300"
                            >
                                <div>
                                    {{ run.executor?.name || 'Surveyor' }}
                                </div>
                                <div
                                    class="font-mono text-[11px] text-neutral-400"
                                >
                                    {{ run.calculated_at }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link :href="`/calculations/${run.id}`">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 px-2 text-xs text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800"
                                        >
                                            <Eye class="mr-1 h-3.5 w-3.5" />
                                            Cek
                                        </Button>
                                    </Link>
                                    <Button
                                        @click="openModal(run, 'reject')"
                                        variant="destructive"
                                        size="sm"
                                        class="h-8 px-2 text-xs"
                                    >
                                        <XCircle class="mr-1 h-3.5 w-3.5" />
                                        Revisi
                                    </Button>
                                    <Button
                                        @click="openModal(run, 'approve')"
                                        size="sm"
                                        class="h-8 px-2.5 text-xs"
                                    >
                                        <CheckCircle2
                                            class="mr-1 h-3.5 w-3.5"
                                        />
                                        Setujui
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="pendingRuns.data.length === 0">
                            <td
                                colspan="7"
                                class="px-6 py-12 text-center text-neutral-400"
                            >
                                <CheckCircle2
                                    class="mx-auto mb-2 h-10 w-10 text-emerald-500"
                                />
                                <p
                                    class="font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Tidak ada kalkulasi yang menunggu
                                    verifikasi.
                                </p>
                                <p class="mt-1 text-xs text-neutral-400">
                                    Semua usulan inventarisasi karbon telah
                                    diverifikasi dan disetujui.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Recent Approvals Audit History -->
        <Card class="border border-neutral-200 dark:border-neutral-800">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <History class="h-4 w-4 text-emerald-600" />
                    Aktivitas Verifikasi Auditor Terbaru
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-3">
                    <div
                        v-for="item in recentApprovals"
                        :key="item.id"
                        class="flex items-center justify-between rounded-lg border border-neutral-200 p-3.5 text-xs dark:border-neutral-800"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-full font-bold',
                                    item.status === 'approved'
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400'
                                        : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400',
                                ]"
                            >
                                <ShieldCheck
                                    v-if="item.status === 'approved'"
                                    class="h-4 w-4"
                                />
                                <XCircle v-else class="h-4 w-4" />
                            </div>
                            <div>
                                <div
                                    class="font-semibold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ item.calculation_run?.land_block?.name }}
                                    ({{
                                        item.calculation_run?.land_block?.code
                                    }})
                                </div>
                                <div class="mt-0.5 text-neutral-400">
                                    Peninjau:
                                    <span
                                        class="font-medium text-neutral-700 dark:text-neutral-300"
                                        >{{ item.reviewer?.name }}</span
                                    >
                                    • {{ item.reviewed_at }}
                                </div>
                                <div
                                    v-if="item.notes"
                                    class="mt-0.5 text-[11px] text-neutral-500 italic"
                                >
                                    "{{ item.notes }}"
                                </div>
                            </div>
                        </div>
                        <Badge
                            :variant="
                                item.status === 'approved'
                                    ? 'outline'
                                    : 'destructive'
                            "
                            :class="
                                item.status === 'approved'
                                    ? 'border-emerald-300 bg-emerald-50 text-emerald-700'
                                    : ''
                            "
                        >
                            {{
                                item.status === 'approved'
                                    ? 'Disetujui'
                                    : 'Ditolak'
                            }}
                        </Badge>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- ACTION DIALOG MODAL -->
        <div
            v-if="activeRun && modalType"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <h3
                    class="flex items-center gap-2 text-lg font-bold text-neutral-900 dark:text-neutral-100"
                >
                    <CheckCircle2
                        v-if="modalType === 'approve'"
                        class="h-5 w-5 text-emerald-600"
                    />
                    <AlertTriangle v-else class="h-5 w-5 text-rose-600" />
                    {{
                        modalType === 'approve'
                            ? 'Konfirmasi Persetujuan Auditor'
                            : 'Minta Revisi Data Sensus'
                    }}
                </h3>

                <div
                    class="space-y-1 rounded-lg bg-neutral-50 p-3 text-xs dark:bg-neutral-800"
                >
                    <div>
                        <span class="text-neutral-400">Blok Target:</span>
                        <span class="font-semibold"
                            >{{ activeRun.land_block?.name }} ({{
                                activeRun.land_block?.code
                            }})</span
                        >
                    </div>
                    <div>
                        <span class="text-neutral-400">Total Karbon:</span>
                        <span class="font-semibold text-emerald-600"
                            >{{
                                Number(activeRun.result?.total_co2e_t).toFixed(
                                    1,
                                )
                            }}
                            t CO2e</span
                        >
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                    >
                        {{
                            modalType === 'approve'
                                ? 'Catatan Verifikasi (Opsional)'
                                : 'Catatan Revisi Wajib *'
                        }}
                    </label>
                    <textarea
                        v-model="auditNotes"
                        rows="3"
                        :placeholder="
                            modalType === 'approve'
                                ? 'Catatan kepatuhan audit...'
                                : 'Jelaskan parameter sensus atau uji lab yang salah...'
                        "
                        class="w-full rounded-lg border border-neutral-300 bg-white p-2.5 text-xs text-neutral-900 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button @click="closeModal" variant="outline" size="sm">
                        Batal
                    </Button>
                    <Button
                        @click="submitApprovalAction"
                        size="sm"
                        :variant="
                            modalType === 'approve' ? 'default' : 'destructive'
                        "
                    >
                        {{
                            modalType === 'approve'
                                ? 'Setujui & Kunci Permanen'
                                : 'Kirim Catatan Revisi'
                        }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
