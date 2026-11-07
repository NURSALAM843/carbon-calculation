<?php

namespace App\Http\Controllers;

use App\Models\Estate;
use App\Models\LandBlock;
use Inertia\Inertia;
use Inertia\Response;

class GisController extends Controller
{
    public function index(): Response
    {
        $estates = Estate::with(['organization'])
            ->get();

        $blocks = LandBlock::with([
            'estate',
            'calculationRuns' => fn ($q) => $q->with('result')->where('status', 'approved')->latest('period_end'),
        ])->get()->map(function ($b) {
            $latestRun = $b->calculationRuns->first();

            return [
                'id' => $b->id,
                'code' => $b->code,
                'name' => $b->name,
                'estate_name' => $b->estate->name ?? '',
                'ecosystem_type' => $b->ecosystem_type,
                'area_ha' => (float) $b->area_ha,
                'soil_type' => $b->soil_type,
                'land_type' => $b->land_type,
                'geometry' => $b->geometry,
                'latitude' => (float) ($b->estate->latitude ?? 0),
                'longitude' => (float) ($b->estate->longitude ?? 0),
                'latest_stock_co2e_ha' => (float) ($latestRun->result->co2e_stock_t_ha ?? 0),
                'latest_total_co2e' => (float) ($latestRun->result->total_co2e_t ?? 0),
                'latest_sequestration_year' => (float) ($latestRun->result->sequestration_t_co2e_year ?? 0),
                'has_approved_run' => $latestRun !== null,
            ];
        });

        return Inertia::render('gis/Index', [
            'estates' => $estates,
            'blocks' => $blocks,
        ]);
    }
}
