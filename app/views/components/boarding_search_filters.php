<?php
// Reusable search + price filter form for boarding listings.
// Expected variables:
// - $filters: array with keys q, min_price, max_price (strings or null)
// - $route: string (e.g. 'boarding/index')
?>
<form method="GET" action="/tenant/" class="filters-form" autocomplete="off">
    <input type="hidden" name="url" value="<?= htmlspecialchars((string)$route, ENT_QUOTES, 'UTF-8') ?>">

    <div class="filters-grid">
        <div class="field">
            <label>Search (name/address)</label>
            <input type="text" name="q" placeholder="e.g. Cebu" value="<?= htmlspecialchars((string)($filters['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="field">
            <label>Min price</label>
            <input type="number" name="min_price" placeholder="0" value="<?= htmlspecialchars((string)($filters['min_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="field">
            <label>Max price</label>
            <input type="number" name="max_price" placeholder="1000" value="<?= htmlspecialchars((string)($filters['max_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
    </div>

    <div class="filters-actions">
        <button class="btn primary" type="submit">Search</button>
        <a class="btn" href="/tenant/?url=<?= htmlspecialchars((string)$route, ENT_QUOTES, 'UTF-8') ?>">Reset</a>
    </div>
</form>

