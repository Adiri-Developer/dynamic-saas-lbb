<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

class TenantProvisioner
{
    /**
     * Create a new tenant along with its standard default roles.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createTenant(string $name, ?string $slug = null, array $attributes = []): Tenant
    {
        $baseSlug = $slug ? Str::slug($slug) : Str::slug($name);
        $finalSlug = $baseSlug;
        $counter = 1;

        while (Tenant::where('slug', $finalSlug)->exists()) {
            $finalSlug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $finalSlug,
            'domain' => $attributes['domain'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'address' => $attributes['address'] ?? null,
            'description' => $attributes['description'] ?? null,
            'logo_path' => $attributes['logo_path'] ?? null,
            'primary_color' => $attributes['primary_color'] ?? '#2563eb',
            'is_active' => $attributes['is_active'] ?? true,
            'settings' => $attributes['settings'] ?? [],
        ]);

        $defaultRoles = config('tenancy.default_roles', [
            'owner' => 'Tenant Owner',
            'admin' => 'Administrator',
            'member' => 'Team Member',
        ]);

        foreach ($defaultRoles as $roleSlug => $roleName) {
            Role::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $roleName,
                'slug' => $roleSlug,
            ]);
        }

        return $tenant;
    }

    /**
     * Attach a user to a tenant and assign a default role.
     */
    public function attachUser(Tenant $tenant, User $user, string $roleSlug = 'member'): void
    {
        $user->tenant_id = $tenant->id;
        $user->save();

        $defaultRoles = config('tenancy.default_roles', []);
        $roleName = $defaultRoles[$roleSlug] ?? ucfirst($roleSlug);

        $role = Role::withoutGlobalScopes()->firstOrCreate([
            'tenant_id' => $tenant->id,
            'slug' => $roleSlug,
        ], [
            'name' => $roleName,
        ]);

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
