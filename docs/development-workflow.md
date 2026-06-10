# Development Workflow

MarketHub development follows a testable, typed, and domain-oriented workflow. Changes should keep business concerns inside domain boundaries and use Laravel’s framework conventions for HTTP, validation, authentication, authorization, queues, and database access.

## Setup

Install dependencies:

```bash
composer install
npm install
```

Prepare the application environment:

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Run the application:

```bash
php artisan serve
npm run dev
```

## Backend Workflow

Backend changes should be covered by feature or unit tests based on behavior and risk. HTTP behavior belongs in feature tests. Domain-level value objects, enums, and isolated services belong in unit tests.

Run the backend test suite:

```bash
php artisan test
```

Format PHP code:

```bash
vendor/bin/pint
```

## Frontend Workflow

Frontend code is written with React and TypeScript. Shared page props are defined in `resources/js/types.ts`, and authenticated pages use the admin layout.

Run TypeScript validation:

```bash
npm run typecheck
```

Create a production frontend build:

```bash
npm run build
```

## Database Workflow

Schema changes should be introduced through migrations. Tenant-owned tables should include a `tenant_id` foreign key and indexes that match expected access patterns.

Seeders should provide stable baseline data for local development and automated verification, including tenant, role, permission, and user foundations.

## Quality Checklist

Before merging application changes, run:

```bash
php artisan test
php artisan route:list
npm run typecheck
npm run build
```
