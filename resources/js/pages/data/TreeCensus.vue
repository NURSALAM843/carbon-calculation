<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Info, Plus, Search, TreePine } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import SearchableSelect from '@/components/SearchableSelect.vue';

interface TreeItem {
    id: string;
    tree_tag_number: string;
    botanical_species: string;
    dbh_cm: number;
    total_height_m: number | null;
    wood_density_g_cm3: number;
    field_measurement?: {
        measurement_date: string;
        measurement_year: number;
        land_block?: {
            id: string;
            code: string;
            name: string;
            estate?: { name: string };
        };
    };
}

interface BlockOption {
    id: string;
    code: string;
    name: string;
    ecosystem_type: string;
}

interface Props {
    trees: {
        data: TreeItem[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    blocks: BlockOption[];
}

const props = defineProps<Props>();

const showModal = ref(false);
const form = ref({
    land_block_id: props.blocks[0]?.id || '',
    tree_tag_number: '',
    botanical_species: 'Shorea leprosula (Meranti Merah)',
    wood_density_g_cm3: 0.52,
    dbh_cm: null as number | null,
    total_height_m: null as number | null,
});

const presetSpecies = [
    { name: 'Shorea leprosula (Meranti Merah)', rho: 0.52 },
    { name: 'Dipterocarpus cornutus (Keruing)', rho: 0.72 },
    { name: 'Koompassia excelsa (Kempas / Tualang)', rho: 0.83 },
    { name: 'Acacia mangium (Akasia)', rho: 0.55 },
    { name: 'Eucalyptus pellita (Eukaliptus)', rho: 0.6 },
    { name: 'Rhizophora apiculata (Bakau Minyak)', rho: 0.85 },
    { name: 'Bruguiera gymnorrhiza (Lindur)', rho: 0.76 },
];

const blockOptions = computed(() => {
    return props.blocks.map((b) => ({
        value: b.id,
        label: `[${b.code}] ${b.name}`,
    }));
});

const speciesOptions = presetSpecies.map((s) => ({
    value: s.name,
    label: `${s.name} (ρ=${s.rho})`,
    description: `Massa Jenis Kayu (ρ) = ${s.rho} g/cm³`,
}));

const onSpeciesChange = () => {
    const found = presetSpecies.find(
        (s) => s.name === form.value.botanical_species,
    );
    if (found) {
        form.value.wood_density_g_cm3 = found.rho;
    }
};

const submitTree = () => {
    router.post('/data/tree-census', form.value, {
        onSuccess: () => {
            showModal.value = false;
            form.value.tree_tag_number = '';
            form.value.dbh_cm = null;
            form.value.total_height_m = null;
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Forest Tree Census', href: '/data/tree-census' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Sensus Inventarisasi Pohon Hutan" />
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <TreePine
                        class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
                    />
                    Sensus Pohon Hutan &amp; HTI (Woody Biomass)
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Inventarisasi tegakan pohon berkayu berbasis DBH, tinggi
                    tajuk, dan berat jenis kayu (&rho;) untuk Chave et al. 2014
                    &amp; SNI 7724.
                </p>
            </div>
            <Button
                @click="showModal = true"
                class="gap-2 self-start sm:self-auto"
            >
                <Plus class="h-4 w-4" />
                Catat Sensus Pohon
            </Button>
        </div>

        <!-- Scientific Info Alert -->
        <div
            class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs text-neutral-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-neutral-300"
        >
            <Info class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
            <div class="space-y-1">
                <span class="font-bold text-emerald-900 dark:text-emerald-300"
                    >Prinsip Alometrik Pohon Hutan Berkayu (Dikotil /
                    Gymnospermae):</span
                >
                <p>
                    Berbeda dengan kelapa sawit yang tidak memiliki kambium
                    vaskular, pohon hutan mengalami pertumbuhan sekunder
                    berkelanjutan pada diameter batang (DBH). Biomassa dihitung
                    dengan formula non-linear Chave:
                    <span class="font-mono font-semibold"
                        >AGB = 0.0673 &times; (&rho; &times; D&sup2; &times;
                        H)<sup>0.976</sup></span
                    >.
                </p>
            </div>
        </div>

        <!-- Tree Census Table -->
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
                            <th class="px-5 py-3.5">Tag Pohon</th>
                            <th class="px-4 py-3.5">Spesies Botani</th>
                            <th class="px-4 py-3.5">Blok Lahan &amp; Estate</th>
                            <th class="px-4 py-3.5 text-right">DBH (cm)</th>
                            <th class="px-4 py-3.5 text-right">Tinggi (m)</th>
                            <th class="px-4 py-3.5 text-right">
                                Berat Jenis (&rho;)
                            </th>
                            <th class="px-4 py-3.5 text-center">
                                Tahun Sensus
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-100 font-medium dark:divide-neutral-800"
                    >
                        <tr
                            v-for="t in trees.data"
                            :key="t.id"
                            class="hover:bg-neutral-50/80 dark:hover:bg-neutral-800/50"
                        >
                            <td
                                class="px-5 py-4 font-mono text-xs font-bold text-neutral-900 dark:text-neutral-100"
                            >
                                {{ t.tree_tag_number }}
                            </td>
                            <td
                                class="px-4 py-4 text-xs text-neutral-900 italic dark:text-neutral-100"
                            >
                                {{ t.botanical_species }}
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <div
                                    class="font-medium text-neutral-800 dark:text-neutral-200"
                                >
                                    {{ t.field_measurement?.land_block?.name }}
                                </div>
                                <div class="text-[11px] text-neutral-400">
                                    {{
                                        t.field_measurement?.land_block?.estate
                                            ?.name
                                    }}
                                    ({{
                                        t.field_measurement?.land_block?.code
                                    }})
                                </div>
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-sm font-bold text-emerald-600"
                            >
                                {{ Number(t.dbh_cm).toFixed(1) }} cm
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-xs text-neutral-800 dark:text-neutral-200"
                            >
                                {{
                                    t.total_height_m
                                        ? Number(t.total_height_m).toFixed(1) +
                                          ' m'
                                        : '-'
                                }}
                            </td>
                            <td
                                class="px-4 py-4 text-right font-mono text-xs text-neutral-600 dark:text-neutral-400"
                            >
                                {{ Number(t.wood_density_g_cm3).toFixed(2) }}
                                g/cm&sup3;
                            </td>
                            <td class="px-4 py-4 text-center font-mono text-xs">
                                {{ t.field_measurement?.measurement_year }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="trees.total > 0"
                class="flex items-center justify-between border-t px-6 py-4 text-xs text-neutral-500"
            >
                <div>Total {{ trees.total }} pohon tersensus</div>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="trees.prev_page_url"
                        :href="trees.prev_page_url"
                        class="rounded border px-2.5 py-1"
                        >Sebelumnya</Link
                    >
                    <span
                        >Hal {{ trees.current_page }} dari
                        {{ trees.last_page }}</span
                    >
                    <Link
                        v-if="trees.next_page_url"
                        :href="trees.next_page_url"
                        class="rounded border px-2.5 py-1"
                        >Berikutnya</Link
                    >
                </div>
            </div>
        </Card>

        <!-- MODAL CATAT POHON -->
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
                    <TreePine class="h-5 w-5 text-emerald-600" />
                    Catat Sensus Pohon Hutan Baru
                </h3>
                <form @submit.prevent="submitTree" class="space-y-3 text-xs">
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Blok Hutan Target *</label
                        >
                        <SearchableSelect
                            v-model="form.land_block_id"
                            :options="blockOptions"
                            placeholder="-- Cari / Pilih Blok Hutan --"
                            search-placeholder="Ketik kode atau nama blok..."
                            required
                        />
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold"
                            >Spesies Botani *</label
                        >
                        <SearchableSelect
                            v-model="form.botanical_species"
                            :options="speciesOptions"
                            @change="onSpeciesChange"
                            placeholder="-- Cari / Pilih Spesies Botani --"
                            search-placeholder="Ketik nama ilmiah / pohon..."
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Nomor Tag Pohon</label
                            >
                            <input
                                v-model="form.tree_tag_number"
                                placeholder="Contoh: TAG-042"
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Berat Jenis (&rho; g/cm&sup3;) *</label
                            >
                            <input
                                v-model.number="form.wood_density_g_cm3"
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
                                >DBH (cm) *</label
                            >
                            <input
                                v-model.number="form.dbh_cm"
                                type="number"
                                step="0.1"
                                required
                                placeholder="Contoh: 35.8"
                                class="w-full rounded-lg border bg-transparent p-2 text-xs"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Tinggi Tajuk (m)</label
                            >
                            <input
                                v-model.number="form.total_height_m"
                                type="number"
                                step="0.1"
                                placeholder="Contoh: 24.5"
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
                            >Batal</Button
                        >
                        <Button type="submit" size="sm"
                            >Simpan Sensus Pohon</Button
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
