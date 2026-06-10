# MarketHub

MarketHub is a multi-tenant SaaS platform for marketplace integration operations. It provides an API-first backend and an Inertia React admin panel for managing tenants, users, roles, permissions, and marketplace integration foundations.

## Overview

MarketHub is designed for businesses that need a centralized integration layer between internal commerce operations and external marketplace channels. The platform separates tenant context, identity, authorization, and marketplace provider boundaries so integration workflows can evolve without coupling core application logic to a specific channel.

The current foundation includes the application shell, authentication flow, tenant model, role and permission baseline, API authentication support, and domain-oriented source structure.

## Core Features

- Tenant-aware user model with single database tenancy.
- Web authentication for the admin panel.
- API authentication support with Laravel Sanctum.
- Tenant-scoped roles and permissions with Spatie Permission.
- Inertia React admin dashboard shell.
- Domain-based backend organization for marketplace operations.
- Typed frontend foundation with React and TypeScript.

## Technology Stack

- Laravel 13
- PHP 8.4
- MySQL
- Redis queue
- Laravel Sanctum
- Spatie Permission
- Inertia
- React
- TypeScript
- Tailwind CSS
- Vite

## Architecture

MarketHub follows a modular Laravel architecture organized around business domains. HTTP controllers, middleware, and frontend pages provide the application interface, while domain folders define the boundaries for tenant management, marketplace integration, catalog, listings, orders, shipments, invoices, returns, and synchronization.

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

The application entry points are split between the Inertia-powered admin panel and authenticated API routes. Shared authentication and tenant context are resolved through middleware.

## Multi-Tenant Structure

MarketHub uses a single database multi-tenancy model. Tenant ownership is represented through `tenant_id` relationships on tenant-scoped records. Users belong to a tenant, and tenant context is used when resolving role and permission assignments.

The tenant model stores the workspace name, slug, lifecycle status, billing email, and timezone. This structure keeps tenant identity independent from marketplace provider configuration and later operational data.

## Marketplace Integration Architecture

Marketplace integrations are represented through provider contracts and capability enums. This allows provider implementations to be added behind stable interfaces while keeping marketplace-specific behavior isolated from the rest of the platform.

The provider boundary is centered around:

- `MarketplaceProvider`
- `MarketplaceCode`
- `MarketplaceCapability`

This structure supports future provider modules that can declare capabilities such as catalog access, listing updates, order reads, shipment writes, invoice writes, and return reads.

## Local Development

Install backend and frontend dependencies:

```bash
composer install
npm install
```

Create an environment file, generate an application key, and run migrations:

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Run the application services:

```bash
php artisan serve
npm run dev
```

## Testing

Run the backend test suite:

```bash
php artisan test
```

Run frontend type checks and production build:

```bash
npm run typecheck
npm run build
```

## Code Quality

PHP code formatting is managed with Laravel Pint:

```bash
vendor/bin/pint
```

TypeScript strict mode is enabled for the React admin shell. Backend tests cover authentication redirects, tenant registration, dashboard access, API identity response, and stable domain enum values.

## Roadmap

- Tenant administration screens.
- User, role, and permission management screens.
- Marketplace account configuration model.
- Provider-specific marketplace modules.
- Catalog and listing workflows.
- Order, shipment, invoice, return, and synchronization workflows.
- Operational monitoring for queued integration jobs.
