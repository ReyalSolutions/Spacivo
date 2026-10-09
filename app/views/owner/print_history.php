<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Archival Residency Summary' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --primary: #0f172a; --accent: #3b82f6; --gray-light: #f8fafc; --text-muted: #64748b; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: var(--primary); font-size: 0.85rem; padding: 20px; }
        .document-canvas { max-width: 900px; margin: 0 auto; background: white; padding: 50px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        
        .fw-900 { font-weight: 900; } .fw-800 { font-weight: 800; }
        .letter-spacing-1 { letter-spacing: 1px; }
        .text-xsmall { font-size: 0.65rem; }
        .text-small { font-size: 0.75rem; }
        
        .doc-header { border-bottom: 3px solid var(--primary); padding-bottom: 25px; margin-bottom: 40px; }
        .doc-title { font-size: 2.2rem; letter-spacing: -1.5px; line-height: 0.85; }
        .meta-badge { background: var(--gray-light); border: 1px solid #e2e8f0; padding: 12px 18px; border-radius: 10px; }

        .stat-banner { background: var(--primary); color: white; border-radius: 14px; padding: 25px; margin-bottom: 40px; }
        .stat-item { border-right: 1px solid rgba(255,255,255,0.1); }
        .stat-item:last-child { border-right: none; }

        .table thead th { background: var(--gray-light); border-top: none; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; padding: 15px; letter-spacing: 1px; }
        .table tbody td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

        .sig-box { text-align: center; margin-top: 60px; }
        .sig-line { border-bottom: 1.5px solid #e2e8f0; height: 50px; margin-bottom: 12px; width: 85%; margin-left: auto; margin-right: auto; }

        @media print {
            body { background: white; padding: 0; }
            .document-canvas { box-shadow: none; border: none; padding: 0; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
            .stat-banner { -webkit-print-color-adjust: exact; background-color: var(--primary) !important; color: white !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-dark rounded-pill px-5 fw-bold shadow-sm py-2">
        <i class="fa-solid fa-print me-2"></i>GENERATE OFFICIAL ARCHIVE REPORT
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary rounded-pill px-4 ms-2 py-2 fw-bold">CLOSE</button>
</div>

<div class="document-canvas">
    <!-- Header -->
    <div class="row align-items-end doc-header">
        <div class="col-7">
            <h6 class="text-primary fw-900 text-uppercase text-xsmall mb-2 letter-spacing-1">Historical Residency Registry</h6>
            <h1 class="doc-title fw-900 mb-0">ARCHIVAL<br>RESIDENCY <span class="text-primary">SUMMARY</span></h1>
        </div>
        <div class="col-5 text-end">
            <h4 class="fw-900 mb-1 text-uppercase text-dark"><?= htmlspecialchars($house['name'] ?? 'GLOBAL PORTFOLIO') ?></h4>
            <p class="text-muted text-xsmall fw-bold mb-3 italic"><?= htmlspecialchars($house['address'] ?? 'Comprehensive Portfolio Management') ?></p>
            <div class="meta-badge d-inline-block text-start">
                <span class="text-xsmall text-muted fw-800 text-uppercase d-block mb-1">Report Generated</span>
                <span class="fw-900 h6 mb-0"><?= date('F d, Y') ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Banner -->
    <div class="stat-banner">
        <div class="row text-center">
            <div class="col-4 stat-item">
                <div class="text-white-50 text-xsmall fw-800 text-uppercase mb-1">Former Residents</div>
                <div class="h3 fw-900 mb-0"><?= count($history) ?></div>
            </div>
            <div class="col-4 stat-item">
                <div class="text-white-50 text-xsmall fw-800 text-uppercase mb-1">Historical Revenue</div>
                <div class="h3 fw-900 mb-0">₱<?= number_format(array_reduce($history, fn($c, $b) => $c + (float)$b['total_amount'], 0), 2) ?></div>
            </div>
            <div class="col-4 stat-item">
                <div class="text-white-50 text-xsmall fw-800 text-uppercase mb-1">Archive Status</div>
                <div class="h3 fw-900 mb-0 text-success text-uppercase" style="font-size: 1.2rem;">Verified</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="mb-5">
        <h6 class="text-uppercase fw-800 text-muted mb-3 text-xsmall letter-spacing-1">Registry Detailed Logs</h6>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Resident Name</th>
                        <th style="width: 15%;">Unit</th>
                        <th style="width: 35%;">Residency Period</th>
                        <th class="text-end" style="width: 25%;">Total Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted fw-bold">No archival records found for this scope.</td></tr>
                    <?php else: ?>
                        <?php foreach ($history as $b): ?>
                            <tr>
                                <td>
                                    <div class="fw-900 text-dark"><?= htmlspecialchars($b['tenant_name']) ?></div>
                                    <div class="text-xsmall text-muted fw-bold text-uppercase mt-1">Ref ID: #RES-<?= $b['id'] ?></div>
                                </td>
                                <td>
                                    <div class="fw-800 text-primary"><?= htmlspecialchars($b['room_name']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-700 text-dark small">
                                        <?= date('M d, Y', strtotime($b['start_date'])) ?> 
                                        <span class="text-muted mx-1">/</span> 
                                        <?= date('M d, Y', strtotime($b['end_date'])) ?>
                                    </div>
                                    <div class="text-xsmall text-muted fw-bold mt-1">
                                        <?php 
                                            $days = round((strtotime($b['end_date']) - strtotime($b['start_date'])) / 86400);
                                            echo $days . " DIMENSIONAL DAYS";
                                        ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="fw-900 text-dark">₱<?= number_format((float)$b['total_amount'], 2) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Signatures -->
    <div class="sig-area mt-5 pt-4">
        <div class="row">
            <div class="col-6">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="fw-900 text-dark small mb-0">Authorized Registrar</div>
                    <div class="text-muted text-xsmall text-uppercase fw-bold letter-spacing-1 mt-1">Documented By</div>
                </div>
            </div>
            <div class="col-6">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="fw-900 text-dark small mb-0">Property Owner / Manager</div>
                    <div class="text-muted text-xsmall text-uppercase fw-bold letter-spacing-1 mt-1">Verified By</div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-5 text-center">
            <div class="d-inline-block border rounded-pill px-4 py-2 bg-light shadow-sm text-xsmall text-muted fw-bold">
                <i class="fa-solid fa-lock me-2 text-primary"></i>SECURE ARCHIVAL LOG | SYSTEM GENERATED DOCUMENT
            </div>
        </div>
    </div>
</div>

</body>
</html>
