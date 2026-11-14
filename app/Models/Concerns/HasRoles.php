<?php

namespace App\Models\Concerns;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    /**
     * The roles that belong to the user.
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role|string $role): void
    {
        $roleModel = is_string($role)
            ? Role::where('slug', $role)->firstOrFail()
            : $role;

        $this->roles()->syncWithoutDetaching([$roleModel->id]);
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role|string $role): void
    {
        $roleModel = is_string($role)
            ? Role::where('slug', $role)->first()
            : $role;

        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
        }
    }

    /**
     * Determine if the user has any of the given roles.
     *
     * @param  string|list<string>  $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $roles = is_array($roles) ? $roles : func_get_args();

        return $this->roles->contains(function (Role $role) use ($roles) {
            return in_array($role->slug, $roles, true);
        });
    }

    /**
     * Determine if the user has a specific permission via their roles.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->roles->loadMissing('permissions')
            ->flatMap(fn (Role $role) => $role->permissions)
            ->contains('slug', $permission);
    }

    /**
     * Get all permissions granted to the user across all their roles.
     *
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->roles->loadMissing('permissions')
            ->flatMap(fn (Role $role) => $role->permissions)
            ->unique('id');
    }
}
