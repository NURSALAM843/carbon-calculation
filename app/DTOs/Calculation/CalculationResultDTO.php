<?php

namespace App\DTOs\Calculation;

class CalculationResultDTO
{
    /**
     * @param  float  $totalBiomassTon  Total 5 pools biomass (ton)
     * @param  float  $totalCarbonTon  Total cadangan karbon (t C)
     * @param  float  $totalCo2eTon  Total setara CO2 (t CO2e)
     * @param  float  $carbonStockTonPerHa  Cadangan karbon per hektar (t C/ha)
     * @param  float  $co2eStockTonPerHa  Setara CO2 per hektar (t CO2e/ha)
     * @param  array<string, array{biomass_t: float, carbon_t: float, co2e_t: float, co2e_per_ha: float}>  $poolsBreakdown  Rincian 5 pools (AGB, BGB, DEADWOOD, LITTER, SOIL)
     * @param  array<string, mixed>  $inputSnapshot
     * @param  array<string, mixed>  $formulaSnapshot
     * @param  array<string, mixed>  $parameterSnapshot
     * @param  float|null  $sequestrationCo2eYear  Laju sekuestrasi tahunan jika komparasi multi-periode
     * @param  array<string, mixed>|null  $comparisonDelta
     */
    public function __construct(
        public float $totalBiomassTon,
        public float $totalCarbonTon,
        public float $totalCo2eTon,
        public float $carbonStockTonPerHa,
        public float $co2eStockTonPerHa,
        public array $poolsBreakdown,
        public array $inputSnapshot,
        public array $formulaSnapshot,
        public array $parameterSnapshot,
        public ?float $sequestrationCo2eYear = null,
        public ?array $comparisonDelta = null,
    ) {}
}
