<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Portfolio Transaction Audit' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --primary: #0f172a; --accent: #6366f1; --gray-light: #f8fafc; --text-muted: #64748b; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: var(--primary); font-size: 0.85rem; padding: 20px; }
        .document-canvas { max-width: 900px; margin: 0 auto; background: white; padding: 50px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        
        .fw-900 { font-weight: 900; } .fw-800 { font-weight: 800; }
        .letter-spacing-1 { letter-spacing: 1px; }
        .text-xsmall { font-size: 0.65rem; }
        
        .doc-header { border-bottom: 3px solid var(--primary); padding-bottom: 25px; margin-bottom: 40px; }
        .doc-title { font-size: 2.2rem; letter-spacing: -1.5px; line-height: 0.85; }
        .meta-badge { background: var(--gray-light); border: 1px solid #e2e8f0; padding: 12px 18px; border-radius: 10px; }

        .meta-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 40px; }
        .grid-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .stat-item .label { font-size: 0.65rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted); margin-bottom: 5px; letter-spacing: 0.5px; }
        .stat-item .value { font-size: 1.25rem; font-weight: 900; color: var(--primary); }

        .transaction-table { width: 100%; margin-bottom: 40px; border: 1px solid #f1f5f9; border-radius: 8px; overflow: hidden; }
        .transaction-table thead th { background: var(--gray-light); color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; padding: 15px; letter-spacing: 1px; border-bottom: 2px solid #e2e8f0; }
        .transaction-table tbody td { padding: 15px; font-size: 0.8rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .status-badge { font-weight: 800; text-transform: uppercase; font-size: 0.6rem; padding: 4px 10px; border-radius: 100px; border: 1px solid currentColor; }

        .sig-box { text-align: center; margin-top: 60px; }
        .sig-line { border-bottom: 1.5px solid #e2e8f0; height: 50px; margin-bottom: 12px; width: 85%; margin-left: auto; margin-right: auto; }

        @media print {
            body { background: white; padding: 0; }
            .document-canvas { box-shadow: none; border: none; padding: 0; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
            .meta-box { -webkit-print-color-adjust: exact; background-color: #f8fafc !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm py-2">
        <i class="fa-solid fa-print me-2"></i>GENERATE TRANSACTION AUDIT
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary rounded-pill px-4 ms-2 py-2 fw-bold">CLOSE</button>
</div>

<div class="document-canvas">
    <!-- Header -->
    <div class="row align-items-end doc-header">
        <div class="col-7">
            <h6 class="text-accent fw-900 text-uppercase text-xsmall mb-2 letter-spacing-1">Financial Portfolio Audit</h6>
            <h1 class="doc-title fw-900 mb-0">TRANSACTION<br>LEDGER <span class="text-accent">SUMMARY</span></h1>
        </div>
        <div class="col-5 text-end">
            <h4 class="fw-900 mb-1 text-uppercase text-dark"><?= htmlspecialchars($houseName ?? 'STAYHUB LEDGER') ?></h4>
            <div class="meta-badge d-inline-block text-start mt-2">
                <span class="text-xsmall text-muted fw-800 text-uppercase d-block mb-1">Audit Timestamp</span>
                <span class="fw-900 h6 mb-0"><?= date('F d, Y h:i A') ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Box -->
    <div class="meta-box">
        <div class="grid-stats">
            <div class="stat-item">
                <div class="label">Total Events</div>
                <div class="value"><?= $totals['count'] ?> Entries</div>
            </div>
            <div class="stat-item">
                <div class="label">Gross Revenue</div>
                <div class="value text-success">₱<?= number_format($totals['paid'], 2) ?></div>
            </div>
            <div class="stat-item">
                <div class="label">Pending Volume</div>
                <div class="value text-warning">₱<?= number_format($totals['pending'], 2) ?></div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <table class="table transaction-table align-middle">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 30%;">Tenant / Property</th>
                <th style="width: 15%;">Method</th>
                <th style="width: 20%;">Reference</th>
                <th class="text-end" style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td class="fw-700"><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                    <td>
                        <div class="fw-800 text-dark"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></div>
                        <div class="text-xsmall text-muted fw-bold text-uppercase mt-1"><?= htmlspecialchars($t['boarding_house_name'] ?? 'N/A') ?></div>
                    </td>
                    <td class="text-uppercase fw-800 text-xsmall"><?= htmlspecialchars($t['payment_method']) ?></td>
                    <td><code class="fw-bold extra-small" style="color: var(--accent);"><?= htmlspecialchars($t['transaction_ref'] ?: '---') ?></code></td>
                    <td class="text-end">
                        <div class="fw-900 text-dark">₱<?= number_format((float)$t['amount'], 2) ?></div>
                        <div class="<?= $t['status'] === 'paid' ? 'text-success' : 'text-warning' ?> fw-800" style="font-size: 0.6rem; text-transform: uppercase;">
                            <?= $t['status'] ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="bg-light">
                <td colspan="4" class="text-end fw-900 py-3 text-muted">PORTFOLIO ACCUMULATION:</td>
                <td class="text-end fw-950 py-3 h6 mb-0 text-primary">₱<?= number_format($totals['paid'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Signatures -->
    <div class="sig-area mt-5 pt-4">
        <div class="row">
            <div class="col-4">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="fw-900 text-dark small mb-0">Property Owner</div>
                    <div class="text-muted text-xsmall text-uppercase fw-bold letter-spacing-1 mt-1">Prepared By</div>
                </div>
            </div>
            <div class="col-4">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="fw-900 text-dark small mb-0">System Auditor</div>
                    <div class="text-muted text-xsmall text-uppercase fw-bold letter-spacing-1 mt-1">Verified By</div>
                </div>
            </div>
            <div class="col-4">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="fw-900 text-dark small mb-0">Financial Controller</div>
                    <div class="text-muted text-xsmall text-uppercase fw-bold letter-spacing-1 mt-1">Approved By</div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-5 text-center">
            <div class="d-inline-block border rounded-pill px-4 py-2 bg-light shadow-sm text-xsmall text-muted fw-bold">
                <i class="fa-solid fa-shield-halved me-2 text-accent"></i>OFFICIAL TRANSACTION LEDGER | STAYHUB FISCAL SERVICES
            </div>
        </div>
    </div>
</div>

</body>
</html>
