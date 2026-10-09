<?php
/**
 * Professional Plan Manifest Print Template
 * Designed for High-Density Financial Auditing
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Plan Manifest - StayHub Professional</title>
    <!-- Google Fonts for Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --slate: #1e293b;
            --muted: #64748b;
            --light: #f1f5f9;
            --border: #e2e8f0;
            --success: #15803d;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--slate);
            background: white;
            margin: 0;
            padding: 30px;
            line-height: 1.4;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 0.5in; }
        }

        /* Manifest Header */
        .manifest-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--slate);
            padding-bottom: 12px;
            margin-bottom: 25px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            background: var(--slate);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .brand-text p {
            margin: 0;
            font-size: 0.75rem;
            color: var(--muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .meta-section {
            text-align: right;
            font-size: 0.7rem;
        }

        .meta-item { margin: 1px 0; color: var(--muted); }
        .meta-val { color: var(--slate); font-weight: 700; font-family: 'JetBrains Mono', monospace; }

        /* Report Header */
        .report-info { margin-bottom: 20px; }
        .report-info h2 {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 4px;
            color: var(--primary);
            text-transform: uppercase;
        }

        /* High-Density Table */
        .manifest-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
        }

        .manifest-table th {
            text-align: left;
            padding: 10px 15px;
            background: var(--light);
            border-top: 1px solid var(--border);
            border-bottom: 2px solid var(--slate);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .manifest-table td {
            padding: 10px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .manifest-table tr:nth-child(even) { background: #fbfcfe; }

        .price-val {
            font-weight: 700;
            color: var(--slate);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
        }

        .benefit-tag {
            font-weight: 700;
            color: var(--success);
            font-size: 0.65rem;
            display: block;
            margin-top: 2px;
        }

        .features-list {
            margin: 0;
            padding-left: 15px;
            font-size: 0.65rem;
            color: var(--muted);
        }

        /* Manifest Footer */
        .manifest-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 0.7rem;
            color: var(--muted);
            text-align: center;
        }

        .summary-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-bottom: 20px;
            padding: 15px;
            background: var(--light);
            border-radius: 12px;
        }

        .stat-box { text-align: center; }
        .stat-label { font-size: 0.6rem; font-weight: 800; text-transform: uppercase; color: var(--muted); display: block; }
        .stat-value { font-size: 1.1rem; font-weight: 800; color: var(--slate); }

        .print-btn-container { position: fixed; bottom: 30px; right: 30px; }
        .btn-print {
            padding: 12px 24px;
            background: var(--slate);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .btn-print:hover { transform: translateY(-2px); }
    </style>
</head>
<body>

    <div class="manifest-header">
        <div class="brand-section">
            <div class="brand-logo">SH</div>
            <div class="brand-text">
                <h1>StayHub Analytics</h1>
                <p>Service Tier Registry</p>
            </div>
        </div>
        <div class="meta-section">
            <div class="meta-item">MANIFEST ID: <span class="meta-val">PLN-<?= strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)) ?></span></div>
            <div class="meta-item">EXPORTED: <span class="meta-val"><?= date('Y-m-d H:i') ?></span></div>
            <div class="meta-item">AUTH LEVEL: <span class="meta-val">SYS_ADMIN</span></div>
        </div>
    </div>

    <div class="report-info">
        <h2>Active Service Tiers Manifest</h2>
        <p style="font-size: 0.8rem; color: var(--muted); margin: 0;">Comprehensive audit of current billing plans, resource allocations, and pricing structures.</p>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <span class="stat-label">Provisioned Plans</span>
            <span class="stat-value"><?= $totalPlans ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Avg. Monthly Rate</span>
            <span class="stat-value">₱<?= number_format($avgMonthly, 2) ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Access Protocol</span>
            <span class="stat-value">RESTRICTED</span>
        </div>
    </div>

    <table class="manifest-table">
        <thead>
            <tr>
                <th width="25%">Plan Details</th>
                <th width="15%">Monthly Pricing</th>
                <th width="15%">Yearly Pricing</th>
                <th width="15%">Resource Limit</th>
                <th width="30%">Core Features</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $p): ?>
                <tr>
                    <td>
                        <div style="font-weight: 800; font-size: 0.9rem;"><?= htmlspecialchars($p['name']) ?></div>
                        <div style="font-size: 0.6rem; color: var(--muted); font-family: 'JetBrains Mono', monospace;">SLUG: <?= strtoupper(str_replace(' ', '_', $p['name'])) ?></div>
                    </td>
                    <td>
                        <div class="price-val">₱<?= number_format((float)$p['price_monthly'], 2) ?></div>
                        <div style="font-size: 0.6rem; color: var(--muted);">per month</div>
                    </td>
                    <td>
                        <div class="price-val">₱<?= number_format((float)$p['price_yearly'], 2) ?></div>
                        <?php 
                            $saving = ( (float)$p['price_monthly'] * 12 ) - (float)$p['price_yearly'];
                            if ($saving > 0):
                        ?>
                            <span class="benefit-tag"><i class="fa-solid fa-arrow-down me-1"></i>Save ₱<?= number_format($saving, 0) ?>/yr</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight: 800;"><?= $p['room_limit'] ?> Rooms</div>
                        <div style="font-size: 0.6rem; color: var(--muted);">provisioned capacity</div>
                    </td>
                    <td>
                        <?php 
                            $features = json_decode($p['features'] ?? '[]', true);
                            if (!empty($features)):
                        ?>
                            <ul class="features-list">
                                <?php foreach (array_slice($features, 0, 4) as $f): ?>
                                    <li><?= htmlspecialchars($f) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div style="font-size: 0.6rem; color: var(--muted); font-style: italic;">Standard system defaults applied</div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="manifest-footer">
        <div style="font-style: italic; margin-bottom: 8px;">
            CONFIDENTIAL: This manifest is generated for internal system auditing. Distribution without authorization is a violation of the digital security protocol.
        </div>
        <div>StayHub Professional Suite &copy; <?= date('Y') ?> &bull; Internal Audit & Compliance Division</div>
    </div>

    <div class="print-btn-container no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Execute Print
        </button>
    </div>

</body>
</html>
