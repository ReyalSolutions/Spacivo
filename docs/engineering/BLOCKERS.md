# External blockers and pending gates

Resolved: PHP version decision, Composer installation, baseline reconciliation/testing, portal boot verification, and approval to apply the first three migrations. Existing PHP 7.4.33 is explicitly authorized.

Resolved: the user explicitly approved publication to https://github.com/ReyalSolutions/Spacivo.git. Source is published on main; GitHub Actions runs 37892096783 and 37892501297 passed for c73f304 and e043b77 respectively. PHPMailer implementation commit 486df96 passed hosted run 37892943844.

User selected PHPMailer. The SMTP adapter is implemented and locally tested; private Gmail SMTP settings are configured; TLS/authentication passed and Gmail accepted one approved test email. The user confirmed receipt. Local/testing defaults to the private outbox. Production permits only configured SMTP; configuration and MIME tests do not prove actual delivery.

Future Mapbox, payment sandbox, Firebase, hosting and mobile production configuration are not available yet. Those remain later-phase external dependencies, not invented integrations.

Approval audit: automatic review initially rejected applying schema changes to the existing database because authorization was unclear. The user then explicitly approved the baseline and subsequently both Phase 2 migrations/enabling their features. Those exact migrations were applied successfully; this blocker is resolved.

Mapbox: the user confirmed no token is available yet. Phase 4 map verification requires a user-owned public token; do not substitute fabricated or borrowed credentials. Other Phase 4 tasks can proceed after Phase 3 acceptance.


2026-10-09: User-priority action permissions and shared skeleton loading implemented and locally verified. Hosted gate pending; resume incremental shared-page consolidation afterward. Previously recorded Mapbox/browser limitations remain.
