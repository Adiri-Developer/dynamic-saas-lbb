<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    /**
     * Get the currently active tenant.
     */
    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the ID of the currently active tenant.
     */
    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    /**
     * Set the currently active tenant.
     */
    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Determine if a tenant is currently set.
     */
    public function has(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Clear the current tenant context.
     */
    public function clear(): void
    {
        $this->tenant = null;
    }
}
