# Marketplace Provider Architecture

MarketHub defines marketplace integration boundaries through contracts and enums. Provider modules can be introduced behind stable interfaces while marketplace-specific API details remain isolated from tenant, authorization, and admin panel concerns.

## Provider Contract

The provider contract is defined by `MarketplaceProvider`. A provider exposes:

- A marketplace code.
- A list of supported capabilities.

This creates a consistent application-level shape for provider modules without coupling the core platform to a specific marketplace API.

## Marketplace Codes

`MarketplaceCode` defines supported marketplace identifiers. Codes are stable values used by provider configuration, tenant marketplace accounts, job payloads, logs, and operational records.

## Capabilities

`MarketplaceCapability` describes provider-level functionality. Capabilities are intentionally granular so the platform can reason about what a provider supports before invoking channel-specific workflows.

Current capability categories include:

- Catalog read
- Listing write
- Order read
- Shipment write
- Invoice write
- Return read

## Provider Module Shape

A provider module should keep marketplace-specific concerns within its own implementation boundary. Typical provider module components include:

- HTTP client configuration.
- Authentication strategy.
- Request and response data objects.
- Provider actions for supported capabilities.
- Error normalization.
- Rate limit handling.
- Integration-specific logging context.

Application workflows should depend on contracts and capability checks rather than concrete provider classes.

## Future Extension Points

Provider implementations can be registered through Laravel’s service container and selected by marketplace code. Tenant marketplace accounts can store credentials and configuration separately from provider classes, allowing multiple tenants to use the same provider implementation with isolated account settings.
