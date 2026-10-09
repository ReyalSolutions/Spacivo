# Migration policy

Each versioned PHP file returns a callable accepting a MySQLi connection. Use prepared statements for data operations. Files run in filename order, are checksum verified, and are serialized using a database advisory lock.

MySQL DDL auto-commits: migrations must be restartable. Do not claim transactional DDL rollback. Back up and verify recovery before releasing schema changes.

001_legacy_baseline.php installs the structure captured and verified from the active 19-table database, or adopts an exactly matching existing schema while preserving rows. Its associated JSON definition is checksum protected. Different definitions are rejected before missing tables are created. No user records or sample accounts are imported.

002_organizations.php adds organizations and scoped member permissions. 003_password_recovery.php adds hashed reset tokens and credential versions. Both were tested on disposable databases and explicitly approved/applied locally on 2026-10-09. Existing SQL dumps remain historical snapshots and are excluded from Git.
