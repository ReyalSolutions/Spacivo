# Current engineering state

Updated: 2026-10-09, Asia/Singapore. Authority: architecture.md with the user's PHP 7.4 runtime override.

Active runtime is PHP 7.4.33. Composer 2.10.3 is installed locally in tools/composer.phar, manifest validates, lockfile generated and autoload installed. The active StayHub app remains compatible while new Identity/Organizations modules follow services, repositories and policies.

Phase 1 is complete: migrations, recovery after partial DDL failure, existing-data preservation, all portal boots, logging/configuration and automated checks pass. The user approved source publication to https://github.com/ReyalSolutions/Spacivo.git. GitHub Actions runs 37892096783 and 37892501297 passed for c73f304 and e043b77, including expanded lint and the private storage guard.

Phase 2 changes are implemented and enabled locally: safe public registration, opaque passwords, login lockouts, CSRF logout/payment protections, persisted-role refresh, scoped organizations/members/staff permissions, owner onboarding/admin verification, local password recovery and session invalidation. Organization/recovery migrations were explicitly approved and applied. Legacy room/ledger/statement/checkout account boundaries are tested.

All three migrations are applied to tenant_boarding. No existing user/application rows were changed by migrations; five new Phase 2 tables plus migration tracking were added. All seeded accounts and behavioral mutations belong only to disposable test databases, which were removed afterward.

Latest checks: 171 PHP files linted, 29 foundation/mail assertions and 90 database/HTTP assertions passed. Composer strict validation passes. Local Apache login/recovery return 200, unauthenticated organization API 401, GET logout 405, private diagnostics 403 and unauthenticated admin pages redirect to login.

User selected PHPMailer; version 7.1.1 is installed, secure SMTP delivery and environment configuration are implemented and tested. Local recovery still defaults to the private outbox; no real email was sent. SMTP settings and actual inbox verification await user configuration. Legacy absolute /tenant assets/admin paths require migration before production public-only deployment. Later category/inventory/booking/payment/mobile phases remain incomplete.

PHPMailer implementation commit 486df96 passed hosted run 37892943844. Next: configure SMTP and verify inbox delivery, finalize Phase 2 acceptance and proceed to Phase 3 categories and organization-owned inventory. Real delivery remains an external dependency; local outbox/MIME tests are not delivery evidence.
