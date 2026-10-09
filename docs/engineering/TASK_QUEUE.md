# Ordered task queue

## Phase 1

- [x] Inspect architecture, source, runtime and database metadata.
- [x] Initialize Git and configure the user-supplied GitHub remote.
- [x] Use explicitly approved PHP 7.4.33; install/validate local Composer and generate lock/autoload.
- [x] Bootstrap/environment/logging/session configuration and protected source/uploads.
- [x] Harden route dispatch and verify HTTP behavior.
- [x] Reconcile/capture the existing 19-table baseline without exporting records.
- [x] Test fresh install, matching-schema adoption, row preservation, drift rejection, checksum verification, partial DDL recovery and lock release.
- [x] Apply baseline with explicit user approval.
- [x] Verify admin/owner/tenant login and real portal/shared layout boot on a disposable database.
- [x] Run lint, foundation tests, database/HTTP tests and Composer validation locally.
- [x] Publish user-approved source; hosted run 37892096783 passed for c73f304.
- [x] Verify expanded-check commit e043b77 in hosted run 37892501297 (success).

## Phase 2

- [x] Reject administrator role injection during public registration.
- [x] Preserve password bytes; enforce supported bcrypt length for new passwords.
- [x] Implement five-attempt login lockout and expiration.
- [x] Enforce CSRF logout/manual-payment/payment-initiation and repair associated forms.
- [x] Refresh persisted roles and revoke stale administrator privileges.
- [x] Implement organization onboarding, memberships, explicit staff grants and admin verification.
- [x] Test cross-account/cross-organization, suspended/inactive, self-verification and privilege escalation denials.
- [x] Add organization management/verification page and navigation.
- [x] Implement hashed/single-use/expiring reset tokens, request limits and session-version invalidation.
- [x] Test full registration/login/logout/recovery HTTP flows and selected legacy room/ledger/statement/checkout boundaries.
- [x] Apply organization/recovery migrations and enable features with explicit approval.
- [x] Install user-selected PHPMailer and implement/test secure SMTP delivery configuration.
- [x] Configure Gmail SMTP privately and verify TLS/authentication and test acceptance.
- [x] User confirmed the Spacivo delivery test was received.
- [x] Verify PHPMailer source commit 486df96 in hosted run 37892943844 (success).
- [x] Review passing hosted CI and Phase 2 acceptance; test transport isolation is verified locally.

## Phase 3

- [x] Inspect existing inventory and architecture capability requirements.
- [x] Implement normalized category/capability tables and restartable migration without seeded application data.
- [x] Implement administrator configuration, capability validation, optimistic versions and public active-only reads.
- [x] Add category management UI and versioned category REST endpoints.
- [x] Verify category authorization, malformed combinations, activation/deactivation, duplicate codes, stale writes and HTTP/CSRF behavior.
- [x] Verify category commit 5c4263e in hosted run 37894183813.
- [x] Apply migration 004 and enable categories with explicit user approval; Apache category API returns 200.
- [x] Implement/test organization-owned property/unit CRUD, staff grants, REST/UI, approval/reviewer records and listing lifecycle.
- [x] Implement/test amenities, safe private raster photos, location, approval and listing lifecycle.
- [x] Verify approved publication and anonymous photo access through REST/HTTP; edits and suspension revoke visibility.

## Following phases (strict order)

4. Published inventory discovery and Mapbox.
5. Reservation/pricing/availability services with concurrency tests.
6. Payment sandbox/webhooks/reconciliation and rental operations.
7. Owner/staff tools and enforced subscription limits.
8. Platform moderation and operational controls.
9. Release validation and approved deployment.
10. Flutter using the shared API.

Break each phase into small tasks during inspect/plan. Do not substitute legacy feature overlap for architecture acceptance tests.

Inventory task gate passed: hosted run 37895621406 succeeded for 8ea45f8; migration 005 applied and inventory enabled with explicit user approval. Metadata migration 006/activation and hosted verification passed; shared UI gate and final acceptance remain.


## 2026-10-09 — Shared management interface

[x] User-priority shared admin/owner light UI implementation, obsolete layout cleanup and permission regression verification. Browser visual acceptance remains to be confirmed because the automation helper failed. Metadata migration 006 and activation are complete; hosted metadata run 37896990758 and public listing run 37897515890 passed. Shared UI hosted gate follows publication.

Hosted shared-UI source gate passed: run [37899186218](https://github.com/ReyalSolutions/Spacivo/actions/runs/37899186218), commit a85407b. Complete project lint: 197 files, zero failures; 29 foundation assertions and 190 database/HTTP assertions passed. Shared CSS/scripts return HTTP 200 on local Apache. Retained business-page content matches the original source apart from layout references. Visual browser verification remains outstanding because the automation helper could not initialize.


## 2026-10-09 — Paid owner subscription access

[x] Repair paid-owner subscription redirects, unchanged-plan renewal activation, consistent status/expiry/limits and renewal-notice layout. Local acceptance passed; hosted verification pending.

Hosted paid-owner repair gate passed: [run 37900510660](https://github.com/ReyalSolutions/Spacivo/actions/runs/37900510660), source commit a6627ad. All subscription repair acceptance checks are complete; browser visual automation remains unavailable as previously recorded.


## 2026-10-09 — Unified reference management theme

User selected a new neutral dashboard reference for all admin and owner pages. Added one management-theme stylesheet loaded by their common asset partial: slim gray sidebar, white header/cards, subtle borders, black primary actions, blue accents, compact tables/forms, consistent dialogs and responsive shell. Dashboard-specific welcome banner now uses the same theme. Existing role/organization guards remain unchanged. 215 database/HTTP checks pass. Visual browser automation remains unavailable as previously recorded; no pixel-perfect visual acceptance is claimed.


## 2026-10-09 — Shared listings migration

User explicitly authorized incremental shared admin/owner implementations, with admin seeing all listings and owners seeing only their own. First feature completed: canonical app/views/admin/houses.php serves both roles. Physical admin/houses.php and legacy owner/houses delegate to it; the obsolete owner listing view is removed. Existing owner-scoped write handlers remain as compatibility delegates while canonical admin action URLs reuse them. Each listing create/edit/delete/photo/amenity action checks its exact permission-table slug; moderation requires admin role and approve_houses. Ownership checks remain unchanged; shared visibility does not grant global editing authority. Missing granular owner grants are not automatically broadened. Read-only listing users cannot mutate listings, and revocation applies on the next request. Shared modals preserve editing/upload functionality for authorized owned records. Other feature controllers/views remain separate pending their own migration gates.

Verification: 198 PHP files linted without errors, 29 foundation/mail assertions and 227 disposable database/HTTP assertions passed. Includes all-owner admin data, owner filter tampering, all three shared entry routes, direct write denial, edit permission with foreign property rejection, and permission revocation. No live rows or role grants changed. Hosted gate follows publication. Browser visual automation remains unavailable as previously recorded.

Shared-listings hosted gate passed: [run 37905013391](https://github.com/ReyalSolutions/Spacivo/actions/runs/37905013391), source f555616. Six inline shared-page scripts passed Node syntax parsing. Next consolidation feature: rooms, then bookings and tenant operations, preserving explicit action permissions and data boundaries.


2026-10-09: User-priority action permissions and shared skeleton loading implemented and locally verified. Hosted gate pending; resume incremental shared-page consolidation afterward. Previously recorded Mapbox/browser limitations remain.


Hosted action-permissions/loading gate passed: [run 37908631123](https://github.com/ReyalSolutions/Spacivo/actions/runs/37908631123), source e7efb4f. PHP validation, foundation/integration/database checks and Node loader checks all succeeded. This user-priority implementation loop is complete; resume shared rooms consolidation next.


## 2026-10-09 — Global toast across pages

Notification feedback uses the existing ToastStack engine across admin, owner, tenant, public and authentication views, including role/recovery flash messages and organization/category/inventory saves. Shared headers load the engine before page scripts. Feedback.fire sends notices to ToastStack and preserves interactive confirmations, input prompts and loading dialogs. Post-notice redirects retain display delays; success/error notices close pending loading dialogs. Titles/messages are escaped, server flash values are JSON encoded and toast roles support accessible announcements. Browser visual verification remains unavailable. Local verification: 202 PHP files linted, 29 foundation assertions, 348 database/HTTP assertions, skeleton checks and global-toast safety/confirmation/callback tests passed. Hosted gate pending.


Global-toast hosted gate passed: [run 37909608793](https://github.com/ReyalSolutions/Spacivo/actions/runs/37909608793), source b26529e. All PHP, integration, database, skeleton and toast checks succeeded. User-priority toast implementation loop complete.
