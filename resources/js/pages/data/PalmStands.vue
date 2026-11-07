<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Layers, Plus, Sprout } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import SearchableSelect from '@/components/SearchableSelect.vue';

interface PalmBlock {
    id: string;
    code: string;
    name: string;
    area_ha: number;
    soil_type: string;
    estate?: { name: string; code: string };
    crop_cycles?: Array<{
        id: string;
        planting_year: number;
        planting_density: number;
        palm_stands?: Array<{
            id: string;
            growth_stage: string;
            tree_count: number;
            density_per_ha: number;
            average_height_m: number;
            age_years: number;
        }>;
    }>;
}

interface Props {
    blocks: PalmBlock[];
}

const props = defineProps<Props>();

const showModal = ref(false);
const form = ref({
    land_block_id: props.blocks[0]?.id || '',
    planting_year: 2018,
    tree_count: 4200,
    growth_stage: 'TM_PRIMA',
    average_height_m: 4.8,
    avg_frond_count: 38,
});

const blockOptions = computed(() => {
    return props.blocks.map((b) => ({
        value: b.id,
        label: `[${b.code}] ${b.name} (${b.area_ha} ha)`,
        description: b.estate?.name ? `Estate: ${b.estate.name}` : undefined,
    }));
});

const stageOptions = [
    {
        value: 'TBM_1',
        label: 'TBM 1 (Tahun ke-1) - R/S = 0.205',
        description: 'Roset pelepah dasar, batang 0 m',
    },
    {
        value: 'TBM_2',
        label: 'TBM 2 (Tahun ke-2)',
        description: 'Pertumbuhan pelepah aktif, tinggi 0.5 m',
    },
    {
        value: 'TBM_3',
        label: 'TBM 3 (Tahun ke-3)',
        description: 'Menjelang TM, tinggi 1.0 m',
    },
    {
        value: 'TM_MUDA',
        label: 'TM Muda (Tahun ke-4 s/d 8)',
        description: 'Mulai berbuah, pemanjangan 45 cm/thn',
    },
    {
        value: 'TM_PRIMA',
        label: 'TM Prima (Tahun ke-9 s/d 18) - Puncak Stok',
        description: 'Biomassa & penyerapan karbon maksimal',
    },
    {
        value: 'TM_TUA',
        label: 'TM Tua (> 18 tahun) - R/S bermutasi ke 0.180',
        description: 'Fase senesens, laju melambat',
    },
];

const submitPalmStand = () => {
    router.post('/data/palm-stands', form.value, {
        onSuccess: () => {
            showModal.value = false;
        },
    });
};

const getStageBadge = (stage: string) => {
    switch (stage) {
        case 'TBM_1':
            return {
                label: 'TBM 1 (Umur 1 thn)',
                desc: 'Roset pelepah dasar, batang 0 m, R/S = 0.205',
                class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
            };
        case 'TBM_2':
            return {
                label: 'TBM 2 (Umur 2 thn)',
                desc: 'Pertumbuhan pelepah aktif, tinggi 0.5 m',
                class: 'bg-teal-500/15 text-teal-700 dark:text-teal-400 border-teal-500/30',
            };
        case 'TBM_3':
            return {
                label: 'TBM 3 (Umur 3 thn)',
                desc: 'Menjelang TM, tinggi 1.0 m',
                class: 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-400 border-cyan-500/30',
            };
        case 'TM_MUDA':
            return {
                label: 'TM Muda (4-8 thn)',
                desc: 'Mulai berbuah, laju pemanjangan 45 cm/thn',
                class: 'bg-lime-500/15 text-lime-800 dark:text-lime-300 border-lime-500/30',
            };
        case 'TM_PRIMA':
            return {
                label: 'TM Prima (9-18 thn)',
                desc: 'Puncak biomassa & penyerapan karbon maksimal',
                class: 'bg-green-600/15 text-green-800 dark:text-green-300 border-green-600/30',
            };
        case 'TM_TUA':
            return {
                label: 'TM Tua (> 18 thn)',
                desc: 'Batang tinggi > 8m, mutasi R/S = 0.180',
                class: 'bg-amber-600/15 text-amber-800 dark:text-amber-400 border-amber-600/30',
            };
        default:
            return {
                label: stage,
                desc: '',
                class: 'bg-neutral-500/15 text-neutral-600',
            };
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Palm Stands & Phenology', href: '/data/palm-stands' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Sensus Tegakan & Mutasi Fase Sawit" />
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <Sprout class="h-7 w-7 text-lime-600 dark:text-lime-400" />
                    Sensus Tegakan &amp; Mutasi Pertumbuhan Sawit
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Pelacakan fenologi kelapa sawit (*Elaeis guineensis*): TBM
                    1&ndash;3 &rarr; TM Muda &rarr; TM Prima &rarr; TM Tua.
                </p>
            </div>
            <Button
                @click="showModal = true"
                class="gap-2 self-start sm:self-auto"
            >
                <Plus class="h-4 w-4" />
                Catat Mutasi Sawit
            </Button>
        </div>

        <!-- Palm Phenology Guide Cards -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
            <div
                v-for="stg in [
                    'TBM_1',
                    'TBM_2',
                    'TBM_3',
                    'TM_MUDA',
                    'TM_PRIMA',
                    'TM_TUA',
                ]"
                :key="stg"
                class="space-y-1.5 rounded-xl border border-neutral-200 bg-white/70 p-3 dark:border-neutral-800 dark:bg-neutral-900/70"
            >
                <Badge
                    :class="getStageBadge(stg).class"
                    variant="outline"
                    class="text-[11px] font-bold"
                >
                    {{ getStageBadge(stg).label }}
                </Badge>
                <p class="text-[10px] leading-tight text-neutral-500">
                    {{ getStageBadge(stg).desc }}
                </p>
            </div>
        </div>

        <!-- Palm Blocks Table -->
        <Card
            class="overflow-hidden border border-neutral-200 shadow-sm dark:border-neutral-800"
        >
            <CardHeader
                class="border-b bg-neutral-50/50 dark:bg-neutral-900/50"
            >
                <CardTitle class="flex items-center gap-2 text-base">
                    <Layers class="h-4 w-4 text-lime-600" />
                    Data Populasi &amp; Fase Mutasi per Blok Kebun Sawit
                </CardTitle>
            </CardHeader>
            <div class="overflow-x-auto">
                <table
                    class="w-full text-left text-sm text-neutral-600 dark:text-neutral-300"
                >
                    <thead
                        class="border-b bg-neutral-50 text-xs font-semibold text-neutral-500 uppercase dark:bg-neutral-800/80 dark:text-neutral-400"
                    >
                        <tr>
                            <th class="px-5 py-3.5">Blok Kebun &amp; Estate</th>
                            <th class="px-4 py-3.5 text-right">Luas (ha)</th>
                            <th class="px-4 py-3.5 text-center">Tahun Tanam</th>
                            <th class="px-4 py-3.5 text-center">
                                Fase Mutasi Terkini
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Populasi (pokok)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Kerapatan (pkk/ha)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Tinggi Batang (m)
                            </th>
                            <th class="px-4 py-3.5 text-right">Umur Tegakan</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-100 font-medium dark:divide-neutral-800"
                    >
                        <tr
                            v-for="b in blocks"
                            :key="b.id"
                            class="hover:bg-neutral-50/80 dark:hover:bg-neutral-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-bold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ b.name }}
                                </div>
                                <div
                                    class="mt-0.5 flex items-center gap-1.5 text-xs text-neutral-400"
                                >
                                    <span class="font-mono">{{ b.code }}</span>
                                    <span>•</span>
                                    <span>{{ b.estate?.name }}</span>
                                </div>
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ Number(b.area_ha).toFixed(2) }} ha
                            </td>
                            <td class="px-4 py-4 text-center font-mono">
                                {{ b.crop_cycles?.[0]?.planting_year || '-' }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <Badge
                                    v-if="b.crop_cycles?.[0]?.palm_stands?.[0]"
                                    :class="
                                        getStageBadge(
                                            b.crop_cycles[0].palm_stands[0]
                                                .growth_stage,
                                        ).class
                                    "
                                    variant="outline"
                                    class="text-xs font-normal"
                                >
                                    {{
                                        getStageBadge(
                                            b.crop_cycles[0].palm_stands[0]
                                                .growth_stage,
                                        ).label
                                    }}
                                </Badge>
                                <span v-else class="text-xs text-neutral-400"
                                    >Belum disensus</span
                                >
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono font-bold text-emerald-600"
                            >
                                {{
                                    Number(
                                        b.crop_cycles?.[0]?.palm_stands?.[0]
                                            ?.tree_count || 0,
                                    ).toLocaleString('id-ID')
                                }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-neutral-700 dark:text-neutral-300"
                            >
                                {{
                                    Number(
                                        b.crop_cycles?.[0]?.palm_stands?.[0]
                                            ?.density_per_ha || 0,
                                    ).toFixed(0)
                                }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono font-bold text-lime-700 dark:text-lime-400"
                            >
                                {{
                                    Number(
                                        b.crop_cycles?.[0]?.palm_stands?.[0]
                                            ?.average_height_m || 0,
                                    ).toFixed(2)
                                }}
                                m
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-xs">
                                {{
                                    b.crop_cycles?.[0]?.palm_stands?.[0]
                                        ?.age_years || '-'
                                }}
                                thn
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- MODAL CATAT MUTASI SAWIT -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border bg-white p-6 shadow-2xl dark:bg-neutral-900"
            >
                <h3
                    class="flex items-center gap-2 text-lg font-bold text-neutral-900 dark:text-neutral-100"
                >
                    <Sprout class="h-5 w-5 text-lime-600" />
                    Catat Mutasi Fase / Sensus Sawit
                </h3>
                <form
                    @submit.prevent="submitPalmStand"
                    class="space-y-3 text-xs"
                >
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Blok Sawit Target *</label
                        >
                        <SearchableSelect
                            v-model="form.land_block_id"
                            :options="blockOptions"
                            placeholder="-- Cari / Pilih Blok Sawit --"
                            search-placeholder="Ketik nama atau kode blok..."
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Tahun Tanam *</label
                            >
                            <input
                                v-model.number="form.planting_year"
                                type="number"
                                min="1980"
                                max="2026"
                                required
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Total Populasi (pokok) *</label
                            >
                            <input
                                v-model.number="form.tree_count"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Fase Mutasi Pertumbuhan *</label
                        >
                        <SearchableSelect
                            v-model="form.growth_stage"
                            :options="stageOptions"
                            placeholder="-- Cari / Pilih Fase Pertumbuhan --"
                            search-placeholder="Ketik fase (TBM 1, TM Prima, dll)..."
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Tinggi Batang Rata-rata (m) *</label
                            >
                            <input
                                v-model.number="form.average_height_m"
                                type="number"
                                step="0.1"
                                required
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Rata-rata Pelepah Aktif</label
                            >
                            <input
                                v-model.number="form.avg_frond_count"
                                type="number"
                                step="1"
                                placeholder="38-42 pelepah"
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3">
                        <Button
                            @click="showModal = false"
                            type="button"
                            variant="outline"
                            size="sm"
                            class="cursor-pointer"
                            >Batal</Button
                        >
                        <Button type="submit" size="sm">
                            Simpan Sensus Sawit
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
