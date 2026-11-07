<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'calculations.view',
            'calculations.create',
            'calculations.delete',
            'approvals.view',
            'approvals.action',
            'data.estates.view',
            'data.estates.manage',
            'data.blocks.view',
            'data.blocks.manage',
            'data.trees.view',
            'data.trees.manage',
            'data.palms.view',
            'data.palms.manage',
            'data.soil.view',
            'data.soil.manage',
            'data.evidence.view',
            'data.evidence.manage',
            'reports.view',
            'reports.export',
            'gis.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 1. Super Admin role
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Sustainability Manager role
        $manager = Role::firstOrCreate(['name' => 'sustainability_manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'calculations.view',
            'calculations.create',
            'calculations.delete',
            'approvals.view',
            'approvals.action',
            'data.estates.view',
            'data.estates.manage',
            'data.blocks.view',
            'data.blocks.manage',
            'data.trees.view',
            'data.palms.view',
            'data.soil.view',
            'data.evidence.view',
            'reports.view',
            'reports.export',
            'gis.view',
        ]);

        // 3. Field Surveyor role
        $surveyor = Role::firstOrCreate(['name' => 'field_surveyor', 'guard_name' => 'web']);
        $surveyor->syncPermissions([
            'calculations.view',
            'data.estates.view',
            'data.blocks.view',
            'data.trees.view',
            'data.trees.manage',
            'data.palms.view',
            'data.palms.manage',
            'data.soil.view',
            'data.soil.manage',
            'data.evidence.view',
            'data.evidence.manage',
            'gis.view',
        ]);

        // 4. Verifier / Auditor role
        $auditor = Role::firstOrCreate(['name' => 'verifier_auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'calculations.view',
            'approvals.view',
            'data.estates.view',
            'data.blocks.view',
            'data.trees.view',
            'data.palms.view',
            'data.soil.view',
            'data.evidence.view',
            'reports.view',
            'reports.export',
            'gis.view',
        ]);
    }
}
