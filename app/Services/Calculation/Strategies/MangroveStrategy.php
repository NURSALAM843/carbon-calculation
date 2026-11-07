<?php

namespace App\Services\Calculation\Strategies;

use App\Contracts\Calculation\EcosystemStrategyInterface;
use App\DTOs\Calculation\BiomassResultDTO;
use App\DTOs\Calculation\CalculationInputDTO;

class MangroveStrategy implements EcosystemStrategyInterface
{
    public function calculateAboveGroundBiomass(CalculationInputDTO $input, array $parameters): BiomassResultDTO
    {
        $defaultDensity = $parameters['DEFAULT_WOOD_DENSITY'] ?? 0.780; // Rhizophora bakau

        $treeCount = count($input->trees);

        if ($treeCount === 0) {
            $agbTonPerHa = 165.0;
            $agbTotalTon = round($agbTonPerHa * $input->areaHa, 4);

            return new BiomassResultDTO(
                agbTotalTon: $agbTotalTon,
                agbTonPerHa: $agbTonPerHa,
                biomassPerTreeKg: 0.0,
                formulaUsed: 'IPCC Wetland Supplement Default Mangrove Baseline (165 t/ha)',
                componentBreakdown: ['method' => 'wetland_default', 'trees_measured' => 0]
            );
        }

        $totalTreeKg = 0.0;

        foreach ($input->trees as $tree) {
            $dbh = (float) $tree['dbh_cm'];
            $density = (float) ($tree['wood_density'] ?? $defaultDensity);

            // Alometrik Komiyama et al. (2005) & Kauffman (2012) untuk Rhizophora:
            // W_top = 0.0509 * rho * DBH^2.5
            // W_prop_root = 0.00974 * rho * DBH^2.71
            $wTopKg = 0.0509 * $density * pow($dbh, 2.5);
            $wPropRootKg = 0.00974 * $density * pow($dbh, 2.71);
            $treeAgbKg = $wTopKg + $wPropRootKg;

            $totalTreeKg += $treeAgbKg;
        }

        $avgTreeKg = $totalTreeKg / $treeCount;
        $standDensity = 650.0; // Standar kerapatan mangrove alami
        $agbTonPerHa = round(($avgTreeKg * $standDensity) / 1000.0, 4);
        $agbTotalTon = round($agbTonPerHa * $input->areaHa, 4);

        return new BiomassResultDTO(
            agbTotalTon: $agbTotalTon,
            agbTonPerHa: $agbTonPerHa,
            biomassPerTreeKg: round($avgTreeKg, 2),
            formulaUsed: 'Komiyama et al. (2005) Mangrove Model: AGB = (0.0509 * rho * D^2.5) + (0.00974 * rho * D^2.71)',
            componentBreakdown: [
                'trees_measured' => $treeCount,
                'avg_tree_biomass_kg' => round($avgTreeKg, 2),
                'stand_density_ha' => $standDensity,
            ]
        );
    }

    public function calculateBelowGroundBiomass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): float
    {
        $rootShootRatio = $parameters['ROOT_SHOOT_RATIO_MANGROVE'] ?? 0.420;

        return round($agbTonPerHa * $rootShootRatio, 4);
    }

    public function calculateNecromass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): array
    {
        return [
            'deadwood_t_ha' => round($agbTonPerHa * 0.08, 4),
            'litter_t_ha' => round($agbTonPerHa * 0.015, 4), // Serasah mangrove sering tersapu pasang surut
        ];
    }

    public function supports(string $ecosystemType): bool
    {
        return in_array($ecosystemType, ['mangrove', 'wetland', 'peat_swamp_forest'], true);
    }
}
