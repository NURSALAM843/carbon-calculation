<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterData\StorePalmStandRequest;
use App\Models\CropCycle;
use App\Models\LandBlock;
use App\Models\PalmStand;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PalmStandController extends Controller
{
    public function index(): Response
    {
        $palmBlocks = LandBlock::oilPalm()
            ->with(['estate', 'cropCycles.palmStands'])
            ->get();

        return Inertia::render('data/PalmStands', [
            'blocks' => $palmBlocks,
        ]);
    }

    public function store(StorePalmStandRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $block = LandBlock::findOrFail($validated['land_block_id']);

        $cycle = CropCycle::firstOrCreate(
            ['land_block_id' => $block->id],
            [
                'crop_type' => 'Oil Palm',
                'planting_year' => $validated['planting_year'],
                'planting_date' => $validated['planting_year'].'-01-01',
                'planting_density' => round($validated['tree_count'] / max(0.1, $block->area_ha), 2),
                'initial_population' => $validated['tree_count'],
            ]
        );

        $stand = PalmStand::create([
            'crop_cycle_id' => $cycle->id,
            'tree_count' => $validated['tree_count'],
            'density_per_ha' => round($validated['tree_count'] / max(0.1, $block->area_ha), 2),
            'growth_stage' => $validated['growth_stage'],
            'average_height_m' => $validated['average_height_m'],
            'average_stem_diameter_cm' => 50.0,
            'age_years' => max(1, now()->year - $validated['planting_year']),
        ]);

        AuditLogger::log('create', $stand, null, $validated);

        return back()->with('success', 'Data tegakan sawit dan mutasi fase berhasil disimpan.');
    }
}
