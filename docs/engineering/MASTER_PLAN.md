# Spacivo master plan

Authority: project-root architecture.md. Approved scope: inspect and develop all ten phases in order using inspect -> plan -> implement -> verify -> repair -> review -> document -> continue.

Preserve the working StayHub system while migrating to PHP 7.4+ (explicit user override), modular MVC, services, repositories, prepared MySQLi queries, organization isolation and REST API v1.

## Phase gates

1. Foundation: Git/Composer, bootstrap/router/environment, migrations, errors/logging/layouts, local development/CI. Boot, migrations and initial CI pass.
2. Identity: registration/login/logout/reset, secure sessions, organization RBAC, onboarding/verification/account management. Automated tests reject cross-account and cross-organization access.
3. Inventory: configurable categories/capabilities, properties/units, amenities/photos/location/approval and listing states. Verified owners publish approved listings.
4. Marketplace: responsive discovery, filters/sorting/pagination, Mapbox, galleries/details/favorites/profiles. Published units discoverable across categories.
5. Booking: availability/blocks, hourly/nightly/monthly modes, authoritative pricing/quotes/holds/approval/cancellation. Integration tests verify concurrency, timezones and expiration.
6. Payments: gateway sandbox/webhook verification/reconciliation, refunds/deposits/invoices/ledgers/agreements/receipts. No duplicate charges or inconsistent reservation states.
7. Owner SaaS: analytics, staff permissions, calendar/customers, maintenance/announcements, subscriptions/limits. Operations obey plan limits and permissions.
8. Administration: moderation/verification, commission/subscription configuration, disputes/refunds, reports/audit/feature flags. Operation without direct DB intervention.
9. Release: unit/integration/feature/security, performance/accessibility, secrets, backup/recovery/rollback and CI deployment. Release criteria and rollback verified.
10. Flutter: shared API/authentication, maps/discovery/booking, push/payments, Android tests/release. Supported flows against production API.

Each gate uses architecture.md's full requirements. Complete small testable tasks; never mark generated files or legacy overlap as verified functionality. Repair regressions before continuing. Record external blockers honestly. Production data changes and deployment require authorization.


2026-10-09: User-priority action permissions and shared skeleton loading implemented and locally verified. Hosted gate pending; resume incremental shared-page consolidation afterward. Previously recorded Mapbox/browser limitations remain.


## 2026-10-09 — Global toast across pages

Notification feedback uses the existing ToastStack engine across admin, owner, tenant, public and authentication views, including role/recovery flash messages and organization/category/inventory saves. Shared headers load the engine before page scripts. Feedback.fire sends notices to ToastStack and preserves interactive confirmations, input prompts and loading dialogs. Post-notice redirects retain display delays; success/error notices close pending loading dialogs. Titles/messages are escaped, server flash values are JSON encoded and toast roles support accessible announcements. Browser visual verification remains unavailable. Local verification: 202 PHP files linted, 29 foundation assertions, 348 database/HTTP assertions, skeleton checks and global-toast safety/confirmation/callback tests passed. Hosted gate pending.
