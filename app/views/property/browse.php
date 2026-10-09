<?php require __DIR__ . '/../layouts/header.php'; ?>
<main class="container py-5">
    <h1>Find your next space</h1>
    <p>Explore spaces published by verified businesses.</p>
    <?php if (!$properties): ?><p>No spaces have been published yet. Please check back soon.</p><?php endif; ?>
    <div class="row g-4">
    <?php foreach ($properties as $property): ?>
    <div class="col-12 col-md-6 col-lg-4">
        <article class="card h-100">
            <?php if (!empty($property['cover_photo_id'])): ?><img class="card-img-top" style="height:220px;object-fit:cover" alt="<?= htmlspecialchars($property['name'], ENT_QUOTES, 'UTF-8') ?>" src="/tenant/api/v1/properties/<?= (int)$property['id'] ?>/photos/<?= (int)$property['cover_photo_id'] ?>"><?php endif; ?>
            <div class="card-body">
                <h2 class="h5"><?= htmlspecialchars($property['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($property['address'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="mb-0"><?= htmlspecialchars($property['description'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </article>
    </div>
    <?php endforeach; ?>
    </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
