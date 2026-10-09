# Spacivo development

`architecture.md` is the authoritative development process. The existing StayHub MVC application is migrated incrementally; the unrelated nested `reyal_solutions/` project is excluded.

Active runtime: PHP 7.4.33, explicitly approved by the project owner. Use MySQL/MariaDB with MySQLi, Composer 2 and Git. Configure production to expose `public/` only; migrate legacy absolute `/tenant/` and `/tenant/admin/` paths before a production release.

1. Copy `.env.example` to `.env` and configure your local database. Keep secrets out of Git.
2. Run `composer install`, or use `php tools/composer.phar install` with the workspace-local Composer installation.
3. Run `composer check`, `php tests/database.php`, and `php scripts/health-check.php --database`.
4. Existing local Apache mount: `http://localhost/tenant/`. A standalone `php -S localhost:8000 -t public` serves the front controller but legacy assets/admin URLs still require migration.

The legacy `?url=controller/method` routes remain supported. New namespaced modules use `App\`; the existing lowercase `app/core/` directory remains mapped as `App\Core\` for Linux compatibility.

Use `php scripts/migrate.php` for read-only migration status; `--apply` changes the configured nonproduction database. The baseline contains the verified 19-table structure, never user records. Matching existing schema is adopted without recreating tables; different definitions are rejected. All three migrations were tested and explicitly approved/applied locally. Production changes require an approved release.

Run `php tests/run.php --integration` only against a dedicated disposable database with a name ending in `_test`. It creates and removes its own migration tables. GitHub Actions configures this isolated database automatically.

Organization workspace: `http://localhost/tenant/?url=organization/index`. Owners create organizations and assign existing accounts as staff; administrators verify/reject organizations. Every organization query enforces membership/permissions. Enable only after migration with `ORGANIZATIONS_ENABLED=true`.

Password recovery: `http://localhost/tenant/?url=auth/forgot_password`. Enable after migration with `PASSWORD_RECOVERY_ENABLED=true`. Local/testing delivery defaults to protected `storage/private/password-reset-outbox/`; it does not send email. The user-selected PHPMailer SMTP adapter is installed through Composer. Set `MAIL_DRIVER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_ENCRYPTION=tls` (STARTTLS) or `ssl` (implicit TLS), `MAIL_FROM_ADDRESS`, and your provider's `MAIL_USERNAME`/`MAIL_PASSWORD` in the ignored `.env`. TLS certificate verification stays enabled. Production refuses the local outbox and incomplete SMTP configuration. Configuration checks do not prove server connectivity or inbox delivery; verify delivery with your own account before enabling real recovery. Tokens expire after 30 minutes, are stored hashed, consumed once, and invalidate old sessions. Five failed logins lock an account for 15 minutes.

The repository excludes `.env`, dependency/cache files, SQL dumps, uploaded media, diagnostic scripts and the independent nested project. Progress, phase gates, next tasks and blockers live under `docs/engineering/`.
