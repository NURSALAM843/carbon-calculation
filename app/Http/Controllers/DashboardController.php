<?php

namespace App\Http\Controllers;

use App\Models\CalculationRun;
use App\Models\Estate;
use App\Models\LandBlock;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        // Agregasi metrik dari calculation runs yang disetujui (atau terbaru)
        $approvedRuns = CalculationRun::with(['result.poolResults.carbonPool', 'landBlock', 'estate'])
            ->where('status', 'approved')
            ->latest('period_end')
            ->get();

        $totalCo2eStock = $approvedRuns->sum(fn ($r) => (float) ($r->result->total_co2e_t ?? 0));
        $totalCarbonStock = $approvedRuns->sum(fn ($r) => (float) ($r->result->total_carbon_t ?? 0));
        $totalAssessedArea = LandBlock::sum('area_ha');
        $netAnnualSequestration = $approvedRuns->sum(fn ($r) => (float) ($r->result->sequestration_t_co2e_year ?? 0));

        // Komposisi 5 Kolam Karbon
        $poolSums = [
            'AGB' => 0.0,
            'BGB' => 0.0,
            'DEADWOOD' => 0.0,
            'LITTER' => 0.0,
            'SOIL' => 0.0,
        ];

        foreach ($approvedRuns as $run) {
            if ($run->result) {
                foreach ($run->result->poolResults as $poolRes) {
                    $code = $poolRes->carbonPool->code ?? null;
                    if ($code && isset($poolSums[$code])) {
                        $poolSums[$code] += (float) $poolRes->co2e_t;
                    }
                }
            }
        }

        // Sebaran per tipe ekosistem
        $ecosystemStats = LandBlock::selectRaw('ecosystem_type, count(*) as count, sum(area_ha) as total_area')
            ->groupBy('ecosystem_type')
            ->get()
            ->map(function ($row) use ($approvedRuns) {
                $typeRuns = $approvedRuns->where('landBlock.ecosystem_type', $row->ecosystem_type);
                $co2e = $typeRuns->sum(fn ($r) => (float) ($r->result->total_co2e_t ?? 0));

                return [
                    'ecosystem_type' => $row->ecosystem_type,
                    'block_count' => (int) $row->count,
                    'total_area_ha' => (float) $row->total_area,
                    'total_co2e_t' => round($co2e, 2),
                ];
            });

        // Mutasi pertumbuhan kelapa sawit berdasarkan fase umur
        $palmStages = LandBlock::where('ecosystem_type', 'oil_palm')
            ->with('cropCycles.palmStands')
            ->get()
            ->map(function ($b) {
                $stand = $b->cropCycles->first()?->palmStands->first();

                return [
                    'block_code' => $b->code,
                    'block_name' => $b->name,
                    'stage' => $stand->growth_stage ?? 'TM_PRIMA',
                    'age_years' => $stand->age_years ?? 8,
                    'density_ha' => $stand->density_per_ha ?? 136,
                    'height_m' => $stand->average_height_m ?? 0,
                ];
            });

        $recentRuns = CalculationRun::with(['landBlock', 'estate', 'methodologyVersion.methodology'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total_co2e_stock' => round($totalCo2eStock, 2),
                'total_carbon_stock' => round($totalCarbonStock, 2),
                'total_assessed_area_ha' => round((float) $totalAssessedArea, 2),
                'net_annual_sequestration' => round($netAnnualSequestration, 2),
                'avg_density_co2e_ha' => $totalAssessedArea > 0 ? round($totalCo2eStock / $totalAssessedArea, 2) : 0,
            ],
            'pools_composition' => $poolSums,
            'ecosystems' => $ecosystemStats,
            'palm_stages' => $palmStages,
            'recent_runs' => $recentRuns,
            'estates_count' => Estate::count(),
            'blocks_count' => LandBlock::count(),
        ]);
    }
}
