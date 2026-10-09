# StayHub (Capstone Documentation)

## Goal

Build a custom PHP MVC (no framework) web system following `features.md` and `structure.md`:

- Tenants can register/login, browse boarding houses, search/filter, view details, favorite, book/reserve rooms, pay online (simulated), track booking status, leave reviews, and chat with owners.
- Owners can register/subscribe, manage boarding houses and rooms, approve/reject reservations, view earnings/occupancy (basic), and message tenants.
- Admin can manage users/owners, manage subscription plans, verify boarding houses, monitor transactions, and view reports/analytics (basic).

## Capstone Log

This file is updated after each implementation step.

### Step 1 — Project scaffolding (MVC + routing)

- Created the MVC folder structure under `app/` and the `public/` assets folders.
- Added initial entrypoints:
  - `public/index.php` (bootstrap + router dispatch)
  - root `index.php` (thin wrapper to keep `/tenant/` working)
- Added base infrastructure:
  - `config/database.php` (MySQLi connection)
  - `app/core/Router.php` (simple controller/method routing using `?url=...`)
  - `app/core/BaseController.php` (render, JSON responses, role guards)
  - `app/helpers/Csrf.php` (CSRF token utilities)

At this point the app can load and dispatch requests to `app/controllers/*Controller.php` using the `?url=controller/method` convention.

### Step 2 — MVP feature wiring (Auth, Browse, Booking, Favorites)

- Added models for core MVP data access:
  - `app/models/User.php`, `app/models/BoardingHouse.php`
  - `app/models/Favorite.php`, `app/models/Booking.php`, `app/models/Payment.php`
- Added controllers + AJAX endpoints:
  - `AuthController` for `auth/login`, `auth/register`, `auth/logout`
  - `BoardingHouseController` for `boarding/index`, `boarding/show`
  - `BookingController@store` (AJAX POST) to create a pending booking + pending payment
  - `PaymentController@checkout` (renders) + `PaymentController@simulate` (AJAX POST to simulate payment success)
  - `FavoritesController@toggle` (AJAX POST) to add/remove favorites
  - `TenantController`, `OwnerController`, `AdminController` for dashboard/bookings/favorites MVP pages
- Added views + UI:
  - Auth pages: `app/views/auth/*`
  - Browse pages: `app/views/boarding/*` (Leaflet map + room listing)
  - Tenant pages: `app/views/tenant/*`
  - Owner pages: `app/views/owner/*`
  - Payment checkout page: `app/views/payment/checkout.php`
- Added UI assets:
  - `public/assets/css/style.css`
  - `public/assets/js/app.js` (AJAX booking + favorites handlers)

Note: the bootstrap (`public/index.php`) now also loads `app/core/BaseController.php` so controllers extending it can run correctly.

### Step 3 — Database schema (MVP tables)

- Added `database.sql` with MySQL tables that match the current PHP models:
  - `users`, `boarding_houses`, `rooms`
  - `bookings`, `payments`
  - `favorites`
  - `plans`, `subscriptions` (scaffolding for next milestones)

### Step 4 — Seed data for testing

- Added `seed.sql` with:
  - 3 demo users (`admin@example.com`, `owner@example.com`, `tenant@example.com`) with password `password123`
  - 2 approved boarding houses + 3 rooms for immediate browsing/testing

### Step 5 — Reservation approval flow fix

- Updated simulated payment behavior so it only marks `payments.status = paid` and keeps `bookings.status = pending`.
- Booking approval (updating room availability + setting booking to `approved`) is handled by the owner via `OwnerController` approval actions.

### Step 6 — Routing bug fix (boarding controller alias)

- Added `app/controllers/BoardingController.php` so `?url=boarding/index` correctly resolves to a controller class.
- `BoardingController` renders the same `boarding/index` and `boarding/show` views using your existing `BoardingHouse` model.

### Step 7 — UI enhancement (reusable boarding listings design)

- Enhanced `boarding/index` design with a modern hero + two-panel layout (list + map).
- Extracted reusable components:
  - `app/views/components/boarding_search_filters.php`
  - `app/views/components/boarding_house_item.php`
- Added reusable styling utilities/classes in `public/assets/css/style.css` (hero, panels, listing items, map container, empty state).

### Step 8 — Semi OOP + MVC backbone cleanup

- Added a lightweight class autoloader: `app/core/Autoloader.php`.
- Added a simple database service (no more `$GLOBALS['db']`): `app/core/Database.php`.
- Updated bootstrap to use autoload: `public/index.php`.
- Updated `BaseController` to provide `db()` and fix login redirect to `/tenant/`.
- Updated controllers to use `$this->db()` instead of `$GLOBALS['db']`.
- Updated `Router` to rely on autoload (removed manual controller `require_once`).

Notes for setup:

- Database credentials are now in `app/core/Database.php` (or via env vars `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).

### Step 9 — StayHub Branding & Design Iteration

- Rebranded the system to **StayHub**.
- Implemented a modern Light Mode aesthetic with Blue Gradients (`#2563eb` to `#0ea5e9`).
- Added a premium brand logo to the hero sections.

### Step 10 — Registration Overhaul (Multi-step + Full Width)

- **Database Schema Migration**:
  - Added `roles` table (Normalization).
  - Updated `users` table: Split `name` into `first_name`, `middle_name`, and `last_name`, added `phone`, and linked via `role_id`.
- **UI/UX Enhancement**:
  - Set registration page to occupy the **full width** of the viewport.
  - Redesigned the form to be a multi-step experience (Step 1: Identity & Role).
  - Added fields for First Name, **Middle Name**, Last Name, and Phone Number.
  - Clarified role selection label to "**Who are you registering as?**".
  - Removed Password field (moved to Step 2).
  - Implemented a three-column grid for Name fields.
  - Fixed layout constraints to allow proper column splitting and page scrolling.
