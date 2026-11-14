<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
    ];

    /**
     * The permissions that belong to the role.
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * The users that belong to the role.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Give a permission to the role.
     */
    public function givePermissionTo(Permission|string $permission): void
    {
        $permModel = is_string($permission)
            ? Permission::firstOrCreate(['slug' => $permission], ['name' => ucfirst(str_replace(['.', '_', '-'], ' ', $permission))])
            : $permission;

        $this->permissions()->syncWithoutDetaching([$permModel->id]);
    }

    /**
     * Determine if the role has a given permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions->contains('slug', $permissionSlug);
    }
}
