<?php

namespace App\Http\Controllers;

use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TenantSettingsController extends Controller
{
    /**
     * Show the tenant profile settings edit screen.
     */
    public function edit(TenantContext $context): Response|RedirectResponse
    {
        $tenant = $context->get();

        if (! $tenant) {
            return redirect()->route('onboarding.create');
        }

        return Inertia::render('Tenant/Settings', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'domain' => $tenant->domain,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'description' => $tenant->description,
                'logo_url' => $tenant->logo_url,
                'primary_color' => $tenant->primary_color ?? '#2563eb',
            ],
        ]);
    }

    /**
     * Update the tenant profile and branding.
     */
    public function update(Request $request, TenantContext $context): RedirectResponse
    {
        $tenant = $context->get();

        if (! $tenant) {
            return redirect()->route('onboarding.create');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp,svg'],
            'primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($tenant->logo_path && Storage::disk('public')->exists($tenant->logo_path)) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $tenant->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'logo_path' => $validated['logo_path'] ?? $tenant->logo_path,
            'primary_color' => $validated['primary_color'] ?? $tenant->primary_color,
        ]);

        return redirect()->back()->with('status', 'Profil lembaga berhasil diperbarui.');
    }
}
