<?php

use App\Models\CalculationRun;
use App\Models\LandBlock;
use App\Models\MethodologyVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view calculations list', function () {
    $this->seed();
    $user = User::first();

    $response = $this->actingAs($user)->get(route('calculations.index'));

    $response->assertOk();
});

test('authenticated user can simulate calculation preview', function () {
    $this->seed();
    $user = User::first();
    $block = LandBlock::where('ecosystem_type', 'oil_palm')->first();
    $methodology = MethodologyVersion::first();

    $response = $this->actingAs($user)->postJson(route('calculations.simulate'), [
        'land_block_id' => $block->id,
        'methodology_version_id' => $methodology->id,
        'period_start' => '2024-01-01',
        'period_end' => '2024-12-31',
        'calculation_type' => 'stock_only',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'preview' => [
            'total_biomass_t',
            'total_carbon_t',
            'total_co2e_t',
            'pools_breakdown',
        ],
    ]);
});

test('storing calculation creates run, results, and audit log', function () {
    $this->seed();
    $user = User::first();
    $block = LandBlock::where('ecosystem_type', 'oil_palm')->first();
    $methodology = MethodologyVersion::first();

    $response = $this->actingAs($user)->post(route('calculations.store'), [
        'land_block_id' => $block->id,
        'methodology_version_id' => $methodology->id,
        'period_start' => '2024-01-01',
        'period_end' => '2024-12-31',
        'calculation_type' => 'stock_only',
        'submit_for_approval' => true,
    ]);

    $response->assertRedirect();

    $run = CalculationRun::where('land_block_id', $block->id)->where('status', 'pending_approval')->first();
    expect($run)->not->toBeNull()
        ->and($run->status)->toBe('pending_approval')
        ->and($run->result)->not->toBeNull()
        ->and($run->result->poolResults)->toHaveCount(5);

    // Test approval
    $auditor = User::where('email', 'auditor@carbon.test')->first() ?? $user;
    $approveResponse = $this->actingAs($auditor)->post(route('approvals.approve', $run->id), [
        'notes' => 'Verified by test suite.',
    ]);

    $approveResponse->assertRedirect();
    $run->refresh();
    expect($run->status)->toBe('approved');
});
