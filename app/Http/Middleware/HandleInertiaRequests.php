<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = app(TenantContext::class)->get();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'domain' => $tenant->domain,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'description' => $tenant->description,
                'logo_url' => $tenant->logo_url,
                'primary_color' => $tenant->primary_color ?? '#2563eb',
            ] : null,
        ];
    }
}
