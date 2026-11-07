<?php

use App\DTOs\Calculation\CalculationInputDTO;
use App\Models\CalculationRun;
use App\Models\MethodologyVersion;
use App\Services\Calculation\CarbonCalculationEngine;
use App\Services\Calculation\SequestrationEngine;
use App\Services\Calculation\SoilCarbonCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('CarbonCalculationEngine executes full 5-pool calculation on oil palm and generates auditable snapshots', function () {
    $this->seed();

    $engine = new CarbonCalculationEngine(new SoilCarbonCalculator, new SequestrationEngine);

    $methodologyVersion = MethodologyVersion::with('calculationParameters.versions')
        ->whereHas('methodology', fn ($q) => $q->where('code', 'PPKS_PALM_MUTATION'))
        ->firstOrFail();

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
        ],
        soilData: [
            'soil_type' => 'mineral',
            'depth_cm' => 30.0,
            'bulk_density' => 1.15,
            'soil_carbon_percent' => 2.4,
            'coarse_fragments_percent' => 0.0,
        ]
    );

    $previousRun = CalculationRun::where('calculation_type', 'stock_only')->latest()->first();

    $result = $engine->execute($input, $methodologyVersion, $previousRun);

    expect($result->totalCarbonTon)->toBeGreaterThan(3000.0)
        ->and($result->totalCo2eTon)->toBeGreaterThan(10000.0)
        ->and($result->poolsBreakdown)->toHaveKeys(['AGB', 'BGB', 'DEADWOOD', 'LITTER', 'SOIL'])
        ->and($result->inputSnapshot)->toHaveKeys(['ecosystem_type', 'area_ha', 'palm_data', 'soil_data'])
        ->and($result->formulaSnapshot)->toHaveKey('agb_model')
        ->and($result->parameterSnapshot)->toHaveKey('CF');

    if ($previousRun) {
        expect($result->sequestrationCo2eYear)->not()->toBeNull()
            ->and($result->comparisonDelta)->toHaveKey('sequestration_status');
    }
});
