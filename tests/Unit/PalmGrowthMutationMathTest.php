<?php

use App\DTOs\Calculation\CalculationInputDTO;
use App\Services\Calculation\SequestrationEngine;
use App\Services\Calculation\Strategies\OilPalmGrowthMutationStrategy;

test('it calculates mature oil palm biomass using Khalid et al 1999 stem alometrics and fronds', function () {
    $strategy = new OilPalmGrowthMutationStrategy;

    // Sawit TM Prima: H = 4.85 m, density = 137.25 pohon/ha, luas = 25.5 ha
    // Khalid: Trunk Mg = (0.0706 + 0.0976 * 4.85) = 0.54396 Mg = 543.96 kg
    // Crown Fronds = 38 pelepah * 3.65 kg = 138.70 kg
    // Bunch Biomass = 18.50 kg
    // Total Tree = 543.96 + 138.70 + 18.50 = 701.16 kg/tree
    $input = new CalculationInputDTO(
        ecosystemType: 'oil_palm',
        areaHa: 25.5,
        palmData: [
            'tree_count' => 3500,
            'density_per_ha' => 137.25,
            'growth_stage' => 'TM_PRIMA',
            'avg_height_m' => 4.85,
            'avg_stem_diameter_cm' => 52.4,
            'avg_frond_count' => 38.0,
            'avg_frond_biomass_kg' => 3.65,
            'avg_fruit_bunch_biomass_kg' => 18.50,
            'annual_pruned_fronds' => 38.0,
            'age_years' => 10,
        ]
    );

    $parameters = [
        'KHALID_A' => 0.0706,
        'KHALID_B' => 0.0976,
        'ROOT_SHOOT_PALM_MATURE' => 0.180,
    ];

    $result = $strategy->calculateAboveGroundBiomass($input, $parameters);

    expect($result->biomassPerTreeKg)->toBeGreaterThan(695.0)
        ->and($result->biomassPerTreeKg)->toBeLessThan(705.0)
        ->and($result->agbTonPerHa)->toBeGreaterThan(95.0); // 0.701 t * 137.25 ≈ 96.2 t/ha

    // Verifikasi mutasi rasio akar sawit dewasa: R = 0.180
    $bgb = $strategy->calculateBelowGroundBiomass($result->agbTonPerHa, $input, $parameters);
    expect($bgb)->toEqual(round($result->agbTonPerHa * 0.180, 4));
});

test('it handles TBM young palm growth stage with higher root shoot ratio', function () {
    $strategy = new OilPalmGrowthMutationStrategy;

    // Sawit TBM 2: Umur 2 tahun, H ≈ 0.4 m
    $input = new CalculationInputDTO(
        ecosystemType: 'oil_palm',
        areaHa: 18.0,
        palmData: [
            'tree_count' => 2500,
            'density_per_ha' => 138.89,
            'growth_stage' => 'TBM_2',
            'avg_height_m' => 0.40,
            'avg_stem_diameter_cm' => 28.0,
            'avg_frond_count' => 24.0,
            'avg_frond_biomass_kg' => 1.80,
            'avg_fruit_bunch_biomass_kg' => 0.0,
            'annual_pruned_fronds' => 0.0,
            'age_years' => 2,
        ]
    );

    $parameters = [
        'ROOT_SHOOT_PALM_YOUNG' => 0.205,
    ];

    $result = $strategy->calculateAboveGroundBiomass($input, $parameters);

    expect($result->biomassPerTreeKg)->toBeLessThan(100.0);

    // Verifikasi mutasi akar fase muda: R = 0.205
    $bgb = $strategy->calculateBelowGroundBiomass($result->agbTonPerHa, $input, $parameters);
    expect($bgb)->toEqual(round($result->agbTonPerHa * 0.205, 4));
});

test('it calculates multi-period carbon sequestration delta accurately', function () {
    $engine = new SequestrationEngine;

    // Periode 1: 3130.40 t C pada 2023-12-31
    // Periode 2: 3280.60 t C pada 2024-12-31
    // Delta C = 150.20 t C
    // Delta CO2e = 150.20 * (44/12) ≈ 550.7333 t CO2e
    $delta = $engine->calculateDelta(
        carbonT1: 3130.4000,
        dateT1: '2023-12-31',
        carbonT2: 3280.6000,
        dateT2: '2024-12-31',
        areaHa: 25.5
    );

    expect($delta['delta_carbon_t'])->toEqual(150.2000)
        ->and($delta['delta_co2e_t'])->toBeGreaterThan(550.0)
        ->and($delta['delta_co2e_t'])->toBeLessThan(551.5)
        ->and($delta['sequestration_status'])->toBe('net_sink');
});
