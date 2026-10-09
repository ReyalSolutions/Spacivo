# External blockers and pending gates

Resolved: PHP version decision, Composer installation, baseline reconciliation/testing, portal boot verification, and approval to apply the first three migrations. Existing PHP 7.4.33 is explicitly authorized.

Resolved: the user explicitly approved publication to https://github.com/ReyalSolutions/Spacivo.git. Source is published on main; GitHub Actions runs 37892096783 and 37892501297 passed for c73f304 and e043b77 respectively. PHPMailer implementation commit 486df96 passed hosted run 37892943844.

User selected PHPMailer. The SMTP adapter is implemented and locally tested; private Gmail SMTP settings are configured; TLS/authentication passed and Gmail accepted one approved test email. The user confirmed receipt. Local/testing defaults to the private outbox. Production permits only configured SMTP; configuration and MIME tests do not prove actual delivery.

Future Mapbox, payment sandbox, Firebase, hosting and mobile production configuration are not available yet. Those remain later-phase external dependencies, not invented integrations.

Approval audit: automatic review initially rejected applying schema changes to the existing database because authorization was unclear. The user then explicitly approved the baseline and subsequently both Phase 2 migrations/enabling their features. Those exact migrations were applied successfully; this blocker is resolved.

Mapbox: the user confirmed no token is available yet. Phase 4 map verification requires a user-owned public token; do not substitute fabricated or borrowed credentials. Other Phase 4 tasks can proceed after Phase 3 acceptance.


2026-10-09: User-priority action permissions and shared skeleton loading implemented and locally verified. Hosted gate pending; resume incremental shared-page consolidation afterward. Previously recorded Mapbox/browser limitations remain.


## 2026-10-09 — Global toast across pages

Notification feedback uses the existing ToastStack engine across admin, owner, tenant, public and authentication views, including role/recovery flash messages and organization/category/inventory saves. Shared headers load the engine before page scripts. Feedback.fire sends notices to ToastStack and preserves interactive confirmations, input prompts and loading dialogs. Post-notice redirects retain display delays; success/error notices close pending loading dialogs. Titles/messages are escaped, server flash values are JSON encoded and toast roles support accessible announcements. Browser visual verification remains unavailable. Local verification: 202 PHP files linted, 29 foundation assertions, 348 database/HTTP assertions, skeleton checks and global-toast safety/confirmation/callback tests passed. Hosted gate pending.


## 2026-10-09 — Listing success toast repair

Added omitted global success notifications for listing approval/rejection and photo deletion. Listing form success/error flashes now use ToastStack after DOM readiness and display for five seconds; removed the duplicate management toast container. Both shared headers version the toast script with its modification timestamp to invalidate stale cached engines. Regression checks confirm successful owned-listing update redirects, emits its success toast with versioned assets, and consumes the flash exactly once. Local verification: 351 database/HTTP assertions, 29 foundation assertions, 202 PHP files linted, and global toast tests including moderation notification plus table refresh. Browser visual automation remains unavailable. Hosted gate pending.


2026-10-09 - Amenities checkbox restoration: normalized selected/catalog IDs before comparison in the shared listing modal. Prepared-query numeric IDs now match text-query string IDs. Focused tests cover both type directions, saved additions/removals and empty selections across reopen; PHP lint passed. Hosted full-suite gate pending. Existing stored amenities and permissions are unchanged.


2026-10-09 - Upgrade modal shared design: replaced the dark/purple presentation with the neutral management theme, white header, bordered plan cards, black billing/primary controls and restrained blue recommendation highlight. Styles are scoped to the modal so moving it to document.body retains the theme. Added a bounded scrolling body and accessible title/close/focus controls. Billing prices, limits, selection, permissions and payment functions remain intact. PHP lint and existing toast/amenity tests passed. Browser visual verification remains unavailable; hosted gate pending.
