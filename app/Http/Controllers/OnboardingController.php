<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantContext;
use App\Services\TenantProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /**
     * Show the LBB onboarding screen.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            return redirect()->route('dashboard');
        }

        $appHost = parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';

        return Inertia::render('Onboarding/Create', [
            'appHost' => $appHost,
        ]);
    }

    /**
     * Check if a proposed subdomain slug is available.
     */
    public function checkSubdomain(Request $request): JsonResponse
    {
        $rawSlug = (string) $request->query('slug', '');
        $slug = Str::slug($rawSlug);

        if (empty($slug) || strlen($slug) < 3) {
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'message' => 'Subdomain minimal 3 karakter.',
            ]);
        }

        $exemptSubdomains = config('tenancy.exempt_subdomains', []);

        if (in_array(strtolower($slug), array_map('strtolower', $exemptSubdomains), true)) {
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'message' => "Subdomain '{$slug}' merupakan kata terlarang / sistem.",
            ]);
        }

        $exists = Tenant::withoutGlobalScopes()->where('slug', $slug)->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'message' => "Subdomain '{$slug}' sudah digunakan oleh lembaga lain.",
            ]);
        }

        return response()->json([
            'available' => true,
            'slug' => $slug,
            'message' => "Subdomain '{$slug}' tersedia!",
        ]);
    }

    /**
     * Handle the LBB creation and onboarding submission.
     */
    public function store(Request $request, TenantProvisioner $provisioner, TenantContext $context): RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            return redirect()->route('dashboard');
        }

        $exemptSubdomains = config('tenancy.exempt_subdomains', []);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                'unique:tenants,slug',
                Rule::notIn($exemptSubdomains),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp,svg'],
            'primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ], [
            'slug.regex' => 'Subdomain hanya boleh berisi huruf kecil, angka, dan tanda hubung (-).',
            'slug.not_in' => 'Subdomain ini merupakan kata khusus sistem dan tidak dapat digunakan.',
            'slug.unique' => 'Subdomain ini sudah digunakan lembaga lain.',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $tenant = $provisioner->createTenant($validated['name'], $validated['slug'], [
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'logo_path' => $logoPath,
            'primary_color' => $validated['primary_color'] ?? '#2563eb',
        ]);

        $provisioner->attachUser($tenant, $user, 'owner');
        $context->set($tenant);

        if ($request->hasSession()) {
            $request->session()->put('current_tenant_id', $tenant->id);
            $request->session()->flash('status', 'Lembaga Bimbingan Belajar berhasil dibuat!');
        }

        return redirect()->route('dashboard');
    }
}
