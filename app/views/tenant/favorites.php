<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="dash-container">
    <div class="welcome-header">
        <h1><i class="fas fa-heart" style="color: #ff4f6d; margin-right: 12px;"></i>My Favorites</h1>
        <p>Your saved boarding houses for quick and easy access later.</p>
    </div>

    <!-- Stats & Filters Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin: 24px 0 16px; flex-wrap: wrap; gap: 12px;">
        <h2 class="section-title" style="margin: 0;">
            <i class="fas fa-bookmark" style="color: var(--dash-primary);"></i>
            Saved Listings
        </h2>
        <span style="background: var(--dash-primary); color: white; font-size: 0.8rem; font-weight: 800; padding: 6px 14px; border-radius: 20px;">
            <?= count($favorites) ?> saved
        </span>
    </div>

    <!-- Favorites Grid -->
    <?php if (empty($favorites)): ?>
        <div class="premium-stat-card" style="padding: 60px 24px; text-align: center; color: var(--dash-text-muted);">
            <div style="width: 80px; height: 80px; background: #fff1f2; color: #fb7185; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 20px; box-shadow: 0 10px 20px rgba(251, 113, 133, 0.1);">
                <i class="fa-solid fa-heart-crack"></i>
            </div>
            <h3 style="font-weight: 800; font-size: 1.25rem; color: var(--dash-text-main); margin: 0 0 8px;">No favorites yet</h3>
            <p style="font-size: 0.95rem; margin: 0 0 24px; opacity: 0.8;">Start exploring boarding houses and save the ones you love!</p>
            <a href="/tenant/?url=boarding/index" class="btn-res-action btn-res-primary" style="display: inline-flex; width: auto; min-width: 220px; margin: 0 auto;">
                <i class="fas fa-search"></i> Browse Listings
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
            <?php foreach ($favorites as $f): ?>
                <?php
                    // Map favorite record to housing component structure
                    $house = [
                        'id' => $f['boarding_house_id'],
                        'name' => $f['name'],
                        'address' => $f['address'],
                        'description' => $f['description'] ?? ''
                    ];
                ?>
                <div style="position: relative;">
                    <?php require __DIR__ . '/../components/boarding_house_item.php'; ?>
                    
                    <!-- Favorite Toggle Button Overlay -->
                    <button 
                        class="favorite-toggle-btn active" 
                        data-house-id="<?= (int)$f['boarding_house_id'] ?>"
                        style="position: absolute; top: 18px; right: 18px; width: 36px; height: 36px; border-radius: 50%; background: white; border: 1px solid #f1f5f9; color: #ff4f6d; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 5; transition: all 0.2s;"
                        onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 15px rgba(0,0,0,0.15)'"
                        onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 10px rgba(0,0,0,0.1)'"
                    >
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(function() {
    // Re-initialize any specific UI logic for favorites if needed
    $('.favorite-toggle-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = $(this);
        const houseId = btn.data('house-id');
        
        $.ajax({
            url: '/tenant/?url=boarding/toggle_favorite', // Assuming this endpoint exists based on usual patterns
            method: 'POST',
            data: { house_id: houseId },
            success: function(resp) {
                if (resp.status === 'success') {
                    // For the favorites page, we might want to remove the card or just update the UI
                    // If it was removed from favorites, maybe reload or fade out
                    if (resp.action === 'removed') {
                        btn.closest('div[style*="position: relative"]').fadeOut(300, function() {
                            $(this).remove();
                            if ($('.favorite-toggle-btn').length === 0) {
                                location.reload(); // Show empty state
                            }
                        });
                    }
                }
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
