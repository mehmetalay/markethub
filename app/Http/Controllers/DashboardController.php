<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'summary' => [
                'tenantStatus' => $user->tenant?->status->value,
                'roles' => $user->getRoleNames()->values(),
                'apiFirst' => true,
                'marketplaceProviders' => 0,
            ],
        ]);
    }
}
