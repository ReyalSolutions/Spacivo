# Validation report

Date: 2026-10-09, Asia/Singapore. Runtime: user-approved PHP 7.4.33; local Composer 2.10.3.

## Latest verified results

- 197 active PHP files linted, zero failures.
- 29 foundation/mail assertions passed: environment handling, namespaced/legacy autoload, CSRF including malformed arrays, registration role/password policy, route restrictions, PHPMailer MIME/link/expiration, recipient isolation, sanitized failures, production outbox rejection, secure SMTP configuration and malformed/incomplete settings.
- 185 database/HTTP assertions passed: migrations, baseline adoption/preservation/drift, restart recovery/checksums/locks, portal boots, role revocation, public registration, opaque passwords, logout/CSRF, login lockout/expiration, organization membership/isolation/verification, password recovery/session invalidation/token replay/expiry/request limits, and legacy room/ledger/statement/checkout account boundaries.
- Database tests created uniquely named disposable *_test databases and removed only the databases they created. No fixture users were added to the existing application database.
- Composer install/autoload/lockfile and strict manifest validation pass.
- All health checks pass: approved PHP version, mysqli/JSON, writable logs, environment configuration and read-only database connectivity.
- Migrations 001–006 report applied locally following explicit user approval.
- Apache login/recovery HTTP 200; unauthenticated organization API 401; GET logout 405; diagnostics/private source 403; unauthenticated admin page 302.

## Remaining verification

GitHub Actions runs [37892096783](https://github.com/ReyalSolutions/Spacivo/actions/runs/37892096783) and [37892501297](https://github.com/ReyalSolutions/Spacivo/actions/runs/37892501297) passed for c73f304 and e043b77 on PHP 7.4 and MariaDB 10.4. PHPMailer implementation commit 486df96 passed [hosted run 37892943844](https://github.com/ReyalSolutions/Spacivo/actions/runs/37892943844), including mail assertions and database/HTTP flows. Gmail TLS/authentication and the user-approved test email were verified; the user confirmed receipt. PHPMailer tests generate actual MIME with a test transport; they do not contact an external SMTP server. Development recovery defaults to a private local outbox. Existing /tenant absolute assets/admin links require deployment-layout migration before a production release. Later phase requirements including universal inventory, concurrent bookings, real payment reconciliation and mobile flows are not yet verified.

Gmail verification: TLS connection and authentication passed. Gmail accepted one user-approved email titled Spacivo email delivery test using the Spacivo sender display name. The user confirmed receipt. No application account or password was changed. Credentials and sender address are excluded from source records.

Category task: versioned public/admin API, page rendering, CSRF, create/PATCH/deactivation, inactive visibility, administrator authorization, normalized capabilities, invalid dependencies, duplicate rollback and optimistic version conflicts pass on disposable databases. Migration 004 is applied/enabled following explicit approval. Hosted category run 37894183813 passed; real Apache public categories return 200.

Inventory task: property/unit service and REST/UI assertions pass for scoped CRUD, read/manage grants, invalid coordinates/timezones, stale writes, verification/approval publication gates, reviewer identity/time, category/owner/organization suspension, platform listing suspension/restoration and archival. Inventory hosted run 37895621406 passed; migration 005 is applied and inventory is enabled following explicit approval.

Metadata task: real multipart raster reencoding strips embedded executable content; SVG rejection, CSRF, private previews, public published photos, suspension revocation, failed-upload file cleanup, scoped amenity replacement/rollback, photo removal and composite media foreign keys pass. 196 PHP files, 29 foundation/mail and 183 database/HTTP assertions pass. Migration 006 applied and metadata enabled with explicit approval; hosted run 37896990758 passed for 35bf315. Public marketplace publication/suspension HTTP assertions pass; final visibility commit hosted verification follows.
