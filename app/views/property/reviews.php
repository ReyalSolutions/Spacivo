<?php require __DIR__ . '/../layouts/management_header.php'; ?>
<main class="module-page">
    <div class="module-header"><div><h1>Listing review</h1><p>Review property details, rental units and photos before approving a listing.</p></div><div class="module-actions"><a class="btn btn-outline-primary" href="/tenant/?url=organization/index">Organizations</a></div></div>
    <div id="inventory-message" class="alert d-none" role="status"></div>
    <?php if (!$properties): ?><div class="module-empty">No listings are awaiting review.</div><?php endif; ?>
    <?php foreach ($properties as $property): ?>
    <section class="module-card">
        <details><summary><div><h2 class="h5"><?= htmlspecialchars($property['name'], ENT_QUOTES, 'UTF-8') ?></h2><span class="module-status"><?= htmlspecialchars(ucfirst($property['approval_status']), ENT_QUOTES, 'UTF-8') ?></span></div></summary><div class="module-card-body">
        <p><?= htmlspecialchars($property['address'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><?= htmlspecialchars($property['description'], ENT_QUOTES, 'UTF-8') ?></p>
        <p>Status: <?= htmlspecialchars($property['state'], ENT_QUOTES, 'UTF-8') ?> · Review: <?= htmlspecialchars($property['approval_status'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if (isset($property['metadata'])): $metadata = $property['metadata']; $metadata_unit = 0; $metadata_editable = false; $metadata_admin = true; require __DIR__ . '/metadata.php'; endif; ?>
        <?php foreach ($property['units'] as $unit): if (isset($unit['metadata'])): $metadata = $unit['metadata']; $metadata_unit = (int)$unit['id']; $metadata_editable = false; require __DIR__ . '/metadata.php'; endif; endforeach; ?>
        <ul><?php foreach ($property['units'] as $unit): ?><li><?= htmlspecialchars($unit['name'], ENT_QUOTES, 'UTF-8') ?> · <?= (int)$unit['capacity'] ?> guests · <?= htmlspecialchars($unit['state'], ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
        <?php if ($property['state'] === 'draft'): ?>
        <form class="inventory-form" data-path="admin/properties/<?= (int)$property['id'] ?>/review" data-method="POST">
            <input type="hidden" name="organization_id" value="<?= (int)$property['organization_id'] ?>">
            <input type="hidden" name="version" value="<?= (int)$property['version'] ?>">
            <label class="form-label">Decision<select class="form-select" name="decision"><option value="approved">Approve</option><option value="rejected">Reject</option></select></label>
            <button class="btn btn-primary" type="submit">Save decision</button>
        </form>
        <?php endif; ?>
        <form class="inventory-form mt-3" data-path="admin/properties/<?= (int)$property['id'] ?>/moderate" data-method="POST">
            <input type="hidden" name="organization_id" value="<?= (int)$property['organization_id'] ?>">
            <input type="hidden" name="version" value="<?= (int)$property['version'] ?>">
            <input type="hidden" name="state" value="<?= $property['state'] === 'suspended' ? 'draft' : 'suspended' ?>">
            <button class="btn btn-outline-danger" type="submit"><?= $property['state'] === 'suspended' ? 'Return to draft for review' : 'Suspend listing' ?></button>
        </form>
        </div></details>
    </section>
    <?php endforeach; ?>
</main>
<?php require __DIR__ . '/script.php'; require __DIR__ . '/../layouts/management_footer.php'; ?>
