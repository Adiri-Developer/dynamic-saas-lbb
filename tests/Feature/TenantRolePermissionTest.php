<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TenantRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register dummy routes protected by role and permission middleware
        Route::get('/test-role-protected', function () {
            return response()->json(['message' => 'ok']);
        })->middleware(['web', 'auth', 'role:owner,admin']);

        Route::get('/test-permission-protected', function () {
            return response()->json(['message' => 'ok']);
        })->middleware(['web', 'auth', 'permission:manage-billing']);
    }

    public function test_roles_and_permissions_are_scoped_per_tenant(): void
    {
        $context = app(TenantContext::class);

        $tenantA = Tenant::factory()->create(['slug' => 'company-a']);
        $tenantB = Tenant::factory()->create(['slug' => 'company-b']);

        // Roles in tenant A
        $roleAdminA = Role::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Administrator',
            'slug' => 'admin',
        ]);

        // Roles in tenant B
        $roleAdminB = Role::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Administrator',
            'slug' => 'admin',
        ]);

        $perm = Permission::create([
            'name' => 'Manage Billing',
            'slug' => 'manage-billing',
        ]);

        // Give permission only to tenant A's admin role
        $roleAdminA->permissions()->attach($perm->id);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userA->assignRole($roleAdminA);

        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);
        $userB->assignRole($roleAdminB);

        // User A in Tenant A context has role admin and permission manage-billing
        $context->set($tenantA);
        $this->assertTrue($userA->hasRole('admin'));
        $this->assertTrue($userA->hasPermission('manage-billing'));

        // User B has admin role but does NOT have manage-billing permission
        $context->set($tenantB);
        $this->assertTrue($userB->hasRole('admin'));
        $this->assertFalse($userB->hasPermission('manage-billing'));

        $context->clear();
    }

    public function test_super_admin_bypasses_all_permissions(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertTrue($superAdmin->hasRole('owner'));
        $this->assertTrue($superAdmin->hasRole('admin'));
        $this->assertTrue($superAdmin->hasPermission('any-unregistered-permission'));
    }

    public function test_role_middleware_authorizes_valid_role(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'auth-test']);
        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'slug' => 'owner',
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        // Access route with owner role -> allowed
        $response = $this->actingAs($user)->get('/test-role-protected');
        $response->assertStatus(200);

        // Another user with 'member' role -> forbidden
        $memberRole = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Member',
            'slug' => 'member',
        ]);
        $memberUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $memberUser->assignRole($memberRole);

        $responseForbidden = $this->actingAs($memberUser)->get('/test-role-protected');
        $responseForbidden->assertStatus(403);
    }

    public function test_permission_middleware_authorizes_valid_permission(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'perm-test']);
        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Finance',
            'slug' => 'finance',
        ]);
        $perm = Permission::create([
            'name' => 'Manage Billing',
            'slug' => 'manage-billing',
        ]);
        $role->permissions()->attach($perm->id);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/test-permission-protected');
        $response->assertStatus(200);
    }
}
