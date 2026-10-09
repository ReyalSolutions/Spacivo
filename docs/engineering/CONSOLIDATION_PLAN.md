# Shared admin management pages — proposal

Status: incremental changes explicitly authorized by the user. Listings migration implemented and locally verified; remaining features still pending. Database grants are unchanged.

## Requested result

One canonical admin page and handler for each feature, used by both administrator and owner. Permissions control page visibility and each create/edit/delete/approve/export action. Owners retain account/organization data boundaries even if an action permission is granted.

## Current evidence

Separate AdminController and OwnerController files and app/views/admin and app/views/owner folders still exist. The presentation components and theme are already shared. Physical admin/*.php pages are another implementation. Owner grants are mostly legacy aggregate permissions; granular add/edit/delete grants are absent. Enabling exact action checks without a reviewed grant migration would therefore remove some current access.

## Concrete action proposal

management-permissions-proposal.json enumerates every public legacy AdminController and OwnerController action and its proposed existing permission-table slug. No new permission names or automatic role grants are introduced. This is a reviewed proposal, not an enabled authorization policy. Existing broad permissions and proposed granular grants must be reconciled explicitly before switching production behavior.

## Incremental implementation and acceptance

1. Consolidate one feature at a time under the canonical admin pages, starting with houses. Keep legacy URLs as compatibility delegates until passing verification.
2. Retain owner-scoped repository queries and platform-wide queries as explicit scope choices. Do not expose global admin queries to owners merely because they share a page.
3. Apply the action mapping to PHP handlers and corresponding UI controls; a hidden button is never the sole access check. Preserve CSRF checks for writes.
4. Verify authenticated permission revocation on the next request, read access without write access, denied direct calls, cross-owner isolation and unchanged-plan renewal behavior in disposable databases.
5. Verify real pages and assets for both roles, update engineering records, publish and pass CI for each feature before removing its obsolete implementation.
6. Repeat for bookings, rooms, tenants, payments, subscriptions, account/role administration, catalogs, reporting and settings. Organization/inventory API scopes remain governed by their existing organization permission model.

## Approval-review limitation

Automatic approval review rejected the bulk controller/view replacement and the router-wide permission gate because their broad unverified mapping could break access or owner boundaries. These proposals are not applied. Explicit approval is required to proceed with the reviewed consolidation and authorization changes; no database grants will be automatically broadened.

## First verified feature: listings

User explicitly authorized incremental shared admin/owner implementations, with admin seeing all listings and owners seeing only their own. First feature completed: canonical app/views/admin/houses.php serves both roles. Physical admin/houses.php and legacy owner/houses delegate to it; the obsolete owner listing view is removed. Existing owner-scoped write handlers remain as compatibility delegates while canonical admin action URLs reuse them. Each listing create/edit/delete/photo/amenity action checks its exact permission-table slug; moderation requires admin role and approve_houses. Ownership checks remain unchanged; shared visibility does not grant global editing authority. Missing granular owner grants are not automatically broadened. Read-only listing users cannot mutate listings, and revocation applies on the next request. Shared modals preserve editing/upload functionality for authorized owned records. Other feature controllers/views remain separate pending their own migration gates.

Verification: 198 PHP files linted without errors, 29 foundation/mail assertions and 227 disposable database/HTTP assertions passed. Includes all-owner admin data, owner filter tampering, all three shared entry routes, direct write denial, edit permission with foreign property rejection, and permission revocation. No live rows or role grants changed. Hosted gate follows publication. Browser visual automation remains unavailable as previously recorded.
