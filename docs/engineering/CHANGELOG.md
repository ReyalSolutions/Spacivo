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

Hosted shared-UI source gate passed: run [37899186218](https://github.com/ReyalSolutions/Spacivo/actions/runs/37899186218), commit a85407b. Complete project lint: 197 files, zero failures; 29 foundation assertions and 190 database/HTTP assertions passed. Shared CSS/scripts return HTTP 200 on local Apache. Retained business-page content matches the original source apart from layout references. Visual browser verification remains outstanding because the automation helper could not initialize.


## 2026-10-09 — Paid owner subscription access

Fixed owner paid-renewal redirects and unchanged-plan activation, removed duplicate subscription expiry calculation from BaseController, synchronized effective subscription listing/limits/details and migrated the remaining renewal notice to the light management shell.


## 2026-10-09 — Header spacing repair

Scoped public dashboard nav-item rules to .bottom-nav, preventing its 18% width and small typography from compressing management controls. Shared header now has nonshrinking items, a single-line clock hidden below desktop width, normal account line-height, fixed avatar sizing and truncated long names. PHP header lint and 215 database/HTTP checks passed. Browser visual automation remains unavailable as previously recorded.


## 2026-10-09 — Unified reference management theme

User selected a new neutral dashboard reference for all admin and owner pages. Added one management-theme stylesheet loaded by their common asset partial: slim gray sidebar, white header/cards, subtle borders, black primary actions, blue accents, compact tables/forms, consistent dialogs and responsive shell. Dashboard-specific welcome banner now uses the same theme. Existing role/organization guards remain unchanged. 215 database/HTTP checks pass. Visual browser automation remains unavailable as previously recorded; no pixel-perfect visual acceptance is claimed.


## 2026-10-09 — Shared listings migration

User explicitly authorized incremental shared admin/owner implementations, with admin seeing all listings and owners seeing only their own. First feature completed: canonical app/views/admin/houses.php serves both roles. Physical admin/houses.php and legacy owner/houses delegate to it; the obsolete owner listing view is removed. Existing owner-scoped write handlers remain as compatibility delegates while canonical admin action URLs reuse them. Each listing create/edit/delete/photo/amenity action checks its exact permission-table slug; moderation requires admin role and approve_houses. Ownership checks remain unchanged; shared visibility does not grant global editing authority. Missing granular owner grants are not automatically broadened. Read-only listing users cannot mutate listings, and revocation applies on the next request. Shared modals preserve editing/upload functionality for authorized owned records. Other feature controllers/views remain separate pending their own migration gates.

Verification: 198 PHP files linted without errors, 29 foundation/mail assertions and 227 disposable database/HTTP assertions passed. Includes all-owner admin data, owner filter tampering, all three shared entry routes, direct write denial, edit permission with foreign property rejection, and permission revocation. No live rows or role grants changed. Hosted gate follows publication. Browser visual automation remains unavailable as previously recorded.

Shared-listings hosted gate passed: [run 37905013391](https://github.com/ReyalSolutions/Spacivo/actions/runs/37905013391), source f555616. Six inline shared-page scripts passed Node syntax parsing. Next consolidation feature: rooms, then bookings and tenant operations, preserving explicit action permissions and data boundaries.

## 2026-10-09 — Action permissions and shared skeleton loading

The canonical role editor manages database-backed action grants. Every declared admin/owner controller action checks its mapped grants and state-changing requests require CSRF. Physical admin pages delegate to verified handlers and reject legacy direct POST processing. Admin listing visibility and owner account boundaries remain intact. Role synchronization validates IDs before replacing grants. The idempotent CLI seeder preserves existing definitions and role grants; five missing booking approval/rejection and plan-payment editing/printing permissions were added locally without automatic grants.

Shared management assets provide skeleton shimmer loading for same-origin jQuery/fetch requests, concurrent-request tracking, failure cleanup, lazy images and reduced-motion support. Existing management spinner markup and SweetAlert loaders use skeleton bars. Photo uploads retain linear progress. Verification: 202 PHP files linted, 29 foundation assertions, 348 disposable database/HTTP assertions, and Node loader success/failure/concurrency checks passed. Hosted verification follows publication. Browser visual verification remains unavailable.

Automatic review declined broad router/global-authentication and transport monkey-patching proposals. Those proposals were not applied. Feature groups were migrated and verified separately; loading uses ordinary AJAX events and a tested fetch wrapper.

Hosted action-permissions/loading gate passed: [run 37908631123](https://github.com/ReyalSolutions/Spacivo/actions/runs/37908631123), source e7efb4f. PHP validation, foundation/integration/database checks and Node loader checks all succeeded. This user-priority implementation loop is complete; resume shared rooms consolidation next.


## 2026-10-09 — Global toast across pages

Notification feedback uses the existing ToastStack engine across admin, owner, tenant, public and authentication views, including role/recovery flash messages and organization/category/inventory saves. Shared headers load the engine before page scripts. Feedback.fire sends notices to ToastStack and preserves interactive confirmations, input prompts and loading dialogs. Post-notice redirects retain display delays; success/error notices close pending loading dialogs. Titles/messages are escaped, server flash values are JSON encoded and toast roles support accessible announcements. Browser visual verification remains unavailable. Local verification: 202 PHP files linted, 29 foundation assertions, 348 database/HTTP assertions, skeleton checks and global-toast safety/confirmation/callback tests passed. Hosted gate pending.


Global-toast hosted gate passed: [run 37909608793](https://github.com/ReyalSolutions/Spacivo/actions/runs/37909608793), source b26529e. All PHP, integration, database, skeleton and toast checks succeeded. User-priority toast implementation loop complete.


## 2026-10-09 — Listing success toast repair

Added omitted global success notifications for listing approval/rejection and photo deletion. Listing form success/error flashes now use ToastStack after DOM readiness and display for five seconds; removed the duplicate management toast container. Both shared headers version the toast script with its modification timestamp to invalidate stale cached engines. Regression checks confirm successful owned-listing update redirects, emits its success toast with versioned assets, and consumes the flash exactly once. Local verification: 351 database/HTTP assertions, 29 foundation assertions, 202 PHP files linted, and global toast tests including moderation notification plus table refresh. Browser visual automation remains unavailable. Hosted gate pending.


Listing-toast hosted gate passed: [run 37910282471](https://github.com/ReyalSolutions/Spacivo/actions/runs/37910282471), source 7cb77b0. PHP, foundation, integration, 351 database/HTTP assertions and global toast regression checks succeeded. Listing notification repair loop complete.


2026-10-09 - Amenities checkbox restoration: normalized selected/catalog IDs before comparison in the shared listing modal. Prepared-query numeric IDs now match text-query string IDs. Focused tests cover both type directions, saved additions/removals and empty selections across reopen; PHP lint passed. Hosted full-suite gate pending. Existing stored amenities and permissions are unchanged.


Amenities checkbox hosted gate passed: [run 37910749284](https://github.com/ReyalSolutions/Spacivo/actions/runs/37910749284), source 6645cd1. Full PHP/integration/database suites and the mixed-ID/reopen modal regression check succeeded. Repair loop complete.


2026-10-09 - Upgrade modal shared design: replaced the dark/purple presentation with the neutral management theme, white header, bordered plan cards, black billing/primary controls and restrained blue recommendation highlight. Styles are scoped to the modal so moving it to document.body retains the theme. Added a bounded scrolling body and accessible title/close/focus controls. Billing prices, limits, selection, permissions and payment functions remain intact. PHP lint and existing toast/amenity tests passed. Browser visual verification remains unavailable; hosted gate pending.


Upgrade modal design hosted gate passed: [run 37911327017](https://github.com/ReyalSolutions/Spacivo/actions/runs/37911327017), source 1244b0e. Full automated CI passed; comparison confirms billing/payment script blocks are unchanged. Visual browser acceptance remains unavailable. Design update loop complete.


2026-10-09 - Upgrade plans moved to admin/upgrade: replaced the plan modal with a shared page section and natural document scrolling. Houses, rooms and subscription upgrade links open the page; subscription/cycle context is retained. The route checks existing subscription-view permissions and rejects foreign owner subscription IDs. Plan payment selection remains available. Removed the unused upgrade modal component. Verification: 203 PHP files linted, 29 foundation assertions and 355 database/HTTP checks passed, including page/billing/payment/ownership regressions. Browser visual verification remains unavailable; hosted gate pending.


Upgrade-page hosted gate passed: [run 37911984985](https://github.com/ReyalSolutions/Spacivo/actions/runs/37911984985), source 4158d80. PHP, foundation/integration, 355 database/HTTP checks and all JavaScript regressions passed. Upgrade plans use document scrolling and preserve payment/subscription scope. Migration loop complete.


2026-10-09 - Upgrade entry confirmation and focused page: shared upgrade actions display an Upgrade required dialog with View plans/Not now before navigation. Confirmation retains subscription/yearly context; cancellation leaves the current page intact. admin/upgrade now renders a standalone layout with no dashboard sidebar/header, while preserving shared styles, toast, CSRF and payment selection. Verification: 356 database/HTTP assertions, upgrade prompt confirmation/cancellation/context tests and page PHP lint passed. Visual browser verification remains unavailable; hosted gate pending.
