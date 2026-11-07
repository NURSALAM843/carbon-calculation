<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    Activity,
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    Calculator,
    Check,
    CheckCircle2,
    Clock,
    Database,
    FileSpreadsheet,
    Flame,
    Info,
    Layers,
    Leaf,
    MapPin,
    RotateCcw,
    Send,
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
import SearchableSelect from '@/components/SearchableSelect.vue';

interface BlockItem {
    id: string;
    code: string;
    name: string;
    ecosystem_type: string;
    area_ha: number;
    soil_type: string;
    land_type: string;
    estate?: { id: string; name: string; code: string };
    crop_cycles?: Array<{
        id: string;
        crop_type: string;
        planting_year: number;
        palm_stands?: Array<{
            growth_stage: string;
            average_height_m: number;
            tree_count: number;
            density_per_ha: number;
            age_years: number;
        }>;
    }>;
    field_measurements?: Array<{
        measurement_date: string;
        tree_measurements?: Array<{
            dbh_cm: number;
            wood_density_g_cm3: number;
            botanical_species: string;
        }>;
        palm_measurements?: Array<{
            stem_height_m: number;
            frond_count: number;
            growth_stage: string;
        }>;
    }>;
    soil_measurements?: Array<{
        measurement_date: string;
        bulk_density: number;
        soil_carbon_percent: number;
        soil_organic_carbon_t_ha: number;
    }>;
}

interface MethodologyVersionItem {
    id: string;
    version: string;
    methodology_id: string;
    methodology?: {
        id: string;
        name: string;
        code: string;
        description: string;
        tier_level: number;
        ecosystem_scope?: string;
    };
}

interface Props {
    blocks: BlockItem[];
    methodologies: MethodologyVersionItem[];
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Calculation Runs', href: '/calculations' },
            { title: 'Wizard Kalkulasi', href: '/calculations/create' },
        ],
    },
});

// Stepper state
const currentStep = ref(1);

// Form state
const form = ref({
    land_block_id: '',
    period_start: `${new Date().getFullYear()}-01-01`,
    period_end: `${new Date().getFullYear()}-12-31`,
    calculation_type: 'stock_only',
    methodology_version_id: '',
    previous_calculation_run_id: '',
    submit_for_approval: false,
    // Custom overrides
    custom_height_m: null as number | null,
    custom_density_ha: null as number | null,
    custom_soil_c_pct: null as number | null,
    custom_bulk_density: null as number | null,
});

// Simulation state
const isSimulating = ref(false);
const simulationError = ref<string | null>(null);
const simulationResult = ref<{
    total_biomass_t: number;
    total_carbon_t: number;
    total_co2e_t: number;
    carbon_stock_t_ha: number;
    co2e_stock_t_ha: number;
    pools_breakdown: Record<
        string,
        {
            biomass_t: number;
            carbon_t: number;
            co2e_t: number;
            co2e_per_ha: number;
        }
    >;
    formula_snapshot?: Record<string, any>;
    sequestration_t_co2e_year?: number | null;
    comparison_delta?: any;
} | null>(null);

// Selected block
const selectedBlock = computed(() => {
    return props.blocks.find((b) => b.id === form.value.land_block_id);
});

// Auto-select methodology when block ecosystem changes
watch(
    () => selectedBlock.value,
    (block) => {
        if (!block) return;
        if (block.ecosystem_type === 'oil_palm') {
            const palmMeth = props.methodologies.find(
                (m) =>
                    m.methodology?.code?.toLowerCase().includes('ppks') ||
                    m.methodology?.code?.toLowerCase().includes('palm'),
            );
            if (palmMeth) form.value.methodology_version_id = palmMeth.id;
        } else {
            const forestMeth = props.methodologies.find(
                (m) =>
                    m.methodology?.code?.toLowerCase().includes('chave') ||
                    m.methodology?.code?.toLowerCase().includes('ipcc'),
            );
            if (forestMeth) form.value.methodology_version_id = forestMeth.id;
        }
    },
);

// Helpers
const getEcosystemName = (type?: string) => {
    switch (type) {
        case 'oil_palm':
            return 'Kelapa Sawit (Elaeis guineensis)';
        case 'natural_forest':
            return 'Hutan Alam Tropis (Natural Forest)';
        case 'hti_plantation':
            return 'HTI / Tanaman Industri Berkayu';
        case 'mangrove':
            return 'Mangrove / Ekosistem Karbon Biru';
        case 'peat_swamp_forest':
            return 'Hutan Rawa Gambut (Peat Swamp)';
        default:
            return type || 'Tidak diketahui';
    }
};

const blockSelectOptions = computed(() => {
    return props.blocks.map((b) => ({
        value: b.id,
        label: `[${b.code}] ${b.name} - ${b.estate?.name || ''}`,
        description: `${b.area_ha} ha • ${getEcosystemName(b.ecosystem_type)} • Tanah: ${b.soil_type}`,
    }));
});

const getEcosystemBadgeClass = (type?: string) => {
    switch (type) {
        case 'oil_palm':
            return 'bg-lime-500/15 text-lime-700 dark:text-lime-400 border-lime-500/30';
        case 'natural_forest':
            return 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30';
        case 'hti_plantation':
            return 'bg-teal-500/15 text-teal-700 dark:text-teal-400 border-teal-500/30';
        case 'mangrove':
            return 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-400 border-cyan-500/30';
        default:
            return 'bg-neutral-500/15 text-neutral-600';
    }
};

// Simulation Action
const runSimulation = async () => {
    isSimulating.value = true;
    simulationError.value = null;
    simulationResult.value = null;

    try {
        const response = await fetch('/calculations/simulate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content || '',
                Accept: 'application/json',
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();
        if (response.ok && data.success) {
            simulationResult.value = data.preview;
        } else {
            simulationError.value =
                data.message || 'Gagal menjalankan simulasi perhitungan.';
        }
    } catch (err: any) {
        simulationError.value =
            err?.message || 'Terjadi kesalahan jaringan saat simulasi.';
    } finally {
        isSimulating.value = false;
    }
};

// Final Submit
const isSubmitting = ref(false);
const submitCalculation = () => {
    isSubmitting.value = true;
    router.post('/calculations', form.value, {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <Head title="Wizard Kalkulasi Karbon Adaptif" />
        <!-- Back & Title -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link href="/calculations">
                    <Button
                        variant="outline"
                        size="sm"
                        class="cursor-pointer gap-1.5"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                    >
                        <Calculator class="h-6 w-6 text-emerald-600" />
                        Wizard Kalkulasi Karbon Adaptif
                    </h1>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Mesin alometrik polimorfik otomatis mendeteksi ekosistem
                        kelapa sawit vs pohon hutan berkayu.
                    </p>
                </div>
            </div>
            <Badge
                variant="outline"
                class="border-emerald-500/30 bg-emerald-50 font-mono text-xs text-emerald-700 dark:bg-emerald-950/40"
            >
                Langkah {{ currentStep }} dari 6
            </Badge>
        </div>

        <!-- Stepper Indicators -->
        <div class="grid grid-cols-6 gap-2 text-center text-xs font-medium">
            <div
                v-for="(label, idx) in [
                    'Blok Lahan',
                    'Periode',
                    'Metodologi',
                    'Data Input',
                    'Simulasi',
                    'Simpan',
                ]"
                :key="idx"
                :class="[
                    'flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border px-1.5 py-2.5 transition-all duration-200',
                    currentStep === idx + 1
                        ? 'border-emerald-600 bg-emerald-600 font-semibold text-white shadow-sm'
                        : currentStep > idx + 1
                          ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400'
                          : 'border-neutral-200 bg-neutral-50 text-neutral-400 dark:border-neutral-800 dark:bg-neutral-800/40',
                ]"
                @click="currentStep = idx + 1"
            >
                <span class="text-[10px] tracking-wider uppercase opacity-80"
                    >0{{ idx + 1 }}</span
                >
                <span class="w-full truncate">{{ label }}</span>
            </div>
        </div>

        <!-- STEP 1: Pilih Blok Lahan -->
        <Card
            v-if="currentStep === 1"
            class="border border-neutral-200 dark:border-neutral-800"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <MapPin class="h-5 w-5 text-emerald-600" />
                    Langkah 1: Pilih Blok Lahan Target
                </CardTitle>
                <CardDescription>
                    Pilih blok konsesi untuk mendeteksi tipe ekosistem, struktur
                    tanaman, dan profil tanah secara otomatis.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div>
                    <label
                        class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300"
                    >
                        Pilih Blok Lahan
                        <span class="text-rose-500">*</span>
                    </label>
                    <SearchableSelect
                        v-model="form.land_block_id"
                        :options="blockSelectOptions"
                        placeholder="-- Cari / Pilih Salah Satu Blok Lahan --"
                        search-placeholder="Ketik kode blok, nama, atau unit estate..."
                        required
                    />
                </div>

                <!-- Highlight Info Blok Terpilih -->
                <div
                    v-if="selectedBlock"
                    class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50 p-5 dark:border-neutral-700 dark:bg-neutral-800/60"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                            >Karakteristik Ekosistem</span
                        >
                        <Badge
                            :class="
                                getEcosystemBadgeClass(
                                    selectedBlock.ecosystem_type,
                                )
                            "
                            variant="outline"
                        >
                            {{ getEcosystemName(selectedBlock.ecosystem_type) }}
                        </Badge>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-xs sm:grid-cols-4">
                        <div>
                            <span class="block text-neutral-400"
                                >Unit Estate</span
                            >
                            <span
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-200"
                                >{{ selectedBlock.estate?.name }}</span
                            >
                        </div>
                        <div>
                            <span class="block text-neutral-400"
                                >Luas Efektif</span
                            >
                            <span
                                class="font-mono text-sm font-semibold text-neutral-800 dark:text-neutral-200"
                                >{{ selectedBlock.area_ha }} ha</span
                            >
                        </div>
                        <div>
                            <span class="block text-neutral-400"
                                >Tipe Tanah</span
                            >
                            <span
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-200"
                                >{{ selectedBlock.soil_type }}</span
                            >
                        </div>
                        <div>
                            <span class="block text-neutral-400"
                                >Topografi / Lahan</span
                            >
                            <span
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-200"
                                >{{ selectedBlock.land_type }}</span
                            >
                        </div>
                    </div>

                    <!-- Deteksi Khusus Sawit -->
                    <div
                        v-if="selectedBlock.ecosystem_type === 'oil_palm'"
                        class="rounded-lg border border-lime-500/20 bg-lime-500/10 p-3 text-xs text-lime-900 dark:text-lime-300"
                    >
                        <span
                            class="mb-1 flex items-center gap-1.5 font-semibold"
                        >
                            <Sprout class="h-4 w-4" /> Deteksi Alometrik
                            Monokotil Sawit (Elaeis guineensis):
                        </span>
                        Sistem akan mengaktifkan model pemanjangan batang Khalid
                        et al. 1999 dan rasio mutasi akar-ke-pucuk (0.205 &rarr;
                        0.180).
                    </div>

                    <!-- Deteksi Hutan Alam -->
                    <div
                        v-else
                        class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3 text-xs text-emerald-900 dark:text-emerald-300"
                    >
                        <span
                            class="mb-1 flex items-center gap-1.5 font-semibold"
                        >
                            <TreePine class="h-4 w-4" /> Deteksi Alometrik Pohon
                            Berkayu Hutan Tropis:
                        </span>
                        Sistem akan menerapkan model alometrik Chave et al. 2014
                        &amp; SNI 7724 berbasis DBH, tinggi, dan berat jenis
                        kayu (&rho;).
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <Button
                        :disabled="!form.land_block_id"
                        @click="currentStep = 2"
                        class="gap-2"
                    >
                        Lanjut: Periode & Tipe
                        <ArrowRight class="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- STEP 2: Periode & Tipe Kalkulasi -->
        <Card
            v-if="currentStep === 2"
            class="border border-neutral-200 dark:border-neutral-800"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Clock class="h-5 w-5 text-emerald-600" />
                    Langkah 2: Tentukan Periode Waktu & Tipe Akuntansi
                </CardTitle>
                <CardDescription>
                    Pilih rentang tanggal penilaian inventarisasi dan model
                    analisis (stok tunggal atau sekuestrasi delta
                    multi-periode).
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-neutral-700 dark:text-neutral-300"
                        >
                            Tanggal Mulai Periode
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.period_start"
                            type="date"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-neutral-700 dark:text-neutral-300"
                        >
                            Tanggal Akhir Periode
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.period_end"
                            type="date"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-semibold text-neutral-700 dark:text-neutral-300"
                    >
                        Mode Akuntansi Karbon
                        <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div
                            :class="[
                                'flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition-all',
                                form.calculation_type === 'stock_only'
                                    ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20'
                                    : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700',
                            ]"
                            @click="form.calculation_type = 'stock_only'"
                        >
                            <input
                                type="radio"
                                name="calc_type"
                                value="stock_only"
                                v-model="form.calculation_type"
                                class="mt-1 text-emerald-600 focus:ring-emerald-500"
                            />
                            <div>
                                <div
                                    class="text-sm font-semibold text-neutral-900 dark:text-neutral-100"
                                >
                                    Cadangan Karbon Tunggal (Carbon Stock)
                                </div>
                                <p
                                    class="mt-1 text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    Menilai total biomassa tegakan dan 5 kolam
                                    karbon IPCC pada satu titik waktu
                                    (baseline).
                                </p>
                            </div>
                        </div>

                        <div
                            :class="[
                                'flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition-all',
                                form.calculation_type === 'sequestration'
                                    ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20'
                                    : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700',
                            ]"
                            @click="form.calculation_type = 'sequestration'"
                        >
                            <input
                                type="radio"
                                name="calc_type"
                                value="sequestration"
                                v-model="form.calculation_type"
                                class="mt-1 text-emerald-600 focus:ring-emerald-500"
                            />
                            <div>
                                <div
                                    class="text-sm font-semibold text-neutral-900 dark:text-neutral-100"
                                >
                                    Laju Sekuestrasi Karbon Bersih (Delta ΔC)
                                </div>
                                <p
                                    class="mt-1 text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    Menghitung penyerapan bersih tahunan (t
                                    CO2e/ha/tahun) dibandingkan sesi
                                    inventarisasi sebelumnya.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <Button
                        @click="currentStep = 1"
                        variant="outline"
                        class="cursor-pointer gap-1.5"
                    >
                        <ArrowLeft class="h-4 w-4" /> Kembali
                    </Button>
                    <Button @click="currentStep = 3" class="gap-2">
                        Lanjut: Pilih Metodologi
                        <ArrowRight class="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- STEP 3: Pilih Metodologi -->
        <Card
            v-if="currentStep === 3"
            class="border border-neutral-200 dark:border-neutral-800"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Layers class="h-5 w-5 text-emerald-600" />
                    Langkah 3: Pilih Metodologi Ilmiah & Parameter Terversi
                </CardTitle>
                <CardDescription>
                    Katalog standar sertifikasi karbon (IPCC 2019, Chave et al.
                    2014, SNI 7724, PPKS Sawit).
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="space-y-3">
                    <div
                        v-for="m in methodologies"
                        :key="m.id"
                        :class="[
                            'flex cursor-pointer items-start justify-between gap-3 rounded-xl border-2 p-4 transition-all',
                            form.methodology_version_id === m.id
                                ? 'border-emerald-600 bg-emerald-50/50 shadow-sm dark:bg-emerald-950/20'
                                : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-800 dark:hover:border-neutral-700',
                        ]"
                        @click="form.methodology_version_id = m.id"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="methodology"
                                    :value="m.id"
                                    v-model="form.methodology_version_id"
                                    class="text-emerald-600 focus:ring-emerald-500"
                                />
                                <span
                                    class="text-sm font-bold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ m.methodology?.name }}
                                </span>
                                <Badge
                                    variant="outline"
                                    class="font-mono text-[10px]"
                                >
                                    Ver: {{ m.version }}
                                </Badge>
                            </div>
                            <p
                                class="pl-6 text-xs text-neutral-500 dark:text-neutral-400"
                            >
                                {{ m.methodology?.description }}
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            <Badge variant="secondary" class="text-xs">
                                IPCC Tier
                                {{ m.methodology?.tier_level || 2 }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <Button
                        @click="currentStep = 2"
                        variant="outline"
                        class="cursor-pointer gap-1.5"
                    >
                        <ArrowLeft class="h-4 w-4" /> Kembali
                    </Button>
                    <Button
                        :disabled="!form.methodology_version_id"
                        @click="currentStep = 4"
                        class="gap-2"
                    >
                        Lanjut: Validasi Data
                        <ArrowRight class="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- STEP 4: Validasi & Input Data Adaptif -->
        <Card
            v-if="currentStep === 4"
            class="border border-neutral-200 dark:border-neutral-800"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Database class="h-5 w-5 text-emerald-600" />
                    Langkah 4: Parameter & Data Lapangan Adaptif
                </CardTitle>
                <CardDescription>
                    Sistem mendeteksi data sensus lapangan terbaru yang
                    terhubung dengan blok ini. Anda juga dapat memberikan nilai
                    override manual jika diperlukan.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Form Khusus Sawit -->
                <div
                    v-if="selectedBlock?.ecosystem_type === 'oil_palm'"
                    class="space-y-4"
                >
                    <div
                        class="flex items-center justify-between rounded-xl border border-lime-500/20 bg-lime-500/10 p-4 text-xs text-lime-900 dark:text-lime-200"
                    >
                        <span class="font-semibold"
                            >Format Adaptif: Kelapa Sawit (Palm Phenology)</span
                        >
                        <Badge
                            variant="outline"
                            class="border-lime-500/40 bg-lime-500/20 text-lime-800"
                            >Elaeis guineensis</Badge
                        >
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                Tinggi Batang Rata-rata (meter) [Opsional
                                Override]
                            </label>
                            <input
                                v-model.number="form.custom_height_m"
                                type="number"
                                step="0.1"
                                placeholder="Contoh: 3.5 (default dari sensus)"
                                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                Kerapatan Pohon (pokok / ha) [Opsional Override]
                            </label>
                            <input
                                v-model.number="form.custom_density_ha"
                                type="number"
                                step="1"
                                placeholder="Contoh: 136 (default: 136-143 pokok/ha)"
                                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                    </div>
                </div>

                <!-- Form Khusus Hutan / Pohon Berkayu -->
                <div v-else class="space-y-4">
                    <div
                        class="flex items-center justify-between rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-xs text-emerald-900 dark:text-emerald-200"
                    >
                        <span class="font-semibold"
                            >Format Adaptif: Pohon Berkayu (Forest Woody
                            Biomass)</span
                        >
                        <Badge
                            variant="outline"
                            class="border-emerald-500/40 bg-emerald-500/20 text-emerald-800"
                            >Chave 2014 &amp; SNI</Badge
                        >
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                Kerapatan Tegakan Pohon (batang / ha) [Opsional
                                Override]
                            </label>
                            <input
                                v-model.number="form.custom_density_ha"
                                type="number"
                                step="1"
                                placeholder="Contoh: 450 (pohon berkayu / ha)"
                                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                Tinggi Tajuk Rata-rata (meter) [Opsional
                                Override]
                            </label>
                            <input
                                v-model.number="form.custom_height_m"
                                type="number"
                                step="0.5"
                                placeholder="Contoh: 22.5 m"
                                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                    </div>
                </div>

                <!-- Data Tanah / SOC -->
                <div
                    class="space-y-4 border-t border-neutral-200 pt-2 dark:border-neutral-800"
                >
                    <h4
                        class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                    >
                        Parameter Uji Tanah (Soil Organic Carbon)
                    </h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                Kandungan Karbon Organik Tanah (% C) [Opsional]
                            </label>
                            <input
                                v-model.number="form.custom_soil_c_pct"
                                type="number"
                                step="0.01"
                                placeholder="Contoh: 2.15 (mineral) atau 45.0 (gambut)"
                                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                Bulk Density (g/cm³) [Opsional]
                            </label>
                            <input
                                v-model.number="form.custom_bulk_density"
                                type="number"
                                step="0.01"
                                placeholder="Contoh: 1.15 (mineral) atau 0.15 (gambut)"
                                class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <Button
                        @click="currentStep = 3"
                        variant="outline"
                        class="cursor-pointer gap-1.5"
                    >
                        <ArrowLeft class="h-4 w-4" /> Kembali
                    </Button>
                    <Button
                        @click="
                            () => {
                                currentStep = 5;
                                runSimulation();
                            }
                        "
                        class="gap-2"
                    >
                        Lanjut: Simulasi Cepat
                        <Activity class="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- STEP 5: Pratinjau Cepat / Instant Simulation -->
        <Card
            v-if="currentStep === 5"
            class="border border-neutral-200 dark:border-neutral-800"
        >
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <Layers class="h-5 w-5 text-emerald-600" />
                            Langkah 5: Pratinjau Simulasi Hasil Engine Karbon
                        </CardTitle>
                        <CardDescription>
                            Hasil kalkulasi instan sebelum disimpan ke basis
                            data dan dikunci.
                        </CardDescription>
                    </div>
                    <Button
                        :disabled="isSimulating"
                        @click="runSimulation"
                        variant="outline"
                        size="sm"
                        class="cursor-pointer gap-1.5"
                    >
                        <RotateCcw
                            :class="[
                                'h-3.5 w-3.5',
                                isSimulating && 'animate-spin',
                            ]"
                        />
                        Hitung Ulang
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="space-y-6">
                <div v-if="isSimulating" class="space-y-3 py-12 text-center">
                    <div
                        class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-emerald-600 border-t-transparent"
                    ></div>
                    <p
                        class="text-sm font-semibold text-neutral-700 dark:text-neutral-300"
                    >
                        Menjalankan alometrik matematis dan 5 kolam karbon...
                    </p>
                </div>

                <div
                    v-else-if="simulationError"
                    class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300"
                >
                    <AlertCircle class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <div class="font-bold">Simulasi Gagal</div>
                        <div>{{ simulationError }}</div>
                    </div>
                </div>

                <div v-else-if="simulationResult" class="space-y-6">
                    <!-- KPI Ribbon -->
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div
                            class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/30"
                        >
                            <span
                                class="text-xs font-medium text-emerald-700 dark:text-emerald-400"
                                >Total Cadangan CO2e</span
                            >
                            <div
                                class="mt-1 font-mono text-2xl font-bold text-emerald-900 dark:text-emerald-200"
                            >
                                {{
                                    Number(
                                        simulationResult.total_co2e_t,
                                    ).toLocaleString('id-ID', {
                                        maximumFractionDigits: 1,
                                    })
                                }}
                            </div>
                            <span class="text-[11px] text-emerald-600"
                                >t CO2e</span
                            >
                        </div>

                        <div
                            class="rounded-xl border border-teal-200 bg-teal-50 p-4 dark:border-teal-800 dark:bg-teal-950/30"
                        >
                            <span
                                class="text-xs font-medium text-teal-700 dark:text-teal-400"
                                >Total Karbon Murni (C)</span
                            >
                            <div
                                class="mt-1 font-mono text-2xl font-bold text-teal-900 dark:text-teal-200"
                            >
                                {{
                                    Number(
                                        simulationResult.total_carbon_t,
                                    ).toLocaleString('id-ID', {
                                        maximumFractionDigits: 1,
                                    })
                                }}
                            </div>
                            <span class="text-[11px] text-teal-600">t C</span>
                        </div>

                        <div
                            class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800"
                        >
                            <span class="text-xs font-medium text-neutral-500"
                                >Kerapatan Karbon</span
                            >
                            <div
                                class="mt-1 font-mono text-2xl font-bold text-neutral-900 dark:text-neutral-100"
                            >
                                {{
                                    Number(
                                        simulationResult.co2e_stock_t_ha,
                                    ).toFixed(2)
                                }}
                            </div>
                            <span class="text-[11px] text-neutral-500"
                                >t CO2e / ha</span
                            >
                        </div>

                        <div
                            class="rounded-xl border border-lime-200 bg-lime-50 p-4 dark:border-lime-800 dark:bg-lime-950/30"
                        >
                            <span
                                class="text-xs font-medium text-lime-700 dark:text-lime-400"
                                >Total Biomassa Kering</span
                            >
                            <div
                                class="mt-1 font-mono text-2xl font-bold text-lime-900 dark:text-lime-200"
                            >
                                {{
                                    Number(
                                        simulationResult.total_biomass_t,
                                    ).toLocaleString('id-ID', {
                                        maximumFractionDigits: 1,
                                    })
                                }}
                            </div>
                            <span class="text-[11px] text-lime-600"
                                >ton biomassa</span
                            >
                        </div>
                    </div>

                    <!-- 5 IPCC Carbon Pools Breakdown -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                        >
                            Rincian 5 Kolam Karbon IPCC
                        </h4>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                            <div
                                v-for="(
                                    val, code
                                ) in simulationResult.pools_breakdown"
                                :key="code"
                                class="rounded-lg border border-neutral-200 bg-neutral-50/50 p-3.5 text-xs dark:border-neutral-700 dark:bg-neutral-800/40"
                            >
                                <div
                                    class="mb-1 flex items-center justify-between font-bold text-neutral-800 dark:text-neutral-200"
                                >
                                    <span>{{ code }}</span>
                                </div>
                                <div
                                    class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400"
                                >
                                    {{
                                        Number(val.co2e_t).toLocaleString(
                                            'id-ID',
                                            { maximumFractionDigits: 1 },
                                        )
                                    }}
                                    <span
                                        class="text-[10px] font-normal text-neutral-400"
                                        >t CO2e</span
                                    >
                                </div>
                                <div class="mt-1 text-[11px] text-neutral-400">
                                    {{ Number(val.co2e_per_ha).toFixed(1) }}
                                    t CO2e/ha
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sekuestrasi Delta Jika ada -->
                    <div
                        v-if="simulationResult.sequestration_t_co2e_year"
                        class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-900 dark:text-emerald-300"
                    >
                        <div class="flex items-center gap-2 font-bold">
                            <Activity class="h-4 w-4" /> Laju Penyerapan Bersih
                            Tahunan (Net Sequestration Rate):
                        </div>
                        <div class="mt-1 font-mono text-xl font-bold">
                            +{{
                                Number(
                                    simulationResult.sequestration_t_co2e_year,
                                ).toLocaleString('id-ID', {
                                    maximumFractionDigits: 2,
                                })
                            }}
                            t CO2e / tahun
                        </div>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <Button
                        @click="currentStep = 4"
                        variant="outline"
                        class="cursor-pointer gap-1.5"
                    >
                        <ArrowLeft class="h-4 w-4" /> Kembali
                    </Button>
                    <Button
                        :disabled="!simulationResult"
                        @click="currentStep = 6"
                        class="gap-2"
                    >
                        Lanjut: Konfirmasi & Simpan
                        <ArrowRight class="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- STEP 6: Konfirmasi & Eksekusi Simpan -->
        <Card
            v-if="currentStep === 6"
            class="border border-neutral-200 dark:border-neutral-800"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <CheckCircle2 class="h-5 w-5 text-emerald-600" />
                    Langkah 6: Konfirmasi Penyimpanan & Alur Persetujuan
                </CardTitle>
                <CardDescription>
                    Kalkulasi akan direkam bersama snapshot permanen parameter
                    dan rumus untuk kepatuhan audit karbon.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <div
                    class="space-y-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800/80"
                >
                    <div
                        class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                    >
                        Ringkasan Sesi
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-xs sm:grid-cols-3">
                        <div>
                            <span class="block text-neutral-400"
                                >Blok Target</span
                            >
                            <span
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ selectedBlock?.name }} ({{
                                    selectedBlock?.code
                                }})
                            </span>
                        </div>
                        <div>
                            <span class="block text-neutral-400">Periode</span>
                            <span
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ form.period_start }} s/d
                                {{ form.period_end }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-neutral-400"
                                >Total Emisi Diserap</span
                            >
                            <span
                                class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400"
                            >
                                {{
                                    Number(
                                        simulationResult?.total_co2e_t || 0,
                                    ).toLocaleString('id-ID', {
                                        maximumFractionDigits: 1,
                                    })
                                }}
                                t CO2e
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Verifikasi Langsung -->
                <div
                    class="flex items-start gap-3 rounded-xl border-2 border-amber-500/40 bg-amber-50/40 p-4 dark:bg-amber-950/20"
                >
                    <input
                        type="checkbox"
                        id="submit_for_approval"
                        v-model="form.submit_for_approval"
                        class="mt-1 h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                    />
                    <label for="submit_for_approval" class="cursor-pointer">
                        <span
                            class="block text-sm font-semibold text-neutral-900 dark:text-neutral-100"
                        >
                            Ajukan langsung ke Antrean Verifikasi Auditor
                            (Submit for Approval)
                        </span>
                        <span
                            class="mt-0.5 block text-xs text-neutral-500 dark:text-neutral-400"
                        >
                            Jika dicentang, status akan menjadi "Pending
                            Approval" dan data sensus terkunci sementara
                            menunggu peninjauan auditor ESG. Jika tidak, akan
                            disimpan sebagai Draf.
                        </span>
                    </label>
                </div>

                <div class="flex justify-between pt-4">
                    <Button
                        @click="currentStep = 5"
                        variant="outline"
                        class="cursor-pointer gap-1.5"
                    >
                        <ArrowLeft class="h-4 w-4" /> Kembali
                    </Button>
                    <Button
                        :disabled="isSubmitting"
                        @click="submitCalculation"
                        class="gap-2 px-6"
                    >
                        <Send class="h-4 w-4" />
                        {{
                            isSubmitting
                                ? 'Menyimpan...'
                                : 'Simpan & Kunci Kalkulasi'
                        }}
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
