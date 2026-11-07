<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    ArrowUpRight,
    Building2,
    CheckCircle2,
    FileSpreadsheet,
    Globe,
    Layers,
    MapPin,
    Plus,
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
import {
    INDONESIA_PROVINCE_GROUPS,
    PROVINCE_SELECT_GROUPS,
} from '@/lib/carbonOptions';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { Badge } from '@/components/ui/badge';

interface EstateItem {
    id: string;
    organization_id: string;
    code: string;
    name: string;
    total_area_ha: number;
    province: string;
    regency: string;
    district: string;
    village: string;
    latitude: number | null;
    longitude: number | null;
    land_blocks_count?: number;
    organization?: { id: string; name: string; code: string };
}

interface Props {
    estates: EstateItem[];
}

const props = defineProps<Props>();

const showModal = ref(false);
const form = ref({
    organization_id: props.estates[0]?.organization_id || '',
    code: '',
    name: '',
    total_area_ha: null as number | null,
    province: '',
    regency: '',
    district: '',
    village: '',
    latitude: null as number | null,
    longitude: null as number | null,
});

const submitEstate = () => {
    router.post('/data/estates', form.value, {
        onSuccess: () => {
            showModal.value = false;
            form.value = {
                organization_id: props.estates[0]?.organization_id || '',
                code: '',
                name: '',
                total_area_ha: null,
                province: '',
                regency: '',
                district: '',
                village: '',
                latitude: null,
                longitude: null,
            };
        },
    });
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Estates & Concessions', href: '/data/estates' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Manajemen Unit Estate & Konsesi" />
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <Building2
                        class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
                    />
                    Unit Estate &amp; Konsesi Hutan
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Daftar unit kebun kelapa sawit dan konsesi kehutanan (PBPH /
                    HGU) pengelola blok karbon.
                </p>
            </div>
            <Button
                @click="showModal = true"
                class="gap-2 self-start sm:self-auto"
            >
                <Plus class="h-4 w-4" />
                Tambah Estate Baru
            </Button>
        </div>

        <!-- Estate Cards Grid -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            <Card
                v-for="estate in estates"
                :key="estate.id"
                class="relative overflow-hidden border border-neutral-200 shadow-sm transition-shadow hover:shadow-md dark:border-neutral-800"
            >
                <div class="h-2 w-full bg-emerald-600"></div>
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <span
                            class="font-mono text-xs font-bold tracking-wider text-neutral-400 uppercase"
                        >
                            {{ estate.code }}
                        </span>
                        <Badge variant="outline" class="text-xs font-normal">
                            {{ estate.land_blocks_count || 0 }} Blok Lahan
                        </Badge>
                    </div>
                    <CardTitle
                        class="mt-1 text-lg font-bold text-neutral-900 dark:text-neutral-100"
                    >
                        {{ estate.name }}
                    </CardTitle>
                    <CardDescription
                        class="flex items-center gap-1 text-xs text-neutral-500"
                    >
                        <MapPin class="h-3.5 w-3.5 shrink-0 text-neutral-400" />
                        {{ estate.regency }}, {{ estate.province }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3 pt-0">
                    <div
                        class="grid grid-cols-2 gap-2 rounded-lg border border-neutral-100 bg-neutral-50 p-3 text-xs dark:border-neutral-800 dark:bg-neutral-800/60"
                    >
                        <div>
                            <span class="block text-neutral-400"
                                >Total Luas</span
                            >
                            <span
                                class="font-mono text-sm font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{
                                    Number(estate.total_area_ha).toLocaleString(
                                        'id-ID',
                                    )
                                }}
                                ha
                            </span>
                        </div>
                        <div>
                            <span class="block text-neutral-400"
                                >Entitas / PT</span
                            >
                            <span
                                class="block truncate font-semibold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ estate.organization?.name || 'Korporasi' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between pt-1 text-[11px] text-neutral-400"
                    >
                        <span
                            >Kec. {{ estate.district }} - Desa
                            {{ estate.village }}</span
                        >
                        <span v-if="estate.latitude" class="font-mono">
                            {{ Number(estate.latitude).toFixed(3) }},
                            {{ Number(estate.longitude).toFixed(3) }}
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- MODAL FORM TAMBAH ESTATE -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        >
            <div
                class="w-full max-w-lg space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900"
            >
                <h3
                    class="flex items-center gap-2 text-lg font-bold text-neutral-900 dark:text-neutral-100"
                >
                    <Building2 class="h-5 w-5 text-emerald-600" />
                    Tambah Unit Estate / Konsesi Baru
                </h3>

                <form @submit.prevent="submitEstate" class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Kode Estate *</label
                            >
                            <input
                                v-model="form.code"
                                required
                                placeholder="Contoh: EST-RIAU-02"
                                class="w-full rounded-lg border border-neutral-300 bg-white p-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Nama Estate *</label
                            >
                            <input
                                v-model="form.name"
                                required
                                placeholder="Contoh: Kebun Riau Mandiri"
                                class="w-full rounded-lg border border-neutral-300 bg-white p-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Total Luas (ha) *</label
                            >
                            <input
                                v-model.number="form.total_area_ha"
                                type="number"
                                step="0.01"
                                required
                                placeholder="5000"
                                class="w-full rounded-lg border border-neutral-300 bg-white p-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Provinsi *</label
                            >
                            <SearchableSelect
                                v-model="form.province"
                                :options="PROVINCE_SELECT_GROUPS"
                                placeholder="-- Cari / Pilih Provinsi --"
                                search-placeholder="Ketik provinsi (misal: Riau, Kaltim, Papua)..."
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Kabupaten *</label
                            >
                            <input
                                v-model="form.regency"
                                required
                                placeholder="Siak"
                                class="w-full rounded-lg border border-neutral-300 bg-white p-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Kecamatan *</label
                            >
                            <input
                                v-model="form.district"
                                required
                                placeholder="Kandis"
                                class="w-full rounded-lg border border-neutral-300 bg-white p-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Desa *</label
                            >
                            <input
                                v-model="form.village"
                                required
                                placeholder="Sam-Sam"
                                class="w-full rounded-lg border border-neutral-300 bg-white p-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Latitude</label
                            >
                            <input
                                v-model.number="form.latitude"
                                type="number"
                                step="0.0001"
                                placeholder="0.9500"
                                class="w-full rounded-lg border border-neutral-300 bg-white p-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold"
                                >Longitude</label
                            >
                            <input
                                v-model.number="form.longitude"
                                type="number"
                                step="0.0001"
                                placeholder="101.3500"
                                class="w-full rounded-lg border border-neutral-300 bg-white p-2 text-xs dark:border-neutral-700 dark:bg-neutral-900"
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
                        >
                            Batal
                        </Button>
                        <Button type="submit" size="sm"> Simpan Estate </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
