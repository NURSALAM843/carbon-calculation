<?php

namespace App\Services\Calculation\Strategies;

use App\Contracts\Calculation\EcosystemStrategyInterface;
use App\DTOs\Calculation\BiomassResultDTO;
use App\DTOs\Calculation\CalculationInputDTO;

class HtiTimberStrategy implements EcosystemStrategyInterface
{
    public function calculateAboveGroundBiomass(CalculationInputDTO $input, array $parameters): BiomassResultDTO
    {
        $defaultDensity = $parameters['DEFAULT_WOOD_DENSITY'] ?? 0.560; // Akasia / Eukaliptus
        $formFactor = 0.65;
        $bef = 1.30; // Biomass Expansion Factor

        $treeCount = count($input->trees);

        if ($treeCount === 0) {
            $agbTonPerHa = 120.0;
            $agbTotalTon = round($agbTonPerHa * $input->areaHa, 4);

            return new BiomassResultDTO(
                agbTotalTon: $agbTotalTon,
                agbTonPerHa: $agbTonPerHa,
                biomassPerTreeKg: 0.0,
                formulaUsed: 'HTI Timber Average Yield Model Baseline (120 t/ha)',
                componentBreakdown: ['method' => 'default_timber_stand', 'trees_measured' => 0]
            );
        }

        $totalTreeKg = 0.0;

        foreach ($input->trees as $tree) {
            $dbh = (float) $tree['dbh_cm'];
            $density = (float) ($tree['wood_density'] ?? $defaultDensity);
            $height = isset($tree['height_m']) && $tree['height_m'] > 0
                ? (float) $tree['height_m']
                : round(1.2 * pow($dbh, 0.75), 2);

            // Volume batang silinder modifikasi faktor bentuk: V = pi/4 * (DBH/100)^2 * H * form_factor (m3)
            $volumeM3 = (pi() / 4.0) * pow($dbh / 100.0, 2) * $height * $formFactor;
            $stemBiomassKg = $volumeM3 * ($density * 1000.0);
            $treeAgbKg = $stemBiomassKg * $bef;

            $totalTreeKg += $treeAgbKg;
        }

        $avgTreeKg = $totalTreeKg / $treeCount;
        $standDensity = 833.0; // Standar HTI spasi 3m x 4m (833 pohon/ha)
        $agbTonPerHa = round(($avgTreeKg * $standDensity) / 1000.0, 4);
        $agbTotalTon = round($agbTonPerHa * $input->areaHa, 4);

        return new BiomassResultDTO(
            agbTotalTon: $agbTotalTon,
            agbTonPerHa: $agbTonPerHa,
            biomassPerTreeKg: round($avgTreeKg, 2),
            formulaUsed: 'HTI Timber Volume & BEF Model: AGB = (pi/4 * DBH^2 * H * f) * rho * BEF',
            componentBreakdown: [
                'trees_measured' => $treeCount,
                'avg_tree_biomass_kg' => round($avgTreeKg, 2),
                'density_trees_ha' => $standDensity,
                'form_factor' => $formFactor,
                'bef' => $bef,
            ]
        );
    }

    public function calculateBelowGroundBiomass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): float
    {
        $rootShootRatio = $parameters['ROOT_SHOOT_RATIO_FOREST'] ?? 0.200;

        return round($agbTonPerHa * $rootShootRatio, 4);
    }

    public function calculateNecromass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): array
    {
        return [
            'deadwood_t_ha' => round($agbTonPerHa * 0.05, 4),
            'litter_t_ha' => round($agbTonPerHa * 0.03, 4),
        ];
    }

    public function supports(string $ecosystemType): bool
    {
        return in_array($ecosystemType, ['hti_plantation', 'timber_plantation', 'agroforestry'], true);
    }
}
