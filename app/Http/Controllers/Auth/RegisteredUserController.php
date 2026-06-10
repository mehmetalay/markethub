<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Tenant\Enums\TenantStatus;
use App\Domain\Tenant\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant_name' => ['required', 'string', 'max:160'],
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        [$tenant, $user] = DB::transaction(function () use ($data): array {
            $tenant = Tenant::create([
                'name' => $data['tenant_name'],
                'slug' => $this->uniqueTenantSlug($data['tenant_name']),
                'status' => TenantStatus::Active,
                'billing_email' => $data['email'],
                'timezone' => config('app.timezone'),
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

            $ownerRole = Role::firstOrCreate([
                'name' => 'owner',
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);

            $user->assignRole($ownerRole);

            return [$tenant, $user];
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    private function uniqueTenantSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'tenant';
        $slug = $baseSlug;
        $suffix = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
