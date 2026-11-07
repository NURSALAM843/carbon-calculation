<?php

namespace Database\Seeders;

use App\Models\Approval;
use App\Models\CalculationComparison;
use App\Models\CalculationResult;
use App\Models\CalculationRun;
use App\Models\CarbonPool;
use App\Models\CarbonPoolResult;
use App\Models\CropCycle;
use App\Models\Estate;
use App\Models\FieldMeasurement;
use App\Models\LandBlock;
use App\Models\MethodologyVersion;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\PalmMeasurement;
use App\Models\PalmStand;
use App\Models\SoilMeasurement;
use App\Models\TreeMeasurement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoMultiEcosystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@carbon.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@carbon.test'],
            [
                'name' => 'Ahmad Pratama (ESG Manager)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $surveyor = User::firstOrCreate(
            ['email' => 'surveyor@carbon.test'],
            [
                'name' => 'Budi Santoso (Field Surveyor)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $auditor = User::firstOrCreate(
            ['email' => 'auditor@carbon.test'],
            [
                'name' => 'Dr. Siti Rahma (Lead Carbon Auditor)',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign Spatie Roles
        $admin->syncRoles(['super_admin']);
        $manager->syncRoles(['sustainability_manager']);
        $surveyor->syncRoles(['field_surveyor']);
        $auditor->syncRoles(['verifier_auditor']);

        // 2. Organization
        $org = Organization::firstOrCreate(
            ['code' => 'CORP-NCAG'],
            [
                'name' => 'PT Nusantara Carbon & Agroforestry TBK',
                'description' => 'Korporasi pengelolaan perkebunan kelapa sawit berkelanjutan dan konsesi restorasi ekosistem hutan tropis.',
            ]
        );

        // Map users to organization
        OrganizationUser::firstOrCreate(['organization_id' => $org->id, 'user_id' => $admin->id], ['role' => 'super_admin']);
        OrganizationUser::firstOrCreate(['organization_id' => $org->id, 'user_id' => $manager->id], ['role' => 'esg_manager']);
        OrganizationUser::firstOrCreate(['organization_id' => $org->id, 'user_id' => $surveyor->id], ['role' => 'surveyor']);
        OrganizationUser::firstOrCreate(['organization_id' => $org->id, 'user_id' => $auditor->id], ['role' => 'auditor']);

        // 3. Estates
        $estateRiau = Estate::firstOrCreate(
            ['code' => 'EST-RIAU-01'],
            [
                'organization_id' => $org->id,
                'name' => 'Riau Agro & Conservation Estate',
                'total_area_ha' => 1250.5000,
                'province' => 'Riau',
                'regency' => 'Pelalawan',
                'district' => 'Teluk Meranti',
                'village' => 'Kuala Panduk',
                'latitude' => 0.2854000,
                'longitude' => 102.4820000,
                'status' => 'active',
            ]
        );

        $estateKalteng = Estate::firstOrCreate(
            ['code' => 'EST-KTG-02'],
            [
                'organization_id' => $org->id,
                'name' => 'Kalteng Peatland & Wetland Forest Reserve',
                'total_area_ha' => 2400.0000,
                'province' => 'Kalimantan Tengah',
                'regency' => 'Pulang Pisau',
                'district' => 'Kahayan Hilir',
                'village' => 'Kalawa',
                'latitude' => -2.7650000,
                'longitude' => 114.2450000,
                'status' => 'active',
            ]
        );

        // 4. Land Blocks
        // Blok Sawit TM Prima (Riau)
        $blockPalm1 = LandBlock::firstOrCreate(
            ['code' => 'BLK-OP-01'],
            [
                'estate_id' => $estateRiau->id,
                'name' => 'Blok Sawit Produktif A1 (TM Prima)',
                'ecosystem_type' => 'oil_palm',
                'area_ha' => 25.5000,
                'soil_type' => 'mineral',
                'land_type' => 'palm_productive',
                'status' => 'active',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [102.4800, 0.2840],
                        [102.4850, 0.2840],
                        [102.4850, 0.2880],
                        [102.4800, 0.2880],
                        [102.4800, 0.2840],
                    ]],
                ],
            ]
        );

        // Blok Sawit TBM 2 Lahan Gambut (Riau)
        $blockPalm2 = LandBlock::firstOrCreate(
            ['code' => 'BLK-OP-02'],
            [
                'estate_id' => $estateRiau->id,
                'name' => 'Blok Sawit Muda B2 (TBM Gambut)',
                'ecosystem_type' => 'oil_palm',
                'area_ha' => 18.0000,
                'soil_type' => 'peat',
                'land_type' => 'palm_productive',
                'status' => 'active',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [102.4860, 0.2840],
                        [102.4900, 0.2840],
                        [102.4900, 0.2880],
                        [102.4860, 0.2880],
                        [102.4860, 0.2840],
                    ]],
                ],
            ]
        );

        // Blok Hutan Alam Konservasi HCV (Riau)
        $blockForest1 = LandBlock::firstOrCreate(
            ['code' => 'BLK-FOR-01'],
            [
                'estate_id' => $estateRiau->id,
                'name' => 'Zona Konservasi Hutan Alam HCV',
                'ecosystem_type' => 'natural_forest',
                'area_ha' => 54.2000,
                'soil_type' => 'mineral',
                'land_type' => 'hcv',
                'status' => 'active',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [102.4700, 0.2800],
                        [102.4780, 0.2800],
                        [102.4780, 0.2890],
                        [102.4700, 0.2890],
                        [102.4700, 0.2800],
                    ]],
                ],
            ]
        );

        // Blok HTI Akasia (Kalteng)
        $blockHti1 = LandBlock::firstOrCreate(
            ['code' => 'BLK-HTI-01'],
            [
                'estate_id' => $estateKalteng->id,
                'name' => 'Kompartemen HTI Acacia mangium Rotasi 4',
                'ecosystem_type' => 'hti_plantation',
                'area_ha' => 40.0000,
                'soil_type' => 'podzolic',
                'land_type' => 'hti_timber',
                'status' => 'active',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [114.2400, -2.7620],
                        [114.2480, -2.7620],
                        [114.2480, -2.7680],
                        [114.2400, -2.7680],
                        [114.2400, -2.7620],
                    ]],
                ],
            ]
        );

        // Blok Mangrove Pesisir (Kalteng)
        $blockMgv1 = LandBlock::firstOrCreate(
            ['code' => 'BLK-MGV-01'],
            [
                'estate_id' => $estateKalteng->id,
                'name' => 'Suaka Mangrove Estuari Kahayan',
                'ecosystem_type' => 'mangrove',
                'area_ha' => 15.8000,
                'soil_type' => 'alluvium',
                'land_type' => 'riparian',
                'status' => 'active',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [114.2500, -2.7600],
                        [114.2560, -2.7600],
                        [114.2560, -2.7660],
                        [114.2500, -2.7660],
                        [114.2500, -2.7600],
                    ]],
                ],
            ]
        );

        // 5. Crop Cycles & Stands
        // Sawit TM Prima (BLK-OP-01)
        $cyclePalm1 = CropCycle::firstOrCreate(
            ['land_block_id' => $blockPalm1->id],
            [
                'crop_type' => 'Oil Palm',
                'planting_date' => '2014-06-15',
                'planting_year' => 2014,
                'initial_population' => 3570,
                'planting_density' => 140.00,
                'variety' => 'DxP Marihat PPKS',
                'status' => 'active',
            ]
        );

        PalmStand::firstOrCreate(
            ['crop_cycle_id' => $cyclePalm1->id],
            [
                'tree_count' => 3500,
                'density_per_ha' => 137.25,
                'growth_stage' => 'TM_PRIMA',
                'average_height_m' => 4.85,
                'average_stem_diameter_cm' => 52.40,
                'age_years' => 10,
            ]
        );

        // Sawit TBM 2 (BLK-OP-02)
        $cyclePalm2 = CropCycle::firstOrCreate(
            ['land_block_id' => $blockPalm2->id],
            [
                'crop_type' => 'Oil Palm',
                'planting_date' => '2024-02-10',
                'planting_year' => 2024,
                'initial_population' => 2520,
                'planting_density' => 140.00,
                'variety' => 'Socfindo Lame',
                'status' => 'active',
            ]
        );

        PalmStand::firstOrCreate(
            ['crop_cycle_id' => $cyclePalm2->id],
            [
                'tree_count' => 2500,
                'density_per_ha' => 138.89,
                'growth_stage' => 'TBM_2',
                'average_height_m' => 0.40,
                'average_stem_diameter_cm' => 28.00,
                'age_years' => 2,
            ]
        );

        // 6. Field & Soil Measurements
        // Pengukuran Lapangan Sawit BLK-OP-01
        $fieldPalm1 = FieldMeasurement::firstOrCreate(
            [
                'land_block_id' => $blockPalm1->id,
                'measurement_year' => 2024,
            ],
            [
                'measurement_date' => '2024-09-10',
                'measurement_method' => 'sample_plot_census',
                'plot_area_m2' => 5000.00,
                'status' => 'verified',
                'measured_by' => $surveyor->id,
            ]
        );

        PalmMeasurement::firstOrCreate(
            ['field_measurement_id' => $fieldPalm1->id],
            [
                'sample_count' => 35,
                'avg_height_m' => 4.85,
                'avg_stem_diameter_cm' => 52.40,
                'avg_frond_count' => 38.00,
                'avg_frond_biomass_kg' => 3.65,
                'avg_fruit_bunch_biomass_kg' => 18.50,
                'annual_pruned_fronds' => 38.00,
            ]
        );

        SoilMeasurement::firstOrCreate(
            [
                'land_block_id' => $blockPalm1->id,
                'measurement_date' => '2024-09-11',
            ],
            [
                'soil_type' => 'mineral',
                'depth_cm' => 30.00,
                'bulk_density' => 1.150,
                'soil_carbon_percent' => 2.400,
                'coarse_fragments_percent' => 0.000,
                'soil_organic_carbon_t_ha' => 82.8000,
                'laboratory' => 'Pusat Penelitian Kelapa Sawit (PPKS)',
            ]
        );

        // Pengukuran Tanah Gambut BLK-OP-02
        SoilMeasurement::firstOrCreate(
            [
                'land_block_id' => $blockPalm2->id,
                'measurement_date' => '2024-08-20',
            ],
            [
                'soil_type' => 'peat',
                'depth_cm' => 100.00,
                'bulk_density' => 0.180,
                'soil_carbon_percent' => 48.500,
                'coarse_fragments_percent' => 0.000,
                'soil_organic_carbon_t_ha' => 873.0000,
                'laboratory' => 'Balai Penelitian Tanah Bogor',
            ]
        );

        // Pengukuran Pohon Hutan Alam BLK-FOR-01
        $fieldForest = FieldMeasurement::firstOrCreate(
            [
                'land_block_id' => $blockForest1->id,
                'measurement_year' => 2024,
            ],
            [
                'measurement_date' => '2024-07-15',
                'measurement_method' => 'sample_plot_census',
                'plot_area_m2' => 10000.00, // 1 ha sample plot
                'status' => 'verified',
                'measured_by' => $surveyor->id,
            ]
        );

        $forestTrees = [
            ['tag' => 'T-01', 'species' => 'Shorea leprosula (Meranti Merah)', 'dbh' => 58.5, 'height' => 34.0, 'wood_density' => 0.520],
            ['tag' => 'T-02', 'species' => 'Dipterocarpus crinitus (Keruing)', 'dbh' => 64.0, 'height' => 38.0, 'wood_density' => 0.720],
            ['tag' => 'T-03', 'species' => 'Koompassia excelsa (Kempas)', 'dbh' => 82.0, 'height' => 44.0, 'wood_density' => 0.760],
            ['tag' => 'T-04', 'species' => 'Palaquium gutta (Nyatoh)', 'dbh' => 42.0, 'height' => 28.0, 'wood_density' => 0.610],
            ['tag' => 'T-05', 'species' => 'Dyera costulata (Jelutung)', 'dbh' => 48.0, 'height' => 30.0, 'wood_density' => 0.440],
        ];

        foreach ($forestTrees as $tree) {
            TreeMeasurement::firstOrCreate(
                [
                    'field_measurement_id' => $fieldForest->id,
                    'tree_tag_number' => $tree['tag'],
                ],
                [
                    'botanical_species' => $tree['species'],
                    'dbh_cm' => $tree['dbh'],
                    'total_height_m' => $tree['height'],
                    'wood_density_g_cm3' => $tree['wood_density'],
                ]
            );
        }

        // 7. Seed Sample Calculation Runs & Results
        $palmMethodologyVer = MethodologyVersion::whereHas('methodology', fn ($q) => $q->where('code', 'PPKS_PALM_MUTATION'))->first();
        if ($palmMethodologyVer) {
            // Periode 2023 (Baseline)
            $run2023 = CalculationRun::firstOrCreate(
                [
                    'land_block_id' => $blockPalm1->id,
                    'period_end' => '2023-12-31',
                ],
                [
                    'organization_id' => $org->id,
                    'estate_id' => $estateRiau->id,
                    'methodology_version_id' => $palmMethodologyVer->id,
                    'executed_by' => $manager->id,
                    'ecosystem_type' => 'oil_palm',
                    'period_start' => '2023-01-01',
                    'area_ha' => 25.5000,
                    'calculation_type' => 'stock_only',
                    'status' => 'approved',
                    'input_snapshot' => [
                        'block_code' => 'BLK-OP-01',
                        'ecosystem_type' => 'oil_palm',
                        'tree_count' => 3500,
                        'density_per_ha' => 137.25,
                        'growth_stage' => 'TM_PRIMA',
                        'average_height_m' => 4.45,
                        'bulk_density' => 1.15,
                        'soil_carbon_percent' => 2.35,
                    ],
                    'formula_snapshot' => [
                        'agb_formula' => 'Biomass = (0.0706 + 0.0976 * H) * trees_per_ha',
                        'bgb_formula' => 'BGB = AGB * 0.180',
                        'soc_formula' => 'SOC = (SOC% / 100) * BD * Depth * 100',
                    ],
                    'parameter_snapshot' => [
                        'CF' => 0.47,
                        'CO2_RATIO' => 3.666667,
                        'ROOT_SHOOT_RATIO' => 0.180,
                    ],
                    'calculated_at' => '2023-12-31 10:00:00',
                ]
            );

            $result2023 = CalculationResult::firstOrCreate(
                ['calculation_run_id' => $run2023->id],
                [
                    'total_biomass_t' => 2185.5000,
                    'total_carbon_t' => 3130.4000,
                    'total_co2e_t' => 11478.1300,
                    'carbon_stock_t_ha' => 122.7608,
                    'co2e_stock_t_ha' => 450.1227,
                    'sequestration_t_co2e_year' => null,
                ]
            );

            // Approval record 2023
            Approval::firstOrCreate(
                ['calculation_run_id' => $run2023->id],
                [
                    'reviewer_id' => $auditor->id,
                    'status' => 'approved',
                    'notes' => 'Baseline calculation verified against 2023 field survey and certified lab soil test.',
                    'reviewed_at' => '2024-01-05 14:30:00',
                ]
            );

            // Periode 2024 (Current Run with Sequestration vs 2023)
            $run2024 = CalculationRun::firstOrCreate(
                [
                    'land_block_id' => $blockPalm1->id,
                    'period_end' => '2024-12-31',
                ],
                [
                    'organization_id' => $org->id,
                    'estate_id' => $estateRiau->id,
                    'methodology_version_id' => $palmMethodologyVer->id,
                    'executed_by' => $manager->id,
                    'ecosystem_type' => 'oil_palm',
                    'period_start' => '2024-01-01',
                    'area_ha' => 25.5000,
                    'calculation_type' => 'sequestration',
                    'status' => 'approved',
                    'input_snapshot' => [
                        'block_code' => 'BLK-OP-01',
                        'ecosystem_type' => 'oil_palm',
                        'tree_count' => 3500,
                        'density_per_ha' => 137.25,
                        'growth_stage' => 'TM_PRIMA',
                        'average_height_m' => 4.85,
                        'bulk_density' => 1.15,
                        'soil_carbon_percent' => 2.40,
                    ],
                    'formula_snapshot' => [
                        'agb_formula' => 'Biomass = (0.0706 + 0.0976 * H) * trees_per_ha',
                        'bgb_formula' => 'BGB = AGB * 0.180',
                        'soc_formula' => 'SOC = (SOC% / 100) * BD * Depth * 100',
                    ],
                    'parameter_snapshot' => [
                        'CF' => 0.47,
                        'CO2_RATIO' => 3.666667,
                        'ROOT_SHOOT_RATIO' => 0.180,
                    ],
                    'calculated_at' => '2024-12-31 16:00:00',
                ]
            );

            $result2024 = CalculationResult::firstOrCreate(
                ['calculation_run_id' => $run2024->id],
                [
                    'total_biomass_t' => 2345.2000,
                    'total_carbon_t' => 3280.6000,
                    'total_co2e_t' => 12028.8700,
                    'carbon_stock_t_ha' => 128.6510,
                    'co2e_stock_t_ha' => 471.7204,
                    'sequestration_t_co2e_year' => 550.7400,
                ]
            );

            // Pool Breakdown for 2024
            $poolsMap = CarbonPool::pluck('id', 'code');
            if (isset($poolsMap['AGB'])) {
                CarbonPoolResult::firstOrCreate(
                    ['calculation_result_id' => $result2024->id, 'carbon_pool_id' => $poolsMap['AGB']],
                    ['biomass_t' => 1987.40, 'carbon_t' => 934.08, 'co2e_t' => 3424.96, 'co2e_per_ha' => 134.31]
                );
            }
            if (isset($poolsMap['BGB'])) {
                CarbonPoolResult::firstOrCreate(
                    ['calculation_result_id' => $result2024->id, 'carbon_pool_id' => $poolsMap['BGB']],
                    ['biomass_t' => 357.73, 'carbon_t' => 168.13, 'co2e_t' => 616.48, 'co2e_per_ha' => 24.18]
                );
            }
            if (isset($poolsMap['LITTER'])) {
                CarbonPoolResult::firstOrCreate(
                    ['calculation_result_id' => $result2024->id, 'carbon_pool_id' => $poolsMap['LITTER']],
                    ['biomass_t' => 140.25, 'carbon_t' => 65.92, 'co2e_t' => 241.71, 'co2e_per_ha' => 9.48]
                );
            }
            if (isset($poolsMap['SOIL'])) {
                CarbonPoolResult::firstOrCreate(
                    ['calculation_result_id' => $result2024->id, 'carbon_pool_id' => $poolsMap['SOIL']],
                    ['biomass_t' => 0.00, 'carbon_t' => 2111.40, 'co2e_t' => 7741.80, 'co2e_per_ha' => 303.60]
                );
            }

            // Comparison Delta record
            CalculationComparison::firstOrCreate(
                [
                    'calculation_run_id' => $run2024->id,
                    'previous_calculation_run_id' => $run2023->id,
                ],
                [
                    'delta_carbon_t' => 150.2000,
                    'delta_co2e_t' => 550.7400,
                    'annualized_co2e_t' => 550.7400,
                ]
            );

            // Approval record 2024
            Approval::firstOrCreate(
                ['calculation_run_id' => $run2024->id],
                [
                    'reviewer_id' => $auditor->id,
                    'status' => 'approved',
                    'notes' => 'Annual sequestration calculation verified. Net carbon sink confirmed (+550.74 t CO2e/year).',
                    'reviewed_at' => '2025-01-08 11:15:00',
                ]
            );
        }
    }
}
