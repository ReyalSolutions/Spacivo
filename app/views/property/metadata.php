<?php
$metadataPrefix = ($metadata_admin ?? false) ? 'admin' : 'owner';
$metadataBase = $metadataPrefix . '/properties/' . (int)$property['id'] . ($metadata_unit ? '/units/' . (int)$metadata_unit : '');
$metadataOrganization = (int)$property['organization_id'];
?>
<div class="my-3">
    <h4 class="h6">Photos and amenities</h4>
    <?php foreach ($metadata['photos'] as $photo): ?>
    <img class="img-thumbnail me-2 mb-2" style="max-width:180px;max-height:140px" alt="Listing photo" src="/tenant/api/v1/<?= $metadataBase ?>/photos/<?= (int)$photo['id'] ?>?organization_id=<?= $metadataOrganization ?>">
    <?php if ($metadata_editable): ?><form class="inventory-form mb-2" data-path="<?= $metadataBase ?>/photos/<?= (int)$photo['id'] ?>" data-method="DELETE"><input type="hidden" name="version" value="<?= (int)$property['version'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Remove photo</button></form><?php endif; ?>
    <?php endforeach; ?>
    <?php if ($metadata_editable): ?>
    <form class="inventory-form mb-3" data-upload="true" data-path="<?= $metadataBase ?>/photos" data-method="POST">
        <input type="hidden" name="version" value="<?= (int)$property['version'] ?>">
        <label class="form-label">Photo (JPEG, PNG or WebP, up to 5 MB)<input class="form-control" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
        <button class="btn btn-outline-primary" type="submit">Upload photo</button>
    </form>
    <form class="inventory-form mb-3" data-path="<?= $metadataBase ?>/amenities" data-method="PUT">
        <input type="hidden" name="version" value="<?= (int)$property['version'] ?>">
        <fieldset><legend class="h6">Amenities</legend>
        <?php $selectedAmenities = array_map('intval', array_column($metadata['amenities'], 'id')); ?>
        <?php foreach ($amenity_options as $amenity): ?><label class="d-block"><input type="checkbox" name="amenities[]" value="<?= (int)$amenity['id'] ?>" <?= in_array((int)$amenity['id'], $selectedAmenities, true) ? 'checked' : '' ?>> <?= htmlspecialchars($amenity['name'], ENT_QUOTES, 'UTF-8') ?></label><?php endforeach; ?>
        </fieldset><button class="btn btn-outline-primary mt-2" type="submit">Save amenities</button>
    </form>
    <?php else: ?>
    <ul><?php foreach ($metadata['amenities'] as $amenity): ?><li><?= htmlspecialchars($amenity['name'], ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
</div>
