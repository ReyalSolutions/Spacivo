<?php require __DIR__ . '/../layouts/header.php'; $escape = static function ($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }; ?>
<main class="container py-5">
    <h1>Properties and rental units</h1>
    <p>New listings begin as drafts. An administrator must approve each listing before a verified organization can publish it.</p>
    <p><a href="/tenant/?url=organization/index">Back to organizations</a></p>
    <div id="inventory-message" class="alert d-none" role="status"></div>
    <?php if (!$categories): ?><p>No categories are available yet. Ask your administrator to configure a category.</p><?php endif; ?>
    <?php if ($can_manage && $categories): $properties[] = ['id' => 0, 'version' => 0, 'category_id' => 0, 'name' => '', 'description' => '', 'address' => '', 'timezone' => 'Asia/Singapore', 'latitude' => '', 'longitude' => '', 'state' => 'draft', 'approval_status' => 'pending', 'units' => []]; endif; ?>
    <?php foreach ($properties as $property): $editable = $can_manage && !in_array($property['state'], ['archived', 'suspended'], true); ?>
    <section class="card card-body mb-4">
        <h2 class="h4"><?= $property['id'] ? $escape($property['name']) : 'Add a property' ?></h2>
        <?php if ($property['id']): ?><p>Status: <?= $escape($property['state']) ?> · Review: <?= $escape($property['approval_status']) ?></p><?php endif; ?>
        <?php if ($editable): ?>
        <form class="inventory-form" data-path="owner/properties<?= $property['id'] ? '/' . (int)$property['id'] : '' ?>" data-method="<?= $property['id'] ? 'PATCH' : 'POST' ?>">
            <input type="hidden" name="version" value="<?= (int)$property['version'] ?>">
            <?php foreach (['name' => 'Property name', 'address' => 'Address', 'timezone' => 'Timezone', 'latitude' => 'Latitude (optional)', 'longitude' => 'Longitude (optional)'] as $key => $label): ?>
            <label class="form-label d-block"><?= $label ?><input class="form-control" name="<?= $key ?>" value="<?= $escape($property[$key]) ?>" <?= in_array($key, ['latitude','longitude'], true) ? '' : 'required' ?>></label>
            <?php endforeach; ?>
            <label class="form-label d-block">Description<textarea class="form-control" name="description" required><?= $escape($property['description']) ?></textarea></label>
            <label class="form-label d-block">Primary category<select class="form-select" name="category_id" required>
            <?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)$category['id'] === (int)$property['category_id'] ? 'selected' : '' ?>><?= $escape($category['name']) ?></option><?php endforeach; ?>
            </select></label>
            <button class="btn btn-primary" type="submit">Save property</button>
        </form>
        <?php else: ?><p><?= $escape($property['address']) ?></p><p><?= $escape($property['description']) ?></p><?php endif; ?>
        <?php if ($property['id']): ?>
        <h3 class="h5 mt-4">Rental units</h3>
        <?php $units = $property['units']; if ($editable && $categories) { $units[] = ['id' => 0, 'version' => 0, 'name' => '', 'capacity' => 1, 'category_id' => $property['category_id'], 'state' => 'active']; } ?>
        <?php foreach ($units as $unit): ?>
            <?php if ($editable && $unit['state'] === 'active'): ?>
            <form class="inventory-form border rounded p-3 mb-3" data-path="owner/properties/<?= (int)$property['id'] ?>/units<?= $unit['id'] ? '/' . (int)$unit['id'] : '' ?>" data-method="<?= $unit['id'] ? 'PATCH' : 'POST' ?>">
                <input type="hidden" name="version" value="<?= (int)$unit['version'] ?>">
                <label class="form-label">Unit name<input class="form-control" name="name" value="<?= $escape($unit['name']) ?>" required></label>
                <label class="form-label">Capacity<input class="form-control" type="number" min="1" max="100000" name="capacity" value="<?= (int)$unit['capacity'] ?>" required></label>
                <label class="form-label">Category<select class="form-select" name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)$category['id'] === (int)$unit['category_id'] ? 'selected' : '' ?>><?= $escape($category['name']) ?></option><?php endforeach; ?></select></label>
                <button class="btn btn-primary" type="submit"><?= $unit['id'] ? 'Save unit' : 'Add unit' ?></button>
            </form>
            <?php if ($unit['id']): ?><form class="inventory-form mb-3" data-path="owner/properties/<?= (int)$property['id'] ?>/units/<?= (int)$unit['id'] ?>" data-method="DELETE"><input type="hidden" name="version" value="<?= (int)$unit['version'] ?>"><button class="btn btn-outline-danger" type="submit">Archive <?= $escape($unit['name']) ?></button></form><?php endif; ?>
            <?php else: ?><p><?= $escape($unit['name']) ?> · <?= (int)$unit['capacity'] ?> guests · <?= $escape($unit['state']) ?></p><?php endif; ?>
        <?php endforeach; ?>
        <?php if ($editable): ?>
        <form class="inventory-form" data-path="owner/properties/<?= (int)$property['id'] ?>/state" data-method="POST">
            <input type="hidden" name="version" value="<?= (int)$property['version'] ?>">
            <label class="form-label">Listing status<select class="form-select" name="state"><option value="draft">Draft</option><option value="published">Publish approved listing</option><option value="archived">Archive</option></select></label>
            <button class="btn btn-outline-primary" type="submit">Update status</button>
        </form>
        <?php endif; endif; ?>
    </section>
    <?php endforeach; ?>
</main>
<?php require __DIR__ . '/script.php'; require __DIR__ . '/../layouts/footer.php'; ?>
