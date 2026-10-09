# Architecture decisions

## 2026-10-09: Incremental migration

architecture.md supersedes older structure.md/documentation.md for new work. Preserve global MVC classes and ?url actions while adding App namespaces. Map App\Core to existing lowercase app/core explicitly for Linux compatibility. Do not rewrite working modules before regression coverage exists.

## Runtime gate

Require PHP 8.2+ for the platform. Foundation code remains compatible with installed PHP 7.4 to verify legacy behavior during transition. These checks do not waive the architecture gate; migration CLI rejects runtimes below 8.2.

## Schema safety

Inspect metadata only. Historical SQL snapshots are not authoritative migrations. CLI migration writes require --apply and are serialized/checksum checked. MySQL DDL auto-commits: use restartable migrations and tested recovery rather than claiming DDL transaction rollback.

## Hosting

Keep the local /tenant root wrapper during transition and deny private directories/diagnostics with Apache rules. Production document root must be public/. Existing absolute /tenant links require migration before root-domain hosting.

## Integration boundaries

Exclude the independent reyal_solutions project. Existing role IDs/boarding-house ownership do not replace organization policies. Do not fabricate gateway/Mapbox/Firebase credentials or claim simulations as verified integrations.

## 2026-10-09: User runtime override and verified local features

The user explicitly instructed use of existing PHP 7.4.33. This supersedes the earlier PHP 8.2 gate. Composer requirements, health/migration commands, CI and architecture examples now target PHP 7.4. Composer was installed from a SHA-384 verified official installer inside the workspace.

Capture and verify the actual schema structure, excluding identity counters and all records. Adopt matching existing schemas without rebuilding tables. Keep migration tracking and all new module tables additive; apply only explicitly approved existing-database changes.

Organization roles/grants are separate from platform roles. Platform administrators can explicitly verify an organization but do not automatically become members. Membership grants are owner-controlled. New organization services do not yet port legacy boarding-house records into organization inventory; that belongs to Phase 3.

Local password recovery defaults to a private outbox adapter. The user selected PHPMailer on 2026-10-09; Composer installed 7.1.1 on the existing PHP 7.4 runtime. SMTP delivery uses TLS and configured sender/authentication, with debug disabled and sanitized errors. Production refuses local delivery and incomplete configuration. Actual inbox delivery awaits private SMTP configuration and verification. Reset tokens revoke existing sessions through versioned credentials; role changes refresh from the database on protected requests.

The empty user-supplied GitHub repository is public. Publish reviewed source only; exclude .env, SQL dumps, uploaded user media, diagnostics, dependencies/cache and the separate nested application.

Inventory photos are private raster files, reencoded with GD and served through permission/visibility checks. No draft or suspended photo is served directly from public uploads. Metadata edits invalidate listing approval. Existing legacy inventory stays intact; organization ownership is never inferred or fabricated from old records.


## 2026-10-09 — Shared management interface

Admin and owner share one presentation shell (admin/components with MVC management_header/footer adapters). Role and permission checks control navigation; existing server authorization and ownership boundaries remain authoritative. Keep active legacy business pages during phased replacement; remove superseded presentation code only after reference checks.


## 2026-10-09 — Paid owner subscription access

Derive legacy owner access from the shared subscription model: active first-period assignments and settled, owner/subscription/plan/cycle-matched renewals cover one period. An expired stored flag can be reconciled by a current paid renewal, but pending/cancelled states, explicit end dates and overdue periods remain enforced. Duplicate receipts do not stack paid periods. Same-plan renewals must succeed when an authorized row exists even if plan fields are unchanged. Billing ledger and stored status are preserved during read checks.


## 2026-10-09 — Unified reference management theme

User selected a new neutral dashboard reference for all admin and owner pages. Added one management-theme stylesheet loaded by their common asset partial: slim gray sidebar, white header/cards, subtle borders, black primary actions, blue accents, compact tables/forms, consistent dialogs and responsive shell. Dashboard-specific welcome banner now uses the same theme. Existing role/organization guards remain unchanged. 215 database/HTTP checks pass. Visual browser automation remains unavailable as previously recorded; no pixel-perfect visual acceptance is claimed.


## 2026-10-09 — Shared listings migration

User explicitly authorized incremental shared admin/owner implementations, with admin seeing all listings and owners seeing only their own. First feature completed: canonical app/views/admin/houses.php serves both roles. Physical admin/houses.php and legacy owner/houses delegate to it; the obsolete owner listing view is removed. Existing owner-scoped write handlers remain as compatibility delegates while canonical admin action URLs reuse them. Each listing create/edit/delete/photo/amenity action checks its exact permission-table slug; moderation requires admin role and approve_houses. Ownership checks remain unchanged; shared visibility does not grant global editing authority. Missing granular owner grants are not automatically broadened. Read-only listing users cannot mutate listings, and revocation applies on the next request. Shared modals preserve editing/upload functionality for authorized owned records. Other feature controllers/views remain separate pending their own migration gates.

Verification: 198 PHP files linted without errors, 29 foundation/mail assertions and 227 disposable database/HTTP assertions passed. Includes all-owner admin data, owner filter tampering, all three shared entry routes, direct write denial, edit permission with foreign property rejection, and permission revocation. No live rows or role grants changed. Hosted gate follows publication. Browser visual automation remains unavailable as previously recorded.

## 2026-10-09 — Action permissions and shared skeleton loading

The canonical role editor manages database-backed action grants. Every declared admin/owner controller action checks its mapped grants and state-changing requests require CSRF. Physical admin pages delegate to verified handlers and reject legacy direct POST processing. Admin listing visibility and owner account boundaries remain intact. Role synchronization validates IDs before replacing grants. The idempotent CLI seeder preserves existing definitions and role grants; five missing booking approval/rejection and plan-payment editing/printing permissions were added locally without automatic grants.

Shared management assets provide skeleton shimmer loading for same-origin jQuery/fetch requests, concurrent-request tracking, failure cleanup, lazy images and reduced-motion support. Existing management spinner markup and SweetAlert loaders use skeleton bars. Photo uploads retain linear progress. Verification: 202 PHP files linted, 29 foundation assertions, 348 disposable database/HTTP assertions, and Node loader success/failure/concurrency checks passed. Hosted verification follows publication. Browser visual verification remains unavailable.

Automatic review declined broad router/global-authentication and transport monkey-patching proposals. Those proposals were not applied. Feature groups were migrated and verified separately; loading uses ordinary AJAX events and a tested fetch wrapper.