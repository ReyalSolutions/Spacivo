# Validation report

Date: 2026-10-09, Asia/Singapore. Runtime: user-approved PHP 7.4.33; local Composer 2.10.3.

## Latest verified results

- 168 active PHP files linted, zero failures.
- 21 foundation assertions passed: environment handling, namespaced/legacy autoload, CSRF including malformed arrays, registration role/password policy and route action restrictions.
- 90 database/HTTP assertions passed: migrations, baseline adoption/preservation/drift, restart recovery/checksums/locks, portal boots, role revocation, public registration, opaque passwords, logout/CSRF, login lockout/expiration, organization membership/isolation/verification, password recovery/session invalidation/token replay/expiry/request limits, and legacy room/ledger/statement/checkout account boundaries.
- Database tests created uniquely named disposable *_test databases and removed only the databases they created. No fixture users were added to the existing application database.
- Composer install/autoload/lockfile and strict manifest validation pass.
- All health checks pass: approved PHP version, mysqli/JSON, writable logs, environment configuration and read-only database connectivity.
- All three migrations report applied locally following explicit user approval.
- Apache login/recovery HTTP 200; unauthenticated organization API 401; GET logout 405; diagnostics/private source 403; unauthenticated admin page 302.

## Remaining verification

GitHub Actions run [37892096783](https://github.com/ReyalSolutions/Spacivo/actions/runs/37892096783) passed for c73f304 on PHP 7.4 and MariaDB 10.4. Final expanded-check commit verification follows. Real email delivery is pending. Development recovery uses a private local outbox and does not send email. Existing /tenant absolute assets/admin links require deployment-layout migration before a production release. Later phase requirements including universal inventory, concurrent bookings, real payment reconciliation and mobile flows are not yet verified.
