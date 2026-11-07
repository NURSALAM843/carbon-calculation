/**
 * Standar Referensi Opsi Karbon, Geospasial, dan Agronomi Indonesia
 * Mengacu pada BBSDLP / Balittanah (Puslitbangtanak), Ditjen Perkebunan, dan SNI 7724
 */

export interface ProvinceRegion {
    region: string;
    provinces: string[];
}

export const INDONESIA_PROVINCE_GROUPS: ProvinceRegion[] = [
    {
        region: 'Sumatera',
        provinces: [
            'Aceh',
            'Sumatera Utara',
            'Sumatera Barat',
            'Riau',
            'Kepulauan Riau',
            'Jambi',
            'Sumatera Selatan',
            'Kepulauan Bangka Belitung',
            'Bengkulu',
            'Lampung',
        ],
    },
    {
        region: 'Jawa',
        provinces: [
            'DKI Jakarta',
            'Jawa Barat',
            'Banten',
            'Jawa Tengah',
            'DI Yogyakarta',
            'Jawa Timur',
        ],
    },
    {
        region: 'Bali & Nusa Tenggara',
        provinces: ['Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur'],
    },
    {
        region: 'Kalimantan',
        provinces: [
            'Kalimantan Barat',
            'Kalimantan Tengah',
            'Kalimantan Selatan',
            'Kalimantan Timur',
            'Kalimantan Utara',
        ],
    },
    {
        region: 'Sulawesi',
        provinces: [
            'Sulawesi Utara',
            'Gorontalo',
            'Sulawesi Tengah',
            'Sulawesi Barat',
            'Sulawesi Selatan',
            'Sulawesi Tenggara',
        ],
    },
    {
        region: 'Maluku',
        provinces: ['Maluku', 'Maluku Utara'],
    },
    {
        region: 'Papua',
        provinces: [
            'Papua',
            'Papua Barat',
            'Papua Selatan',
            'Papua Tengah',
            'Papua Pegunungan',
            'Papua Barat Daya',
        ],
    },
];

export const INDONESIA_PROVINCES: string[] = INDONESIA_PROVINCE_GROUPS.flatMap(
    (g) => g.provinces,
);

export const PROVINCE_SELECT_GROUPS = INDONESIA_PROVINCE_GROUPS.map((g) => ({
    group: g.region,
    options: g.provinces,
}));

export interface SoilTypeGroup {
    group: string;
    options: Array<{
        value: string;
        label: string;
        description?: string;
    }>;
}

export const SOIL_TYPE_GROUPS: SoilTypeGroup[] = [
    {
        group: 'Lahan Gambut Organik (Peat Soils / Histosols)',
        options: [
            {
                value: 'Histosol - Gambut Dangkal (50-100 cm)',
                label: 'Histosol - Gambut Dangkal (50 - 100 cm, Saprik/Hemik)',
            },
            {
                value: 'Histosol - Gambut Sedang (100-200 cm)',
                label: 'Histosol - Gambut Sedang (100 - 200 cm, Hemik)',
            },
            {
                value: 'Histosol - Gambut Dalam (200-300 cm)',
                label: 'Histosol - Gambut Dalam (200 - 300 cm, Fibrik/Hemik)',
            },
            {
                value: 'Histosol - Gambut Sangat Dalam (>300 cm)',
                label: 'Histosol - Gambut Sangat Dalam (> 300 cm, Kubah Gambut)',
            },
        ],
    },
    {
        group: 'Tanah Mineral Dataran Rendah & Perkebunan',
        options: [
            {
                value: 'Ultisol (Podsolik Merah Kuning / Typic Hapludult)',
                label: 'Ultisol (Podsolik Merah Kuning / Typic Hapludult)',
            },
            {
                value: 'Inseptisol (Typic Dystrudept / Eutrudept)',
                label: 'Inseptisol (Tanah Mineral Muda / Typic Dystrudept)',
            },
            {
                value: 'Oksisol / Latosol (Typic Hapludox)',
                label: 'Oksisol / Latosol (Tanah Tua Sangat Lapuk / Hapludox)',
            },
            {
                value: 'Entisol / Aluvial (Typic Fluvaquent)',
                label: 'Entisol / Aluvial (Dataran Banjir Endapan / Fluvaquent)',
            },
            {
                value: 'Spodosol / Podsol Pasir Kuarsa (Typic Haplorthod)',
                label: 'Spodosol / Podsol Pasir Kuarsa (Hutan Kerangas / Haplorthod)',
            },
        ],
    },
    {
        group: 'Tanah Vulkanik & Dataran Tinggi',
        options: [
            {
                value: 'Andisol / Andosol Vulkanik (Typic Hapludand)',
                label: 'Andisol / Andosol (Abu Vulkanik Subur / Hapludand)',
            },
            {
                value: 'Mollisol (Typic Hapludoll)',
                label: 'Mollisol (Tanah Kaya Bahan Organik Permukaan / Hapludoll)',
            },
            {
                value: 'Vertisol / Grumusol Lempung (Typic Haplustert)',
                label: 'Vertisol / Grumusol (Lempung Berat Smektit / Haplustert)',
            },
        ],
    },
    {
        group: 'Tanah Pesisir, Rawa & Lahan Basah (Wetlands)',
        options: [
            {
                value: 'Tanah Sulfat Masam / Mangrove (Typic Sulfaquent)',
                label: 'Tanah Sulfat Masam / Mangrove (Pesisir Berpirit / Sulfaquent)',
            },
            {
                value: 'Gleysol Hidromorf Kelabu (Aquic Dystrudept)',
                label: 'Gleysol / Hidromorf Kelabu (Tanah Rawa Tergenang Air)',
            },
            {
                value: 'Regosol Pasir Pantai (Typic Udipsamment)',
                label: 'Regosol Pasir Pantai (Pesisir Berbutir Kasar / Udipsamment)',
            },
        ],
    },
];

export interface TopographyOption {
    value: string;
    label: string;
    slope: string;
    description: string;
}

export const TOPOGRAPHY_OPTIONS: TopographyOption[] = [
    {
        value: 'Datar / Flat (0-3%)',
        label: 'Datar / Flat (Lereng 0 - 3%)',
        slope: '0-3%',
        description: 'Dataran aluvial, kubah gambut, lembah datar',
    },
    {
        value: 'Berombak / Undulating (3-8%)',
        label: 'Berombak / Undulating (Lereng 3 - 8%)',
        slope: '3-8%',
        description: 'Landai, zona perkebunan sawit & HTI ideal',
    },
    {
        value: 'Bergelombang / Rolling (8-15%)',
        label: 'Bergelombang / Rolling (Lereng 8 - 15%)',
        slope: '8-15%',
        description: 'Perbukitan rendah, teras kontur standar',
    },
    {
        value: 'Agak Curam / Hilly (15-30%)',
        label: 'Agak Curam / Hilly (Lereng 15 - 30%)',
        slope: '15-30%',
        description: 'Perbukitan sedang, konservasi teras bersambung',
    },
    {
        value: 'Curam / Steep (30-45%)',
        label: 'Curam / Steep (Lereng 30 - 45%)',
        slope: '30-45%',
        description: 'Lereng curam, zona penyangga / agroforestri',
    },
    {
        value: 'Sangat Curam / Very Steep (>45%)',
        label: 'Sangat Curam / Very Steep (Lereng > 45%)',
        slope: '>45%',
        description:
            'Pegunungan terjal, kawasan lindung mutlak HCV/Hutan Lindung',
    },
];
