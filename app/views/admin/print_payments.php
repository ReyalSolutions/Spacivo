<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Audit Ledger - StayHub Professional</title>
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
            --warning: #a16207;
            --danger: #b91c1c;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--slate);
            background: white;
            margin: 0;
            padding: 25px; /* Reduced from 40px */
            line-height: 1.4;
        }

        /* Print Specific Optimization */
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 0.5in; }
        }

        /* Compact Professional Header */
        .manifest-header {
            display: flex;
            justify-content: space-between;
            align-items: center; /* Centered for density */
            border-bottom: 2px solid var(--slate);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 38px; /* Reduced from 50px */
            height: 38px;
            background: var(--slate);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 1.35rem; /* Reduced from 1.75rem */
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

        /* Content Title */
        .report-info { margin-bottom: 15px; }
        .report-info h2 {
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 4px;
            color: var(--primary);
            text-transform: uppercase;
        }

        /* High-Density Transaction Table */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem; /* Reduced from 0.8rem */
        }

        .ledger-table th {
            text-align: left;
            padding: 8px 12px;
            background: var(--light);
            border-top: 1px solid var(--border);
            border-bottom: 1.5px solid var(--slate);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .ledger-table td {
            padding: 6px 12px; /* Reduced padding */
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        /* Subtle Grid Structure */
        .ledger-table th:not(:last-child), 
        .ledger-table td:not(:last-child) {
            border-right: 1px solid rgba(226, 232, 240, 0.5);
        }

        .ledger-table tr:nth-child(even) { background: #fbfcfe; }

        /* Components */
        .status-badge {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.6rem;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            border: 1px solid currentColor;
        }

        .status-paid { color: var(--success); background: #f0fdf4; }
        .status-pending { color: var(--warning); background: #fffbeb; }
        .status-failed { color: var(--danger); background: #fef2f2; }

        .currency-val {
            font-weight: 700;
            color: var(--slate);
            font-family: 'JetBrains Mono', monospace;
            display: block;
        }

        /* Professional Footer */
        .manifest-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
            font-size: 0.65rem;
            color: var(--muted);
            text-align: center;
        }

        .legal-notice { font-style: italic; margin-bottom: 5px; }

        .print-btn-container { position: fixed; bottom: 20px; right: 20px; }
        .btn-print {
            padding: 10px 20px;
            background: var(--slate);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

    <div class="manifest-header">
        <div class="brand-section">
            <div class="brand-logo">SH</div>
            <div class="brand-text">
                <h1>StayHub Finance</h1>
                <p>Digital Transaction Ledger</p>
            </div>
        </div>
        <div class="meta-section">
            <div class="meta-item">BATCH ID: <span class="meta-val">TXR-<?= strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)) ?></span></div>
            <div class="meta-item">TIMESTAMP: <span class="meta-val"><?= date('Y-m-d H:i') ?></span></div>
            <div class="meta-item">LEVEL: <span class="meta-val">RESTRICTED_AUTH</span></div>
        </div>
    </div>

    <div class="report-info">
        <h2>Transaction Audit Manifest <?= !empty($filter_status) ? '- ' . strtoupper($filter_status) : '' ?></h2>
        <?php if (!empty($filter_house_name)): ?>
            <p style="font-size: 0.85rem; color: var(--primary); font-weight: 700; margin-top: -5px;"><?= htmlspecialchars($filter_house_name) ?></p>
        <?php endif; ?>
        <p style="font-size: 0.75rem; color: var(--muted); margin: 0;">Comprehensive cryptographic record of financial entries and payment submissions.</p>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="18%">Reference</th>
                <th width="25%">Tenant</th>
                <th width="25%">Property/Source</th>
                <th width="12%">Amount</th>
                <th width="20%">Timeline</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td>
                        <div class="meta-val" style="font-size: 0.75rem;"><?= htmlspecialchars($p['transaction_ref'] ?: 'T-'.str_pad((string)$p['id'], 6, '0', STR_PAD_LEFT)) ?></div>
                        <div style="font-size: 0.6rem; color: var(--muted); font-weight: 700;"><?= strtoupper($p['payment_method']) ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--slate);"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></div>
                        <div style="font-size: 0.65rem; color: var(--muted); font-family: 'JetBrains Mono', monospace;"><?= htmlspecialchars($p['email']) ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($p['boarding_house_name'] ?: 'SECURE_SYS') ?></div>
                        <div style="font-size: 0.6rem; color: var(--muted); font-weight: 800; text-transform: uppercase;"><?= ucfirst($p['payment_type'] ?: 'Entry') ?></div>
                    </td>
                    <td>
                        <div class="currency-val">₱<?= number_format((float)$p['amount'], 2) ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 700;"><?= date('M d, Y', strtotime($p['created_at'])) ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background: var(--slate); color: white;">
                <td colspan="3" style="text-align: right; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 10px 15px;">Grand Total</td>
                <td style="padding: 10px 15px;">
                    <div style="font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 0.9rem;">₱<?= number_format($totalAmount, 2) ?></div>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="manifest-footer">
        <div class="legal-notice">
            CONFIDENTIAL: This manifest is a secure cryptographic record of system transactions. Unauthorized access or reproduction is strictly prohibited. Cross-referenced with internal audit logs ID: <?= strtoupper(bin2hex(random_bytes(2))) ?>.
        </div>
        <div>StayHub Professional Finance Suite &copy; <?= date('Y') ?> &bull; Audit & Compliance</div>
    </div>

    <div class="print-btn-container no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Execute Print
        </button>
    </div>

</body>
</html>
