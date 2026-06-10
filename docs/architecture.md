# Architecture

MarketHub is organized as a Laravel application with a domain-oriented backend and an Inertia React admin panel. The backend defines tenant, identity, authorization, and marketplace integration boundaries. The frontend provides an authenticated admin shell that consumes server-rendered Inertia pages.

## Application Layers

The application is structured around clear responsibilities:

- HTTP controllers receive web requests and return Inertia responses or redirects.
- Middleware resolves shared request context such as authenticated user, tenant, and permission scope.
- Domain folders contain business-oriented models, contracts, and enums.
- Database migrations define durable application state.
- React pages and layouts provide the admin panel interface.

## Backend Entry Points

Web routes provide the browser-based admin panel:

- `/login`
- `/register`
- `/dashboard`
- `/logout`

API routes are versioned under `/api/v1` and protected by Sanctum. The current API foundation exposes authenticated identity data through `/api/v1/me`.

## Domain Boundaries

MarketHub uses explicit domain folders to keep integration concerns separated:

```text
app/Domain/Tenant
app/Domain/Marketplace
app/Domain/Catalog
app/Domain/Listing
app/Domain/Order
app/Domain/Shipment
app/Domain/Invoice
app/Domain/Return
app/Domain/Sync
```

Each domain is expected to own its models, enums, contracts, actions, data transfer objects, and policies as functionality grows. Shared framework concerns remain in Laravel’s standard `app/Http`, `app/Models`, and `app/Providers` namespaces.

## Admin Panel

The admin panel is built with Inertia, React, TypeScript, Tailwind CSS, and Vite. It uses a shared layout for authenticated pages and page-level components for authentication and dashboard screens.

The initial admin shell provides:

- Login screen.
- Tenant registration screen.
- Authenticated dashboard.
- Shared authenticated layout.
- Shared typed page props for authenticated user and tenant data.

## Data Model Foundation

The current data model includes:

- `tenants`
- `users`
- Sanctum access tokens
- Spatie role and permission tables
- Laravel session, cache, job, and failed job tables

Tenant identity is represented by the `tenants` table. Users reference tenants through `tenant_id`. Roles are scoped to tenants through Spatie Permission team support.
