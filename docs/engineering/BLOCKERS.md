# External blockers and pending gates

Resolved: PHP version decision, Composer installation, baseline reconciliation/testing, portal boot verification, and approval to apply the first three migrations. Existing PHP 7.4.33 is explicitly authorized.

GitHub remote supplied: https://github.com/ReyalSolutions/Spacivo.git. Repository metadata confirms main as the default branch and push access. It was empty when inspected. Hosted CI still requires reviewed source publication and an actual Actions result; local equivalent checks pass.

Awaiting the email provider choice for real password recovery delivery. The local/testing adapter queues private messages only. Do not claim those messages were sent as email; recovery is intentionally unavailable outside local/testing until a production adapter exists.

Future Mapbox, payment sandbox, Firebase, hosting and mobile production configuration are not available yet. Those remain later-phase external dependencies, not invented integrations.

Approval audit: automatic review initially rejected applying schema changes to the existing database because authorization was unclear. The user then explicitly approved the baseline and subsequently both Phase 2 migrations/enabling their features. Those exact migrations were applied successfully; this blocker is resolved.
