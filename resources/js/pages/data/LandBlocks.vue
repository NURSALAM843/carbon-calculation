<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Filter, Layers, Plus, RefreshCw, Search } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { SOIL_TYPE_GROUPS, TOPOGRAPHY_OPTIONS } from '@/lib/carbonOptions';
import SearchableSelect from '@/components/SearchableSelect.vue';

interface BlockItem {
    id: string;
    estate_id: string;
    code: string;
    name: string;
    ecosystem_type: string;
    area_ha: number;
    soil_type: string;
    land_type: string;
    estate?: { id: string; name: string; code: string };
    crop_cycles?: Array<{
        crop_type: string;
        planting_year: number;
        palm_stands?: Array<{ growth_stage: string; tree_count: number }>;
    }>;
}

interface Props {
    blocks: {
        data: BlockItem[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    estates: Array<{ id: string; name: string; code: string }>;
    filters: {
        ecosystem_type?: string;
        estate_id?: string;
    };
}

const props = defineProps<Props>();

const filterEcosystem = ref(props.filters.ecosystem_type || '');
const filterEstate = ref(props.filters.estate_id || '');
const showModal = ref(false);

const form = ref({
    estate_id: props.estates[0]?.id || '',
    code: '',
    name: '',
    ecosystem_type: 'oil_palm',
    area_ha: null as number | null,
    soil_type: 'Ultisol (Podsolik Merah Kuning / Typic Hapludult)',
    land_type: 'Datar / Flat (0-3%)',
});

const estateOptions = computed(() => {
    return props.estates.map((e) => ({
        value: e.id,
        label: `${e.name} (${e.code})`,
    }));
});

const ecosystemOptions = [
    { value: 'oil_palm', label: 'Kelapa Sawit (Oil Palm)' },
    {
        value: 'natural_forest',
        label: 'Hutan Alam Konservasi (Natural Forest)',
    },
    { value: 'hti_plantation', label: 'HTI Tanaman Industri (HTI)' },
    { value: 'mangrove', label: 'Mangrove / Lahan Basah' },
    { value: 'peat_swamp_forest', label: 'Hutan Rawa Gambut' },
    { value: 'agroforestry', label: 'Agroforestri' },
];

const applyFilters = () => {
    router.get(
        '/data/blocks',
        {
            ecosystem_type: filterEcosystem.value || undefined,
            estate_id: filterEstate.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const resetFilters = () => {
    filterEcosystem.value = '';
    filterEstate.value = '';
    router.get('/data/blocks');
};

const submitBlock = () => {
    router.post('/data/blocks', form.value, {
        onSuccess: () => {
            showModal.value = false;
            form.value = {
                estate_id: props.estates[0]?.id || '',
                code: '',
                name: '',
                ecosystem_type: 'oil_palm',
                area_ha: null,
                soil_type: 'Ultisol (Podsolik Merah Kuning / Typic Hapludult)',
                land_type: 'Datar / Flat (0-3%)',
            };
        },
    });
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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Land Blocks', href: '/data/blocks' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Manajemen Blok Lahan & Kompartemen" />
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <Layers
                        class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
                    />
                    Blok Lahan &amp; Kompartemen Hutan
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Daftar petak sawit dan kompartemen hutan dengan penanda tipe
                    ekosistem untuk penentuan formula alometrik.
                </p>
            </div>
            <Button
                @click="showModal = true"
                class="gap-2 self-start sm:self-auto"
            >
                <Plus class="h-4 w-4" />
                Tambah Blok Lahan
            </Button>
        </div>

        <!-- Filters -->
        <Card class="border border-neutral-200 dark:border-neutral-800">
            <CardContent class="p-4">
                <div class="grid grid-cols-1 items-center gap-3 sm:grid-cols-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                            >Tipe Ekosistem</label
                        >
                        <select
                            v-model="filterEcosystem"
                            @change="applyFilters"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                        >
                            <option value="">Semua Tipe Ekosistem</option>
                            <option value="oil_palm">
                                Kelapa Sawit (Palm Growth)
                            </option>
                            <option value="natural_forest">
                                Hutan Alam Konservasi (HCV)
                            </option>
                            <option value="hti_plantation">
                                HTI Kayu Budidaya
                            </option>
                            <option value="mangrove">Mangrove / Pesisir</option>
                            <option value="peat_swamp_forest">
                                Hutan Rawa Gambut
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-neutral-600 dark:text-neutral-300"
                            >Estate</label
                        >
                        <select
                            v-model="filterEstate"
                            @change="applyFilters"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                        >
                            <option value="">Semua Estate</option>
                            <option
                                v-for="e in estates"
                                :key="e.id"
                                :value="e.id"
                            >
                                {{ e.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2 pt-2 sm:pt-4">
                        <Button @click="applyFilters" size="sm" class="flex-1">
                            Terapkan Filter
                        </Button>
                        <Button
                            @click="resetFilters"
                            variant="outline"
                            size="sm"
                            class="cursor-pointer"
                        >
                            <RefreshCw class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Table -->
        <Card
            class="overflow-hidden border border-neutral-200 shadow-sm dark:border-neutral-800"
        >
            <div class="overflow-x-auto">
                <table
                    class="w-full text-left text-sm text-neutral-600 dark:text-neutral-300"
                >
                    <thead
                        class="border-b bg-neutral-50 text-xs font-semibold text-neutral-500 uppercase dark:bg-neutral-800/80 dark:text-neutral-400"
                    >
                        <tr>
                            <th class="px-5 py-3.5">Kode &amp; Nama Blok</th>
                            <th class="px-4 py-3.5">Unit Estate</th>
                            <th class="px-4 py-3.5">Tipe Ekosistem</th>
                            <th class="px-4 py-3.5 text-right">Luas (ha)</th>
                            <th class="px-4 py-3.5">Jenis Tanah</th>
                            <th class="px-4 py-3.5">Topografi / Lahan</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-100 font-medium dark:divide-neutral-800"
                    >
                        <tr
                            v-for="b in blocks.data"
                            :key="b.id"
                            class="hover:bg-neutral-50/80 dark:hover:bg-neutral-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-bold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ b.name }}
                                </div>
                                <span
                                    class="font-mono text-xs text-neutral-400"
                                    >{{ b.code }}</span
                                >
                            </td>
                            <td class="px-4 py-4 text-xs">
                                {{ b.estate?.name }}
                            </td>
                            <td class="px-4 py-4">
                                <Badge
                                    :class="
                                        getEcosystemBadge(b.ecosystem_type)
                                            .class
                                    "
                                    variant="outline"
                                    class="text-xs font-normal"
                                >
                                    {{
                                        getEcosystemBadge(b.ecosystem_type)
                                            .label
                                    }}
                                </Badge>
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono font-bold text-neutral-900 dark:text-neutral-100"
                            >
                                {{ Number(b.area_ha).toFixed(2) }} ha
                            </td>
                            <td class="px-4 py-4 text-xs">
                                {{ b.soil_type }}
                            </td>
                            <td class="px-4 py-4 text-xs text-neutral-400">
                                {{ b.land_type }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="blocks.total > 0"
                class="flex items-center justify-between border-t px-6 py-4 text-xs text-neutral-500"
            >
                <div>Total {{ blocks.total }} blok lahan terdaftar</div>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="blocks.prev_page_url"
                        :href="blocks.prev_page_url"
                        class="rounded border px-2.5 py-1"
                        >Sebelumnya</Link
                    >
                    <span
                        >Hal {{ blocks.current_page }} dari
                        {{ blocks.last_page }}</span
                    >
                    <Link
                        v-if="blocks.next_page_url"
                        :href="blocks.next_page_url"
                        class="rounded border px-2.5 py-1"
                        >Berikutnya</Link
                    >
                </div>
            </div>
        </Card>

        <!-- MODAL TAMBAH BLOK -->
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
                    <Layers class="h-5 w-5 text-emerald-600" />
                    Tambah Blok Lahan Baru
                </h3>
                <form @submit.prevent="submitBlock" class="space-y-3 text-xs">
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Estate Induk *</label
                        >
                        <SearchableSelect
                            v-model="form.estate_id"
                            :options="estateOptions"
                            placeholder="-- Cari / Pilih Estate Induk --"
                            search-placeholder="Ketik nama atau kode estate..."
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Kode Blok *</label
                            >
                            <input
                                v-model="form.code"
                                required
                                placeholder="BLK-HUT-05"
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Nama Blok *</label
                            >
                            <input
                                v-model="form.name"
                                required
                                placeholder="Kompartemen C2"
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Tipe Ekosistem *</label
                        >
                        <SearchableSelect
                            v-model="form.ecosystem_type"
                            :options="ecosystemOptions"
                            placeholder="-- Cari / Pilih Tipe Ekosistem --"
                            search-placeholder="Ketik tipe ekosistem..."
                            required
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Luas Efektif (ha) *</label
                        >
                        <input
                            v-model.number="form.area_ha"
                            type="number"
                            step="0.01"
                            required
                            placeholder="35.5"
                            class="w-full rounded-lg border bg-transparent p-2 text-xs"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Tipe Tanah / Jenis Tanah *</label
                            >
                            <SearchableSelect
                                v-model="form.soil_type"
                                :options="SOIL_TYPE_GROUPS"
                                placeholder="-- Cari / Pilih Tipe Tanah --"
                                search-placeholder="Ketik tipe tanah (misal: Gambut, Ultisol, Andosol)..."
                                required
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Topografi / Kelerengan *</label
                            >
                            <SearchableSelect
                                v-model="form.land_type"
                                :options="TOPOGRAPHY_OPTIONS"
                                placeholder="-- Cari / Pilih Topografi --"
                                search-placeholder="Ketik topografi / kemiringan (misal: Datar, Curam)..."
                                required
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
                        <Button type="submit" size="sm"> Simpan Blok </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
