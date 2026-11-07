<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterData\StoreTreeCensusRequest;
use App\Models\FieldMeasurement;
use App\Models\LandBlock;
use App\Models\TreeMeasurement;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TreeCensusController extends Controller
{
    public function index(): Response
    {
        $trees = TreeMeasurement::with(['fieldMeasurement.landBlock.estate'])
            ->latest()
            ->paginate(20);

        $forestBlocks = LandBlock::forestEcosystems()->get();

        return Inertia::render('data/TreeCensus', [
            'trees' => $trees,
            'blocks' => $forestBlocks,
        ]);
    }

    public function store(StoreTreeCensusRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Buat atau hubungkan ke field measurement sesi terbaru
        $field = FieldMeasurement::firstOrCreate(
            [
                'land_block_id' => $validated['land_block_id'],
                'measurement_year' => now()->year,
            ],
            [
                'measurement_date' => now()->toDateString(),
                'measurement_method' => 'sample_plot_census',
                'status' => 'verified',
                'measured_by' => Auth::id() ?? 1,
            ]
        );

        $tree = TreeMeasurement::create([
            'field_measurement_id' => $field->id,
            'tree_tag_number' => $validated['tree_tag_number'] ?? 'TAG-'.rand(100, 999),
            'botanical_species' => $validated['botanical_species'],
            'wood_density_g_cm3' => $validated['wood_density_g_cm3'],
            'dbh_cm' => $validated['dbh_cm'],
            'total_height_m' => $validated['total_height_m'] ?? null,
        ]);

        AuditLogger::log('create', $tree, null, $validated);

        return back()->with('success', 'Data sensus pohon hutan berhasil direkam.');
    }
}
