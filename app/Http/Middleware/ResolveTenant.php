<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);
        $host = strtolower($request->getHost());

        $tenant = $this->resolveFromHost($host)
            ?? $this->resolveFromHeader($request);

        // Fallback to session / authenticated user if on central domain (except admin panel)
        if (! $tenant && ! $request->is('admin', 'admin/*')) {
            if ($request->hasSession() && $request->session()->has('current_tenant_id')) {
                $tenant = Tenant::find($request->session()->get('current_tenant_id'));
            } elseif ($request->user() && $request->user()->tenant_id) {
                $tenant = $request->user()->tenant;
            }
        }

        if ($tenant) {
            if (! $tenant->is_active) {
                abort(403, 'This tenant is currently inactive or suspended.');
            }

            $context->set($tenant);

            if ($request->hasSession()) {
                $request->session()->put('current_tenant_id', $tenant->id);
            }
        } else {
            // If the host had a subdomain that wasn't exempt and wasn't found, return 404
            $subdomain = $this->extractSubdomain($host);
            if ($subdomain && ! $this->isExempt($subdomain, $host)) {
                abort(404, "Tenant [{$subdomain}] not found.");
            }
        }

        return $next($request);
    }

    /**
     * Resolve tenant from domain or subdomain.
     */
    protected function resolveFromHost(string $host): ?Tenant
    {
        // 1. Check exact custom domain match
        $tenantByDomain = Tenant::where('domain', $host)->first();
        if ($tenantByDomain) {
            return $tenantByDomain;
        }

        // 2. Extract subdomain and check slug match
        $subdomain = $this->extractSubdomain($host);
        if ($subdomain && ! $this->isExempt($subdomain, $host)) {
            return Tenant::where('slug', $subdomain)->first();
        }

        return null;
    }

    /**
     * Resolve tenant from request header (useful for API & testing).
     */
    protected function resolveFromHeader(Request $request): ?Tenant
    {
        $header = $request->header('X-Tenant');
        if (! $header) {
            return null;
        }

        return Tenant::where('slug', $header)
            ->orWhere('id', is_numeric($header) ? (int) $header : null)
            ->first();
    }

    /**
     * Extract the subdomain part from a host.
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Ignore plain IPs
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $parts = explode('.', $host);

        // e.g. "localhost" (1 part) -> no subdomain
        if (count($parts) < 2) {
            return null;
        }

        return $parts[0];
    }

    /**
     * Determine if a subdomain or host is exempt from tenant resolution.
     */
    protected function isExempt(string $subdomain, string $host): bool
    {
        $exemptList = config('tenancy.exempt_subdomains', []);

        if (in_array($subdomain, $exemptList, true)) {
            return true;
        }

        $centralDomains = config('tenancy.central_domains', []);
        foreach ($centralDomains as $central) {
            if ($central && $host === strtolower($central)) {
                return true;
            }
        }

        return false;
    }
}
