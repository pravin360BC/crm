# Hostinger deployment readiness

**Target:** `crm.360brandclub.com`, using the existing Phase 2 application and its lockfile.

**Status: blocked by the reported CLI runtime.** Composer 2.9.8 is available. The reported executable, `/opt/alt/php82/usr/bin/php`, runs PHP 8.2.35. Laravel 13 and this project's production lockfile require PHP 8.3 or newer. The domain's web PHP version and hosting plan are still unverified. [Laravel server requirements](https://laravel.com/framework/docs/13.x/deployment)

Select or confirm a supported PHP version for the target domain and verify that SSH, Composer, cron and any queue worker use a compatible CLI binary. Hostinger states that hPanel PHP changes can affect the other sites on the plan. Check the scope of any switch before changing settings. [Hostinger PHP configuration](https://www.hostinger.com/support/1575755-how-to-change-the-php-version-of-your-hostinger-hosting-plan/)

After the runtime and the account checks below pass, deploy the current source and lockfile with the built frontend assets. From the release directory, run Composer under the verified PHP binary with `install --no-dev --optimize-autoloader`, then `check-platform-reqs --no-dev`. Configure the deployment environment and preserve its application key across releases. Confirm the target database and take a backup before running `php artisan migrate --force` under the same PHP binary. Verify HTTPS, assets, login and tenant switching before making the release available.

| Area | Account acceptance check |
| --- | --- |
| PHP | Record the PHP version and patch/build on the target domain in hPanel and on SSH. Run `composer check-platform-reqs --no-dev` against the deployed lockfile. Confirm PDO MySQL, OpenSSL, mbstring, XML/DOM, ctype, fileinfo, filter, session and tokenizer. Verify Argon2id is available and check CLI process controls required by the worker setup. |
| Database | Record `SELECT VERSION()` and the InnoDB engine and charset. Verify connection policy and TLS for remote databases. Use dedicated identities and schemas for production, staging and migrations. Run the migration and tenant-isolation checks against the exact engine and version. |
| Scheduler | Confirm the account permits an Artisan cron entry every minute with the same CLI PHP build used for deployment. Confirm successful output and timestamp in UTC. |
| Queue | Confirm a supported supervised worker can restart after deployment and failures. Record timeouts and retry settings. If considering cron-driven bounded queue runs for a pilot, measure lag and verify overlap protection before accepting. |
| Document root | Serve only Laravel's `public/` for the target domain. Check `/up`, assets, Laravel routes, and denial of access to configuration and private files. |
| Private storage | Verify private writable storage outside the public directory, quota, permissions, and backup coverage. Keep future downloads behind application authorization. |
| HTTPS | Confirm valid certificate, renewal, HTTP-to-HTTPS redirects, correct scheme through any proxy, and secure session cookies. |
| Backups | Verify account schedule, retention and coverage for files and database. Restore a copy into staging and record recovery time and last restorable timestamp. |

Hostinger documents MariaDB across its Web and Cloud plans. A MySQL 8.4 verification run does not establish compatibility with that deployment engine. If the selected plan uses MariaDB, confirm the exact version and run the same checks on that engine before approving it. If the deployment must run MySQL, verify a VPS or an approved external MySQL service. [Hostinger database documentation](https://www.hostinger.com/support/1583226-which-database-management-system-is-used-at-hostinger/)

Hostinger documents hPanel PHP Info and cron configuration, and says Web and Cloud plans have weekly backups with daily backups on Business or higher plans. These published capabilities do not establish the target account's configuration. [PHP Info](https://www.hostinger.com/support/5920192-how-to-check-the-php-version-in-hostinger/) · [Cron](https://www.hostinger.com/support/1583465-how-to-set-up-a-cron-job-at-hostinger/) · [Backups](https://www.hostinger.com/support/5981435-how-to-download-backups-at-hostinger/)
