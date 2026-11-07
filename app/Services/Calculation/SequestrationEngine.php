<?php

namespace App\Services\Calculation;

use Carbon\Carbon;

class SequestrationEngine
{
    /**
     * Menghitung laju penyerapan karbon bersih antara dua periode kalkulasi (t1 -> t2).
     *
     * @param  float  $carbonT1  Total karbon periode t1 (ton C)
     * @param  string|Carbon  $dateT1  Tanggal akhir periode t1
     * @param  float  $carbonT2  Total karbon periode t2 (ton C)
     * @param  string|Carbon  $dateT2  Tanggal akhir periode t2
     * @param  float  $areaHa  Luas blok dalam hektar
     * @return array{
     *     delta_carbon_t: float,
     *     delta_co2e_t: float,
     *     time_elapsed_years: float,
     *     annualized_co2e_t: float,
     *     annualized_co2e_per_ha: float,
     *     sequestration_status: string
     * }
     */
    public function calculateDelta(
        float $carbonT1,
        string|Carbon $dateT1,
        float $carbonT2,
        string|Carbon $dateT2,
        float $areaHa
    ): array {
        $c1 = Carbon::parse($dateT1);
        $c2 = Carbon::parse($dateT2);

        $daysElapsed = max(1, $c1->diffInDays($c2));
        $yearsElapsed = round($daysElapsed / 365.25, 4);

        $deltaCarbon = round($carbonT2 - $carbonT1, 4);
        $deltaCo2e = round($deltaCarbon * (44 / 12), 4);

        $annualizedCo2e = round($deltaCo2e / max(0.01, $yearsElapsed), 4);
        $annualizedPerHa = $areaHa > 0 ? round($annualizedCo2e / $areaHa, 4) : 0.0;

        $status = match (true) {
            $annualizedCo2e > 0.01 => 'net_sink',
            $annualizedCo2e < -0.01 => 'net_source',
            default => 'equilibrium',
        };

        return [
            'delta_carbon_t' => $deltaCarbon,
            'delta_co2e_t' => $deltaCo2e,
            'time_elapsed_years' => $yearsElapsed,
            'annualized_co2e_t' => $annualizedCo2e,
            'annualized_co2e_per_ha' => $annualizedPerHa,
            'sequestration_status' => $status,
        ];
    }
}
