# Phase 2 foundation

## Identity schema

- Global users authenticate with email and password. Tenant access comes from an active tenant membership. A tenant selector in the session is resolved against that user's memberships on every workspace request.
- A tenant has its own roles and role grants. Permissions use a global key catalog. The membership-to-role and role-to-permission joins carry tenant_id. Composite foreign keys constrain both ends of tenant-owned joins to the same tenant.
- The initial tenant permission keys are workspace.view and workspace.manage. The owner and company admin get both. The sales roles receive workspace.view until their domain permissions exist. Platform administration requires a separate grant model in its own phase.
- First-owner provisioning assigns the owner role within the tenant creation transaction. Run provisioning through the trusted CLI with an existing user ID. The account bootstrap command creates a new login and rejects a reused email.
- Role and membership mutation screens are outside this initial shell. Any future mutation service must serialize owner changes per tenant and prevent removal, suspension or demotion of the last active owner.

## Environment

Default to MySQL 8 with InnoDB and utf8mb4. Use a dedicated database, a runtime user scoped to that database with the DML rights required by the application, and a separate migration identity with schema change privileges. Use distinct databases and accounts for development, test and production. For a remote database, configure TLS with the server CA and verify the server certificate. Keep application secrets and credentials outside source control. Keep production APP_DEBUG=false and SESSION_SECURE_COOKIE=true behind HTTPS. Set Laravel's document root to public/.

PHP has pdo_mysql. No local MySQL CLI or listener was found on port 3306, and PDO loopback from the sandbox was denied. Composer was absent from PATH and the existing cache contained no Laravel framework package. Sandboxed requests to getcomposer.org, repo.packagist.org and github.com failed with curl error 6. Approved network checks returned HTTP 200, and Composer 2.11.2 was installed temporarily with the official SHA-384 check. Composer resolved the official scaffold and dependencies from upstream; no mirror was needed.

## Setup and verification checklist

1. Install PHP compatible with composer.lock, Composer, Node compatible with package-lock.json, and MySQL. Copy .env.example to .env and set DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD and APP_URL. Configure HTTPS and the secure cookie for deployed environments.
2. Run composer install, npm ci, npm run build and php artisan key:generate. Migrate the dedicated database as the migration identity with php artisan migrate.
3. Create a login with php artisan crm:create-user. Use its printed user ID with php artisan crm:create-tenant. Sign in and select the workspace.
4. Run php artisan test. The default tests use in-memory SQLite. Repeat against an empty dedicated MySQL test database using a separate PHPUnit configuration with MySQL environment values and no SQLite defaults. Verify migrations in both directions and composite foreign-key enforcement on MySQL.
5. Check desktop and mobile navigation, keyboard/focus order, reduced motion and contrast against the Motion Edition PDF. The owner artwork is at public/brand/360bc-logo.png. The supplied red and white version is used on a near-black surface.
6. Confirm host plan and document-root access, HTTPS, private storage, scheduler, queue process supervision, backup restore and rollback before launch. Keep CRM modules behind completion of the Phase 2 isolation and authorization checks.

## Verification results

- Laravel Framework 13.40.0 boots on PHP 8.5.10. Composer validated the manifest and the installed non-development platform requirements.
- Vite production assets built successfully. Manrope and DM Sans are served from the bundled Fontsource packages. GSAP and ScrollTrigger load on the overview only when the user allows motion. Alpine opens the native mobile navigation dialog. The native dialog provides keyboard focus containment and Escape handling.
- SQLite migration, rollback and re-migration succeeded on a disposable file database. The PHPUnit feature suite passed all 7 tests and 73 assertions, including both console commands, Argon2id hashing, login and logout, throttling, workspace switching, stale membership and grant revocation, cross-tenant foreign keys, and transaction rollback.
- See the [MySQL verification](mysql-verification.md), [browser verification](browser-verification.md), and [hosting readiness](hosting-readiness.md) reports for follow-up checks. Role management UI, account recovery and platform administration remain outside the implemented flows.
