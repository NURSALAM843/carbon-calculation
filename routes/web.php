<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CalculationRunController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstateController;
use App\Http\Controllers\EvidenceVaultController;
use App\Http\Controllers\GisController;
use App\Http\Controllers\LandBlockController;
use App\Http\Controllers\PalmStandController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SoilDataController;
use App\Http\Controllers\TreeCensusController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Calculation Runs & Wizard
    Route::get('calculations', [CalculationRunController::class, 'index'])->name('calculations.index');
    Route::get('calculations/create', [CalculationRunController::class, 'create'])->name('calculations.create');
    Route::post('calculations/simulate', [CalculationRunController::class, 'simulate'])->name('calculations.simulate');
    Route::post('calculations', [CalculationRunController::class, 'store'])->name('calculations.store');
    Route::get('calculations/{id}', [CalculationRunController::class, 'show'])->name('calculations.show');
    Route::delete('calculations/{id}', [CalculationRunController::class, 'destroy'])->name('calculations.destroy');

    // Approval Workflow
    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('calculations/{id}/submit', [ApprovalController::class, 'submit'])->name('approvals.submit');
    Route::post('calculations/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('calculations/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');

    // Feature-specific Data Management Controllers
    Route::get('data/estates', [EstateController::class, 'index'])->name('data.estates');
    Route::post('data/estates', [EstateController::class, 'store'])->name('data.estates.store');

    Route::get('data/blocks', [LandBlockController::class, 'index'])->name('data.blocks');
    Route::post('data/blocks', [LandBlockController::class, 'store'])->name('data.blocks.store');

    Route::get('data/tree-census', [TreeCensusController::class, 'index'])->name('data.tree-census');
    Route::post('data/tree-census', [TreeCensusController::class, 'store'])->name('data.tree-census.store');

    Route::get('data/palm-stands', [PalmStandController::class, 'index'])->name('data.palm-stands');
    Route::post('data/palm-stands', [PalmStandController::class, 'store'])->name('data.palm-stands.store');

    Route::get('data/soil', [SoilDataController::class, 'index'])->name('data.soil');
    Route::post('data/soil', [SoilDataController::class, 'store'])->name('data.soil.store');

    Route::get('data/evidence', [EvidenceVaultController::class, 'index'])->name('data.evidence');
    Route::post('data/evidence', [EvidenceVaultController::class, 'upload'])->name('data.evidence.upload');

    // GIS Spasial (OSM + Leaflet)
    Route::get('gis', [GisController::class, 'index'])->name('gis.index');

    // Reports & Compliance
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/download', [ReportController::class, 'downloadCsv'])->name('reports.download');
});

require __DIR__.'/settings.php';
