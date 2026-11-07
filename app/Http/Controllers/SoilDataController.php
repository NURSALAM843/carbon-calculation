<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterData\StoreSoilMeasurementRequest;
use App\Models\LandBlock;
use App\Models\SoilMeasurement;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SoilDataController extends Controller
{
    public function index(): Response
    {
        $soils = SoilMeasurement::with(['landBlock.estate'])
            ->latest('measurement_date')
            ->paginate(15);

        return Inertia::render('data/SoilData', [
            'soils' => $soils,
            'blocks' => LandBlock::select('id', 'name', 'code', 'soil_type')->get(),
        ]);
    }

    public function store(StoreSoilMeasurementRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Hitung SOC baku
        $cFraction = $validated['soil_carbon_percent'] / 100.0;
        $fineSoil = 1.0 - (($validated['coarse_fragments_percent'] ?? 0) / 100.0);
        $socTonHa = round($cFraction * $validated['bulk_density'] * $validated['depth_cm'] * $fineSoil * 100.0, 4);

        $soil = SoilMeasurement::create(array_merge($validated, [
            'soil_organic_carbon_t_ha' => $socTonHa,
        ]));

        AuditLogger::log('create', $soil, null, $validated);

        return back()->with('success', 'Hasil uji laboratorium tanah berhasil disimpan.');
    }
}
