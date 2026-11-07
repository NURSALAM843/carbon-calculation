<?php

namespace Database\Seeders;

use App\Models\CalculationParameter;
use App\Models\CarbonPool;
use App\Models\Methodology;
use App\Models\MethodologyVersion;
use App\Models\ParameterVersion;
use Illuminate\Database\Seeder;

class MethodologySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Carbon Pools IPCC
        $pools = [
            [
                'code' => 'AGB',
                'name' => 'Above Ground Biomass',
                'description' => 'Biomassa hidup di atas permukaan tanah termasuk batang, cabang, ranting, dan daun/pelepah.',
                'active' => true,
            ],
            [
                'code' => 'BGB',
                'name' => 'Below Ground Biomass',
                'description' => 'Biomassa perakaran hidup di bawah permukaan tanah (akar tunggang, akar lateral, serabut).',
                'active' => true,
            ],
            [
                'code' => 'DEADWOOD',
                'name' => 'Dead Wood',
                'description' => 'Biomassa kayu mati berdiri atau roboh di atas permukaan lantai hutan/kebun.',
                'active' => true,
            ],
            [
                'code' => 'LITTER',
                'name' => 'Litter',
                'description' => 'Serasah organik lantai hutan/gawangan mati seperti daun gugur, ranting kecil, dan pelepah pangkasan.',
                'active' => true,
            ],
            [
                'code' => 'SOIL',
                'name' => 'Soil Organic Carbon (SOC)',
                'description' => 'Karbon organik yang tersimpan dalam partikel tanah mineral dan lapisan gambut.',
                'active' => true,
            ],
        ];

        foreach ($pools as $pool) {
            CarbonPool::firstOrCreate(['code' => $pool['code']], $pool);
        }

        // 2. Metodologi IPCC 2019 AFOLU (General Pantropical)
        $ipcc = Methodology::firstOrCreate(
            ['code' => 'IPCC_2019_AFOLU'],
            [
                'name' => '2019 Refinement to the 2006 IPCC Guidelines - AFOLU',
                'description' => 'Pedoman internasional baku IPCC untuk inventarisasi emisi dan serapan sektor Kehutanan dan Lahan (AFOLU).',
                'source' => 'Intergovernmental Panel on Climate Change (IPCC) 2019',
                'status' => 'active',
            ]
        );

        $ipccVer = MethodologyVersion::firstOrCreate(
            [
                'methodology_id' => $ipcc->id,
                'version' => '2019.1',
            ],
            [
                'effective_from' => '2019-01-01',
                'effective_to' => null,
                'formula_definition' => [
                    'carbon_pools' => ['AGB', 'BGB', 'DEADWOOD', 'LITTER', 'SOIL'],
                    'cf_default' => 0.47,
                    'co2_ratio' => 44 / 12,
                ],
                'status' => 'active',
            ]
        );

        $this->createParameter($ipccVer, 'CF', 'Carbon Fraction', 'fraction', 'Fraksi karbon bahan kering (IPCC 2019 default)', 0.470000);
        $this->createParameter($ipccVer, 'CO2_TO_C_RATIO', 'CO2 to C Molecular Ratio', 'ratio', 'Rasio stoikiometri BM CO2 / BA C (44/12)', 3.666667);
        $this->createParameter($ipccVer, 'ROOT_SHOOT_RATIO_FOREST', 'Forest Root to Shoot Ratio', 'ratio', 'Rasio akar-tajuk pohon hutan tropis basah', 0.240000);
        $this->createParameter($ipccVer, 'ROOT_SHOOT_RATIO_MANGROVE', 'Mangrove Root to Shoot Ratio', 'ratio', 'Rasio akar-tajuk mangrove perakaran lumpur', 0.420000);

        // 3. Metodologi Chave et al. 2014 (Hutan Alam Tropis)
        $chave = Methodology::firstOrCreate(
            ['code' => 'CHAVE_2014_FOREST'],
            [
                'name' => 'Chave et al. 2014 Pantropical Tree Biomass Model',
                'description' => 'Model alometrik pantropis paling terpercaya untuk estimasi biomassa pohon hutan alam tropis berbasis DBH, tinggi pohon, dan wood density.',
                'source' => 'Global Change Biology (2014) 20: 3177–3190',
                'status' => 'active',
            ]
        );

        $chaveVer = MethodologyVersion::firstOrCreate(
            [
                'methodology_id' => $chave->id,
                'version' => '1.0',
            ],
            [
                'effective_from' => '2014-01-01',
                'effective_to' => null,
                'formula_definition' => [
                    'equation' => 'AGB = 0.0673 * (rho * DBH^2 * H)^0.976',
                    'variables' => ['DBH', 'total_height', 'wood_density'],
                ],
                'status' => 'active',
            ]
        );

        $this->createParameter($chaveVer, 'CHAVE_COEF', 'Chave Pantropical Coefficient', 'dimensionless', 'Koefisien pengali persamaan Chave', 0.067300);
        $this->createParameter($chaveVer, 'CHAVE_EXP', 'Chave Power Exponent', 'dimensionless', 'Pangkat gabungan volume pohon Chave', 0.976000);
        $this->createParameter($chaveVer, 'DEFAULT_WOOD_DENSITY', 'Default Wood Specific Gravity', 'g/cm3', 'Massa jenis kayu rata-rata hutan tropis Asia Tenggara', 0.570000);

        // 4. Metodologi Alometrik & Mutasi Pertumbuhan Kelapa Sawit (PPKS / Khalid et al. 1999)
        $sawit = Methodology::firstOrCreate(
            ['code' => 'PPKS_PALM_MUTATION'],
            [
                'name' => 'PPKS & Khalid et al. Palm Growth Mutation Model',
                'description' => 'Model perhitungan biomassa sawit bertahap sesuai mutasi fase fenologis (TBM ke TM) dengan alometrik tinggi batang, tajuk pelepah, dan serasah pangkasan.',
                'source' => 'Khalid et al. 1999 / Pusat Penelitian Kelapa Sawit (PPKS)',
                'status' => 'active',
            ]
        );

        $sawitVer = MethodologyVersion::firstOrCreate(
            [
                'methodology_id' => $sawit->id,
                'version' => '2024.1',
            ],
            [
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'formula_definition' => [
                    'trunk_formula' => 'Biomass_trunk = (0.0706 + 0.0976 * H) * 1000 kg',
                    'root_mutation' => [
                        'TBM' => 0.205,
                        'TM_MUDA' => 0.190,
                        'TM_PRIMA' => 0.180,
                    ],
                ],
                'status' => 'active',
            ]
        );

        $this->createParameter($sawitVer, 'KHALID_A', 'Khalid Intercept a', 'Mg/palm', 'Konstanta intersep persamaan alometrik tinggi sawit', 0.070600);
        $this->createParameter($sawitVer, 'KHALID_B', 'Khalid Slope b', 'Mg/palm/m', 'Koefisien kemiringan tinggi batang sawit (H dalam meter)', 0.097600);
        $this->createParameter($sawitVer, 'ROOT_SHOOT_PALM_YOUNG', 'Root/Shoot Ratio Sawit Muda (TBM)', 'ratio', 'Rasio akar sawit fase vegetatif awal (<4 tahun)', 0.205000);
        $this->createParameter($sawitVer, 'ROOT_SHOOT_PALM_MATURE', 'Root/Shoot Ratio Sawit Dewasa (TM)', 'ratio', 'Rasio akar sawit fase produktif (>8 tahun)', 0.180000);
        $this->createParameter($sawitVer, 'FROND_DRY_WEIGHT_AVG', 'Average Dry Frond Weight', 'kg', 'Rata-rata bobot kering 1 pelepah sawit produktif', 3.500000);
        $this->createParameter($sawitVer, 'ANNUAL_PRUNED_FRONDS_AVG', 'Annual Pruned Fronds per Palm', 'fronds/year', 'Jumlah pelepah dipangkas per pohon yang diserap ke litter pool', 38.000000);
    }

    private function createParameter(MethodologyVersion $version, string $code, string $name, string $unit, string $description, float $value): void
    {
        $param = CalculationParameter::firstOrCreate(
            [
                'methodology_version_id' => $version->id,
                'code' => $code,
            ],
            [
                'name' => $name,
                'unit' => $unit,
                'description' => $description,
            ]
        );

        ParameterVersion::firstOrCreate(
            [
                'calculation_parameter_id' => $param->id,
                'effective_from' => '2019-01-01',
            ],
            [
                'numeric_value' => $value,
                'source' => $version->methodology->source,
                'source_reference' => 'Standard Benchmark',
                'effective_to' => null,
            ]
        );
    }
}
