<script>
document.querySelectorAll('.inventory-form').forEach(form => form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = form.querySelector('button'); button.disabled = true;
    const formData = new FormData(form);
    const input = Object.fromEntries(formData);
    input.organization_id = Number(input.organization_id || <?= (int)($organization_id ?? 0) ?>);
    ['version', 'category_id', 'capacity'].forEach(key => { if (key in input) input[key] = Number(input[key]); });
    if (form.dataset.path.endsWith('/amenities')) { input.amenities = formData.getAll('amenities[]').map(Number); delete input['amenities[]']; }
    const upload = form.dataset.upload === 'true';
    formData.set('organization_id', String(input.organization_id));
    try {
        const response = await fetch('/tenant/api/v1/' + form.dataset.path, {method: form.dataset.method, credentials: 'same-origin',
            headers: upload ? {'X-CSRF-Token': <?= json_encode(Csrf::token()) ?>} : {'Content-Type': 'application/json', 'X-CSRF-Token': <?= json_encode(Csrf::token()) ?>}, body: upload ? formData : JSON.stringify(input)});
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to save listing.');
        window.location.reload();
    } catch (error) {
        const message = document.getElementById('inventory-message');
        message.className = 'alert alert-danger'; message.textContent = error.message; button.disabled = false;
    }
}));
</script>
