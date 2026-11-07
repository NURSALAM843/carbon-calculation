<?php

namespace App\Services\Calculation\Strategies;

use App\Contracts\Calculation\EcosystemStrategyInterface;
use App\DTOs\Calculation\BiomassResultDTO;
use App\DTOs\Calculation\CalculationInputDTO;

class NaturalForestStrategy implements EcosystemStrategyInterface
{
    public function calculateAboveGroundBiomass(CalculationInputDTO $input, array $parameters): BiomassResultDTO
    {
        $chaveCoef = $parameters['CHAVE_COEF'] ?? 0.0673;
        $chaveExp = $parameters['CHAVE_EXP'] ?? 0.976;
        $defaultDensity = $parameters['DEFAULT_WOOD_DENSITY'] ?? 0.570;

        $totalTreeBiomassKg = 0.0;
        $treeCount = count($input->trees);

        if ($treeCount === 0) {
            // Default baseline jika belum ada sensus individual (menggunakan estimasi cadangan hutan alam sekunder: 180 t AGB/ha)
            $agbTonPerHa = 180.0;
            $agbTotalTon = round($agbTonPerHa * $input->areaHa, 4);

            return new BiomassResultDTO(
                agbTotalTon: $agbTotalTon,
                agbTonPerHa: $agbTonPerHa,
                biomassPerTreeKg: 0.0,
                formulaUsed: 'IPCC Tier 1 Default Tropical Rainforest Baseline (180 t/ha)',
                componentBreakdown: ['method' => 'tier1_default', 'trees_measured' => 0]
            );
        }

        foreach ($input->trees as $tree) {
            $dbh = (float) $tree['dbh_cm'];
            $density = (float) ($tree['wood_density'] ?? $defaultDensity);

            // Tinggi pohon: jika tidak ada, gunakan alometrik tinggi Chave pantropical
            $height = isset($tree['height_m']) && $tree['height_m'] > 0
                ? (float) $tree['height_m']
                : round(exp(0.893 + 0.760 * log($dbh) - 0.0340 * pow(log($dbh), 2)), 2);

            // Rumus Chave et al. 2014: AGB = 0.0673 * (rho * DBH^2 * H)^0.976 (kg)
            $combinedVar = $density * pow($dbh, 2) * $height;
            $treeAgbKg = $chaveCoef * pow($combinedVar, $chaveExp);

            $totalTreeBiomassKg += $treeAgbKg;
        }

        $avgTreeKg = $totalTreeBiomassKg / $treeCount;

        // Standar kerapatan pohon hutan alam (misal 450 pohon/ha) atau skala sensus plot
        $estimatedTreesPerHa = 450.0;
        $agbTonPerHa = round(($avgTreeKg * $estimatedTreesPerHa) / 1000.0, 4);
        $agbTotalTon = round($agbTonPerHa * $input->areaHa, 4);

        return new BiomassResultDTO(
            agbTotalTon: $agbTotalTon,
            agbTonPerHa: $agbTonPerHa,
            biomassPerTreeKg: round($avgTreeKg, 2),
            formulaUsed: 'Chave et al. (2014) Pantropical Model: AGB = 0.0673 * (rho * DBH^2 * H)^0.976',
            componentBreakdown: [
                'sample_trees_count' => $treeCount,
                'avg_tree_biomass_kg' => round($avgTreeKg, 2),
                'estimated_density_trees_ha' => $estimatedTreesPerHa,
            ]
        );
    }

    public function calculateBelowGroundBiomass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): float
    {
        $rootShootRatio = $parameters['ROOT_SHOOT_RATIO_FOREST'] ?? 0.240;

        return round($agbTonPerHa * $rootShootRatio, 4);
    }

    public function calculateNecromass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): array
    {
        // Standar IPCC AFOLU untuk hutan alam tropis
        return [
            'deadwood_t_ha' => round($agbTonPerHa * 0.10, 4), // 10% dari AGB
            'litter_t_ha' => round($agbTonPerHa * 0.025, 4),  // 2.5% dari AGB
        ];
    }

    public function supports(string $ecosystemType): bool
    {
        return in_array($ecosystemType, ['natural_forest', 'secondary_forest', 'hcv', 'conservation'], true);
    }
}
