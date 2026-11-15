<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_onboarding(): void
    {
        $response = $this->get('/onboarding');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_without_tenant_can_view_onboarding_screen(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        $response = $this->actingAs($user)->get('/onboarding');

        $response->assertStatus(200);
        $response->assertSee('Onboarding');
    }

    public function test_user_already_having_tenant_is_redirected_to_dashboard(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/onboarding');

        $response->assertRedirect('/dashboard');
    }

    public function test_subdomain_availability_check_api(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);
        Tenant::factory()->create(['slug' => 'bintang-prestasi']);

        // 1. Available subdomain
        $response = $this->actingAs($user)->getJson('/onboarding/check-subdomain?slug=bintang-baru');
        $response->assertStatus(200)
            ->assertJson([
                'available' => true,
                'slug' => 'bintang-baru',
            ]);

        // 2. Existing subdomain
        $response = $this->actingAs($user)->getJson('/onboarding/check-subdomain?slug=bintang-prestasi');
        $response->assertStatus(200)
            ->assertJson([
                'available' => false,
                'slug' => 'bintang-prestasi',
            ]);

        // 3. Exempt system subdomain
        $response = $this->actingAs($user)->getJson('/onboarding/check-subdomain?slug=admin');
        $response->assertStatus(200)
            ->assertJson([
                'available' => false,
            ]);

        // 4. Too short subdomain
        $response = $this->actingAs($user)->getJson('/onboarding/check-subdomain?slug=ab');
        $response->assertStatus(200)
            ->assertJson([
                'available' => false,
            ]);
    }

    public function test_user_can_complete_onboarding_with_profile_and_logo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['tenant_id' => null]);
        $logo = UploadedFile::fake()->image('lbb-logo.png', 200, 200);

        $response = $this->actingAs($user)->post('/onboarding', [
            'name' => 'Bimbel Cendekia Mandiri',
            'slug' => 'cendekia-mandiri',
            'phone' => '081298765432',
            'address' => 'Jl. Merdeka No. 45, Bandung',
            'description' => 'Bimbingan belajar spesialis masuk PTN dan kedinasan.',
            'primary_color' => '#059669',
            'logo' => $logo,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('status');

        // Verify tenant was created
        $tenant = Tenant::withoutGlobalScopes()->where('slug', 'cendekia-mandiri')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('Bimbel Cendekia Mandiri', $tenant->name);
        $this->assertEquals('081298765432', $tenant->phone);
        $this->assertEquals('Jl. Merdeka No. 45, Bandung', $tenant->address);
        $this->assertEquals('Bimbingan belajar spesialis masuk PTN dan kedinasan.', $tenant->description);
        $this->assertEquals('#059669', $tenant->primary_color);
        $this->assertNotNull($tenant->logo_path);

        // Verify file stored in fake storage
        Storage::disk('public')->assertExists($tenant->logo_path);

        // Verify user relationship & role
        $user->refresh();
        $this->assertEquals($tenant->id, $user->tenant_id);
        $this->assertTrue($user->hasRole('owner'));

        // Verify active context in session
        $this->assertEquals($tenant->id, session('current_tenant_id'));
    }

    public function test_onboarding_validates_required_fields_and_subdomain_rules(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);
        Tenant::factory()->create(['slug' => 'bimbel-hebat']);

        // Missing required fields
        $response = $this->actingAs($user)->post('/onboarding', []);
        $response->assertSessionHasErrors(['name', 'slug']);

        // Duplicate slug
        $response = $this->actingAs($user)->post('/onboarding', [
            'name' => 'Bimbel Hebat Baru',
            'slug' => 'bimbel-hebat',
        ]);
        $response->assertSessionHasErrors(['slug']);

        // Exempt slug (e.g. admin)
        $response = $this->actingAs($user)->post('/onboarding', [
            'name' => 'Admin Lembaga',
            'slug' => 'admin',
        ]);
        $response->assertSessionHasErrors(['slug']);

        // Invalid format slug
        $response = $this->actingAs($user)->post('/onboarding', [
            'name' => 'Invalid Slug',
            'slug' => 'invalid_slug_with_underscores!',
        ]);
        $response->assertSessionHasErrors(['slug']);
    }

    public function test_lbb_owner_can_update_tenant_settings_and_branding(): void
    {
        Storage::fake('public');

        $tenant = Tenant::factory()->create([
            'name' => 'Bimbel Lama',
            'slug' => 'bimbel-lama',
            'phone' => '0811111111',
            'address' => 'Alamat Lama',
            'description' => 'Deskripsi Lama',
            'primary_color' => '#2563eb',
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        app(TenantContext::class)->set($tenant);

        // View settings
        $response = $this->actingAs($user)->get('/tenant/settings');
        $response->assertStatus(200);

        // Update settings
        $newLogo = UploadedFile::fake()->image('new-logo.png');
        $updateResponse = $this->actingAs($user)->post('/tenant/settings', [
            'name' => 'Bimbel Baru Unggul',
            'phone' => '0822222222',
            'address' => 'Alamat Baru No. 1',
            'description' => 'Deskripsi Baru Lengkap',
            'primary_color' => '#7c3aed',
            'logo' => $newLogo,
        ]);

        $updateResponse->assertSessionHas('status', 'Profil lembaga berhasil diperbarui.');

        $tenant->refresh();
        $this->assertEquals('Bimbel Baru Unggul', $tenant->name);
        $this->assertEquals('0822222222', $tenant->phone);
        $this->assertEquals('Alamat Baru No. 1', $tenant->address);
        $this->assertEquals('Deskripsi Baru Lengkap', $tenant->description);
        $this->assertEquals('#7c3aed', $tenant->primary_color);
        Storage::disk('public')->assertExists($tenant->logo_path);
    }
}
