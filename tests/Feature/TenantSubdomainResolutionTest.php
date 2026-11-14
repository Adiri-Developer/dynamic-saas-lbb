<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSubdomainResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subdomain_resolves_active_tenant(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'acme', 'name' => 'Acme Inc', 'is_active' => true]);

        // Request with subdomain host via full URL
        $response = $this->get('http://acme.localhost/');

        $response->assertStatus(200);
        $this->assertTrue(app(TenantContext::class)->has());
        $this->assertEquals($tenant->id, app(TenantContext::class)->id());
    }

    public function test_header_resolves_tenant(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'stark', 'is_active' => true]);

        $response = $this->withHeader('X-Tenant', 'stark')
            ->get('/');

        $response->assertStatus(200);
        $this->assertTrue(app(TenantContext::class)->has());
        $this->assertEquals($tenant->id, app(TenantContext::class)->id());
    }

    public function test_inactive_tenant_returns_forbidden(): void
    {
        Tenant::factory()->create(['slug' => 'suspended-corp', 'is_active' => false]);

        $response = $this->get('http://suspended-corp.localhost/');

        $response->assertStatus(403);
    }

    public function test_non_existent_tenant_subdomain_returns_not_found(): void
    {
        $response = $this->get('http://nonexistent.localhost/');

        $response->assertStatus(404);
    }

    public function test_exempt_central_subdomains_do_not_abort_as_tenant(): void
    {
        $response = $this->get('http://admin.localhost/admin/login');

        // Admin login page should be accessible without tenant
        $this->assertNotEquals(404, $response->getStatusCode());
    }
}
