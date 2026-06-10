# Multi-Tenancy

MarketHub uses a single database multi-tenancy model. Tenant context is represented by `tenant_id` on tenant-scoped records, allowing all tenants to share the same schema while preserving data ownership boundaries.

## Tenant Model

The tenant model represents a customer workspace. It contains:

- Name
- Slug
- Status
- Billing email
- Timezone

Tenant status is modeled with `TenantStatus`, which provides stable lifecycle values for active, suspended, and archived tenants.

## User Ownership

Users belong to a tenant through `users.tenant_id`. Authentication is handled by Laravel’s web guard for the admin panel and Sanctum for API access.

Tenant-aware user data is shared with Inertia pages through request middleware. This gives the frontend a consistent authenticated user and tenant payload without duplicating lookup logic in individual controllers.

## Permission Scope

Spatie Permission is configured with team support, using `tenant_id` as the team foreign key. During each authenticated web and API request, middleware sets the active permission team to the authenticated user’s tenant.

This allows role names such as `owner`, `admin`, or `operator` to be scoped per tenant while keeping global permission names stable.

## Data Isolation Pattern

Tenant-scoped models should include a `tenant_id` foreign key and apply tenant-aware query constraints at the appropriate application boundary. The foundation keeps tenant identity centralized so future marketplace data can follow the same ownership model.

Recommended tenant-scoped entities include marketplace accounts, listings, imported orders, shipment records, invoices, returns, and synchronization records.

## Operational Considerations

Jobs, API actions, and admin workflows should carry tenant context explicitly. Long-running processes should receive the tenant identifier as part of their payload and resolve tenant-scoped records through that context.
