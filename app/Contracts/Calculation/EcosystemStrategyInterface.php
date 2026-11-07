<?php

namespace App\Contracts\Calculation;

use App\DTOs\Calculation\BiomassResultDTO;
use App\DTOs\Calculation\CalculationInputDTO;

interface EcosystemStrategyInterface
{
    /**
     * Hitung Above Ground Biomass (AGB) untuk tipe ekosistem ini.
     *
     * @param  array<string, float>  $parameters
     */
    public function calculateAboveGroundBiomass(CalculationInputDTO $input, array $parameters): BiomassResultDTO;

    /**
     * Hitung Below Ground Biomass (BGB) per hektar.
     *
     * @param  array<string, float>  $parameters
     */
    public function calculateBelowGroundBiomass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): float;

    /**
     * Hitung estimasi serasah (Litter) dan kayu mati (Deadwood) per hektar jika tidak diukur sensus langsung.
     *
     * @param  array<string, float>  $parameters
     * @return array{litter_t_ha: float, deadwood_t_ha: float}
     */
    public function calculateNecromass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): array;

    /**
     * Periksa apakah strategi ini mendukung tipe ekosistem tertentu.
     */
    public function supports(string $ecosystemType): bool;
}
