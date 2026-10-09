<?php require __DIR__ . '/../layouts/management_header.php'; ?>
<main class="module-page">
    <div class="module-header"><div><h1>Space categories</h1><p>Choose the rental modes and services supported by each kind of space.</p></div><div class="module-actions"><a class="btn btn-outline-primary" href="/tenant/?url=organization/index">Organizations</a></div></div>
    <div id="category-message" class="alert d-none" role="status"></div>
    <?php $categories = array_merge([['id' => 0, 'version' => 0, 'name' => '', 'slug' => '', 'active' => false, 'capabilities' => []]], $categories); ?>
    <?php foreach ($categories as $category): ?>
    <section class="module-card">
        <details <?= $category['id'] ? '' : 'open' ?>><summary><div><h2 class="h4"><?= $category['id'] ? htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') : 'Add a category' ?></h2><?php if ($category['id']): ?><span class="module-status"><?= $category['active'] ? 'Available for listings' : 'Inactive' ?></span><?php endif; ?></div></summary><div class="module-card-body">
        <form class="category-form" data-id="<?= (int)$category['id'] ?>">
            <input type="hidden" name="version" value="<?= (int)$category['version'] ?>">
            <label class="form-label">Category name<input class="form-control" name="name" maxlength="100" required value="<?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>"></label>
            <label class="form-label">Category code<input class="form-control" name="slug" maxlength="80" pattern="[a-z][a-z0-9_]{1,79}" required value="<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>"></label>
            <fieldset class="mb-3"><legend class="h6">Supported capabilities</legend>
            <?php foreach (App\Modules\Categories\Services\CategoryConfiguration::CAPABILITIES as $code => $label): ?>
                <label class="d-block"><input type="checkbox" name="capabilities" value="<?= $code ?>" <?= !empty($category['capabilities'][$code]) ? 'checked' : '' ?>> <?= $label ?></label>
            <?php endforeach; ?>
            </fieldset>
            <label class="module-wide d-block mb-3"><input type="checkbox" name="active" <?= $category['active'] ? 'checked' : '' ?>> Available for listings</label>
            <button class="btn btn-primary" type="submit">Save category</button>
        </form>
        </div></details>
    </section>
    <?php endforeach; ?>
</main>
<script>
document.querySelectorAll('.category-form').forEach(form => form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = form.querySelector('button');
    const message = document.getElementById('category-message');
    button.disabled = true;
    const capabilities = {};
    form.querySelectorAll('[name="capabilities"]').forEach(input => capabilities[input.value] = input.checked);
    const id = Number(form.dataset.id);
    try {
        const response = await fetch('/tenant/api/v1/admin/categories' + (id ? '/' + id : ''), {
            method: id ? 'PATCH' : 'POST', credentials: 'same-origin',
            headers: {'Content-Type': 'application/json', 'X-CSRF-Token': <?= json_encode(Csrf::token()) ?>},
            body: JSON.stringify({name: form.elements.name.value, slug: form.elements.slug.value,
                version: Number(form.elements.version.value), active: form.elements.active.checked, capabilities})
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to save category.');
        Feedback.fire({icon:'success', title:'Saved', text:result.message || 'Your changes were saved.', timer:1500}).then(function(){window.location.reload();});
    } catch (error) {
        ToastStack.error(error.message); button.disabled = false;
    }
}));
</script>
<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
