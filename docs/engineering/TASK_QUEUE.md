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
- [ ] Verify final expanded-check source commit in hosted CI.

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
- [ ] Connect the chosen real email provider and verify actual delivery.
- [ ] Review hosted CI and final phase acceptance before proceeding.

## Following phases (strict order)

3. Configurable categories/capabilities and organization-owned inventory.
4. Published inventory discovery and Mapbox.
5. Reservation/pricing/availability services with concurrency tests.
6. Payment sandbox/webhooks/reconciliation and rental operations.
7. Owner/staff tools and enforced subscription limits.
8. Platform moderation and operational controls.
9. Release validation and approved deployment.
10. Flutter using the shared API.

Break each phase into small tasks during inspect/plan. Do not substitute legacy feature overlap for architecture acceptance tests.
