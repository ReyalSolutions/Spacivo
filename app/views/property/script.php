<script>
document.querySelectorAll('.inventory-form').forEach(form => form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = form.querySelector('button'); button.disabled = true;
    const input = Object.fromEntries(new FormData(form));
    input.organization_id = Number(input.organization_id || <?= (int)($organization_id ?? 0) ?>);
    ['version', 'category_id', 'capacity'].forEach(key => { if (key in input) input[key] = Number(input[key]); });
    try {
        const response = await fetch('/tenant/api/v1/' + form.dataset.path, {method: form.dataset.method, credentials: 'same-origin',
            headers: {'Content-Type': 'application/json', 'X-CSRF-Token': <?= json_encode(Csrf::token()) ?>}, body: JSON.stringify(input)});
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to save listing.');
        window.location.reload();
    } catch (error) {
        const message = document.getElementById('inventory-message');
        message.className = 'alert alert-danger'; message.textContent = error.message; button.disabled = false;
    }
}));
</script>
