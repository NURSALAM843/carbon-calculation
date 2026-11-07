<?php

namespace App\Services\Calculation;

class SoilCarbonCalculator
{
    /**
     * Menghitung Soil Organic Carbon (SOC) dalam satuan ton C/ha dan total ton C.
     *
     * @param array{
     *     soil_type: string,
     *     depth_cm: float,
     *     bulk_density: float,
     *     soil_carbon_percent: float,
     *     coarse_fragments_percent?: float
     * }|null $soilData
     * @return array{
     *     soc_t_c_ha: float,
     *     soc_total_t_c: float,
     *     soc_total_co2e: float,
     *     method_applied: string
     * }
     */
    public function calculate(?array $soilData, float $areaHa): array
    {
        if (! $soilData) {
            // Default baseline jika uji lab belum tersedia (IPCC default mineral soil: 55 t C/ha)
            $socPerHa = 55.0;
            $totalC = round($socPerHa * $areaHa, 4);
            $totalCo2e = round($totalC * (44 / 12), 4);

            return [
                'soc_t_c_ha' => $socPerHa,
                'soc_total_t_c' => $totalC,
                'soc_total_co2e' => $totalCo2e,
                'method_applied' => 'IPCC Tier 1 Default Tropical Mineral Soil (55 t C/ha)',
            ];
        }

        $soilType = strtolower((string) ($soilData['soil_type'] ?? 'mineral'));
        $depthCm = (float) ($soilData['depth_cm'] ?? 30.0);
        $bulkDensity = (float) ($soilData['bulk_density'] ?? 1.15); // g/cm3 = t/m3
        $cPercent = (float) ($soilData['soil_carbon_percent'] ?? 2.0);
        $coarsePercent = (float) ($soilData['coarse_fragments_percent'] ?? 0.0);

        $cFraction = $cPercent / 100.0;
        $fineSoilFraction = max(0.0, 1.0 - ($coarsePercent / 100.0));

        // Rumus Baku Standar:
        // Volume 1 ha pada kedalaman D cm = 10,000 m2 * (D/100) m = 100 * D m3.
        // Berat tanah (t/ha) = (100 * D) * BD.
        // Karbon tanah (t C/ha) = Berat tanah * c_fraction * fine_soil_fraction.
        // Simplifikasi: SOC = c_fraction * BD * Depth * fine_soil_fraction * 100
        $socPerHa = $cFraction * $bulkDensity * $depthCm * $fineSoilFraction * 100.0;
        $socPerHa = round($socPerHa, 4);

        $totalC = round($socPerHa * $areaHa, 4);
        $totalCo2e = round($totalC * (44 / 12), 4);

        $isPeat = in_array($soilType, ['peat', 'gambut', 'organosol', 'histowetland'], true);
        $methodDesc = $isPeat
            ? "Tropical Peatland SOC Model (Depth: {$depthCm}cm, BD: {$bulkDensity} g/cm3, C: {$cPercent}%)"
            : 'Mineral Soil Horizon Model: SOC = (C% / 100) * BD * Depth * (1 - Coarse%) * 100';

        return [
            'soc_t_c_ha' => $socPerHa,
            'soc_total_t_c' => $totalC,
            'soc_total_co2e' => $totalCo2e,
            'method_applied' => $methodDesc,
        ];
    }
}
