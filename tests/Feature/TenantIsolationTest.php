<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_scoped_to_active_tenant(): void
    {
        $context = app(TenantContext::class);

        // 1. Create two separate tenants
        $tenantA = Tenant::factory()->create(['name' => 'Tenant Alpha', 'slug' => 'alpha']);
        $tenantB = Tenant::factory()->create(['name' => 'Tenant Beta', 'slug' => 'beta']);

        // 2. Create roles for Tenant A and Tenant B
        $roleA = Role::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Alpha Manager',
            'slug' => 'alpha-mgr',
        ]);

        $roleB = Role::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Beta Manager',
            'slug' => 'beta-mgr',
        ]);

        // 3. Set context to Tenant A
        $context->set($tenantA);

        $visibleRolesInA = Role::all();
        $this->assertTrue($visibleRolesInA->contains('id', $roleA->id));
        $this->assertFalse($visibleRolesInA->contains('id', $roleB->id));
        $this->assertCount(1, $visibleRolesInA);

        // 4. Set context to Tenant B
        $context->set($tenantB);

        $visibleRolesInB = Role::all();
        $this->assertTrue($visibleRolesInB->contains('id', $roleB->id));
        $this->assertFalse($visibleRolesInB->contains('id', $roleA->id));
        $this->assertCount(1, $visibleRolesInB);

        // 5. Clear context (global/superadmin scope)
        $context->clear();

        $allRoles = Role::all();
        $this->assertCount(2, $allRoles);
        $this->assertTrue($allRoles->contains('id', $roleA->id));
        $this->assertTrue($allRoles->contains('id', $roleB->id));
    }

    public function test_users_scoped_to_active_tenant(): void
    {
        $context = app(TenantContext::class);

        $tenantA = Tenant::factory()->create(['slug' => 'tenant-a']);
        $tenantB = Tenant::factory()->create(['slug' => 'tenant-b']);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id, 'email' => 'user.a@example.com']);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id, 'email' => 'user.b@example.com']);

        // In Tenant A context, User B must never leak
        $context->set($tenantA);
        $usersInA = User::all();
        $this->assertTrue($usersInA->contains('id', $userA->id));
        $this->assertFalse($usersInA->contains('id', $userB->id));

        // In Tenant B context, User A must never leak
        $context->set($tenantB);
        $usersInB = User::all();
        $this->assertTrue($usersInB->contains('id', $userB->id));
        $this->assertFalse($usersInB->contains('id', $userA->id));

        $context->clear();
    }

    public function test_automatically_assigns_tenant_id_on_creation(): void
    {
        $context = app(TenantContext::class);
        $tenant = Tenant::factory()->create();

        $context->set($tenant);

        // Create role without explicitly specifying tenant_id
        $role = Role::create([
            'name' => 'Support Agent',
            'slug' => 'support-agent',
        ]);

        $this->assertEquals($tenant->id, $role->tenant_id);

        $context->clear();
    }
}
