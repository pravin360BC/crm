# MySQL verification

The normal local port 3306 had no listener. Verification used an isolated Oracle MySQL Community Server 8.4.13 with TCP disabled and a private Unix socket. The Oracle archive signature verified against the build key published in the MySQL 8.4 manual. A schema-scoped application identity connected to separate utf8mb4 development and test databases. No production database was used. [MySQL signing documentation](https://dev.mysql.com/doc/refman/8.4/en/checking-gpg-signature.html)

## Results

- All four Laravel migrations applied, rolled back and reapplied on the dedicated development database.
- **22 metadata and transaction assertions passed.** MySQL reported InnoDB for all six tenancy tables. The probe checked eight index definitions and four foreign-key definitions, including column order and both composite role-grant references.
- A database trigger rejected the final owner role insert. The provisioning transaction left all six tenancy tables empty. Provisioning then succeeded once the test trigger was removed.
- The dedicated MySQL test database passed **7 Laravel feature tests with 73 assertions**. The suite exercised independent tenants, foreign workspace selectors, a forged tenant_id request parameter, a foreign workspace ID in the session, cross-tenant grant rejection, membership and grant revocation, and rollback when owner membership creation failed. The test schema's role-grant table was confirmed as InnoDB.

## MySQL-specific notes

The copy of the Oracle signing key served by the RPM repository was expired. The key from the official MySQL manual verified the archive signature. The scoped database identity could not create the diagnostic trigger while binary logging was enabled (MySQL error 1419). The disposable server administrator created the trigger for the probe, while the application identity performed the provisioning. The probe required no production privilege change or permanent trigger. No application migration change was needed.

To reproduce, set `DB_CONNECTION=mysql`, credentials, and `DB_SOCKET` for a local socket or `DB_HOST` and `DB_PORT` for TCP. Use a disposable development schema for the migration/rollback cycle. Set `DB_DATABASE` to a separate disposable test schema before running `php artisan test`. Never aim the test suite at a database containing application data.
