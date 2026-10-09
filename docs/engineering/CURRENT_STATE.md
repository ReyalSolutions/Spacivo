# Current engineering state

Updated: 2026-10-09, Asia/Singapore. Authority: architecture.md with the user's PHP 7.4 runtime override.

Active runtime is PHP 7.4.33. Composer 2.10.3 is installed locally in tools/composer.phar, manifest validates, lockfile generated and autoload installed. The active StayHub app remains compatible while new Identity/Organizations modules follow services, repositories and policies.

Phase 1 is locally verified: migrations, recovery after partial DDL failure, existing-data preservation, all portal boots, logging/configuration and automated checks pass. Hosted CI is pending the reviewed source upload/run. Remote origin is https://github.com/ReyalSolutions/Spacivo.git; it was empty when inspected.

Phase 2 changes are implemented and enabled locally: safe public registration, opaque passwords, login lockouts, CSRF logout/payment protections, persisted-role refresh, scoped organizations/members/staff permissions, owner onboarding/admin verification, local password recovery and session invalidation. Organization/recovery migrations were explicitly approved and applied. Legacy room/ledger/statement/checkout account boundaries are tested.

All three migrations are applied to tenant_boarding. No existing user/application rows were changed by migrations; five new Phase 2 tables plus migration tracking were added. All seeded accounts and behavioral mutations belong only to disposable test databases, which were removed afterward.

Latest checks: 142 PHP files linted, 21 foundation assertions and 90 database/HTTP assertions passed. Composer validation and all health checks pass. Local Apache login/recovery return 200, unauthenticated organization API 401, GET logout 405, private diagnostics 403 and unauthenticated admin pages redirect to login.

Password recovery currently queues private local outbox messages; no real email was sent. A real email provider is awaiting user input. Legacy absolute /tenant assets/admin paths require migration before production public-only deployment. Later category/inventory/booking/payment/mobile phases remain incomplete.

Next: publish the reviewed source and finish hosted CI, connect the chosen email provider, finalize Phase 2 acceptance and proceed to Phase 3 categories and organization-owned inventory.
