# Architecture decisions

## 2026-10-09: Incremental migration

architecture.md supersedes older structure.md/documentation.md for new work. Preserve global MVC classes and ?url actions while adding App namespaces. Map App\Core to existing lowercase app/core explicitly for Linux compatibility. Do not rewrite working modules before regression coverage exists.

## Runtime gate

Require PHP 8.2+ for the platform. Foundation code remains compatible with installed PHP 7.4 to verify legacy behavior during transition. These checks do not waive the architecture gate; migration CLI rejects runtimes below 8.2.

## Schema safety

Inspect metadata only. Historical SQL snapshots are not authoritative migrations. CLI migration writes require --apply and are serialized/checksum checked. MySQL DDL auto-commits: use restartable migrations and tested recovery rather than claiming DDL transaction rollback.

## Hosting

Keep the local /tenant root wrapper during transition and deny private directories/diagnostics with Apache rules. Production document root must be public/. Existing absolute /tenant links require migration before root-domain hosting.

## Integration boundaries

Exclude the independent reyal_solutions project. Existing role IDs/boarding-house ownership do not replace organization policies. Do not fabricate gateway/Mapbox/Firebase credentials or claim simulations as verified integrations.

## 2026-10-09: User runtime override and verified local features

The user explicitly instructed use of existing PHP 7.4.33. This supersedes the earlier PHP 8.2 gate. Composer requirements, health/migration commands, CI and architecture examples now target PHP 7.4. Composer was installed from a SHA-384 verified official installer inside the workspace.

Capture and verify the actual schema structure, excluding identity counters and all records. Adopt matching existing schemas without rebuilding tables. Keep migration tracking and all new module tables additive; apply only explicitly approved existing-database changes.

Organization roles/grants are separate from platform roles. Platform administrators can explicitly verify an organization but do not automatically become members. Membership grants are owner-controlled. New organization services do not yet port legacy boarding-house records into organization inventory; that belongs to Phase 3.

Local password recovery defaults to a private outbox adapter. The user selected PHPMailer on 2026-10-09; Composer installed 7.1.1 on the existing PHP 7.4 runtime. SMTP delivery uses TLS and configured sender/authentication, with debug disabled and sanitized errors. Production refuses local delivery and incomplete configuration. Actual inbox delivery awaits private SMTP configuration and verification. Reset tokens revoke existing sessions through versioned credentials; role changes refresh from the database on protected requests.

The empty user-supplied GitHub repository is public. Publish reviewed source only; exclude .env, SQL dumps, uploaded user media, diagnostics, dependencies/cache and the separate nested application.

Inventory photos are private raster files, reencoded with GD and served through permission/visibility checks. No draft or suspended photo is served directly from public uploads. Metadata edits invalidate listing approval. Existing legacy inventory stays intact; organization ownership is never inferred or fabricated from old records.


## 2026-10-09 — Shared management interface

Admin and owner share one presentation shell (admin/components with MVC management_header/footer adapters). Role and permission checks control navigation; existing server authorization and ownership boundaries remain authoritative. Keep active legacy business pages during phased replacement; remove superseded presentation code only after reference checks.


## 2026-10-09 — Paid owner subscription access

Derive legacy owner access from the shared subscription model: active first-period assignments and settled, owner/subscription/plan/cycle-matched renewals cover one period. An expired stored flag can be reconciled by a current paid renewal, but pending/cancelled states, explicit end dates and overdue periods remain enforced. Duplicate receipts do not stack paid periods. Same-plan renewals must succeed when an authorized row exists even if plan fields are unchanged. Billing ledger and stored status are preserved during read checks.


## 2026-10-09 — Unified reference management theme

User selected a new neutral dashboard reference for all admin and owner pages. Added one management-theme stylesheet loaded by their common asset partial: slim gray sidebar, white header/cards, subtle borders, black primary actions, blue accents, compact tables/forms, consistent dialogs and responsive shell. Dashboard-specific welcome banner now uses the same theme. Existing role/organization guards remain unchanged. 215 database/HTTP checks pass. Visual browser automation remains unavailable as previously recorded; no pixel-perfect visual acceptance is claimed.


## 2026-10-09 — Shared listings migration

User explicitly authorized incremental shared admin/owner implementations, with admin seeing all listings and owners seeing only their own. First feature completed: canonical app/views/admin/houses.php serves both roles. Physical admin/houses.php and legacy owner/houses delegate to it; the obsolete owner listing view is removed. Existing owner-scoped write handlers remain as compatibility delegates while canonical admin action URLs reuse them. Each listing create/edit/delete/photo/amenity action checks its exact permission-table slug; moderation requires admin role and approve_houses. Ownership checks remain unchanged; shared visibility does not grant global editing authority. Missing granular owner grants are not automatically broadened. Read-only listing users cannot mutate listings, and revocation applies on the next request. Shared modals preserve editing/upload functionality for authorized owned records. Other feature controllers/views remain separate pending their own migration gates.

Verification: 198 PHP files linted without errors, 29 foundation/mail assertions and 227 disposable database/HTTP assertions passed. Includes all-owner admin data, owner filter tampering, all three shared entry routes, direct write denial, edit permission with foreign property rejection, and permission revocation. No live rows or role grants changed. Hosted gate follows publication. Browser visual automation remains unavailable as previously recorded.

## 2026-10-09 — Action permissions and shared skeleton loading

The canonical role editor manages database-backed action grants. Every declared admin/owner controller action checks its mapped grants and state-changing requests require CSRF. Physical admin pages delegate to verified handlers and reject legacy direct POST processing. Admin listing visibility and owner account boundaries remain intact. Role synchronization validates IDs before replacing grants. The idempotent CLI seeder preserves existing definitions and role grants; five missing booking approval/rejection and plan-payment editing/printing permissions were added locally without automatic grants.

Shared management assets provide skeleton shimmer loading for same-origin jQuery/fetch requests, concurrent-request tracking, failure cleanup, lazy images and reduced-motion support. Existing management spinner markup and SweetAlert loaders use skeleton bars. Photo uploads retain linear progress. Verification: 202 PHP files linted, 29 foundation assertions, 348 disposable database/HTTP assertions, and Node loader success/failure/concurrency checks passed. Hosted verification follows publication. Browser visual verification remains unavailable.

Automatic review declined broad router/global-authentication and transport monkey-patching proposals. Those proposals were not applied. Feature groups were migrated and verified separately; loading uses ordinary AJAX events and a tested fetch wrapper.

## 2026-10-09 — Global toast across pages

Notification feedback uses the existing ToastStack engine across admin, owner, tenant, public and authentication views, including role/recovery flash messages and organization/category/inventory saves. Shared headers load the engine before page scripts. Feedback.fire sends notices to ToastStack and preserves interactive confirmations, input prompts and loading dialogs. Post-notice redirects retain display delays; success/error notices close pending loading dialogs. Titles/messages are escaped, server flash values are JSON encoded and toast roles support accessible announcements. Browser visual verification remains unavailable. Local verification: 202 PHP files linted, 29 foundation assertions, 348 database/HTTP assertions, skeleton checks and global-toast safety/confirmation/callback tests passed. Hosted gate pending.


## 2026-10-09 — Listing success toast repair

Added omitted global success notifications for listing approval/rejection and photo deletion. Listing form success/error flashes now use ToastStack after DOM readiness and display for five seconds; removed the duplicate management toast container. Both shared headers version the toast script with its modification timestamp to invalidate stale cached engines. Regression checks confirm successful owned-listing update redirects, emits its success toast with versioned assets, and consumes the flash exactly once. Local verification: 351 database/HTTP assertions, 29 foundation assertions, 202 PHP files linted, and global toast tests including moderation notification plus table refresh. Browser visual automation remains unavailable. Hosted gate pending.


2026-10-09 - Amenities checkbox restoration: normalized selected/catalog IDs before comparison in the shared listing modal. Prepared-query numeric IDs now match text-query string IDs. Focused tests cover both type directions, saved additions/removals and empty selections across reopen; PHP lint passed. Hosted full-suite gate pending. Existing stored amenities and permissions are unchanged.


2026-10-09 - Upgrade modal shared design: replaced the dark/purple presentation with the neutral management theme, white header, bordered plan cards, black billing/primary controls and restrained blue recommendation highlight. Styles are scoped to the modal so moving it to document.body retains the theme. Added a bounded scrolling body and accessible title/close/focus controls. Billing prices, limits, selection, permissions and payment functions remain intact. PHP lint and existing toast/amenity tests passed. Browser visual verification remains unavailable; hosted gate pending.


2026-10-09 - Upgrade plans moved to admin/upgrade: replaced the plan modal with a shared page section and natural document scrolling. Houses, rooms and subscription upgrade links open the page; subscription/cycle context is retained. The route checks existing subscription-view permissions and rejects foreign owner subscription IDs. Plan payment selection remains available. Removed the unused upgrade modal component. Verification: 203 PHP files linted, 29 foundation assertions and 355 database/HTTP checks passed, including page/billing/payment/ownership regressions. Browser visual verification remains unavailable; hosted gate pending.


2026-10-09 - Upgrade entry confirmation and focused page: shared upgrade actions display an Upgrade required dialog with View plans/Not now before navigation. Confirmation retains subscription/yearly context; cancellation leaves the current page intact. admin/upgrade now renders a standalone layout with no dashboard sidebar/header, while preserving shared styles, toast, CSRF and payment selection. Verification: 356 database/HTTP assertions, upgrade prompt confirmation/cancellation/context tests and page PHP lint passed. Visual browser verification remains unavailable; hosted gate pending.


2026-10-09 - Organization/category/property workspace design organized: added one scoped shared module stylesheet with consistent headings/actions, neutral bordered cards, responsive grouped form fields, capability grids, clear empty states and compact status badges. Existing category/property records are expandable; new forms remain open. Organization staff access is expandable and admin property/index review uses the same layout. Form controls, permissions, ownership guards and request scripts remain unchanged. Local checks: 356 database/HTTP assertions, 29 foundation assertions, 203 PHP files linted, and form/control/script comparison passed. Browser visual verification remains unavailable; hosted gate pending.
