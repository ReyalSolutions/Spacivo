<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Revenue Audit - StayHub Professional</title>
    <!-- Google Fonts for Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #6366f1;
            --slate: #0f172a;
            --muted: #64748b;
            --light: #f8fafc;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
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

        .manifest-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--slate);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            background: var(--slate);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            font-weight: 800;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .brand-text p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .meta-section {
            text-align: right;
            font-size: 0.75rem;
        }

        .meta-item { margin: 2px 0; color: var(--muted); }
        .meta-val { color: var(--slate); font-weight: 700; font-family: 'JetBrains Mono', monospace; }

        .report-info { margin-bottom: 20px; }
        .report-info h2 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 5px;
            color: var(--primary);
            text-transform: uppercase;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .ledger-table th {
            text-align: left;
            padding: 10px 15px;
            background: var(--light);
            border-top: 1px solid var(--border);
            border-bottom: 2px solid var(--slate);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .ledger-table td {
            padding: 8px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .ledger-table tr:nth-child(even) { background: #fafafa; }

        .status-badge {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
            border: 1.5px solid currentColor;
        }

        .status-paid { color: var(--success); background: #ecfdf5; }
        .status-pending { color: var(--warning); background: #fffbeb; }
        .status-failed { color: var(--danger); background: #fef2f2; }

        .currency-val {
            font-weight: 700;
            color: var(--slate);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
        }

        .manifest-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 0.7rem;
            color: var(--muted);
            text-align: center;
        }

        .legal-notice { font-style: italic; margin-bottom: 8px; }

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
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <div class="manifest-header">
        <div class="brand-section">
            <div class="brand-logo">SH</div>
            <div class="brand-text">
                <h1>StayHub Admin</h1>
                <p>Subscription Revenue Audit</p>
            </div>
        </div>
        <div class="meta-section">
            <div class="meta-item">AUDIT BATCH: <span class="meta-val">SUB-<?= strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)) ?></span></div>
            <div class="meta-item">GENERATE DATE: <span class="meta-val"><?= date('Y-m-d H:i:s') ?></span></div>
            <div class="meta-item">SECURITY: <span class="meta-val">CONFIDENTIAL_FIN</span></div>
        </div>
    </div>

    <div class="report-info">
        <h2>Subscription Payment Manifest</h2>
        <p style="font-size: 0.85rem; color: var(--muted); margin: 0;">
            Filtering: 
            <span class="meta-val"><?= !empty($filter_status) ? strtoupper($filter_status) : 'ALL STATUS' ?></span> | 
            <span class="meta-val"><?= !empty($filter_cycle) ? strtoupper($filter_cycle) : 'ALL CYCLES' ?></span>
        </p>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="15%">Ref / Gateway</th>
                <th width="25%">Owner Identity</th>
                <th width="20%">Plan details</th>
                <th width="15%">Amount</th>
                <th width="10%">Status</th>
                <th width="15%">Timeline</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
                    <td>
                        <div class="meta-val" style="font-size: 0.8rem;"><?= htmlspecialchars($p['transaction_ref'] ?: 'PENDING_AUDIT') ?></div>
                        <div style="font-size: 0.65rem; color: var(--muted); font-weight: 800; text-transform: uppercase;">
                            <?= htmlspecialchars($p['gateway'] ?: 'manual') ?>
                            <?php if (!empty($p['session_id'])): ?>
                                <span style="font-family: 'JetBrains Mono', monospace; font-weight: 400; opacity: 0.8; margin-left: 5px;"><?= htmlspecialchars($p['session_id']) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--slate);"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></div>
                        <div style="font-size: 0.7rem; color: var(--muted); font-family: 'JetBrains Mono', monospace;"><?= htmlspecialchars($p['email']) ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($p['plan_name'] ?: 'System Plan') ?></div>
                        <div style="font-size: 0.65rem; color: var(--muted); font-weight: 800; text-transform: uppercase;"><?= htmlspecialchars($p['billing_cycle']) ?></div>
                    </td>
                    <td>
                        <div class="currency-val">₱<?= number_format((float)$p['amount'], 2) ?></div>
                    </td>
                    <td>
                        <?php 
                            $status = strtolower($p['status'] ?? 'pending');
                            $statusClass = 'status-' . $status;
                        ?>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= strtoupper($status) ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700;"><?= date('M d, Y', strtotime($p['paid_at'] ?: $p['created_at'])) ?></div>
                        <div style="font-size: 0.65rem; color: var(--muted); font-weight: 600;"><?= date('h:i A', strtotime($p['paid_at'] ?: $p['created_at'])) ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background: var(--slate); color: white;">
                <td colspan="3" style="text-align: right; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 15px;">Total Audited Revenue</td>
                <td style="padding: 15px;">
                    <div style="font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 1.1rem;">₱<?= number_format($totalAmount, 2) ?></div>
                </td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="manifest-footer">
        <div class="legal-notice">
            CONFIDENTIAL FINANCE RECORD: This document is automatically generated by the StayHub Administrative Core. All transactions are cross-verified with merchant processor logs and internal database state. Audit ID: <?= strtoupper(bin2hex(random_bytes(4))) ?>.
        </div>
        <div>StayHub Professional &bull; Administrative Finance Suite &bull; &copy; <?= date('Y') ?></div>
    </div>

    <div class="print-btn-container no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Execute Audit Print
        </button>
    </div>

</body>
</html>
