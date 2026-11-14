<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_regular_tenant_user_cannot_access_super_admin_panel(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_super_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_super_admin_panel(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_tenants_list(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $tenantA = Tenant::factory()->create(['name' => 'Mega Corp', 'is_active' => true]);
        $tenantB = Tenant::factory()->create(['name' => 'Mini Corp', 'is_active' => false]);

        $response = $this->actingAs($superAdmin)->get('/admin/tenants');

        $response->assertStatus(200);
        $response->assertSee('Mega Corp');
        $response->assertSee('Mini Corp');
    }

    public function test_tenant_activation_status_can_be_updated(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);

        $tenant->update(['is_active' => false]);

        $this->assertFalse($tenant->fresh()->is_active);

        $tenant->update(['is_active' => true]);

        $this->assertTrue($tenant->fresh()->is_active);
    }
}
