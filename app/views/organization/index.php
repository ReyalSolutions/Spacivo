<?php require __DIR__ . '/../layouts/management_header.php'; ?>
<main class="container py-5">
    <h1>Organizations</h1>
    <p>Manage your business accounts and staff access.</p>
    <?php if ($platform_role === 'admin' && getenv('CATEGORIES_ENABLED') === 'true'): ?>
    <p><a href="/tenant/?url=category/index">Manage space categories</a></p>
    <?php endif; ?>
    <div id="organization-message" class="alert d-none" role="status"></div>
    <?php if ($platform_role === 'owner'): ?>
        <section class="card card-body mb-4">
            <h2 class="h4">Create an organization</h2>
            <form class="organization-form" action="/tenant/?url=organization/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <label class="form-label" for="organization-name">Business name</label>
                <input class="form-control mb-3" id="organization-name" name="name" maxlength="150" required>
                <button class="btn btn-primary" type="submit">Create organization</button>
            </form>
        </section>
    <?php endif; ?>
    <?php if (!$organizations): ?><p>You do not have an active organization membership yet.</p><?php endif; ?>
    <?php foreach ($organizations as $organization): ?>
        <section class="card card-body mb-4">
            <h2 class="h4"><?= htmlspecialchars($organization['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Verification: <?= htmlspecialchars(ucfirst($organization['verification_status']), ENT_QUOTES, 'UTF-8') ?> · Your role: <?= htmlspecialchars(ucfirst($organization['role']), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (getenv('INVENTORY_ENABLED') === 'true'): ?><p><a href="/tenant/?url=property/index&amp;organization_id=<?= (int)$organization['id'] ?>">Properties and rental units</a></p><?php endif; ?>
            <?php if ((int)$organization['owner_user_id'] === (int)$_SESSION['user_id']): ?>
                <form class="organization-form" action="/tenant/?url=organization/add_member" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="organization_id" value="<?= (int)$organization['id'] ?>">
                    <input type="hidden" name="permissions[]" value="organization.read">
                    <?php if (getenv('INVENTORY_ENABLED') === 'true'): ?>
                    <fieldset class="mb-3"><legend class="h6">Inventory access</legend><label class="d-block"><input type="checkbox" name="permissions[]" value="inventory.read"> View properties and units</label><label class="d-block"><input type="checkbox" name="permissions[]" value="inventory.manage"> Manage properties and units</label></fieldset>
                    <?php endif; ?>
                    <label class="form-label" for="member-email-<?= (int)$organization['id'] ?>">Staff account email</label>
                    <input class="form-control mb-3" id="member-email-<?= (int)$organization['id'] ?>" type="email" name="email" required>
                    <label class="form-label" for="member-role-<?= (int)$organization['id'] ?>">Staff role</label>
                    <select class="form-select mb-3" id="member-role-<?= (int)$organization['id'] ?>" name="role"><option value="staff">Staff</option><option value="manager">Manager</option></select>
                    <p class="text-muted">The account must already exist. New members receive permission to view this organization.</p>
                    <button class="btn btn-outline-primary" type="submit">Assign staff access</button>
                </form>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    <?php if ($platform_role === 'admin'): ?>
        <?php if (getenv('INVENTORY_ENABLED') === 'true'): ?><p><a href="/tenant/?url=property/index">Review property listings</a></p><?php endif; ?>
        <section>
            <h2 class="h4">Pending owner verification</h2>
            <?php if (!$pending_verifications): ?><p>No organizations are awaiting verification.</p><?php endif; ?>
            <?php foreach ($pending_verifications as $organization): ?>
                <div class="card card-body mb-3">
                    <h3 class="h5"><?= htmlspecialchars($organization['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <form class="organization-form" action="/tenant/?url=organization/verify" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="organization_id" value="<?= (int)$organization['id'] ?>">
                        <label class="form-label" for="verify-<?= (int)$organization['id'] ?>">Decision</label>
                        <select class="form-select mb-3" id="verify-<?= (int)$organization['id'] ?>" name="decision"><option value="verified">Verify</option><option value="rejected">Reject</option></select>
                        <button class="btn btn-primary" type="submit">Save decision</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<script>
document.querySelectorAll('.organization-form').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const message = document.getElementById('organization-message');
        const button = form.querySelector('button');
        button.disabled = true;
        try {
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), credentials: 'same-origin'});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Unable to save your changes.');
            window.location.reload();
        } catch (error) {
            message.className = 'alert alert-danger';
            message.textContent = error.message;
            button.disabled = false;
        }
    });
});
</script>
<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
