<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Activity,
    AlertTriangle,
    ArrowLeft,
    CheckCircle2,
    Clock,
    Code,
    Database,
    Download,
    Eye,
    FileSpreadsheet,
    FileText,
    History,
    Layers,
    Leaf,
    MapPin,
    RotateCcw,
    Send,
    ShieldAlert,
    ShieldCheck,
    TreePine,
    UserCheck,
    XCircle,
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

interface RunDetail {
    id: string;
    organization_id: string;
    estate_id: string;
    land_block_id: string;
    ecosystem_type: string;
    period_start: string;
    period_end: string;
    area_ha: number;
    calculation_type: string;
    status: 'draft' | 'pending_approval' | 'approved' | 'rejected';
    calculated_at: string;
    input_snapshot: Record<string, any>;
    formula_snapshot: Record<string, any>;
    parameter_snapshot: Record<string, any>;
    estate?: { name: string; code: string };
    land_block?: {
        name: string;
        code: string;
        soil_type: string;
        land_type: string;
    };
    methodology_version?: {
        version: string;
        methodology?: { name: string; code: string; tier_level: number };
    };
    executor?: { name: string; email: string };
    result?: {
        id: string;
        total_biomass_t: number;
        total_carbon_t: number;
        total_co2e_t: number;
        carbon_stock_t_ha: number;
        co2e_stock_t_ha: number;
        sequestration_t_co2e_year: number | null;
        pool_results?: Array<{
            id: string;
            biomass_t: number;
            carbon_t: number;
            co2e_t: number;
            co2e_per_ha: number;
            carbon_pool?: { name: string; code: string; category: string };
        }>;
    };
    comparisons?: Array<{
        id: string;
        delta_carbon_t: number;
        delta_co2e_t: number;
        annualized_co2e_t: number;
        previous_calculation_run?: {
            id: string;
            period_start: string;
            period_end: string;
            result?: { total_co2e_t: number };
        };
    }>;
    approvals?: Array<{
        id: string;
        status: string;
        notes: string | null;
        reviewed_at: string;
        reviewer?: { name: string; email: string };
    }>;
}

interface Props {
    run: RunDetail;
}

const props = defineProps<Props>();

// Modals state
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const approvalNotes = ref('');
const activeSnapshotTab = ref<'input' | 'formula' | 'parameter'>('formula');

const submitForApproval = () => {
    if (
        confirm(
            'Ajukan kalkulasi ini untuk verifikasi auditor? Data akan terkunci sementara.',
        )
    ) {
        router.post(`/calculations/${props.run.id}/submit`);
    }
};

const approveRun = () => {
    router.post(
        `/calculations/${props.run.id}/approve`,
        { notes: approvalNotes.value },
        {
            onSuccess: () => {
                showApproveModal.value = false;
                approvalNotes.value = '';
            },
        },
    );
};

const rejectRun = () => {
    if (!approvalNotes.value.trim()) {
        alert('Harap masukkan catatan revisi untuk surveyor.');
        return;
    }
    router.post(
        `/calculations/${props.run.id}/reject`,
        { notes: approvalNotes.value },
        {
            onSuccess: () => {
                showRejectModal.value = false;
                approvalNotes.value = '';
            },
        },
    );
};

const downloadSnapshot = () => {
    const payload = {
        calculation_run_id: props.run.id,
        ecosystem_type: props.run.ecosystem_type,
        period: `${props.run.period_start} to ${props.run.period_end}`,
        input_snapshot: props.run.input_snapshot,
        formula_snapshot: props.run.formula_snapshot,
        parameter_snapshot: props.run.parameter_snapshot,
        result: props.run.result,
        export_date: new Date().toISOString(),
    };
    const blob = new Blob([JSON.stringify(payload, null, 2)], {
        type: 'application/json',
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `audit_snapshot_${props.run.id}.json`;
    a.click();
    URL.revokeObjectURL(url);
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'approved':
            return {
                label: 'Disetujui Permanen (Approved)',
                class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
            };
        case 'pending_approval':
            return {
                label: 'Menunggu Verifikasi Auditor',
                class: 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border-amber-500/30',
            };
        case 'rejected':
            return {
                label: 'Perlu Revisi (Rejected)',
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
                label: 'Kelapa Sawit (Palm Growth Mutation)',
                class: 'bg-lime-500/15 text-lime-700 dark:text-lime-400 border-lime-500/30',
            };
        case 'natural_forest':
            return {
                label: 'Hutan Alam (Chave 2014 & SNI)',
                class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
            };
        case 'hti_plantation':
            return {
                label: 'HTI Kayu Budidaya',
                class: 'bg-teal-500/15 text-teal-700 dark:text-teal-400 border-teal-500/30',
            };
        case 'mangrove':
            return {
                label: 'Mangrove / Lahan Basah',
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
            { title: 'Calculation Runs', href: '/calculations' },
            { title: 'Detail Kalkulasi', href: '#' },
        ],
    },
});
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <Head title="Detail Sesi Kalkulasi Karbon" />
        <!-- Header with Back button and Actions -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-center gap-3">
                <Link href="/calculations">
                    <Button variant="outline" size="sm" class="gap-1.5">
                        <ArrowLeft class="h-4 w-4" />
                        Daftar
                    </Button>
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <h1
                            class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                        >
                            {{ run.land_block?.name || 'Blok Lahan' }}
                        </h1>
                        <Badge
                            :class="getStatusBadge(run.status).class"
                            variant="outline"
                            class="text-xs font-normal"
                        >
                            {{ getStatusBadge(run.status).label }}
                        </Badge>
                    </div>
                    <p
                        class="mt-1 flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400"
                    >
                        <span class="font-mono"
                            >Kode: {{ run.land_block?.code }}</span
                        >
                        <span>•</span>
                        <span>{{ run.estate?.name }}</span>
                        <span>•</span>
                        <span>Luas: {{ run.area_ha }} ha</span>
                        <span>•</span>
                        <span>Dihitung: {{ run.calculated_at }}</span>
                    </p>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="flex items-center gap-2.5">
                <Button
                    @click="downloadSnapshot"
                    variant="outline"
                    size="sm"
                    class="gap-1.5"
                >
                    <Download class="h-4 w-4 text-neutral-600" />
                    Unduh Snapshot JSON
                </Button>

                <Button
                    v-if="run.status === 'draft'"
                    @click="submitForApproval"
                    size="sm"
                    class="gap-1.5"
                >
                    <Send class="h-4 w-4" />
                    Ajukan untuk Persetujuan
                </Button>

                <div
                    v-if="run.status === 'pending_approval'"
                    class="flex items-center gap-2"
                >
                    <Button
                        @click="showRejectModal = true"
                        variant="destructive"
                        size="sm"
                        class="gap-1.5"
                    >
                        <XCircle class="h-4 w-4" />
                        Minta Revisi
                    </Button>
                    <Button
                        @click="showApproveModal = true"
                        size="sm"
                        class="gap-1.5"
                    >
                        <CheckCircle2 class="h-4 w-4" />
                        Setujui (Approve)
                    </Button>
                </div>
            </div>
        </div>

        <!-- Approved Permanent Lock Alert -->
        <div
            v-if="run.status === 'approved'"
            class="flex items-center justify-between rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-xs text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <div class="flex items-center gap-2.5">
                <ShieldCheck class="h-5 w-5 shrink-0 text-emerald-600" />
                <div>
                    <span class="font-bold"
                        >Kalkulasi Terverifikasi &amp; Terkunci Permanen
                        (Immutable Audit Record)</span
                    >
                    <p class="mt-0.5 text-neutral-600 dark:text-neutral-400">
                        Data ini telah disetujui auditor ESG. Seluruh formula,
                        input sensus, dan koefisien tersimpan paten dan dapat
                        dijadikan baseline multi-periode.
                    </p>
                </div>
            </div>
            <Badge
                variant="outline"
                class="border-emerald-400 bg-emerald-100 font-mono text-emerald-800"
            >
                Audit Signed
            </Badge>
        </div>

        <!-- Primary KPI Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <Card
                class="border border-neutral-200 bg-white/70 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <CardHeader class="pb-2">
                    <CardDescription class="text-xs"
                        >Total Stok Karbon Diserap</CardDescription
                    >
                    <CardTitle
                        class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400"
                    >
                        {{
                            Number(
                                run.result?.total_co2e_t || 0,
                            ).toLocaleString('id-ID', {
                                maximumFractionDigits: 1,
                            })
                        }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-xs text-neutral-400"
                    >t CO2e emisi tersimpan</CardContent
                >
            </Card>

            <Card
                class="border border-neutral-200 bg-white/70 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <CardHeader class="pb-2">
                    <CardDescription class="text-xs"
                        >Kerapatan Karbon per Ha</CardDescription
                    >
                    <CardTitle
                        class="font-mono text-2xl font-bold text-neutral-900 dark:text-neutral-100"
                    >
                        {{
                            Number(run.result?.co2e_stock_t_ha || 0).toFixed(2)
                        }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-xs text-neutral-400"
                    >t CO2e / ha</CardContent
                >
            </Card>

            <Card
                class="border border-neutral-200 bg-white/70 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <CardHeader class="pb-2">
                    <CardDescription class="text-xs"
                        >Total Cadangan Karbon (C)</CardDescription
                    >
                    <CardTitle
                        class="font-mono text-2xl font-bold text-teal-600 dark:text-teal-400"
                    >
                        {{
                            Number(
                                run.result?.total_carbon_t || 0,
                            ).toLocaleString('id-ID', {
                                maximumFractionDigits: 1,
                            })
                        }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-xs text-neutral-400"
                    >ton C murni (CF = 0.47)</CardContent
                >
            </Card>

            <Card
                class="border border-neutral-200 bg-white/70 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <CardHeader class="pb-2">
                    <CardDescription class="text-xs"
                        >Laju Sekuestrasi Tahunan</CardDescription
                    >
                    <CardTitle
                        class="font-mono text-2xl font-bold text-neutral-900 dark:text-neutral-100"
                    >
                        {{
                            run.result?.sequestration_t_co2e_year
                                ? '+' +
                                  Number(
                                      run.result.sequestration_t_co2e_year,
                                  ).toFixed(2)
                                : 'Baseline'
                        }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-xs text-neutral-400">
                    {{
                        run.result?.sequestration_t_co2e_year
                            ? 't CO2e / ha / tahun'
                            : 'Stok awal (titik t1)'
                    }}
                </CardContent>
            </Card>
        </div>

        <!-- 5 Carbon Pools Breakdown Card -->
        <Card class="border border-neutral-200 dark:border-neutral-800">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Layers class="h-5 w-5 text-emerald-600" />
                    Distribusi 5 Kolam Karbon Standar IPCC (AFOLU Guidelines)
                </CardTitle>
                <CardDescription>
                    Rincian biomassa kering, kandungan karbon, dan setara emisi
                    karbon dioksida di setiap kolam.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                    <div
                        v-for="pool in run.result?.pool_results"
                        :key="pool.id"
                        class="space-y-2 rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-700 dark:bg-neutral-800/40"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-sm font-bold text-neutral-900 dark:text-neutral-100"
                            >
                                {{ pool.carbon_pool?.code || 'POOL' }}
                            </span>
                            <span
                                class="font-mono text-[10px] text-neutral-400"
                            >
                                {{ pool.carbon_pool?.name }}
                            </span>
                        </div>
                        <div
                            class="font-mono text-lg font-bold text-emerald-600 dark:text-emerald-400"
                        >
                            {{
                                Number(pool.co2e_t).toLocaleString('id-ID', {
                                    maximumFractionDigits: 1,
                                })
                            }}
                            <span class="text-xs font-normal text-neutral-400"
                                >t CO2e</span
                            >
                        </div>
                        <div
                            class="space-y-0.5 border-t border-neutral-200 pt-1 text-xs text-neutral-500 dark:border-neutral-700"
                        >
                            <div class="flex justify-between">
                                <span>Biomassa:</span>
                                <span class="font-mono"
                                    >{{
                                        Number(pool.biomass_t).toFixed(1)
                                    }}
                                    t</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span>Karbon C:</span>
                                <span class="font-mono"
                                    >{{ Number(pool.carbon_t).toFixed(1) }} t
                                    C</span
                                >
                            </div>
                            <div
                                class="flex justify-between font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                <span>Kerapatan:</span>
                                <span class="font-mono"
                                    >{{
                                        Number(pool.co2e_per_ha).toFixed(2)
                                    }}
                                    t/ha</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Multi-Period Sequestration Comparison (If exists) -->
        <Card
            v-if="run.comparisons && run.comparisons.length > 0"
            class="border border-neutral-200 bg-emerald-50/20 dark:border-neutral-800 dark:bg-emerald-950/10"
        >
            <CardHeader>
                <CardTitle
                    class="flex items-center gap-2 text-lg text-emerald-800 dark:text-emerald-300"
                >
                    <Activity class="h-5 w-5 text-emerald-600" />
                    Analisis Sekuestrasi Multi-Periode (&Delta;C)
                </CardTitle>
                <CardDescription>
                    Perubahan cadangan karbon bersih terhadap sesi pengukuran
                    sebelumnya.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-3"
                    v-for="comp in run.comparisons"
                    :key="comp.id"
                >
                    <div
                        class="rounded-lg border bg-white p-3 dark:bg-neutral-800"
                    >
                        <span class="block text-xs text-neutral-400"
                            >Baseline Sebelumnya</span
                        >
                        <span class="text-sm font-semibold">
                            {{ comp.previous_calculation_run?.period_start }}
                            s/d
                            {{ comp.previous_calculation_run?.period_end }}
                        </span>
                        <div class="mt-1 text-xs text-neutral-500">
                            Total Baseline:
                            {{
                                Number(
                                    comp.previous_calculation_run?.result
                                        ?.total_co2e_t || 0,
                                ).toFixed(1)
                            }}
                            t CO2e
                        </div>
                    </div>
                    <div
                        class="rounded-lg border bg-white p-3 dark:bg-neutral-800"
                    >
                        <span class="block text-xs text-neutral-400"
                            >Delta Penyerapan (&Delta;CO2e)</span
                        >
                        <span
                            class="font-mono text-sm font-semibold text-emerald-600"
                        >
                            +{{
                                Number(comp.delta_co2e_t).toLocaleString(
                                    'id-ID',
                                    { maximumFractionDigits: 1 },
                                )
                            }}
                            t CO2e
                        </span>
                        <div class="mt-1 text-xs text-neutral-500">
                            Net Carbon Sink (&Delta;C:
                            {{ Number(comp.delta_carbon_t).toFixed(1) }} t C)
                        </div>
                    </div>
                    <div
                        class="rounded-lg border bg-white p-3 dark:bg-neutral-800"
                    >
                        <span class="block text-xs text-neutral-400"
                            >Laju Penyerapan Tahunan</span
                        >
                        <span
                            class="font-mono text-sm font-semibold text-emerald-600"
                        >
                            +{{ Number(comp.annualized_co2e_t).toFixed(2) }}
                            t CO2e / tahun
                        </span>
                        <div class="mt-1 text-xs text-neutral-500">
                            Laju terannualisasi
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Immutable Snapshots Inspector -->
        <Card class="border border-neutral-200 dark:border-neutral-800">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <Code class="h-5 w-5 text-emerald-600" />
                            Snapshot Permanen (Immutable Audit Trail)
                        </CardTitle>
                        <CardDescription>
                            Snapshot JSON lengkap dari parameter, input sensus,
                            dan formula alometrik untuk audit sertifikasi
                            karbon.
                        </CardDescription>
                    </div>
                    <!-- Snapshot Tabs -->
                    <div
                        class="flex items-center rounded-lg bg-neutral-100 p-1 text-xs font-medium dark:bg-neutral-800"
                    >
                        <button
                            @click="activeSnapshotTab = 'formula'"
                            :class="[
                                'cursor-pointer rounded-md px-3 py-1.5 transition-colors',
                                activeSnapshotTab === 'formula'
                                    ? 'bg-white font-bold shadow-sm dark:bg-neutral-900'
                                    : 'text-neutral-500',
                            ]"
                        >
                            Formula Matematis
                        </button>
                        <button
                            @click="activeSnapshotTab = 'input'"
                            :class="[
                                'cursor-pointer rounded-md px-3 py-1.5 transition-colors',
                                activeSnapshotTab === 'input'
                                    ? 'bg-white font-bold shadow-sm dark:bg-neutral-900'
                                    : 'text-neutral-500',
                            ]"
                        >
                            Input Sensus
                        </button>
                        <button
                            @click="activeSnapshotTab = 'parameter'"
                            :class="[
                                'cursor-pointer rounded-md px-3 py-1.5 transition-colors',
                                activeSnapshotTab === 'parameter'
                                    ? 'bg-white font-bold shadow-sm dark:bg-neutral-900'
                                    : 'text-neutral-500',
                            ]"
                        >
                            Parameter Terversi
                        </button>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div
                    class="max-h-96 overflow-x-auto rounded-xl bg-neutral-950 p-4 font-mono text-xs text-neutral-200"
                >
                    <pre v-if="activeSnapshotTab === 'formula'">{{
                        JSON.stringify(run.formula_snapshot, null, 2)
                    }}</pre>
                    <pre v-if="activeSnapshotTab === 'input'">{{
                        JSON.stringify(run.input_snapshot, null, 2)
                    }}</pre>
                    <pre v-if="activeSnapshotTab === 'parameter'">{{
                        JSON.stringify(run.parameter_snapshot, null, 2)
                    }}</pre>
                </div>
            </CardContent>
        </Card>

        <!-- Audit Trail / Approval History -->
        <Card class="border border-neutral-200 dark:border-neutral-800">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <History class="h-5 w-5 text-emerald-600" />
                    Riwayat Verifikasi &amp; Jejak Audit (Governance)
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div class="flex items-start gap-3 text-xs">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                        >
                            1
                        </div>
                        <div>
                            <span
                                class="font-semibold text-neutral-800 dark:text-neutral-200"
                                >Sesi Kalkulasi Dijalankan</span
                            >
                            <div class="text-neutral-500">
                                Oleh:
                                {{ run.executor?.name || 'User Staf' }} ({{
                                    run.executor?.email
                                }}) pada {{ run.calculated_at }}
                            </div>
                        </div>
                    </div>

                    <div
                        v-for="app in run.approvals"
                        :key="app.id"
                        class="flex items-start gap-3 text-xs"
                    >
                        <div
                            :class="[
                                'flex h-7 w-7 items-center justify-center rounded-full font-bold',
                                app.status === 'approved'
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400'
                                    : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400',
                            ]"
                        >
                            <UserCheck
                                v-if="app.status === 'approved'"
                                class="h-4 w-4"
                            />
                            <XCircle v-else class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="font-semibold text-neutral-800 dark:text-neutral-200"
                            >
                                {{
                                    app.status === 'approved'
                                        ? 'Disetujui Auditor'
                                        : 'Diminta Revisi'
                                }}
                            </span>
                            <div class="text-neutral-500">
                                Peninjau: {{ app.reviewer?.name }} pada
                                {{ app.reviewed_at }}
                            </div>
                            <div
                                v-if="app.notes"
                                class="mt-1 rounded border bg-neutral-50 p-2 text-neutral-700 italic dark:bg-neutral-800 dark:text-neutral-300"
                            >
                                "{{ app.notes }}"
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- APPROVE DIALOG MODAL -->
        <div
            v-if="showApproveModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <h3
                    class="flex items-center gap-2 text-lg font-bold text-neutral-900 dark:text-neutral-100"
                >
                    <CheckCircle2 class="h-5 w-5 text-emerald-600" />
                    Persetujuan Verifikasi Kalkulasi
                </h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Menyetujui sesi ini akan mengunci seluruh data secara
                    permanen. Rekam jejak akan ditandai dengan tanda tangan
                    kriptografis auditor Anda.
                </p>
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                    >
                        Catatan Auditor (Opsional)
                    </label>
                    <textarea
                        v-model="approvalNotes"
                        rows="3"
                        placeholder="Catatan kepatuhan metodologi atau komentar verifikasi..."
                        class="w-full rounded-lg border border-neutral-300 bg-white p-2.5 text-xs text-neutral-900 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                    ></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <Button
                        @click="showApproveModal = false"
                        variant="outline"
                        size="sm"
                    >
                        Batal
                    </Button>
                    <Button @click="approveRun" size="sm">
                        Konfirmasi & Setujui
                    </Button>
                </div>
            </div>
        </div>

        <!-- REJECT DIALOG MODAL -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <h3
                    class="flex items-center gap-2 text-lg font-bold text-neutral-900 dark:text-neutral-100"
                >
                    <AlertTriangle class="h-5 w-5 text-rose-600" />
                    Minta Revisi Data Sensus
                </h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Status akan dikembalikan ke Draft/Perlu Revisi agar surveyor
                    dapat memperbaiki data sensus lapangan atau data lab tanah.
                </p>
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                    >
                        Alasan Penolakan / Catatan Perbaikan
                        <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="approvalNotes"
                        rows="3"
                        placeholder="Tuliskan data atau parameter apa yang perlu diperbaiki oleh surveyor..."
                        class="w-full rounded-lg border border-neutral-300 bg-white p-2.5 text-xs text-neutral-900 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                    ></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <Button
                        @click="showRejectModal = false"
                        variant="outline"
                        size="sm"
                    >
                        Batal
                    </Button>
                    <Button @click="rejectRun" variant="destructive" size="sm">
                        Kirim Catatan Revisi
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
