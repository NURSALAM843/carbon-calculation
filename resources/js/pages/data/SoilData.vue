<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Activity,
    Calendar,
    ChevronLeft,
    ChevronRight,
    CircleDot,
    Database,
    FileSpreadsheet,
    FlaskConical,
    Layers,
    MapPin,
    Plus,
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
import { SOIL_TYPE_GROUPS } from '@/lib/carbonOptions';

interface SoilItem {
    id: string;
    measurement_date: string;
    soil_type: string;
    depth_cm: number;
    bulk_density: number;
    soil_carbon_percent: number;
    coarse_fragments_percent: number | null;
    soil_organic_carbon_t_ha: number;
    laboratory: string | null;
    land_block?: {
        name: string;
        code: string;
        estate?: { name: string };
    };
}

interface BlockOption {
    id: string;
    name: string;
    code: string;
    soil_type: string;
}

interface Props {
    soils: {
        data: SoilItem[];
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    blocks: BlockOption[];
}

const props = defineProps<Props>();

const showModal = ref(false);
const form = ref({
    land_block_id: props.blocks[0]?.id || '',
    measurement_date: new Date().toISOString().split('T')[0],
    soil_type: props.blocks[0]?.soil_type || 'Mineral Typic Hapludult',
    depth_cm: 30.0,
    bulk_density: 1.15,
    soil_carbon_percent: 2.1,
    coarse_fragments_percent: 5.0,
    laboratory: 'Balai Penelitian Tanah (BBSDLP)',
});

const blockOptions = computed(() => {
    return props.blocks.map((b) => ({
        value: b.id,
        label: `[${b.code}] ${b.name}`,
        description: b.soil_type ? `Tanah: ${b.soil_type}` : undefined,
    }));
});

const onBlockChange = () => {
    const selected = props.blocks.find(
        (b) => b.id === form.value.land_block_id,
    );
    if (selected && selected.soil_type) {
        form.value.soil_type = selected.soil_type;
    }
};

const submitSoil = () => {
    router.post('/data/soil', form.value, {
        onSuccess: () => {
            showModal.value = false;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Soil Lab Data', href: '/data/soil' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Data Uji Laboratorium Tanah (SOC)" />
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <FlaskConical
                        class="h-7 w-7 text-amber-600 dark:text-amber-400"
                    />
                    Data Uji Laboratorium Tanah (Soil Organic Carbon)
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Hasil uji laboratorium tanah mineral dan lahan gambut (Bulk
                    Density, %C Organik, dan Kepadatan SOC t C/ha).
                </p>
            </div>
            <Button
                @click="showModal = true"
                class="gap-2 self-start sm:self-auto"
            >
                <Plus class="h-4 w-4" />
                Tambah Hasil Uji Lab
            </Button>
        </div>

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
                            <th class="px-5 py-3.5">Tanggal Uji</th>
                            <th class="px-4 py-3.5">Blok Lahan &amp; Estate</th>
                            <th class="px-4 py-3.5">Jenis Tanah</th>
                            <th class="px-4 py-3.5 text-right">
                                Kedalaman (cm)
                            </th>
                            <th class="px-4 py-3.5 text-right">
                                Bulk Density (g/cm&sup3;)
                            </th>
                            <th class="px-4 py-3.5 text-right">% C Organik</th>
                            <th class="px-4 py-3.5 text-right">
                                Kandungan SOC (t C/ha)
                            </th>
                            <th class="px-4 py-3.5">Laboratorium Penguji</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-100 font-medium dark:divide-neutral-800"
                    >
                        <tr
                            v-for="s in soils.data"
                            :key="s.id"
                            class="hover:bg-neutral-50/80 dark:hover:bg-neutral-800/50"
                        >
                            <td class="px-5 py-4 font-mono text-xs">
                                {{ s.measurement_date }}
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <div
                                    class="font-bold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ s.land_block?.name }}
                                </div>
                                <div class="text-[11px] text-neutral-400">
                                    {{ s.land_block?.estate?.name }} ({{
                                        s.land_block?.code
                                    }})
                                </div>
                            </td>
                            <td
                                class="px-4 py-4 text-xs font-semibold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ s.soil_type }}
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-xs">
                                0 - {{ Number(s.depth_cm).toFixed(0) }} cm
                            </td>
                            <td class="px-4 py-4 text-right font-mono text-xs">
                                {{ Number(s.bulk_density).toFixed(2) }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-xs font-bold text-neutral-900 dark:text-neutral-100"
                            >
                                {{ Number(s.soil_carbon_percent).toFixed(2) }}%
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-sm font-bold text-amber-600 dark:text-amber-400"
                            >
                                {{
                                    Number(s.soil_organic_carbon_t_ha).toFixed(
                                        1,
                                    )
                                }}
                                t C/ha
                            </td>
                            <td class="px-4 py-4 text-xs text-neutral-500">
                                {{ s.laboratory || 'Lab Terakreditasi' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="soils.total > 0"
                class="flex items-center justify-between border-t px-6 py-4 text-xs text-neutral-500"
            >
                <div>Total {{ soils.total }} sampel tanah tercatat</div>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="soils.prev_page_url"
                        :href="soils.prev_page_url"
                        class="rounded border px-2.5 py-1"
                        >Sebelumnya</Link
                    >
                    <span
                        >Hal {{ soils.current_page }} dari
                        {{ soils.last_page }}</span
                    >
                    <Link
                        v-if="soils.next_page_url"
                        :href="soils.next_page_url"
                        class="rounded border px-2.5 py-1"
                        >Berikutnya</Link
                    >
                </div>
            </div>
        </Card>

        <!-- MODAL TAMBAH UJI TANAH -->
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
                    <FlaskConical class="h-5 w-5 text-amber-600" />
                    Rekam Hasil Uji Lab Tanah Baru
                </h3>
                <form @submit.prevent="submitSoil" class="space-y-3 text-xs">
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Blok Lahan *</label
                        >
                        <SearchableSelect
                            v-model="form.land_block_id"
                            :options="blockOptions"
                            @change="onBlockChange"
                            placeholder="-- Cari / Pilih Blok Lahan --"
                            search-placeholder="Ketik kode atau nama blok..."
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Tanggal Sampel *</label
                            >
                            <input
                                v-model="form.measurement_date"
                                type="date"
                                required
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Jenis Tanah *</label
                            >
                            <SearchableSelect
                                v-model="form.soil_type"
                                :options="SOIL_TYPE_GROUPS"
                                placeholder="-- Cari / Pilih Jenis Tanah --"
                                search-placeholder="Ketik jenis tanah (Gambut, Ultisol, dll)..."
                                required
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Kedalaman Profil (cm) *</label
                            >
                            <input
                                v-model.number="form.depth_cm"
                                type="number"
                                step="1"
                                required
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Bulk Density (g/cm&sup3;) *</label
                            >
                            <input
                                v-model.number="form.bulk_density"
                                type="number"
                                step="0.01"
                                required
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >% C Organik Lab *</label
                            >
                            <input
                                v-model.number="form.soil_carbon_percent"
                                type="number"
                                step="0.01"
                                required
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >% Fragmen Kasar (Batu)</label
                            >
                            <input
                                v-model.number="form.coarse_fragments_percent"
                                type="number"
                                step="0.1"
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Nama Laboratorium Terakreditasi</label
                        >
                        <input
                            v-model="form.laboratory"
                            placeholder="Lab Riset IPB / Balittanah"
                            class="w-full rounded-lg border bg-transparent p-2 text-xs"
                        />
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
                            Simpan Data Uji Tanah
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
