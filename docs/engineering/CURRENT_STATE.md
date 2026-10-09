# Current engineering state

Updated: 2026-10-09, Asia/Singapore. Authority: architecture.md with the user's PHP 7.4 runtime override.

Active runtime is PHP 7.4.33. Composer 2.10.3 is installed locally in tools/composer.phar, manifest validates, lockfile generated and autoload installed. The active StayHub app remains compatible while new Identity/Organizations modules follow services, repositories and policies.

Phase 1 is complete: migrations, recovery after partial DDL failure, existing-data preservation, all portal boots, logging/configuration and automated checks pass. The user approved source publication to https://github.com/ReyalSolutions/Spacivo.git. GitHub Actions runs 37892096783 and 37892501297 passed for c73f304 and e043b77, including expanded lint and the private storage guard.

Phase 2 changes are implemented and enabled locally: safe public registration, opaque passwords, login lockouts, CSRF logout/payment protections, persisted-role refresh, scoped organizations/members/staff permissions, owner onboarding/admin verification, local password recovery and session invalidation. Organization/recovery migrations were explicitly approved and applied. Legacy room/ledger/statement/checkout account boundaries are tested.

Migrations 001â€“006 are applied to tenant_boarding; category, inventory and metadata activation were explicitly approved. No existing user/application rows were changed by migrations; five new Phase 2 tables plus migration tracking were added. All seeded accounts and behavioral mutations belong only to disposable test databases, which were removed afterward.

Latest checks: 197 PHP files linted, 29 foundation/mail assertions and 185 database/HTTP assertions passed. Composer strict validation passes. Local Apache login/recovery return 200, unauthenticated organization API 401, GET logout 405, private diagnostics 403 and unauthenticated admin pages redirect to login.

User selected PHPMailer; version 7.1.1 is installed, secure SMTP delivery and environment configuration are implemented and tested. Local recovery now uses Gmail SMTP with the Spacivo display name. Private credentials remain in ignored .env. TLS/authentication passed and Gmail accepted one user-approved test email; the user confirmed receipt. Legacy absolute /tenant assets/admin paths require migration before production public-only deployment. Full marketplace discovery, booking, payments and mobile phases remain incomplete.

PHPMailer implementation commit 486df96 passed hosted run 37892943844. Phase 2 delivery is verified and the user confirmed receipt. Automated HTTP tests force local outbox delivery even when the application uses SMTP. Phase 3 category task is implemented/tested: normalized capability tables, admin configuration UI, active-only public categories and REST v1 endpoints. Migration 004 is applied and categories are enabled; hosted run 37894183813 passed for 5c4263e. Real Apache public categories return HTTP 200 with an empty catalog. Property/unit CRUD, scoped staff permissions, REST/UI, approval/reviewer records and draft/published/suspended/archived lifecycle are implemented and tested. Inventory is enabled; hosted run 37895621406 passed for 8ea45f8 and Apache public properties return 200 with an empty catalog. Photos/amenities, private raster storage, permission-checked image streaming and UI/REST controls are implemented and tested. Migration 006 is applied and metadata enabled; hosted run 37896990758 passed for 35bf315. A public listing page and navigation are implemented/tested; publishing makes approved verified listings visible, while suspension removes them. Its final hosted check follows before Phase 3 acceptance. Phase 4 Mapbox verification is externally blocked because the user has no token yet; search/details/gallery work can proceed after Phase 3 acceptance.


## 2026-10-09 — Shared management interface

User-priority UI consolidation completed: admin and owner management pages share the existing light admin components, absolute assets and permission-filtered navigation. Organization/category/inventory management now uses the same shell. Owner subscription pages no longer load the dark layout. Removed only the two obsolete MVC layout files and their sole-use dark stylesheet; retained active business pages, data and unrelated files. Browser screenshot verification was attempted but the automation helper failed before opening a tab. Automated checks: 190 database/HTTP assertions, 29 foundation assertions, full project lint and Composer validation. Hosted UI verification follows publication. Marketplace commit 966512d passed hosted run 37897515890. Resume phased implementation after this UI verification gate; Mapbox still requires the user token.

Hosted shared-UI source gate passed: run [37899186218](https://github.com/ReyalSolutions/Spacivo/actions/runs/37899186218), commit a85407b. Complete project lint: 197 files, zero failures; 29 foundation assertions and 190 database/HTTP assertions passed. Shared CSS/scripts return HTTP 200 on local Apache. Retained business-page content matches the original source apart from layout references. Visual browser verification remains outstanding because the automation helper could not initialize.


## 2026-10-09 — Paid owner subscription access

Owner subscription redirect repair: confirmed a live expired subscription had settled renewals today. Same-plan updates returned false on zero changed rows, skipping reactivation; access checks also excluded expired rows and computed the period start as expiry. Fixed unchanged-plan success with scoped existence verification, centralized subscription entitlement/list/limits, paid renewal coverage and clamped monthly/yearly expiry. Current paid renewal now resolves active through 2026-11-09 using read-only live verification. No live billing rows changed. Next gate: hosted source verification, then resume phased development.

Hosted paid-owner repair gate passed: [run 37900510660](https://github.com/ReyalSolutions/Spacivo/actions/runs/37900510660), source commit a6627ad. All subscription repair acceptance checks are complete; browser visual automation remains unavailable as previously recorded.


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
