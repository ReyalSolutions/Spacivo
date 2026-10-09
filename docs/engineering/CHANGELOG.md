# Engineering changelog

## 2026-10-09: Phase 1 foundation

Audited architecture/MVC/runtime/database metadata. Initialized Git without commits/staging. Added Composer manifest, environment template, shared bootstrap, namespaced autoload compatibility, environment parsing, secure session defaults and private exception logs. Centralized DB entry points and strict MySQLi reporting.

Hardened dispatch while retaining legacy actions. Added Apache protections for private source/diagnostics and executable uploads. Added migration infrastructure, health/schema inspection, lint/foundation tests and PHP 8.2/MySQL CI configuration. Captured metadata only; no existing records/schema changed.

Created eight required engineering-state files and sequential task queue. Runtime/Composer/CI and migration baseline remain unresolved gates; later phases pending.

Final verification: 116 PHP files linted with zero failures; 16 assertions passed. Ignored local environment initialized; read-only database health passes. Login and marketplace HTTP 200; private source/diagnostics 403; protected auth helper 404; malformed array route 400. Migration CLI correctly refuses PHP 7.4. No migrations applied.

## 2026-10-09: Continue on existing PHP and Phase 2 implementation

User approved PHP 7.4.33. Updated architecture/examples, Composer/runtime checks/CI; installed verified Composer 2.10.3 and generated lock/autoload. Captured and reconciled the 19-table baseline; migration tests prove fresh installation, adoption/preservation, drift rejection and partial-failure recovery.

User explicitly approved baseline application and then both Phase 2 migrations/feature activation. All three are applied; existing application rows were preserved. Added Identity/Organizations services, repositories/policies, local recovery adapter, owner/staff/admin organization page and navigation.

Fixed registration role escalation, opaque password handling, strict-schema user image defaults, CSRF logout/payment writes, stale role authorization and reset-session invalidation. Added login lockouts and negative account/organization tests. Repaired inherited Apache denial affecting legacy admin pages.

Final local verification: 142 PHP files linted; 21 foundation and 90 database/HTTP assertions pass; Composer strict validation and all health checks pass. Real email and hosted CI remain unverified. User supplied ReyalSolutions/Spacivo as the remote; repository is initially empty/public.

## 2026-10-09: Approved publication and hosted verification

Published reviewed source after explicit user approval, excluding local secrets, SQL dumps, uploaded media and diagnostic phpinfo. GitHub Actions run 37892096783 passed for c73f304 with PHP 7.4 and MariaDB 10.4. Expanded lint to include the root entry point and legacy admin application: 168 files pass. Include the private storage Apache denial in source control. Real password-recovery email delivery awaits provider configuration; Phase 2 acceptance and subsequent phases remain open.

## 2026-10-09: PHPMailer recovery delivery

User selected PHPMailer. Installed Composer-locked PHPMailer 7.1.1; added SMTP delivery with TLS, environment configuration, sender validation, recipient isolation, disabled debug and sanitized failures. Recovery supports configured SMTP outside local/testing; local outbox remains the development default. MIME and configuration tests pass: 171 PHP files linted, 29 foundation/mail and 90 database/HTTP assertions. Expanded foundation run 37892501297 passed for e043b77. Private SMTP settings and actual inbox delivery remain external gates.

PHPMailer implementation commit 486df96 passed hosted run 37892943844 on PHP 7.4/MariaDB 10.4. Remaining Phase 2 gate: private SMTP configuration and actual inbox delivery.

Configured the user-selected Gmail SMTP in ignored .env. Verified TLS/authentication and sent one explicitly approved Spacivo delivery test, accepted by Gmail. No application account changes. The user confirmed receipt.

Phase 3 category task: normalized tables, validated configurable capabilities, administrator UI, versioned public/admin API and optimistic writes. 180 PHP files, 29 foundation/mail and 114 database/HTTP assertions pass. Migration 004 remains pending in the existing database; no real category records were added.

Category migration 004 applied/enabled with explicit approval; hosted run 37894183813 passed. Property/unit task implemented/tested: scoped CRUD, REST/UI, staff grants, reviewer records and publication/moderation lifecycle. 189 PHP files linted, 29 foundation/mail and 154 database/HTTP assertions pass. Migration 005 remains pending; photos/amenities and final Phase 3 acceptance remain open.

Inventory migration 005 applied/enabled with explicit approval; hosted run 37895621406 passed. Photos/amenities implemented/tested with private storage, raster reencoding, scoped access, composite foreign keys and publication/suspension image controls. 196 PHP files linted, 29 foundation/mail and 183 database/HTTP assertions pass. Migration 006 and metadata activation remain pending.

Metadata hosted run 37896990758 passed. Applied migration 006 and enabled photos/amenities with explicit approval. Added public listing page and navigation; HTTP tests verify approved listings appear and suspended listings disappear. 197 PHP files, 29 foundation/mail and 185 database/HTTP assertions pass. Mapbox token is unavailable for the next phase.


## 2026-10-09 — Shared management interface

Consolidated owner/admin layouts using the existing light design; migrated management views and new organization/category/inventory screens; fixed asset/account URLs and role-specific menus; removed app/views/layouts/admin_header.php, app/views/layouts/admin_footer.php and public/assets/css/admin.css after eliminating their references.
