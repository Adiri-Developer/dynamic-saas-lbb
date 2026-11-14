<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use App\Services\TenantProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provisioner = app(TenantProvisioner::class);

        // 1. Create Super Admin
        $superAdmin = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'superadmin@saas.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'tenant_id' => null,
            ]
        );

        // 2. Create Global Standard Permissions
        $permissions = [
            'view-dashboard' => ['name' => 'View Dashboard', 'group' => 'general'],
            'manage-team' => ['name' => 'Manage Team', 'group' => 'team'],
            'manage-settings' => ['name' => 'Manage Settings', 'group' => 'settings'],
            'view-reports' => ['name' => 'View Reports', 'group' => 'reports'],
        ];

        $permModels = [];
        foreach ($permissions as $slug => $data) {
            $permModels[$slug] = Permission::updateOrCreate(
                ['slug' => $slug],
                ['name' => $data['name'], 'group' => $data['group']]
            );
        }

        // 3. Create Tenant 1 (Acme Corp)
        $tenantAcme = $provisioner->createTenant('Acme Corp', 'acme');

        // Grant permissions to roles
        $acmeOwnerRole = $tenantAcme->roles()->where('slug', 'owner')->first();
        if ($acmeOwnerRole) {
            $acmeOwnerRole->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $permModels)));
        }

        $acmeMemberRole = $tenantAcme->roles()->where('slug', 'member')->first();
        if ($acmeMemberRole) {
            $acmeMemberRole->permissions()->sync([$permModels['view-dashboard']->id]);
        }

        // Create Acme Users
        $ownerAcme = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'owner@acme.com'],
            [
                'name' => 'Acme Owner',
                'password' => Hash::make('password'),
                'tenant_id' => $tenantAcme->id,
            ]
        );
        $provisioner->attachUser($tenantAcme, $ownerAcme, 'owner');

        $userAcme = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'user@acme.com'],
            [
                'name' => 'Acme Member',
                'password' => Hash::make('password'),
                'tenant_id' => $tenantAcme->id,
            ]
        );
        $provisioner->attachUser($tenantAcme, $userAcme, 'member');

        // 4. Create Tenant 2 (Beta Studio)
        $tenantBeta = $provisioner->createTenant('Beta Studio', 'beta');

        $betaOwner = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'owner@beta.com'],
            [
                'name' => 'Beta Owner',
                'password' => Hash::make('password'),
                'tenant_id' => $tenantBeta->id,
            ]
        );
        $provisioner->attachUser($tenantBeta, $betaOwner, 'owner');
    }
}
