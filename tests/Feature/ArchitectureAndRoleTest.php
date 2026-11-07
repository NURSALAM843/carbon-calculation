<?php

use App\Models\Estate;
use App\Models\LandBlock;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('all primary keys in database models are valid uuids', function () {
    $this->seed();

    $user = User::first();
    expect(Str::isUuid($user->id))->toBeTrue();

    $estate = Estate::first();
    expect(Str::isUuid($estate->id))->toBeTrue();

    $block = LandBlock::first();
    expect(Str::isUuid($block->id))->toBeTrue();

    $role = Role::first();
    expect(Str::isUuid($role->id))->toBeTrue();

    $permission = Permission::first();
    expect(Str::isUuid($permission->id))->toBeTrue();
});

test('spatie roles and permissions are correctly configured and assigned', function () {
    $this->seed();

    $admin = User::where('email', 'admin@carbon.test')->first();
    expect($admin->hasRole('super_admin'))->toBeTrue();
    expect($admin->can('calculations.create'))->toBeTrue();

    $manager = User::where('email', 'manager@carbon.test')->first();
    expect($manager->hasRole('sustainability_manager'))->toBeTrue();

    $surveyor = User::where('email', 'surveyor@carbon.test')->first();
    expect($surveyor->hasRole('field_surveyor'))->toBeTrue();
    expect($surveyor->can('data.trees.manage'))->toBeTrue();

    $auditor = User::where('email', 'auditor@carbon.test')->first();
    expect($auditor->hasRole('verifier_auditor'))->toBeTrue();
    expect($auditor->can('approvals.view'))->toBeTrue();
});

test('dedicated resource controllers handle requests successfully', function () {
    $this->seed();
    $user = User::first();

    $this->actingAs($user)->get(route('data.estates'))->assertOk();
    $this->actingAs($user)->get(route('data.blocks'))->assertOk();
    $this->actingAs($user)->get(route('data.tree-census'))->assertOk();
    $this->actingAs($user)->get(route('data.palm-stands'))->assertOk();
    $this->actingAs($user)->get(route('data.soil'))->assertOk();
    $this->actingAs($user)->get(route('data.evidence'))->assertOk();
    $this->actingAs($user)->get(route('gis.index'))->assertOk();
});
