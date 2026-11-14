<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_and_create_tenant(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'company_name' => 'Acme Innovations',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::withoutGlobalScopes()->where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->tenant_id);

        $tenant = Tenant::find($user->tenant_id);
        $this->assertNotNull($tenant);
        $this->assertEquals('Acme Innovations', $tenant->name);
        $this->assertTrue($user->hasRole('owner'));
    }

    public function test_new_users_can_register_within_subdomain_tenant(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'portal', 'is_active' => true]);

        $response = $this->get('http://portal.localhost/');
        $response->assertStatus(200);

        $postResponse = $this->post('http://portal.localhost/register', [
            'name' => 'Portal Member',
            'email' => 'member@portal.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $postResponse->assertRedirect(route('dashboard', absolute: false));

        $user = User::withoutGlobalScopes()->where('email', 'member@portal.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($tenant->id, $user->tenant_id);
        $this->assertTrue($user->hasRole('member'));
    }
}
