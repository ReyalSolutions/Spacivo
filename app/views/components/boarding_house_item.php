<?php
// Reusable listing card for a single boarding house.
// Expected variable: $house: array with keys: id, name, address, description
?>
<div class="premium-listing-card" onclick="window.location='/tenant/?url=boarding/show&id=<?= (int)($house['id'] ?? 0) ?>'">
    <style>
        .premium-listing-card {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 32px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            padding: 28px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.05);
            transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .premium-listing-card:hover {
            transform: translateY(-12px) scale(1.02);
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 35px 70px -15px rgba(99, 102, 241, 0.2);
        }

        /* Shine Effect Overlay */
        .premium-listing-card::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.15),
                transparent
            );
            transform: rotate(45deg);
            transition: all 0.8s ease;
            opacity: 0;
            pointer-events: none;
        }

        .premium-listing-card:hover::after {
            left: 100%;
            opacity: 1;
        }

        .listing-icon-box {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.25);
            transition: all 0.4s ease;
        }

        .premium-listing-card:hover .listing-icon-box {
            transform: scale(1.1) rotate(5deg);
        }

        .listing-title {
            margin: 0 0 6px;
            font-size: 1.35rem;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            color: #0f172a;
            line-height: 1.25;
            letter-spacing: -0.02em;
        }

        .listing-address {
            margin: 0;
            font-size: 0.95rem;
            color: #64748b;
            font-weight: 600;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .listing-desc {
            font-size: 1rem;
            color: #475569;
            line-height: 1.7;
            margin: 20px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-weight: 500;
        }

        .listing-footer-btn {
            background: rgba(241, 245, 249, 0.6);
            color: #475569;
            border-radius: 16px;
            padding: 14px;
            text-align: center;
            font-weight: 800;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .premium-listing-card:hover .listing-footer-btn {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.25);
            border-color: transparent;
        }
    </style>

    <div style="display: flex; gap: 20px; align-items: flex-start; margin-bottom: 20px;">
        <div class="listing-icon-box">
            <i class="fas fa-house-chimney"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
            <h3 class="listing-title">
                <?= htmlspecialchars((string)($house['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </h3>
            <p class="listing-address">
                <i class="fas fa-location-dot" style="color: #ef4444; margin-top: 4px;"></i>
                <span><?= htmlspecialchars((string)($house['address'] ?? 'No address provided'), ENT_QUOTES, 'UTF-8') ?></span>
            </p>
        </div>
    </div>

    <?php if (!empty($house['description'])): ?>
        <p class="listing-desc">
            <?= htmlspecialchars((string)$house['description'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <div class="listing-footer-btn">
        <i class="fas fa-circle-info"></i> View Details
    </div>
</div>
