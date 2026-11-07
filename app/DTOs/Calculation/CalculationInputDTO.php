<?php

namespace App\DTOs\Calculation;

class CalculationInputDTO
{
    /**
     * @param  string  $ecosystemType  (oil_palm, natural_forest, hti_plantation, mangrove, peat_swamp_forest, agroforestry)
     * @param  float  $areaHa  Luas blok dalam hektar
     * @param  array<int, array{species: string, dbh_cm: float, height_m?: float|null, wood_density: float}>  $trees  Data pohon untuk hutan
     * @param array{
     *     tree_count: int,
     *     density_per_ha: float,
     *     growth_stage: string,
     *     avg_height_m: float,
     *     avg_stem_diameter_cm: float,
     *     avg_frond_count: float,
     *     avg_frond_biomass_kg: float,
     *     avg_fruit_bunch_biomass_kg: float,
     *     annual_pruned_fronds: float,
     *     age_years: int
     * }|null $palmData Data sensus sawit
     * @param array{
     *     soil_type: string,
     *     depth_cm: float,
     *     bulk_density: float,
     *     soil_carbon_percent: float,
     *     coarse_fragments_percent: float
     * }|null $soilData Data uji lab tanah
     */
    public function __construct(
        public string $ecosystemType,
        public float $areaHa,
        public array $trees = [],
        public ?array $palmData = null,
        public ?array $soilData = null,
    ) {}
}
