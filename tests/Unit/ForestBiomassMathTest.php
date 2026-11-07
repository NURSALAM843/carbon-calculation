<?php

use App\DTOs\Calculation\CalculationInputDTO;
use App\Services\Calculation\SoilCarbonCalculator;
use App\Services\Calculation\Strategies\HtiTimberStrategy;
use App\Services\Calculation\Strategies\MangroveStrategy;
use App\Services\Calculation\Strategies\NaturalForestStrategy;

test('it accurately calculates natural forest biomass using Chave et al 2014 model', function () {
    $strategy = new NaturalForestStrategy;

    // Pohon Shorea leprosula: DBH = 60 cm, H = 32 m, rho = 0.52 g/cm3
    // Chave: AGB = 0.0673 * (rho * DBH^2 * H)^0.976
    // rho * DBH^2 * H = 0.52 * 3600 * 32 = 59904
    // (59904)^0.976 ≈ 46399.78
    // AGB = 0.0673 * 46399.78 ≈ 3122.7 kg
    $input = new CalculationInputDTO(
        ecosystemType: 'natural_forest',
        areaHa: 10.0,
        trees: [
            ['species' => 'Shorea leprosula', 'dbh_cm' => 60.0, 'height_m' => 32.0, 'wood_density' => 0.52],
        ]
    );

    $parameters = [
        'CHAVE_COEF' => 0.0673,
        'CHAVE_EXP' => 0.976,
        'DEFAULT_WOOD_DENSITY' => 0.52,
        'ROOT_SHOOT_RATIO_FOREST' => 0.24,
    ];

    $result = $strategy->calculateAboveGroundBiomass($input, $parameters);

    expect($result->biomassPerTreeKg)->toBeGreaterThan(3000.0)
        ->and($result->biomassPerTreeKg)->toBeLessThan(3300.0)
        ->and($result->agbTonPerHa)->toBeGreaterThan(1300.0); // 3.12 ton/tree * 450 trees/ha

    $bgb = $strategy->calculateBelowGroundBiomass($result->agbTonPerHa, $input, $parameters);
    expect($bgb)->toEqual(round($result->agbTonPerHa * 0.24, 4));
});

test('it calculates HTI timber biomass using volume and form factor', function () {
    $strategy = new HtiTimberStrategy;

    $input = new CalculationInputDTO(
        ecosystemType: 'hti_plantation',
        areaHa: 20.0,
        trees: [
            ['species' => 'Acacia mangium', 'dbh_cm' => 25.0, 'height_m' => 20.0, 'wood_density' => 0.56],
        ]
    );

    $parameters = [
        'DEFAULT_WOOD_DENSITY' => 0.56,
        'ROOT_SHOOT_RATIO_FOREST' => 0.20,
    ];

    $result = $strategy->calculateAboveGroundBiomass($input, $parameters);

    expect($result->biomassPerTreeKg)->toBeGreaterThan(300.0)
        ->and($result->biomassPerTreeKg)->toBeLessThan(600.0);
});

test('it calculates mangrove biomass including prop roots', function () {
    $strategy = new MangroveStrategy;

    $input = new CalculationInputDTO(
        ecosystemType: 'mangrove',
        areaHa: 5.0,
        trees: [
            ['species' => 'Rhizophora apiculata', 'dbh_cm' => 20.0, 'wood_density' => 0.78],
        ]
    );

    $parameters = [
        'DEFAULT_WOOD_DENSITY' => 0.78,
        'ROOT_SHOOT_RATIO_MANGROVE' => 0.42,
    ];

    $result = $strategy->calculateAboveGroundBiomass($input, $parameters);

    expect($result->biomassPerTreeKg)->toBeGreaterThan(90.0)
        ->and($result->biomassPerTreeKg)->toBeLessThan(120.0);

    $bgb = $strategy->calculateBelowGroundBiomass($result->agbTonPerHa, $input, $parameters);
    expect($bgb)->toEqual(round($result->agbTonPerHa * 0.42, 4));
});

test('it calculates soil organic carbon accurately for mineral and peat soils', function () {
    $calculator = new SoilCarbonCalculator;

    // 1. Mineral Soil: C% = 2.4%, BD = 1.15 g/cm3, Depth = 30 cm, Coarse = 0%
    // SOC = (2.4 / 100) * 1.15 * 30 * 100 = 82.8 t C/ha
    $mineralData = [
        'soil_type' => 'mineral',
        'depth_cm' => 30.0,
        'bulk_density' => 1.15,
        'soil_carbon_percent' => 2.4,
        'coarse_fragments_percent' => 0.0,
    ];

    $result = $calculator->calculate($mineralData, 10.0);

    expect($result['soc_t_c_ha'])->toEqual(82.8000)
        ->and($result['soc_total_t_c'])->toEqual(828.0000)
        ->and($result['soc_total_co2e'])->toEqual(round(828.0 * (44 / 12), 4));

    // 2. Peat Soil: C% = 48.5%, BD = 0.18 g/cm3, Depth = 100 cm
    // SOC = (48.5 / 100) * 0.18 * 100 * 100 = 873.0 t C/ha
    $peatData = [
        'soil_type' => 'peat',
        'depth_cm' => 100.0,
        'bulk_density' => 0.18,
        'soil_carbon_percent' => 48.5,
        'coarse_fragments_percent' => 0.0,
    ];

    $peatResult = $calculator->calculate($peatData, 5.0);

    expect($peatResult['soc_t_c_ha'])->toEqual(873.0000)
        ->and($peatResult['soc_total_t_c'])->toEqual(4365.0000);
});
