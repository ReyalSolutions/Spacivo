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
