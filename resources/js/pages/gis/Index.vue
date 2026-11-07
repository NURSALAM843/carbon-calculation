<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
    useTemplateRef,
    watch,
} from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import {
    Calculator,
    Compass,
    Eye,
    Globe,
    Layers,
    RotateCcw,
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

interface BlockMapItem {
    id: string;
    code: string;
    name: string;
    estate_name: string;
    ecosystem_type: string;
    area_ha: number;
    soil_type: string;
    land_type: string;
    latitude: number;
    longitude: number;
    geometry: {
        type: string;
        coordinates: number[][][];
    } | null;
    latest_stock_co2e_ha: number;
    latest_total_co2e: number;
    latest_sequestration_year: number;
    has_approved_run: boolean;
}

interface EstateItem {
    id: string;
    name: string;
    code: string;
    province: string;
    latitude?: number;
    longitude?: number;
}

interface Props {
    estates: EstateItem[];
    blocks: BlockMapItem[];
}

const props = defineProps<Props>();

const selectedBlock = ref<BlockMapItem | null>(props.blocks[0] || null);
const viewMode = ref<'ecosystem' | 'stock' | 'sequestration'>('ecosystem');
const filterEcosystem = ref<string>('all');
const mapStyle = ref<'osm' | 'satellite'>('osm');

const mapContainer = useTemplateRef<HTMLElement>('mapContainer');
let map: L.Map | null = null;
let tileLayer: L.TileLayer | null = null;
let featureGroup: L.FeatureGroup | null = null;
const blockLayerMap = new Map<string, L.Layer>();

const filteredBlocks = computed(() => {
    if (filterEcosystem.value === 'all') {
        return props.blocks;
    }
    return props.blocks.filter(
        (b) => b.ecosystem_type === filterEcosystem.value,
    );
});

// Color logic depending on view mode
const getBlockColor = (b: BlockMapItem): string => {
    if (viewMode.value === 'ecosystem') {
        switch (b.ecosystem_type) {
            case 'oil_palm':
                return '#84cc16'; // Lime green
            case 'natural_forest':
                return '#059669'; // Emerald
            case 'hti_plantation':
                return '#0d9488'; // Teal
            case 'mangrove':
                return '#0891b2'; // Cyan
            case 'peat_swamp_forest':
                return '#d97706'; // Amber
            default:
                return '#64748b';
        }
    } else if (viewMode.value === 'stock') {
        const val = b.latest_stock_co2e_ha;
        if (val > 400) return '#064e3b';
        if (val > 250) return '#059669';
        if (val > 150) return '#10b981';
        if (val > 50) return '#6ee7b7';
        return '#cbd5e1';
    } else {
        const seq = b.latest_sequestration_year;
        if (seq > 15) return '#047857';
        if (seq > 8) return '#10b981';
        if (seq > 0) return '#34d399';
        return '#94a3b8';
    }
};

const getEcosystemLabel = (type: string) => {
    switch (type) {
        case 'oil_palm':
            return 'Kelapa Sawit';
        case 'natural_forest':
            return 'Hutan Alam';
        case 'hti_plantation':
            return 'HTI Kayu';
        case 'mangrove':
            return 'Mangrove';
        case 'peat_swamp_forest':
            return 'Rawa Gambut';
        default:
            return type;
    }
};

// Initialize Leaflet Map
const initMap = () => {
    if (!mapContainer.value || map) return;

    // Default center Indonesia
    map = L.map(mapContainer.value, {
        center: [0.5, 102.5],
        zoom: 7,
        zoomControl: false,
    });

    // Custom zoom control in top right
    L.control.zoom({ position: 'topright' }).addTo(map);

    setTileLayer();

    featureGroup = L.featureGroup().addTo(map);
    renderLayers();
};

const setTileLayer = () => {
    if (!map) return;
    if (tileLayer) {
        map.removeLayer(tileLayer);
    }

    if (mapStyle.value === 'osm') {
        // Free OpenStreetMap Tiles
        tileLayer = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
                maxZoom: 19,
            },
        ).addTo(map);
    } else {
        // Free Public Esri World Imagery (Satellite)
        tileLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                attribution:
                    'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                maxZoom: 18,
            },
        ).addTo(map);
    }
};

const renderLayers = () => {
    if (!map || !featureGroup) return;

    featureGroup.clearLayers();
    blockLayerMap.clear();

    const bounds: L.LatLngExpression[] = [];

    filteredBlocks.value.forEach((b) => {
        const color = getBlockColor(b);
        const isSelected = selectedBlock.value?.id === b.id;

        const popupContent = `
            <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4; min-width: 180px;">
                <div style="font-weight: bold; font-size: 13px; color: #064e3b; margin-bottom: 2px;">${b.name}</div>
                <div style="color: #64748b; font-size: 11px; margin-bottom: 6px;">[${b.code}] • ${b.estate_name}</div>
                <div style="margin-bottom: 4px;"><strong>Tipe:</strong> ${getEcosystemLabel(b.ecosystem_type)}</div>
                <div style="margin-bottom: 4px;"><strong>Luas:</strong> ${Number(b.area_ha).toFixed(2)} ha</div>
                <div style="margin-bottom: 4px;"><strong>Stok CO2e:</strong> ${Number(b.latest_total_co2e).toLocaleString('id-ID')} t</div>
                <div style="margin-bottom: 4px;"><strong>Kerapatan:</strong> ${Number(b.latest_stock_co2e_ha).toFixed(2)} t/ha</div>
                <div style="color: #059669; font-weight: 600; margin-top: 6px;">Klik untuk melihat detail lengkap &rarr;</div>
            </div>
        `;

        if (
            b.geometry?.coordinates?.[0] &&
            b.geometry.coordinates[0].length >= 3
        ) {
            // GeoJSON coordinates are [lng, lat], Leaflet requires [lat, lng]
            const latLngs = b.geometry.coordinates[0].map(
                ([lng, lat]) => [lat, lng] as [number, number],
            );
            latLngs.forEach((coord) => bounds.push(coord));

            const polygon = L.polygon(latLngs, {
                color: isSelected ? '#ffffff' : color,
                weight: isSelected ? 3.5 : 2,
                fillColor: color,
                fillOpacity: isSelected ? 0.85 : 0.6,
            });

            polygon.bindPopup(popupContent);
            polygon.on('click', () => {
                selectedBlock.value = b;
            });

            polygon.addTo(featureGroup!);
            blockLayerMap.set(b.id, polygon);
        } else if (b.latitude && b.longitude) {
            bounds.push([b.latitude, b.longitude]);

            const marker = L.circleMarker([b.latitude, b.longitude], {
                radius: isSelected ? 12 : 8,
                color: isSelected ? '#ffffff' : color,
                weight: isSelected ? 3 : 1.5,
                fillColor: color,
                fillOpacity: 0.9,
            });

            marker.bindPopup(popupContent);
            marker.on('click', () => {
                selectedBlock.value = b;
            });

            marker.addTo(featureGroup!);
            blockLayerMap.set(b.id, marker);
        }
    });

    if (bounds.length > 0) {
        map.fitBounds(L.latLngBounds(bounds), {
            padding: [40, 40],
            maxZoom: 15,
        });
    }
};

const focusBlock = (b: BlockMapItem) => {
    selectedBlock.value = b;
    if (!map) return;

    if (b.geometry?.coordinates?.[0]) {
        const latLngs = b.geometry.coordinates[0].map(
            ([lng, lat]) => [lat, lng] as [number, number],
        );
        map.flyToBounds(L.latLngBounds(latLngs), {
            padding: [50, 50],
            maxZoom: 16,
            duration: 1,
        });
    } else if (b.latitude && b.longitude) {
        map.flyTo([b.latitude, b.longitude], 14, { duration: 1 });
    }

    const layer = blockLayerMap.get(b.id);
    if (layer) {
        layer.openPopup();
    }
};

const resetView = () => {
    if (!map || !featureGroup) return;
    const bounds = featureGroup.getBounds();
    if (bounds.isValid()) {
        map.flyToBounds(bounds, { padding: [40, 40], duration: 1 });
    }
};

watch([viewMode, filterEcosystem], () => {
    renderLayers();
});

watch(mapStyle, () => {
    setTileLayer();
});

watch(selectedBlock, (newBlock) => {
    if (!newBlock) return;
    renderLayers();
});

onMounted(() => {
    nextTick(() => {
        initMap();
    });
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'GIS Spatial Maps', href: '/gis' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6 p-6">
        <Head title="Pemetaan GIS Spasial Multi-Ekosistem (OSM + Leaflet)" />

        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    <Globe
                        class="h-7 w-7 text-emerald-600 dark:text-emerald-400"
                    />
                    Peta GIS Spasial OpenStreetMap &amp; Leaflet
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Pemetaan geospasial blok konsesi multi-ekosistem dengan
                    OpenStreetMap (OSM) dan Leaflet Engine gratis.
                </p>
            </div>

            <!-- View Mode Selector -->
            <div
                class="flex items-center self-start rounded-lg bg-neutral-100 p-1 text-xs font-medium sm:self-auto dark:bg-neutral-800"
            >
                <button
                    @click="viewMode = 'ecosystem'"
                    :class="[
                        'cursor-pointer rounded-md px-3 py-1.5 transition-colors',
                        viewMode === 'ecosystem'
                            ? 'bg-white font-bold shadow-sm dark:bg-neutral-900 dark:text-white'
                            : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100',
                    ]"
                >
                    Tipe Ekosistem
                </button>
                <button
                    @click="viewMode = 'stock'"
                    :class="[
                        'cursor-pointer rounded-md px-3 py-1.5 transition-colors',
                        viewMode === 'stock'
                            ? 'bg-white font-bold shadow-sm dark:bg-neutral-900 dark:text-white'
                            : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100',
                    ]"
                >
                    Stok Karbon (t/ha)
                </button>
                <button
                    @click="viewMode = 'sequestration'"
                    :class="[
                        'cursor-pointer rounded-md px-3 py-1.5 transition-colors',
                        viewMode === 'sequestration'
                            ? 'bg-white font-bold shadow-sm dark:bg-neutral-900 dark:text-white'
                            : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100',
                    ]"
                >
                    Sekuestrasi (t/thn)
                </button>
            </div>
        </div>

        <!-- Ecosystem Filter Chips -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <button
                @click="filterEcosystem = 'all'"
                :class="[
                    'shrink-0 cursor-pointer rounded-full border px-3 py-1.5 transition-all',
                    filterEcosystem === 'all'
                        ? 'border-emerald-600 bg-emerald-600 font-semibold text-white'
                        : 'border-neutral-200 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800',
                ]"
            >
                Semua Ekosistem ({{ blocks.length }})
            </button>
            <button
                @click="filterEcosystem = 'natural_forest'"
                :class="[
                    'shrink-0 cursor-pointer rounded-full border px-3 py-1.5 transition-all',
                    filterEcosystem === 'natural_forest'
                        ? 'border-emerald-600 bg-emerald-600 font-semibold text-white'
                        : 'border-neutral-200 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800',
                ]"
            >
                Hutan Alam Konservasi
            </button>
            <button
                @click="filterEcosystem = 'oil_palm'"
                :class="[
                    'shrink-0 cursor-pointer rounded-full border px-3 py-1.5 transition-all',
                    filterEcosystem === 'oil_palm'
                        ? 'border-emerald-600 bg-emerald-600 font-semibold text-white'
                        : 'border-neutral-200 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800',
                ]"
            >
                Kelapa Sawit (Palm Growth)
            </button>
            <button
                @click="filterEcosystem = 'hti_plantation'"
                :class="[
                    'shrink-0 cursor-pointer rounded-full border px-3 py-1.5 transition-all',
                    filterEcosystem === 'hti_plantation'
                        ? 'border-emerald-600 bg-emerald-600 font-semibold text-white'
                        : 'border-neutral-200 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800',
                ]"
            >
                HTI Kayu Budidaya
            </button>
            <button
                @click="filterEcosystem = 'mangrove'"
                :class="[
                    'shrink-0 cursor-pointer rounded-full border px-3 py-1.5 transition-all',
                    filterEcosystem === 'mangrove'
                        ? 'border-emerald-600 bg-emerald-600 font-semibold text-white'
                        : 'border-neutral-200 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800',
                ]"
            >
                Mangrove / Pesisir
            </button>
        </div>

        <!-- Map Viewer & Detail Panel Layout -->
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            <!-- Leaflet OSM Map Canvas (2 cols) -->
            <Card
                class="relative overflow-hidden border border-neutral-200 shadow-sm lg:col-span-2 dark:border-neutral-800"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between border-b bg-neutral-50/70 p-4 dark:bg-neutral-900/70"
                >
                    <div
                        class="flex items-center gap-2 text-xs font-semibold text-neutral-700 dark:text-neutral-300"
                    >
                        <Compass class="h-4 w-4 text-emerald-600" />
                        Peta Interaktif OpenStreetMap (OSM) &bull; Leaflet v1.9
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Tile switcher -->
                        <div
                            class="flex rounded-md border border-neutral-300 bg-white p-0.5 text-xs dark:border-neutral-700 dark:bg-neutral-800"
                        >
                            <button
                                @click="mapStyle = 'osm'"
                                :class="[
                                    'cursor-pointer rounded px-2 py-1 text-[11px]',
                                    mapStyle === 'osm'
                                        ? 'bg-emerald-600 font-semibold text-white'
                                        : 'text-neutral-500',
                                ]"
                            >
                                OSM Jalan
                            </button>
                            <button
                                @click="mapStyle = 'satellite'"
                                :class="[
                                    'cursor-pointer rounded px-2 py-1 text-[11px]',
                                    mapStyle === 'satellite'
                                        ? 'bg-emerald-600 font-semibold text-white'
                                        : 'text-neutral-500',
                                ]"
                            >
                                Satelit
                            </button>
                        </div>

                        <!-- Reset zoom -->
                        <button
                            @click="resetView"
                            class="cursor-pointer rounded border border-neutral-300 bg-white p-1.5 text-neutral-600 hover:bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300"
                            title="Pusatkan Peta ke Semua Blok"
                        >
                            <RotateCcw class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </CardHeader>

                <!-- Leaflet container -->
                <div class="relative bg-neutral-100 dark:bg-neutral-950">
                    <div ref="mapContainer" class="z-0 h-[520px] w-full"></div>
                </div>

                <!-- Legend footer -->
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-t border-neutral-200 bg-white p-3 text-[11px] text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-400"
                >
                    <div class="flex items-center gap-4">
                        <span
                            class="font-semibold text-neutral-700 dark:text-neutral-200"
                            >Legenda:</span
                        >
                        <div class="flex items-center gap-1.5">
                            <div
                                class="h-3 w-3 rounded-sm bg-emerald-600"
                            ></div>
                            <span>Hutan Alam (HCV)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="h-3 w-3 rounded-sm bg-lime-500"></div>
                            <span>Kelapa Sawit</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="h-3 w-3 rounded-sm bg-teal-600"></div>
                            <span>HTI Kayu</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="h-3 w-3 rounded-sm bg-cyan-600"></div>
                            <span>Mangrove</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="h-3 w-3 rounded-sm bg-amber-600"></div>
                            <span>Gambut</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-neutral-400">
                        &copy; OpenStreetMap &bull; Leaflet
                    </span>
                </div>
            </Card>

            <!-- Sidebar Detail Blok Terpilih -->
            <div class="space-y-4">
                <Card
                    v-if="selectedBlock"
                    class="border border-neutral-200 shadow-sm dark:border-neutral-800"
                >
                    <CardHeader class="border-b p-4">
                        <div class="flex items-center justify-between">
                            <Badge
                                variant="outline"
                                class="text-xs font-medium"
                            >
                                {{
                                    getEcosystemLabel(
                                        selectedBlock.ecosystem_type,
                                    )
                                }}
                            </Badge>
                            <Badge
                                v-if="selectedBlock.has_approved_run"
                                class="bg-emerald-600 text-[10px] text-white"
                            >
                                Terverifikasi
                            </Badge>
                        </div>
                        <CardTitle
                            class="mt-2 text-lg font-bold text-neutral-900 dark:text-neutral-100"
                        >
                            {{ selectedBlock.name }}
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kode:
                            <span class="font-mono">{{
                                selectedBlock.code
                            }}</span>
                            &bull; {{ selectedBlock.estate_name }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 p-4 text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                class="rounded-lg border border-neutral-200 bg-neutral-50 p-2.5 dark:border-neutral-800 dark:bg-neutral-800/60"
                            >
                                <span class="block text-neutral-400"
                                    >Luas Efektif</span
                                >
                                <span
                                    class="font-mono text-sm font-bold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{
                                        Number(selectedBlock.area_ha).toFixed(2)
                                    }}
                                    ha
                                </span>
                            </div>
                            <div
                                class="rounded-lg border border-neutral-200 bg-neutral-50 p-2.5 dark:border-neutral-800 dark:bg-neutral-800/60"
                            >
                                <span class="block text-neutral-400"
                                    >Jenis Tanah</span
                                >
                                <span
                                    class="block truncate text-xs font-bold text-neutral-900 dark:text-neutral-100"
                                >
                                    {{ selectedBlock.soil_type }}
                                </span>
                            </div>
                        </div>

                        <!-- Carbon Metrics -->
                        <div
                            class="space-y-2 border-t border-neutral-200 pt-3 dark:border-neutral-800"
                        >
                            <span
                                class="block text-xs font-semibold tracking-wider text-neutral-400 uppercase"
                            >
                                Neraca Karbon Terverifikasi
                            </span>
                            <div
                                class="space-y-2 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3.5 dark:border-emerald-800 dark:bg-emerald-950/30"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-neutral-600 dark:text-neutral-400"
                                        >Total Stok CO2e:</span
                                    >
                                    <span
                                        class="font-mono text-base font-bold text-emerald-700 dark:text-emerald-400"
                                    >
                                        {{
                                            Number(
                                                selectedBlock.latest_total_co2e,
                                            ).toLocaleString('id-ID', {
                                                maximumFractionDigits: 1,
                                            })
                                        }}
                                        t CO2e
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-neutral-600 dark:text-neutral-400"
                                        >Kerapatan per Ha:</span
                                    >
                                    <span
                                        class="font-mono font-bold text-neutral-800 dark:text-neutral-200"
                                    >
                                        {{
                                            Number(
                                                selectedBlock.latest_stock_co2e_ha,
                                            ).toFixed(2)
                                        }}
                                        t CO2e/ha
                                    </span>
                                </div>
                                <div
                                    v-if="
                                        selectedBlock.latest_sequestration_year
                                    "
                                    class="flex items-center justify-between"
                                >
                                    <span
                                        class="text-neutral-600 dark:text-neutral-400"
                                        >Laju Sekuestrasi:</span
                                    >
                                    <span
                                        class="font-mono font-bold text-emerald-600"
                                    >
                                        +{{
                                            Number(
                                                selectedBlock.latest_sequestration_year,
                                            ).toFixed(2)
                                        }}
                                        t/tahun
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-2">
                            <Link
                                href="/calculations/create"
                                class="block w-full"
                            >
                                <Button class="w-full gap-2">
                                    <Calculator class="h-4 w-4" />
                                    Jalankan Kalkulasi untuk Blok Ini
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Concession Navigation List -->
                <Card
                    class="border border-neutral-200 shadow-sm dark:border-neutral-800"
                >
                    <CardHeader class="border-b p-3.5">
                        <CardTitle
                            class="flex items-center justify-between text-xs font-bold text-neutral-900 dark:text-neutral-100"
                        >
                            <span>Daftar Blok Geospasial</span>
                            <span class="font-mono text-[11px] text-neutral-400"
                                >{{ filteredBlocks.length }} Blok</span
                            >
                        </CardTitle>
                    </CardHeader>
                    <div
                        class="max-h-60 divide-y divide-neutral-100 overflow-y-auto p-1 text-xs dark:divide-neutral-800"
                    >
                        <button
                            v-for="b in filteredBlocks"
                            :key="b.id"
                            @click="focusBlock(b)"
                            :class="[
                                'flex w-full cursor-pointer items-center justify-between rounded-lg p-2.5 text-left transition-colors',
                                selectedBlock?.id === b.id
                                    ? 'bg-emerald-50 font-semibold text-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300'
                                    : 'text-neutral-700 hover:bg-neutral-50 dark:text-neutral-300 dark:hover:bg-neutral-800',
                            ]"
                        >
                            <div class="truncate pr-2">
                                <div class="truncate text-xs font-medium">
                                    {{ b.name }}
                                </div>
                                <div
                                    class="font-mono text-[11px] text-neutral-400"
                                >
                                    [{{ b.code }}] &bull;
                                    {{ Number(b.area_ha).toFixed(1) }} ha
                                </div>
                            </div>
                            <div
                                class="shrink-0 text-right font-mono text-[11px] font-bold text-emerald-600 dark:text-emerald-400"
                            >
                                {{ Number(b.latest_stock_co2e_ha).toFixed(0) }}
                                t/ha
                            </div>
                        </button>
                    </div>
                </Card>
            </div>
        </div>
    </div>
</template>
