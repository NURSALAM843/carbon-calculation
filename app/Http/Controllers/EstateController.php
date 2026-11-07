<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterData\StoreEstateRequest;
use App\Models\Estate;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EstateController extends Controller
{
    public function index(): Response
    {
        $estates = Estate::withCount('landBlocks')
            ->with(['organization'])
            ->get();

        return Inertia::render('data/Estates', [
            'estates' => $estates,
        ]);
    }

    public function store(StoreEstateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $estate = Estate::create($validated);
        AuditLogger::log('create', $estate, null, $validated);

        return back()->with('success', 'Unit Estate berhasil didaftarkan.');
    }
}
