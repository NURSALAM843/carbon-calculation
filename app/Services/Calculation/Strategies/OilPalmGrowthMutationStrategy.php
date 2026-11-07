<?php

namespace App\Services\Calculation\Strategies;

use App\Contracts\Calculation\EcosystemStrategyInterface;
use App\DTOs\Calculation\BiomassResultDTO;
use App\DTOs\Calculation\CalculationInputDTO;

class OilPalmGrowthMutationStrategy implements EcosystemStrategyInterface
{
    public function calculateAboveGroundBiomass(CalculationInputDTO $input, array $parameters): BiomassResultDTO
    {
        $khalidA = $parameters['KHALID_A'] ?? 0.0706;
        $khalidB = $parameters['KHALID_B'] ?? 0.0976;
        $defaultFrondWeight = $parameters['FROND_DRY_WEIGHT_AVG'] ?? 3.50; // kg/pelepah

        $palm = $input->palmData ?? [];
        $ageYears = (int) ($palm['age_years'] ?? 8);
        $heightM = (float) ($palm['avg_height_m'] ?? 0.0);
        $densityPerHa = (float) ($palm['density_per_ha'] ?? 136.0);
        $growthStage = strtoupper((string) ($palm['growth_stage'] ?? 'TM_PRIMA'));

        $frondCount = (float) ($palm['avg_frond_count'] ?? 38.0);
        $frondWeight = (float) ($palm['avg_frond_biomass_kg'] ?? $defaultFrondWeight);
        $bunchBiomassKg = (float) ($palm['avg_fruit_bunch_biomass_kg'] ?? 0.0);

        // 1. Mutasi Fase Pertumbuhan Sawit
        if (str_starts_with($growthStage, 'TBM') || $ageYears <= 3) {
            // Fase TBM: Batang belum meninggi secara nyata (H ≈ 0). Biomassa didominasi tajuk pelepah muda dan bonggol akar
            $trunkBiomassKg = max(0.0, $heightM * 50.0); // Bonggol awal
            $crownBiomassKg = max(15.0, $frondCount * 1.8);
            $standingFruitKg = 0.0;
            $formula = 'PPKS TBM Phenological Stage Model (Crown & Basal Expansion)';
        } elseif ($heightM > 0.0) {
            // Fase TM dengan pengukuran tinggi batang: Model Khalid et al. 1999
            // Biomassa Batang = (a + b * H) dalam Megagram (ton) -> konversi ke kg (* 1000)
            $trunkBiomassKg = ($khalidA + $khalidB * $heightM) * 1000.0;
            $crownBiomassKg = $frondCount * $frondWeight;
            $standingFruitKg = $bunchBiomassKg;
            $formula = 'Khalid et al. (1999) Stem Alometric: Trunk = (0.0706 + 0.0976 * H) * 1000 kg + Standing Crown Fronds';
        } else {
            // Fase TM tanpa data tinggi batang: Model Logistik Umur Henson (2005)
            // AGB = AGB_max / (1 + exp(-k * (age - t0)))
            $agbMaxKg = 650.0; // 0.65 Mg
            $k = 0.28;
            $t0 = 8.5;
            $totalTreeAgbKg = $agbMaxKg / (1.0 + exp(-$k * ($ageYears - $t0)));

            $trunkBiomassKg = $totalTreeAgbKg * 0.70;
            $crownBiomassKg = $totalTreeAgbKg * 0.30;
            $standingFruitKg = 0.0;
            $formula = 'Henson (2005) Age-Dependent Logistic Model: AGB(t) = AGB_max / (1 + exp(-k*(t - t0)))';
        }

        $totalTreeKg = $trunkBiomassKg + $crownBiomassKg + $standingFruitKg;

        // Total per hektar dan per blok
        $agbTonPerHa = round(($totalTreeKg * $densityPerHa) / 1000.0, 4);
        $agbTotalTon = round($agbTonPerHa * $input->areaHa, 4);

        return new BiomassResultDTO(
            agbTotalTon: $agbTotalTon,
            agbTonPerHa: $agbTonPerHa,
            biomassPerTreeKg: round($totalTreeKg, 2),
            formulaUsed: $formula,
            componentBreakdown: [
                'growth_stage' => $growthStage,
                'age_years' => $ageYears,
                'trunk_biomass_kg' => round($trunkBiomassKg, 2),
                'crown_fronds_biomass_kg' => round($crownBiomassKg, 2),
                'standing_bunches_biomass_kg' => round($standingFruitKg, 2),
                'effective_density_per_ha' => $densityPerHa,
            ]
        );
    }

    public function calculateBelowGroundBiomass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): float
    {
        $palm = $input->palmData ?? [];
        $ageYears = (int) ($palm['age_years'] ?? 8);
        $growthStage = strtoupper((string) ($palm['growth_stage'] ?? 'TM_PRIMA'));

        // Mutasi dinamis rasio akar-tajuk sawit:
        // Pada fase muda (TBM), perakaran menyerap proporsi biomassa lebih besar (R ≈ 0.205).
        // Pada fase dewasa/tua (TM), batang menjadi sangat masif sehingga rasio akar turun (R ≈ 0.180).
        if (str_starts_with($growthStage, 'TBM') || $ageYears <= 3) {
            $rootShootRatio = $parameters['ROOT_SHOOT_PALM_YOUNG'] ?? 0.205;
        } elseif ($ageYears <= 8) {
            $rootShootRatio = 0.190;
        } else {
            $rootShootRatio = $parameters['ROOT_SHOOT_PALM_MATURE'] ?? 0.180;
        }

        return round($agbTonPerHa * $rootShootRatio, 4);
    }

    public function calculateNecromass(float $agbTonPerHa, CalculationInputDTO $input, array $parameters): array
    {
        $palm = $input->palmData ?? [];
        $densityPerHa = (float) ($palm['density_per_ha'] ?? 136.0);
        $annualPruned = (float) ($palm['annual_pruned_fronds'] ?? 38.0);
        $frondWeight = (float) ($palm['avg_frond_biomass_kg'] ?? 3.50);

        // Pada kelapa sawit, pemangkasan pelepah rutin menyumbang biomassa serasah nyata ke gawangan mati
        // Biomassa serasah pelepah tahunan (t/ha) = (pruned_fronds * weight * density) / 1000
        $litterPruningTonHa = ($annualPruned * $frondWeight * $densityPerHa) / 1000.0;
        // Dekomposisi: sekitar 30% bertahan sebagai serasah aktif di lantai kebun sebelum menjadi humus tanah
        $activeLitterTonHa = round($litterPruningTonHa * 0.30, 4);

        // Kayu mati pada kebun sawit terawat biasanya minimal (< 2% AGB)
        $deadwoodTonHa = round($agbTonPerHa * 0.02, 4);

        return [
            'deadwood_t_ha' => $deadwoodTonHa,
            'litter_t_ha' => $activeLitterTonHa,
        ];
    }

    public function supports(string $ecosystemType): bool
    {
        return in_array($ecosystemType, ['oil_palm', 'palm_plantation', 'kelapa_sawit'], true);
    }
}
