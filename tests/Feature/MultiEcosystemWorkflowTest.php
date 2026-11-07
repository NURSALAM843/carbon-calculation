<?php

use App\Models\CalculationRun;
use App\Models\Estate;
use App\Models\EvidenceFile;
use App\Models\LandBlock;
use App\Models\MethodologyVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('can run calculation for natural forest using Chave 2014 methodology', function () {
    $this->seed();
    $user = User::first();
    $forestBlock = LandBlock::where('ecosystem_type', 'natural_forest')->first();
    $methodology = MethodologyVersion::whereHas('methodology', function ($q) {
        $q->where('code', 'CHAVE_2014_FOREST');
    })->first();

    $response = $this->actingAs($user)->post(route('calculations.store'), [
        'land_block_id' => $forestBlock->id,
        'methodology_version_id' => $methodology->id,
        'period_start' => '2024-01-01',
        'period_end' => '2024-12-31',
        'calculation_type' => 'stock_only',
        'submit_for_approval' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('calculation_runs', [
        'land_block_id' => $forestBlock->id,
        'ecosystem_type' => 'natural_forest',
        'status' => 'pending_approval',
    ]);
});

test('can record estate and land block through master data controller', function () {
    $this->seed();
    $user = User::first();
    $estate = Estate::first();

    // Create Estate
    $estateResponse = $this->actingAs($user)->post(route('data.estates.store'), [
        'organization_id' => $estate->organization_id,
        'code' => 'EST-TEST-99',
        'name' => 'Estate Uji Coba',
        'total_area_ha' => 1250.50,
        'province' => 'Kalimantan Barat',
        'regency' => 'Ketapang',
        'district' => 'Kendawangan',
        'village' => 'Air Hitam',
    ]);
    $estateResponse->assertRedirect();
    $this->assertDatabaseHas('estates', ['code' => 'EST-TEST-99']);

    // Create Land Block
    $blockResponse = $this->actingAs($user)->post(route('data.blocks.store'), [
        'estate_id' => $estate->id,
        'code' => 'BLK-TEST-01',
        'name' => 'Blok Mangrove Pantai',
        'ecosystem_type' => 'mangrove',
        'area_ha' => 45.2,
        'soil_type' => 'Gambut Pesisir / Hydric Soil',
        'land_type' => 'Wetland Pasang Surut',
    ]);
    $blockResponse->assertRedirect();
    $this->assertDatabaseHas('land_blocks', ['code' => 'BLK-TEST-01']);
});

test('can record tree census data for forest blocks', function () {
    $this->seed();
    $user = User::first();
    $forestBlock = LandBlock::where('ecosystem_type', 'natural_forest')->first();

    $response = $this->actingAs($user)->post(route('data.tree-census.store'), [
        'land_block_id' => $forestBlock->id,
        'tree_tag_number' => 'TAG-TEST-888',
        'botanical_species' => 'Shorea leprosula',
        'wood_density_g_cm3' => 0.52,
        'dbh_cm' => 42.5,
        'total_height_m' => 28.0,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tree_measurements', [
        'tree_tag_number' => 'TAG-TEST-888',
        'botanical_species' => 'Shorea leprosula',
    ]);
});

test('can record palm stand and stage mutation', function () {
    $this->seed();
    $user = User::first();
    $palmBlock = LandBlock::where('ecosystem_type', 'oil_palm')->first();

    $response = $this->actingAs($user)->post(route('data.palm-stands.store'), [
        'land_block_id' => $palmBlock->id,
        'planting_year' => 2021,
        'tree_count' => 3800,
        'growth_stage' => 'TBM_3',
        'average_height_m' => 1.2,
        'avg_frond_count' => 32,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('palm_stands', [
        'growth_stage' => 'TBM_3',
        'average_height_m' => 1.2,
    ]);
});

test('can record soil measurement and calculates SOC', function () {
    $this->seed();
    $user = User::first();
    $block = LandBlock::first();

    $response = $this->actingAs($user)->post(route('data.soil.store'), [
        'land_block_id' => $block->id,
        'measurement_date' => '2024-05-15',
        'soil_type' => 'Mineral Ultisol',
        'depth_cm' => 30.0,
        'bulk_density' => 1.20,
        'soil_carbon_percent' => 2.50,
        'coarse_fragments_percent' => 0.0,
        'laboratory' => 'Lab Penguji Mandiri',
    ]);

    $response->assertRedirect();
    // SOC formula: (2.50 / 100) * 1.20 * 30 * 1.0 * 100 = 90.0 t C/ha
    $this->assertDatabaseHas('soil_measurements', [
        'land_block_id' => $block->id,
        'bulk_density' => 1.20,
        'soil_carbon_percent' => 2.50,
        'soil_organic_carbon_t_ha' => 90.0,
    ]);
});

test('can upload evidence file and generates SHA-256 hash', function () {
    Storage::fake('public');
    $this->seed();
    $user = User::first();
    $block = LandBlock::first();

    $file = UploadedFile::fake()->create('lab_report_cert.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($user)->post(route('data.evidence.upload'), [
        'file' => $file,
        'land_block_id' => $block->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('evidence_files', [
        'file_name' => 'lab_report_cert.pdf',
        'land_block_id' => $block->id,
    ]);

    $evidence = EvidenceFile::where('file_name', 'lab_report_cert.pdf')->first();
    expect($evidence->hash)->not->toBeEmpty()
        ->and(strlen($evidence->hash))->toBe(64); // SHA-256 hex length
});

test('auditor can reject calculation run with audit notes', function () {
    $this->seed();
    $auditor = User::where('email', 'auditor@carbon.test')->first() ?? User::first();
    $block = LandBlock::first();
    $methodology = MethodologyVersion::first();

    // Create a pending run
    $user = User::first();
    $this->actingAs($user)->post(route('calculations.store'), [
        'land_block_id' => $block->id,
        'methodology_version_id' => $methodology->id,
        'period_start' => '2024-01-01',
        'period_end' => '2024-12-31',
        'submit_for_approval' => true,
    ]);

    $run = CalculationRun::where('land_block_id', $block->id)->where('status', 'pending_approval')->first();

    $rejectResponse = $this->actingAs($auditor)->post(route('approvals.reject', $run->id), [
        'notes' => 'Tolong perbaiki data tinggi pohon karena tidak sesuai drone.',
    ]);

    $rejectResponse->assertRedirect();
    $run->refresh();
    expect($run->status)->toBe('rejected');
});

test('can access GIS spatial and Reports endpoints', function () {
    $this->seed();
    $user = User::first();

    // GIS
    $gisResponse = $this->actingAs($user)->get(route('gis.index'));
    $gisResponse->assertOk();

    // Reports Index
    $reportsResponse = $this->actingAs($user)->get(route('reports.index'));
    $reportsResponse->assertOk();
    $reportsResponse->assertInertia(fn ($page) => $page
        ->component('reports/Index')
        ->has('approvedRuns')
        ->has('totalRuns')
        ->has('approvedCount')
    );

    // Reports CSV Download
    $csvResponse = $this->actingAs($user)->get(route('reports.download'));
    $csvResponse->assertOk();
    $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});
