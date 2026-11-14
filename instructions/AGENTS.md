# AGENTS.md

Core project rules for all AI coding agents (Claude Code, Gemini CLI, Antigravity, Cursor, etc.).
This file is the **source of truth**. `CLAUDE.md` and `GEMINI.md` only add tool-specific notes.

## Project Summary

Multi-tenant SaaS for tutoring centers (LBB). One app serves many centers, and each center's data is isolated.
The full roadmap is in `README.md`. UI design guidelines are in `DESIGN.md`.

## Stack

- Laravel (PHP) backend
- Inertia.js + React/Vue for student/parent/tutor portals and public pages
- Filament (Livewire) for the super admin and center admin panels
- PostgreSQL on Supabase, used as a plain database (Supabase auth and storage are NOT used)
- Tailwind CSS

## Common Commands

```bash
composer install && npm install
php artisan migrate
php artisan serve
npm run dev
php artisan test          # must pass before committing
./vendor/bin/pint         # format PHP code
```

## Multi-Tenant Rules (MOST IMPORTANT)

1. Every tenant-owned table MUST have a `tenant_id` column (foreign key to `tenants`, indexed).
2. Every tenant-owned model MUST use the `BelongsToTenant` trait, which applies the global scope and fills `tenant_id` automatically on create.
3. Never query tenant data with `withoutGlobalScopes()` except in a super admin context, and always add a comment explaining why.
4. The tenant is resolved from the subdomain (or custom domain) in middleware. Never trust a `tenant_id` from user input or the request.
5. Unique constraints must include `tenant_id` (e.g. unique `(tenant_id, email)`).
6. Every new feature MUST have a test proving tenant A cannot read or modify tenant B's data.
7. Uploaded files are stored under paths prefixed with `tenants/{tenant_id}/`.

## Code Conventions

- Follow Laravel conventions: Eloquent, Form Requests for validation, Policies for authorization, API Resources/DTOs for output.
- Keep controllers thin. Business logic goes in `app/Actions` or `app/Services`.
- Table and column names: snake_case, English. Code, comments, and commit messages: English.
- User-facing UI copy is in Indonesian. Put it in Laravel lang files, not hardcoded in components.
- No raw queries with user input. Use the query builder/Eloquent with bindings.
- Avoid N+1 queries: use eager loading.
- Store money as integers (rupiah), never floats.
- Store timestamps in UTC, display in `Asia/Jakarta` by default (configurable per tenant).
- Use PHP enums for statuses (e.g. applicant status, invoice status).

## Database (Supabase Postgres)

- Connect through the Supabase pooler. If using the pooler in transaction mode, set `PDO::ATTR_EMULATE_PREPARES => true` in the connection config.
- Run migrations over a direct (non-pooler) connection when needed.
- All schema changes go through migrations. Never edit tables manually in the Supabase dashboard.
- Dev seeders must never run in production.

## Security

- Never commit `.env`, API keys, or credentials.
- Passwords are hashed (Laravel default). Never store card data.
- Authorize via Policies and per-tenant role permissions. Do not rely only on hiding buttons in the UI.
- Validate all input on the server. Restrict upload size and file types.
- Rate limit public endpoints (registration, login).
- Student data (especially minors) is sensitive. Never log personal data.

## Agent Workflow

1. Read `README.md` (current phase) and `DESIGN.md` before working on any UI feature.
2. Work on ONE small feature per task. No large refactors beyond what was requested.
3. Order: migration, model + tenant trait, policy, logic, UI (Filament/Inertia), tests.
4. Run `php artisan test` and `./vendor/bin/pint` before declaring a task done.
5. If a business rule is ambiguous, ask instead of guessing.
6. Do not add new dependencies without stating why.
7. Make small commits with clear messages (Conventional Commits: `feat:`, `fix:`, `test:`, `refactor:`).

## Never Do

- Remove or loosen the tenant global scope.
- Run `migrate:fresh` or other destructive commands against a non-local database.
- Change files outside the scope of the task without saying so.
- Store secrets in code or in the repo.
