<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterData\StoreLandBlockRequest;
use App\Models\Estate;
use App\Models\LandBlock;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandBlockController extends Controller
{
    public function index(Request $request): Response
    {
        $blocks = LandBlock::with(['estate', 'cropCycles.palmStands'])
            ->filter($request->only(['ecosystem_type', 'estate_id']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('data/LandBlocks', [
            'blocks' => $blocks,
            'estates' => Estate::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['ecosystem_type', 'estate_id']),
        ]);
    }

    public function store(StoreLandBlockRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $block = LandBlock::create($validated);
        AuditLogger::log('create', $block, null, $validated);

        return back()->with('success', 'Blok Lahan berhasil ditambahkan.');
    }
}
