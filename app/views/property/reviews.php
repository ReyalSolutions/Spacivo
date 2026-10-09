<?php require __DIR__ . '/../layouts/header.php'; ?>
<main class="container py-5">
    <h1>Listing review</h1>
    <div id="inventory-message" class="alert d-none" role="status"></div>
    <?php if (!$properties): ?><p>No listings are awaiting review.</p><?php endif; ?>
    <?php foreach ($properties as $property): ?>
    <section class="card card-body mb-3">
        <h2 class="h5"><?= htmlspecialchars($property['name'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p><?= htmlspecialchars($property['address'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><?= htmlspecialchars($property['description'], ENT_QUOTES, 'UTF-8') ?></p>
        <p>Status: <?= htmlspecialchars($property['state'], ENT_QUOTES, 'UTF-8') ?> · Review: <?= htmlspecialchars($property['approval_status'], ENT_QUOTES, 'UTF-8') ?></p>
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
    </section>
    <?php endforeach; ?>
</main>
<?php require __DIR__ . '/script.php'; require __DIR__ . '/../layouts/footer.php'; ?>
