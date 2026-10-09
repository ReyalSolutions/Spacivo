# Phase tracker

Updated: 2026-10-09, Asia/Singapore. PHP 7.4.33 is the user-approved target.

| Phase | Status | Remaining gate |
| --- | --- | --- |
| 1 Foundation | Complete | Hosted run 37892501297 passed for e043b77 |
| 2 Identity and permissions | Complete | Gmail test received; mail-isolation hosted run 37893511699 passed |
| 3 Categories and inventory | Inventory and metadata enabled; public visibility verified | Shared management UI verification and final acceptance |
| 4 Marketplace | Pending Phase 3 | Cross-category discovery and Mapbox |
| 5 Booking engine | Pending Phase 4 | Holds/pricing/idempotency/concurrency/timezones |
| 6 Payments and operations | Pending Phase 5 | Verified sandbox/reconciliation/rental operations |
| 7 Owner SaaS | Pending Phase 6 | Scoped permissions and enforced plan limits |
| 8 Administration | Pending Phase 7 | Complete auditable operational controls |
| 9 Production readiness | Pending Phase 8 | Release gates and tested recovery/rollback |
| 10 Flutter | Pending Phase 9 | Mobile implementation and release validation |

Local gates are evidence of development functionality, not production readiness. No later phase is complete merely because the legacy application has overlapping features.


## 2026-10-09 — Shared management interface

User requested shared admin/owner design before continuing marketplace work. Shared UI implementation and automated checks complete; hosted check pending publication. Browser visual verification remains unavailable due to helper failure.

Hosted shared-UI source gate passed: run [37899186218](https://github.com/ReyalSolutions/Spacivo/actions/runs/37899186218), commit a85407b. Complete project lint: 197 files, zero failures; 29 foundation assertions and 190 database/HTTP assertions passed. Shared CSS/scripts return HTTP 200 on local Apache. Retained business-page content matches the original source apart from layout references. Visual browser verification remains outstanding because the automation helper could not initialize.


## 2026-10-09 — Paid owner subscription access

User-priority paid-owner access repair implemented and locally verified before continuing marketplace work; hosted source gate pending.

Hosted paid-owner repair gate passed: [run 37900510660](https://github.com/ReyalSolutions/Spacivo/actions/runs/37900510660), source commit a6627ad. All subscription repair acceptance checks are complete; browser visual automation remains unavailable as previously recorded.


2026-10-09: User-priority action permissions and shared skeleton loading implemented and locally verified. Hosted gate pending; resume incremental shared-page consolidation afterward. Previously recorded Mapbox/browser limitations remain.


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
