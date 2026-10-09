# SPACIVO — Complete System Architecture, Folder Structure, Code Architecture & Loop Engineering Process

> Runtime decision (2026-10-09): The project owner explicitly authorized continuing with the installed PHP 7.4.33. PHP 7.4 is the active development target and minimum version; later PHP 8.2 examples must be adapted to PHP 7.4 syntax. All phase sequencing, verification, organization isolation, and engineering-loop requirements still apply.

Recommended Architecture

Spacivo — Every Space. One Place.

We will design Spacivo as a multi-category space rental marketplace and property management SaaS platform, using your existing technology stack while keeping the system scalable, secure, modular, and maintainable.

The architecture will support public space discovery, rental booking, owner management, administrative operations, subscriptions, and future mobile applications.

## 1. Technology Stack

We will retain your preferred technologies rather than introducing an entirely new framework.

| Component            | Technology                     |
| -------------------- | ------------------------------ |
| Backend              | PHP 7.4+ with MySQLi           |
| Database             | MySQL / MariaDB                |
| Frontend             | HTML5, CSS3, JavaScript        |
| UI Framework         | Bootstrap 5                    |
| Dynamic Interactions | jQuery + AJAX                  |
| Tables               | DataTables                     |
| Alerts               | SweetAlert2                    |
| Charts               | Chart.js                       |
| Animations           | AOS                            |
| Icons                | Font Awesome / Lucide          |
| Mobile Application   | Flutter (future phase)         |
| Maps                 | Mapbox                         |
| Notifications        | Firebase Cloud Messaging       |
| Payment Integration  | PayMongo or supported provider |
| Source Control       | Git + GitHub                   |
| CI/CD                | GitHub Actions                 |
| Deployment           | PHP-compatible hosting         |

Architecture pattern: Modular Monolith + MVC + Service Layer + Repository Pattern + REST API.

This means the application will initially use one deployable PHP backend, but each business module will have independent controllers, services, repositories, validation, and permissions.

## 2. High-Level System Architecture

## SPACIVO ECOSYSTEM

One platform · Multiple rental categories

Marketplace

Web + Mobile

Owner Portal

Host + Staff

Admin Portal

Operations

PHP Application / REST API

Router · Authentication · RBAC · Validation · Controllers

Business Modules

Listings

Rental Engine

Availability

Pricing

Bookings

Payments

Tenants

Subscriptions

Reviews

Analytics

Messaging

Notifications

MySQL

Transactional data

File Storage

Photos & documents

Integrations

Maps · Payments · FCM

### Core architectural rules

1. All user-facing portals share one backend and one authoritative database.
2. Controllers only handle HTTP input/output, not complex business logic.
3. Services handle business rules and transactions.
4. Repositories handle database access using prepared statements.
5. Permissions are enforced on the server, never only through hidden UI elements.
6. Rental categories are configured through capabilities, rather than hardcoded category-specific booking logic.
7. Payment gateways, maps, storage, and messaging are accessed through adapters so they can be replaced later.
8. New modules must not directly modify unrelated modules' database tables without going through defined service interfaces.

## 3. Complete Project Folder Structure

I recommend a single GitHub repository initially, organized as follows:

```

spacivo/
│
├── app/
│   ├── Core/
│   │   ├── Application.php
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Database.php
│   │   ├── Session.php
│   │   ├── Auth.php
│   │   ├── Authorization.php
│   │   ├── Validator.php
│   │   ├── Csrf.php
│   │   ├── RateLimiter.php
│   │   ├── ExceptionHandler.php
│   │   └── View.php
│   │
│   ├── Modules/
│   │   ├── Identity/
│   │   ├── Users/
│   │   ├── Organizations/
│   │   ├── RolesPermissions/
│   │   ├── Categories/
│   │   ├── Properties/
│   │   ├── RentalUnits/
│   │   ├── Amenities/
│   │   ├── Media/
│   │   ├── Search/
│   │   ├── Availability/
│   │   ├── Pricing/
│   │   ├── Reservations/
│   │   ├── Rentals/
│   │   ├── Contracts/
│   │   ├── Payments/
│   │   ├── Payouts/
│   │   ├── Subscriptions/
│   │   ├── Reviews/
│   │   ├── Favorites/
│   │   ├── Messaging/
│   │   ├── Notifications/
│   │   ├── Maintenance/
│   │   ├── Disputes/
│   │   ├── Reports/
│   │   ├── Analytics/
│   │   ├── AuditLogs/
│   │   └── SystemSettings/
│   │
│   ├── Shared/
│   │   ├── DTO/
│   │   ├── Enums/
│   │   ├── Exceptions/
│   │   ├── Helpers/
│   │   ├── Interfaces/
│   │   ├── Traits/
│   │   └── ValueObjects/
│   │
│   ├── Integrations/
│   │   ├── Payments/
│   │   │   ├── PaymentGatewayInterface.php
│   │   │   └── PayMongoAdapter.php
│   │   ├── Maps/
│   │   │   └── MapboxAdapter.php
│   │   ├── Storage/
│   │   │   └── StorageInterface.php
│   │   ├── Email/
│   │   └── Firebase/
│   │
│   └── Jobs/
│       ├── ExpireReservations.php
│       ├── SendRentReminders.php
│       ├── ReconcilePayments.php
│       └── DispatchNotifications.php
│
├── bootstrap/
│   ├── app.php
│   └── container.php
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   ├── payments.php
│   ├── storage.php
│   └── features.php
│
├── routes/
│   ├── web.php
│   ├── api_v1.php
│   ├── owner.php
│   ├── admin.php
│   └── webhooks.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       ├── components/
│       ├── marketplace/
│       ├── owner/
│       ├── admin/
│       ├── auth/
│       └── errors/
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/
│   │   │   ├── base.css
│   │   │   ├── marketplace.css
│   │   │   ├── owner.css
│   │   │   └── admin.css
│   │   ├── js/
│   │   │   ├── core/
│   │   │   ├── marketplace/
│   │   │   ├── owner/
│   │   │   ├── admin/
│   │   │   └── components/
│   │   ├── images/
│   │   ├── icons/
│   │   └── fonts/
│   └── uploads/
│       └── .htaccess
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   ├── factories/
│   └── schema/
│
├── storage/
│   ├── logs/
│   ├── cache/
│   ├── sessions/
│   ├── private/
│   ├── temp/
│   └── queue/
│
├── tests/
│   ├── Unit/
│   ├── Integration/
│   ├── Feature/
│   ├── Security/
│   └── Fixtures/
│
├── scripts/
│   ├── migrate.php
│   ├── seed.php
│   ├── scheduler.php
│   └── health-check.php
│
├── docs/
│   ├── architecture/
│   ├── api/
│   ├── database/
│   ├── security/
│   ├── deployment/
│   ├── modules/
│   └── decisions/
│
├── mobile/
│   └── flutter_app/
│
├── .github/
│   └── workflows/
│       ├── ci.yml
│       ├── security.yml
│       └── deploy.yml
│
├── .env.example
├── .gitignore
├── composer.json
├── composer.lock
├── phpunit.xml
└── README.md

```

Important deployment rule: The web server's document root must point to `public/`. The `app/`, `config/`, `database/`, `storage/`, and `.env` files must not be publicly accessible. If hosting cannot support this layout securely, use an appropriate deployment layout or hosting provider rather than exposing the entire repository.

### Standard folder structure for every module

For example, the Reservations module:

```

app/Modules/Reservations/
├── Controllers/
│   ├── Web/
│   │   └── ReservationPageController.php
│   └── Api/
│       └── ReservationController.php
├── Services/
│   ├── ReservationService.php
│   └── CancellationService.php
├── Repositories/
│   └── ReservationRepository.php
├── Models/
│   └── Reservation.php
├── DTO/
│   └── CreateReservationData.php
├── Requests/
│   └── CreateReservationRequest.php
├── Policies/
│   └── ReservationPolicy.php
├── Events/
│   └── ReservationConfirmed.php
├── Listeners/
│   └── SendReservationConfirmation.php
├── Exceptions/
│   └── ReservationConflictException.php
└── Tests/
    └── ReservationServiceTest.php

```

Not every module needs every folder immediately. Create the folders when the module actually requires them.

# 4. Detailed Code Architecture

## 4.1 Request Processing Lifecycle

Every HTTP request should follow a predictable sequence.

Browser / Flutter

Sends request

Public Front Controller

public/index.php

Router & Middleware

Authentication · CSRF · RBAC · Rate limiting

Controller

Input validation and HTTP handling

Application Service

Business rules and transactions

Repository

Parameterized SQL queries

MySQL Database

Persistent data

The controller must never directly construct complicated SQL queries or perform payment business logic. It should delegate these operations to the relevant services.

## 4.2 Database Connection

Use MySQLi prepared statements, strict error reporting, and centralized database configuration.

`app/Core/Database.php`

```

<?php

declare(strict_types=1);

namespace App\Core;

use mysqli;

final class Database
{
    public static function connect(): mysqli
    {
        mysqli_report(
            MYSQLI_REPORT_ERROR |
            MYSQLI_REPORT_STRICT
        );

        $connection = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_NAME'],
            (int) ($_ENV['DB_PORT'] ?? 3306)
        );

        $connection->set_charset('utf8mb4');

        return $connection;
    }
}

```

Environment variables must be loaded during bootstrap. Never place production credentials inside PHP source files or GitHub repositories.

## 4.3 Controller Architecture

Example: retrieving a public rental listing.

`app/Modules/Properties/Controllers/Api/PropertyController.php`

```

<?php

declare(strict_types=1);

namespace App\Modules\Properties\Controllers\Api;

use App\Modules\Properties\Services\PropertyService;

final class PropertyController
{
    private PropertyService $service;
    public function __construct(
        PropertyService $service
    ) { $this->service = $service; }

    public function show(int $id): void
    {
        $property = $this->service->findPublished($id);

        if ($property === null) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Property not found'
            ]);
            return;
        }

        header('Content-Type: application/json');

        echo json_encode([
            'success' => true,
            'data' => $property
        ]);
    }
}

```

The service must ensure that only approved, published, and publicly visible properties are returned. Private owner details should be excluded from the public response.

## 4.4 Service Layer

`app/Modules/Properties/Services/PropertyService.php`

```

<?php

declare(strict_types=1);

namespace App\Modules\Properties\Services;

use App\Modules\Properties\Repositories\PropertyRepository;

final class PropertyService
{
    private PropertyRepository $repository;
    public function __construct(
        PropertyRepository $repository
    ) { $this->repository = $repository; }

    public function findPublished(int $id): ?array
    {
        return $this->repository->findPublishedById($id);
    }
}

```

## 4.5 Repository Layer

`app/Modules/Properties/Repositories/PropertyRepository.php`

```

<?php

declare(strict_types=1);

namespace App\Modules\Properties\Repositories;

use mysqli;

final class PropertyRepository
{
    private mysqli $db;
    public function __construct(
        mysqli $db
    ) { $this->db = $db; }

    public function findPublishedById(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                name,
                description,
                category_id,
                address,
                latitude,
                longitude
            FROM properties
            WHERE id = ?
              AND status = 'published'
              AND deleted_at IS NULL
            LIMIT 1
        ";

        $statement = $this->db->prepare($sql);
        $statement->bind_param('i', $id);
        $statement->execute();

        $result = $statement->get_result();
        $property = $result->fetch_assoc();

        $statement->close();

        return $property ?: null;
    }
}

```

These are foundational code examples, not a complete runnable application. They require the bootstrap, dependency injection, routing, migrations, and error handling described in the architecture.

# 5. Universal Rental Engine

This is the most important component of Spacivo.

Instead of implementing separate booking engines for apartments, hotels, courts, and parking spaces, the platform should have a shared booking system with configurable category capabilities.

## 5.1 Rental Capability Model

| Capability            | Apartment | Hotel    | Sports Court | Parking  |
| --------------------- | --------- | -------- | ------------ | -------- |
| Hourly booking        | Optional  | Optional | Yes          | Yes      |
| Nightly booking       | Optional  | Yes      | No           | Optional |
| Monthly rental        | Yes       | Optional | No           | Yes      |
| Calendar availability | Yes       | Yes      | Yes          | Yes      |
| Tenant ledger         | Yes       | Optional | No           | Optional |
| Check-in/out          | Optional  | Yes      | Optional     | Optional |
| Deposit               | Yes       | Optional | Optional     | Optional |
| Lease contract        | Yes       | Optional | No           | Optional |
| Time-slot reservation | No        | Optional | Yes          | Yes      |

These are proposed defaults, not hardcoded limitations. The administrator should be able to configure supported capabilities per category.

### Example capability configuration

```

{
  "category": "sports_court",
  "booking": {
    "enabled": true,
    "modes": ["hourly"],
    "instant_booking": true,
    "minimum_duration_minutes": 60,
    "slot_interval_minutes": 30
  },
  "pricing": {
    "base_rate": true,
    "peak_hour_rates": true,
    "discounts": true
  },
  "operations": {
    "tenant_ledger": false,
    "lease_contract": false,
    "check_in": true
  }
}

```

In production, store capability definitions in normalized database tables, with JSON used only for suitable flexible settings. Validate configurations before activating a category.

## 5.2 Core Entities

The data model should distinguish these concepts:

- Organization: The owner or business operating rental spaces.
- Property: A physical location or managed facility.
- Rental Unit: The specific bookable resource, such as Room 101, Court A, or Parking Slot 12.
- Availability Rule: When a unit may be reserved.
- Price Rule: How the system calculates charges.
- Reservation: A request or confirmed booking for a defined period.
- Rental Agreement: A longer-term rental relationship, when applicable.
- Payment: A financial transaction associated with a booking, rental, or subscription.

For example:

```

Organization: Spacivo Demo Rentals
│
├── Property: Downtown Residences
│   ├── Unit: Apartment 101
│   ├── Unit: Apartment 102
│   └── Unit: Parking Slot A1
│
├── Property: Downtown Sports Center
│   ├── Unit: Basketball Court A
│   └── Unit: Badminton Court B
│
└── Property: Downtown Events
    ├── Unit: Function Hall
    └── Unit: Meeting Room 1

```

A single owner can therefore manage multiple properties, and each property can contain different types of rentable units where permitted.

## 5.3 Booking Lifecycle

Select Unit & Rental Period

Check Availability & Calculate Price

Create Temporary Reservation Hold

Instant Booking

Payment / confirmation

Request to Book

Owner approval

Confirmed Reservation

Update availability · Record payment · Notify parties

### Booking integrity rules

- Use database transactions and row-level locking when reserving inventory.
- Use a canonical UTC time interval with a clearly stored property timezone.
- Treat reservation intervals as half-open: `[start, end)`.
- Never trust the frontend's calculated price or availability.
- Apply idempotency keys to booking creation and payment operations.
- Expire temporary holds automatically through scheduled jobs.
- Prevent double booking by locking the unit or inventory allocation row and checking all overlapping blocking reservations inside the same transaction.
- Verify payment provider webhook signatures and process duplicate events safely.
- Separate reservation status from payment status.

For example, reservation states might include `pending_approval`, `held`, `confirmed`, `checked_in`, `completed`, `cancelled`, and `expired`.

Payment states might include `unpaid`, `pending`, `partially_paid`, `paid`, `refunded`, and `failed`.

# 6. Database Architecture

Use migrations for every schema change rather than manually editing the production database.

## Recommended Core Tables

| Module         | Tables                                                           |
| -------------- | ---------------------------------------------------------------- |
| Identity       | users, user_sessions, password_resets                            |
| Access Control | roles, permissions, role_permissions, user_roles                 |
| Organizations  | organizations, organization_members                              |
| Categories     | space_categories, category_capabilities                          |
| Properties     | properties, property_media, property_amenities                   |
| Units          | rental_units, unit_media, unit_amenities                         |
| Availability   | availability_rules, availability_blocks, reservation_holds       |
| Pricing        | price_plans, price_rules, fees, discounts                        |
| Reservations   | reservations, reservation_items, reservation_events              |
| Rentals        | rental_agreements, tenants, rent_invoices                        |
| Payments       | payments, payment_transactions, refunds, payouts                 |
| Subscriptions  | subscription_plans, subscriptions, subscription_invoices         |
| Communication  | conversations, messages, notifications                           |
| Reviews        | reviews, review_replies                                          |
| Operations     | maintenance_requests, disputes, audit_logs                       |
| System         | system_settings, feature_flags, webhook_events, idempotency_keys |

### Critical database relationships

```

users
  ├── organization_members
  │     └── organizations
  │           ├── properties
  │           │     └── rental_units
  │           │           ├── availability_rules
  │           │           ├── availability_blocks
  │           │           ├── price_plans
  │           │           └── reservation_items
  │           └── subscriptions
  │
  ├── reservations
  │     ├── reservation_items
  │     ├── payments
  │     └── reservation_events
  │
  ├── reviews
  ├── conversations
  └── notifications

space_categories
  ├── category_capabilities
  └── properties

```

### Important database standards

Every transactional table should use appropriate primary keys, foreign keys, and indexes. Monetary amounts should use `DECIMAL` or integer minor units, never floating-point arithmetic. Financial records should retain immutable transaction references.

Every organization-scoped query must enforce its organization boundary, and every administrative action must pass authorization checks. A simple `organization_id` field alone is not sufficient security.

# 7. Frontend Code Architecture

For the public marketplace and the two dashboards, use reusable components and organized JavaScript modules.

```

public/assets/js/
│
├── core/
│   ├── api-client.js
│   ├── csrf.js
│   ├── auth.js
│   ├── alerts.js
│   ├── forms.js
│   ├── pagination.js
│   └── utilities.js
│
├── components/
│   ├── property-card.js
│   ├── availability-calendar.js
│   ├── image-gallery.js
│   ├── map-view.js
│   ├── price-summary.js
│   ├── search-filters.js
│   └── booking-modal.js
│
├── marketplace/
│   ├── home.js
│   ├── search.js
│   ├── property-details.js
│   ├── checkout.js
│   └── bookings.js
│
├── owner/
│   ├── dashboard.js
│   ├── properties.js
│   ├── units.js
│   ├── calendar.js
│   ├── reservations.js
│   └── financials.js
│
└── admin/
    ├── dashboard.js
    ├── users.js
    ├── categories.js
    ├── subscriptions.js
    └── reports.js

```

### Example AJAX client

`public/assets/js/core/api-client.js`

```

const SpacivoAPI = {
  request(method, url, data = null) {
    const csrf = document.querySelector(
      'meta[name="csrf-token"]'
    )?.content;

    return $.ajax({
      url,
      method,
      data: data ? JSON.stringify(data) : undefined,
      contentType: 'application/json',
      dataType: 'json',
      headers: csrf
        ? { 'X-CSRF-Token': csrf }
        : {},
      xhrFields: {
        withCredentials: true
      }
    });
  },

  get(url) {
    return this.request('GET', url);
  },

  post(url, data) {
    return this.request('POST', url, data);
  }
};

```

The application must also handle request failures, expired sessions, authorization errors, and validation errors consistently. Cookie-based authenticated endpoints require server-side CSRF protection and secure cookie configuration.

# 8. API Architecture

Use a versioned API namespace:

`/api/v1/`

| Method | Endpoint                          | Purpose                    |
| ------ | --------------------------------- | -------------------------- |
| GET    | `/api/v1/categories`              | Available space categories |
| GET    | `/api/v1/properties`              | Search public properties   |
| GET    | `/api/v1/properties/{id}`         | Property details           |
| GET    | `/api/v1/units/{id}/availability` | Available rental periods   |
| POST   | `/api/v1/reservations`            | Create a reservation       |
| GET    | `/api/v1/me/reservations`         | Renter booking history     |
| POST   | `/api/v1/payments/checkout`       | Start payment              |
| GET    | `/api/v1/owner/properties`        | Owner property list        |
| POST   | `/api/v1/owner/units`             | Create rental unit         |
| GET    | `/api/v1/owner/reservations`      | Owner reservations         |
| GET    | `/api/v1/admin/reports`           | Administrator reports      |

The API should use consistent response formats, pagination, validation error structures, authorization policies, and request identifiers.

A future Flutter app can consume the same API without duplicating business logic.

# 9. Step-by-Step Development Plan

The implementation should be divided into independently testable phases. Each phase has a specific deliverable and completion criteria.

Phase 1

Project Foundation

Core setup

- Initialize Git repository and Composer autoloading
- Create application bootstrap, router, and environment configuration
- Implement database connection and migrations
- Build error handling, logging, and reusable UI layouts
- Configure local development and CI

Application boots successfully; database migrations and initial CI checks pass.

Phase 2

Authentication & Permissions

Security foundation

- Registration, login, logout, and password reset
- Secure sessions and authentication policies
- Organization-based RBAC
- Owner onboarding and verification workflow
- Administrator and owner account management

Unauthorized cross-account and cross-organization access is rejected by automated tests.

Phase 3

Categories & Properties

Rental inventory

- Space category management
- Capability configuration
- Property and rental-unit CRUD
- Amenities, photos, location, and listing approval
- Draft, published, suspended, and archived listing states

Verified owners can publish approved listings visible in the marketplace.

Phase 4

Public Marketplace

Discovery experience

- Responsive homepage and search results
- Filters, sorting, and pagination
- Mapbox-based map discovery
- Property detail and photo gallery pages
- Favorites and renter profiles

Users can discover and inspect published rentable units across supported categories.

Phase 5

Rental & Booking Engine

Core transactions

- Availability rules and blocked periods
- Hourly, nightly, and monthly booking modes
- Price calculation and quote expiration
- Reservation holds, approval, and cancellation
- Concurrency tests preventing double booking

Booking conflicts, time zones, expiry, and pricing are correctly handled in integration tests.

Phase 6

Payments & Rental Operations

Financial workflows

- Payment gateway adapter and sandbox checkout
- Verified webhooks and reconciliation
- Refunds, deposits, and invoices
- Tenant ledgers and rental agreements
- Receipts and payment history

Test transactions reconcile correctly without duplicate charges or inconsistent booking states.

Phase 7

Owner Management SaaS

Business management

- Owner dashboards and analytics
- Staff permissions and property operations
- Booking calendar and customer management
- Maintenance and announcements
- Subscription limits and billing

Owners can manage their rental business within plan limits and assigned permissions.

Phase 8

Platform Administration

Operational controls

- Moderation and verification
- Subscriptions and commission configuration
- Dispute and refund oversight
- System reporting and audit logs
- Feature flags and category activation

Administrators can operate and audit the platform without direct database intervention.

Phase 9

Quality, Security & Deployment

Production readiness

- Unit, integration, feature, and security tests
- Performance and accessibility checks
- Production environment and secrets configuration
- Backup, recovery, and rollback verification
- GitHub Actions deployment and release checklist

Production release criteria pass and rollback has been tested.

Phase 10

Flutter Mobile Application

Expansion

- Feature-first Flutter application structure
- Mobile authentication and API integration
- Marketplace, maps, and booking screens
- Push notifications and mobile payments
- Android release and testing

Mobile users can complete supported marketplace flows against the production API.

# 10. Loop Engineering Process

For Spacivo, I recommend an autonomous, test-driven development loop with mandatory verification gates.

This is especially useful when using Codex or another coding agent to work through multiple development phases.

The important distinction is that loop engineering should not mean blindly generating code indefinitely. Every iteration must inspect the current project, implement a small amount of work, validate it, and update project progress.

## The Spacivo Engineering Loop

01

INSPECT

Read project state, existing code, tests, and architecture

02

PLAN

Choose the next incomplete task and acceptance criteria

03

IMPLEMENT

Make focused, production-quality code changes

04

VERIFY

Run tests, static analysis, and relevant security checks

05

REPAIR

Diagnose failures and correct implementation defects

06

REVIEW

Check requirements, integration, maintainability, and regressions

07

DOCUMENT

Update progress, architecture decisions, and change logs

08

CONTINUE

Proceed automatically to the next incomplete task

Repeat until all approved phases meet completion criteria

## 10.1 Persistent Engineering State

Create these files inside the repository:

```

docs/engineering/
├── MASTER_PLAN.md
├── PHASE_TRACKER.md
├── TASK_QUEUE.md
├── CURRENT_STATE.md
├── DECISIONS.md
├── BLOCKERS.md
├── TEST_REPORT.md
└── CHANGELOG.md

```

Their responsibilities are:

| File               | Purpose                                 |
| ------------------ | --------------------------------------- |
| `MASTER_PLAN.md`   | Overall system requirements and phases  |
| `PHASE_TRACKER.md` | Completion status of each phase         |
| `TASK_QUEUE.md`    | Ordered implementation tasks            |
| `CURRENT_STATE.md` | Current working context and next action |
| `DECISIONS.md`     | Architectural decisions and rationale   |
| `BLOCKERS.md`      | Issues requiring external action        |
| `TEST_REPORT.md`   | Validation results                      |
| `CHANGELOG.md`     | Implemented changes                     |

These files allow a coding agent to resume accurately after a session interruption instead of guessing which tasks were completed.

## 10.2 Loop Engineering Rules

1. Complete one small, testable task at a time.
2. Do not mark a task complete merely because files were generated.
3. Verify the actual behavior against acceptance criteria.
4. Run relevant tests after every meaningful change.
5. Do not repeatedly rewrite working modules without justification.
6. Fix newly introduced regressions before progressing.
7. Never bypass security checks just to make tests pass.
8. Never modify production data or deploy to production without authorization.
9. Record external blockers rather than fabricating integrations or credentials.
10. Continue automatically to the next approved task when verification succeeds.

# 11. Master Loop Engineering Prompt for Codex

This is the prompt you can give your coding agent to initialize and progressively implement the entire Spacivo platform.

SPACIVO MASTER ENGINEERING PROMPT

&#x20;Copy prompt

This master prompt instructs the coding agent to audit, plan, implement, test, repair, document, and continue through the approved development phases.

## 12. Example Continuous Development Cycle

Suppose Codex is implementing the booking engine.

Example: Booking Engine

Illustrative

1. Inspect existing reservation, pricing, and availability modules.
2. Create a migration for reservation holds and inventory locking.
3. Implement `AvailabilityService` and the overlap validation logic.
4. Add integration tests for concurrent booking attempts.
5. Run tests and inspect failed cases.
6. Repair race conditions or incorrect time handling.
7. Update the task tracker and mark the task verified.
8. Automatically continue with the pricing or payment integration task.

A coding agent can follow this process without waiting for instructions between every task, but actual continuous execution depends on the tool's session limits and permissions. It cannot guarantee unlimited background operation.

# 13. GitHub Branching and CI/CD Strategy

Use a structured workflow for code development and release.

```

main
│
├── develop
│   ├── feature/authentication
│   ├── feature/organization-rbac
│   ├── feature/property-management
│   ├── feature/rental-capabilities
│   ├── feature/booking-engine
│   ├── feature/payment-integration
│   └── feature/owner-dashboard
│
├── release/v1.0.0
└── hotfix/critical-booking-issue

```

For an individual developer, this can be simplified to short-lived feature branches merged into `main` through pull requests. A permanent `develop` branch is optional.

### CI/CD Workflow

Git Push / Pull Request

Composer Validation + PHP Lint

Unit & Integration Tests

Security & Dependency Checks

Build Release Artifact

Deploy to Staging

Staging Smoke Tests

Approved Production Deployment

For your GitHub Actions deployment, store credentials in GitHub Secrets or protected environments. Production deployment should require approval, run compatible database migrations, preserve persistent storage, and support rollback.

# 14. How to Start the Project

Follow this sequence in your local Windows development environment.

Step 1 — Initialize the repository

```

mkdir spacivo
cd spacivo
git init
composer init
  
```

Step 2 — Create the base directories

```

$folders = @(
  "app/Core",
  "app/Modules",
  "app/Shared",
  "app/Integrations",
  "bootstrap",
  "config",
  "routes",
  "resources/views",
  "public/assets/css",
  "public/assets/js",
  "database/migrations",
  "database/seeders",
  "storage/logs",
  "storage/private",
  "tests/Unit",
  "tests/Integration",
  "docs/engineering",
  ".github/workflows"
)

$folders | ForEach-Object {
    New-Item -ItemType Directory -Force -Path $_
}
  
```

Step 3 — Configure Composer autoloading

Add this to `composer.json` and retain your other Composer settings:

```

{
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  }
}
  
```

Then run:

```

composer dump-autoload
  
```

Step 4 — Initialize environment configuration

Create `.env.example`:

```

APP_NAME=Spacivo
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=spacivo
DB_USER=root
DB_PASSWORD=
  
```

Copy it to `.env`, add `.env` to `.gitignore`, and install/configure an environment loader before using the database connection example.

Step 5 — Start local development

After implementing the front controller and bootstrap:

```

php -S localhost:8000 -t public
  
```

PHP's built-in server is for local development only, not production hosting.

Step 6 — Start the engineering loop

Open the project in Codex, provide the master engineering prompt above, and let it begin with the repository audit and Phase 1 implementation.

# 15. Final Architecture Recommendation

SPACIVO v1 Architecture

| Application style | Modular monolith                 |
| ----------------- | -------------------------------- |
| Backend pattern   | MVC + Services + Repositories    |
| Primary backend   | PHP + MySQLi                     |
| Frontend          | Bootstrap + jQuery/AJAX          |
| Database          | MySQL / MariaDB                  |
| API               | Versioned REST                   |
| Core engine       | Capability-driven rental system  |
| Security          | RBAC + organization isolation    |
| Engineering       | Continuous task/test/review loop |
| Mobile expansion  | Flutter                          |

The priority is to build the universal rental engine correctly from the beginning. That is what will allow Spacivo to evolve from residential rentals into a marketplace for hotels, event spaces, sports facilities, parking, commercial units, and future rental categories without redesigning the entire backend.

Your first implementation milestone should be a working foundation with authentication, organization-aware permissions, configurable space categories, property listings, and automated tests. Once those foundations are verified, the loop engineering process can progressively implement the remaining modules.
