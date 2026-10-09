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
