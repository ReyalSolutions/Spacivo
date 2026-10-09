# Validation report

Date: 2026-10-09, Asia/Singapore. Runtime: user-approved PHP 7.4.33; local Composer 2.10.3.

## Latest verified results

- 197 active PHP files linted, zero failures.
- 29 foundation/mail assertions passed: environment handling, namespaced/legacy autoload, CSRF including malformed arrays, registration role/password policy, route restrictions, PHPMailer MIME/link/expiration, recipient isolation, sanitized failures, production outbox rejection, secure SMTP configuration and malformed/incomplete settings.
- 185 database/HTTP assertions passed: migrations, baseline adoption/preservation/drift, restart recovery/checksums/locks, portal boots, role revocation, public registration, opaque passwords, logout/CSRF, login lockout/expiration, organization membership/isolation/verification, password recovery/session invalidation/token replay/expiry/request limits, and legacy room/ledger/statement/checkout account boundaries.
- Database tests created uniquely named disposable *_test databases and removed only the databases they created. No fixture users were added to the existing application database.
- Composer install/autoload/lockfile and strict manifest validation pass.
- All health checks pass: approved PHP version, mysqli/JSON, writable logs, environment configuration and read-only database connectivity.
- Migrations 001â€“006 report applied locally following explicit user approval.
- Apache login/recovery HTTP 200; unauthenticated organization API 401; GET logout 405; diagnostics/private source 403; unauthenticated admin page 302.

## Remaining verification

GitHub Actions runs [37892096783](https://github.com/ReyalSolutions/Spacivo/actions/runs/37892096783) and [37892501297](https://github.com/ReyalSolutions/Spacivo/actions/runs/37892501297) passed for c73f304 and e043b77 on PHP 7.4 and MariaDB 10.4. PHPMailer implementation commit 486df96 passed [hosted run 37892943844](https://github.com/ReyalSolutions/Spacivo/actions/runs/37892943844), including mail assertions and database/HTTP flows. Gmail TLS/authentication and the user-approved test email were verified; the user confirmed receipt. PHPMailer tests generate actual MIME with a test transport; they do not contact an external SMTP server. Development recovery defaults to a private local outbox. Existing /tenant absolute assets/admin links require deployment-layout migration before a production release. Later phase requirements including universal inventory, concurrent bookings, real payment reconciliation and mobile flows are not yet verified.

Gmail verification: TLS connection and authentication passed. Gmail accepted one user-approved email titled Spacivo email delivery test using the Spacivo sender display name. The user confirmed receipt. No application account or password was changed. Credentials and sender address are excluded from source records.

Category task: versioned public/admin API, page rendering, CSRF, create/PATCH/deactivation, inactive visibility, administrator authorization, normalized capabilities, invalid dependencies, duplicate rollback and optimistic version conflicts pass on disposable databases. Migration 004 is applied/enabled following explicit approval. Hosted category run 37894183813 passed; real Apache public categories return 200.

Inventory task: property/unit service and REST/UI assertions pass for scoped CRUD, read/manage grants, invalid coordinates/timezones, stale writes, verification/approval publication gates, reviewer identity/time, category/owner/organization suspension, platform listing suspension/restoration and archival. Inventory hosted run 37895621406 passed; migration 005 is applied and inventory is enabled following explicit approval.

Metadata task: real multipart raster reencoding strips embedded executable content; SVG rejection, CSRF, private previews, public published photos, suspension revocation, failed-upload file cleanup, scoped amenity replacement/rollback, photo removal and composite media foreign keys pass. 196 PHP files, 29 foundation/mail and 183 database/HTTP assertions pass. Migration 006 applied and metadata enabled with explicit approval; hosted run 37896990758 passed for 35bf315. Public marketplace publication/suspension HTTP assertions pass; final visibility commit hosted verification follows.


## 2026-10-09 — Shared management interface

Shared management UI: 190 database/HTTP assertions passed, including both portals using the light assets, owner subscriptions using the shared sidebar, administrator-only menus absent for owners, and direct owner access to physical admin pages denied. Foundation/mail assertions: 29 passed. Browser visual check could not run: CUA automation helper failed during initialization. No live database rows or credentials changed by this task.

Hosted shared-UI source gate passed: run [37899186218](https://github.com/ReyalSolutions/Spacivo/actions/runs/37899186218), commit a85407b. Complete project lint: 197 files, zero failures; 29 foundation assertions and 190 database/HTTP assertions passed. Shared CSS/scripts return HTTP 200 on local Apache. Retained business-page content matches the original source apart from layout references. Visual browser verification remains outstanding because the automation helper could not initialize.


## 2026-10-09 — Paid owner subscription access

Subscription redirect repair: 215 disposable database/HTTP assertions and 29 foundation/mail assertions passed; 198 PHP files linted with zero failures. Includes paid-expired renewal opening dashboard/houses/rooms/bookings/tenants/payments; overdue renewal notice in shared light layout; calendar month-end/leap-year boundaries; cancelled/pending/foreign/wrong-plan/wrong-cycle payments blocked; same-plan authorization; listing expiry and limits agreement. A read-only live check confirms affected renewed entitlement active through 2026-11-09. Hosted gate follows publication.

Hosted paid-owner repair gate passed: [run 37900510660](https://github.com/ReyalSolutions/Spacivo/actions/runs/37900510660), source commit a6627ad. All subscription repair acceptance checks are complete; browser visual automation remains unavailable as previously recorded.


## 2026-10-09 — Header spacing repair

Scoped public dashboard nav-item rules to .bottom-nav, preventing its 18% width and small typography from compressing management controls. Shared header now has nonshrinking items, a single-line clock hidden below desktop width, normal account line-height, fixed avatar sizing and truncated long names. PHP header lint and 215 database/HTTP checks passed. Browser visual automation remains unavailable as previously recorded.


## 2026-10-09 — Unified reference management theme

User selected a new neutral dashboard reference for all admin and owner pages. Added one management-theme stylesheet loaded by their common asset partial: slim gray sidebar, white header/cards, subtle borders, black primary actions, blue accents, compact tables/forms, consistent dialogs and responsive shell. Dashboard-specific welcome banner now uses the same theme. Existing role/organization guards remain unchanged. 215 database/HTTP checks pass. Visual browser automation remains unavailable as previously recorded; no pixel-perfect visual acceptance is claimed.
