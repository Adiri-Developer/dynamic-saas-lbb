<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantContext;
use App\Services\TenantProvisioner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request, TenantContext $context, TenantProvisioner $provisioner): RedirectResponse
    {
        $hasActiveTenant = $context->has();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_name' => $hasActiveTenant ? 'nullable|string|max:255' : 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($hasActiveTenant) {
            $provisioner->attachUser($context->get(), $user, 'member');
        } else {
            $companyName = $request->input('company_name') ?: "{$user->name}'s Workspace";
            $tenant = $provisioner->createTenant($companyName);
            $provisioner->attachUser($tenant, $user, 'owner');
            $context->set($tenant);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
