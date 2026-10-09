<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Resident Statement' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --primary: #0f172a; --accent: #3b82f6; --gray-light: #f8fafc; --text-muted: #64748b; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: var(--primary); font-size: 0.85rem; padding: 20px; }
        .document-canvas { max-width: 850px; margin: 0 auto; background: white; padding: 45px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        
        .fw-900 { font-weight: 900; } .fw-800 { font-weight: 800; }
        .letter-spacing-1 { letter-spacing: 1px; }
        .text-xsmall { font-size: 0.65rem; }
        
        .doc-header { border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: 30px; }
        .doc-title { font-size: 2rem; letter-spacing: -1.5px; line-height: 0.9; }
        .meta-badge { background: var(--gray-light); border: 1px solid #e2e8f0; padding: 10px 15px; border-radius: 8px; }

        .summary-box { background: var(--primary); color: white; border-radius: 12px; padding: 20px; box-shadow: 0 8px 16px rgba(15, 23, 42, 0.1); }
        .summary-divider { border-top: 1px solid rgba(255,255,255,0.1); margin: 10px 0; }

        .table thead th { background: var(--gray-light); border: none; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; padding: 12px; }
        .table tbody td { padding: 12px; border-bottom: 1px solid #f1f5f9; }

        .sig-box { text-align: center; }
        .sig-line { border-bottom: 1px solid #e2e8f0; height: 45px; margin-bottom: 10px; width: 85%; margin-left: auto; margin-right: auto; }

        @media print {
            body { background: white; padding: 0; }
            .document-canvas { box-shadow: none; border: none; padding: 0; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
            .summary-box { -webkit-print-color-adjust: exact; background-color: var(--primary) !important; color: white !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center mb-4" style="max-width: 850px; margin: 0 auto;">
    <button onclick="window.print()" class="btn btn-dark btn-sm rounded-pill px-5 fw-bold shadow-sm">
        <i class="fa-solid fa-print me-2"></i>PRINT STATEMENT OF ACCOUNT
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-4 ms-2">CLOSE</button>
</div>

<div class="document-canvas">
    <!-- Header -->
    <div class="row align-items-center doc-header">
        <div class="col-6">
            <h6 class="text-primary fw-900 text-uppercase text-xsmall mb-1 letter-spacing-1">Official Summary</h6>
            <h1 class="doc-title fw-900 mb-0">STATEMENT<br>OF <span class="text-primary">ACCOUNT</span></h1>
        </div>
        <div class="col-6 text-end">
            <h4 class="fw-900 mb-1 text-uppercase"><?= htmlspecialchars($booking[' boarding_house_name'] ?? 'MANAGEMENT') ?></h4>
            <p class="text-muted text-xsmall fw-bold mb-2 italic"><?= htmlspecialchars($booking['address'] ?? 'Official Address') ?></p>
            <div class="d-flex justify-content-end gap-2">
                <div class="meta-badge text-start" style="min-width: 140px;">
                    <span class="text-xsmall text-muted fw-800 text-uppercase d-block">Period Date</span>
                    <span class="fw-900"><?= date('M d, Y') ?></span>
                </div>
                <div class="meta-badge text-start" style="min-width: 140px;">
                    <span class="text-xsmall text-muted fw-800 text-uppercase d-block">Document ID</span>
                    <span class="fw-bold font-monospace text-primary">SOA-<?= strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Info & Summary Row -->
    <?php
    $totalContract = (float)$booking['total_amount'];
    $paid = (float)$totalPaid;
    $balance = $totalContract - $paid;
    $isOverpaid = $balance < 0;
    ?>
    <div class="row mb-4 align-items-stretch">
        <div class="col-7">
            <div class="p-3 border rounded-3 h-100 shadow-sm">
                <h6 class="text-uppercase fw-800 text-muted mb-3 text-xsmall letter-spacing-1">Resident Details</h6>
                <div class="h5 fw-900 mb-1 text-primary"><?= htmlspecialchars($booking['tenant_name']) ?></div>
                <div class="d-flex gap-4 text-muted fw-bold text-xsmall mb-2">
                    <span><i class="fa-solid fa-door-open me-1 text-primary"></i>Unit: <?= htmlspecialchars($booking['room_name']) ?></span>
                </div>
                <div class="text-muted text-xsmall fw-bold border-top pt-2 mt-2">
                    <div class="mb-1"><i class="fa-solid fa-envelope me-2 text-primary"></i><?= htmlspecialchars($booking['tenant_email']) ?></div>
                    <div><i class="fa-solid fa-phone me-2 text-primary"></i><?= htmlspecialchars($booking['tenant_phone']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-5">
            <div class="summary-box h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between text-xsmall fw-bold mb-2">
                        <span class="text-white-50">CONTRACT TOTAL:</span>
                        <span>₱<?= number_format($totalContract, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-xsmall fw-bold">
                        <span class="text-white-50">TOTAL PAYMENTS:</span>
                        <span class="text-success">+ ₱<?= number_format($paid, 2) ?></span>
                    </div>
                </div>
                
                <div>
                    <div class="summary-divider"></div>
                    <?php if ($isOverpaid): ?>
                        <div class="d-flex justify-content-between align-items-end text-info animate-pulse">
                            <div>
                                <span class="text-white-50 text-xsmall fw-900 text-uppercase d-block">Exceed Payment</span>
                                <span class="extra-small fw-bold">CREDIT BALANCE</span>
                            </div>
                            <span class="h4 fw-900 mb-0">₱<?= number_format(abs($balance), 2) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between align-items-end <?= $balance == 0 ? 'text-success' : 'text-warning' ?>">
                            <span class="text-white-50 text-xsmall fw-900">BALANCE DUE:</span>
                            <span class="h4 fw-900 mb-0">₱<?= number_format($balance, 2) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="mb-5">
        <h6 class="text-uppercase fw-800 text-muted mb-2 text-xsmall letter-spacing-1">Validated Transactions</h6>
        <div class="border rounded-3 overflow-hidden shadow-sm">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 25%;">Ref #</th>
                        <th>Description</th>
                        <th class="text-end" style="width: 20%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $processed = array_filter($payments, fn($p) => $p['status'] === 'paid');
                    if (empty($processed)): 
                    ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No transactions recorded for this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($processed as $p): ?>
                            <tr>
                                <td class="fw-bold"><?= date('m/d/Y', strtotime($p['created_at'])) ?></td>
                                <td class="small font-monospace text-primary"><?= htmlspecialchars($p['transaction_ref'] ?: '---') ?></td>
                                <td>
                                    <div class="fw-800 text-uppercase text-xsmall d-inline-block px-2 py-1 bg-light rounded me-2">
                                        <?= ucfirst($p['payment_type'] ?: 'Payment') ?>
                                    </div>
                                    <span class="text-muted extra-small italic fw-bold"><?= htmlspecialchars($p['description'] ?: 'Official receipt') ?></span>
                                </td>
                                <td class="text-end fw-900 text-dark">₱<?= number_format((float)$p['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Signatures -->
    <div class="sig-area mt-5 pt-3 border-top border-light">
        <div class="row gx-5">
            <div class="col-4 sig-box">
                <div class="sig-line"></div>
                <div class="fw-900 text-dark small mb-0"><?= htmlspecialchars($booking['tenant_name']) ?></div>
                <div class="text-muted text-xsmall text-uppercase fw-bold letter-spacing-1">Resident Signature</div>
            </div>
            <div class="col-4 sig-box">
                <div class="sig-line"></div>
                <div class="fw-900 text-dark small mb-0">Property Management</div>
                <div class="text-muted text-xsmall text-uppercase fw-bold letter-spacing-1">Authorized Agent</div>
            </div>
            <div class="col-4 sig-box">
                <div class="sig-line"></div>
                <div class="fw-900 text-dark small mb-0"><?= htmlspecialchars($booking['owner_name']) ?></div>
                <div class="text-muted text-xsmall text-uppercase fw-bold letter-spacing-1">Management/Owner</div>
            </div>
        </div>

        <div class="mt-5 pt-4 text-center">
            <div class="badge bg-light text-muted fw-bold py-2 px-3 border shadow-sm" style="font-size: 0.65rem;">
                <i class="fa-solid fa-circle-check me-2 text-success"></i>This document is electronically generated and verified for tenancy purposes.
            </div>
        </div>
    </div>
</div>

</body>
</html>
