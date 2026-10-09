<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($filter ?? 'Global') ?> Identity Manifest - StayHub Professional</title>
    <!-- Google Fonts for Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1e40af;
            --slate: #0f172a;
            --muted: #64748b;
            --light: #f8fafc;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--slate);
            background: white;
            margin: 0;
            padding: 40px;
            line-height: 1.5;
        }

        /* Print Specific Optimization */
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            @page { margin: 2cm; }
        }

        /* Professional Header */
        .manifest-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid var(--slate);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            background: var(--slate);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: var(--slate);
        }

        .brand-text p {
            margin: 0;
            font-size: 0.875rem;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .meta-section {
            text-align: right;
        }

        .meta-item {
            font-size: 0.75rem;
            color: var(--muted);
            margin: 2px 0;
        }

        .meta-val {
            color: var(--slate);
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Content Title */
        .report-info {
            margin-bottom: 30px;
        }

        .report-info h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary);
        }

        /* Identity Table */
        .identity-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .identity-table th {
            text-align: left;
            padding: 12px 15px;
            background: var(--light);
            border-bottom: 2px solid var(--slate);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--slate);
        }

        .identity-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .identity-table tr:nth-child(even) {
            background: #fafafa;
        }

        /* Components */
        .avatar-mini {
            width: 32px;
            height: 32px;
            background: var(--light);
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.75rem;
            color: var(--primary);
        }

        .user-meta-name { font-weight: 700; color: var(--slate); }
        .user-meta-email { font-size: 0.75rem; color: var(--muted); font-family: 'JetBrains Mono', monospace; }

        .role-indicator {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid currentColor;
            display: inline-block;
        }

        .status-active { color: #16a34a; }
        .status-restricted { color: #dc2626; }

        /* Professional Footer */
        .manifest-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 0.7rem;
            color: var(--muted);
            text-align: center;
        }

        .legal-notice {
            max-width: 600px;
            margin: 0 auto 10px;
            font-style: italic;
        }

        .print-btn-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
        }

        .btn-print {
            padding: 12px 24px;
            background: var(--slate);
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .btn-print:hover { transform: translateY(-3px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }

    </style>
</head>
<body>

    <div class="manifest-header">
        <div class="brand-section">
            <div class="brand-logo">S</div>
            <div class="brand-text">
                <h1>StayHub Global</h1>
                <p>Identity Management Systems</p>
            </div>
        </div>
        <div class="meta-section">
            <div class="meta-item">Document ID: <span class="meta-val">SH-<?= strtoupper(bin2hex(random_bytes(4))) ?></span></div>
            <div class="meta-item">Generated: <span class="meta-val"><?= date('Y-m-d H:i:s') ?></span></div>
            <div class="meta-item">Administrator: <span class="meta-val">SYSTEM_ROOT</span></div>
        </div>
    </div>

    <div class="report-info">
        <h2><?= ucfirst($filter ?? 'Global') ?> Identity Manifest</h2>
        <p style="font-size: 0.85rem; color: var(--muted); margin: 0;">Comprehensive audit of registered <?= $filter ?? 'personnel' ?> and access credentials within the StayHub ecosystem.</p>
    </div>

    <table class="identity-table">
        <thead>
            <tr>
                <th width="30%">Identity Identity</th>
                <th width="20%">Access Role</th>
                <th width="15%">Account State</th>
                <th width="35%">Telemetry & Registration</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="avatar-mini"><?= strtoupper(substr($u['first_name'], 0, 1)) ?></div>
                            <div>
                                <div class="user-meta-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                <div class="user-meta-email"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-indicator"><?= strtoupper($u['role']) ?></span>
                    </td>
                    <td>
                        <span class="role-indicator <?= ($u['status'] ?? 1) == 1 ? 'status-active' : 'status-restricted' ?>">
                            <?= ($u['status'] ?? 1) == 1 ? 'ACTIVE' : 'RESTRICTED' ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 0.8rem;">
                            <i class="fa-solid fa-calendar me-1"></i> Registered: <span class="meta-val"><?= date('Y-m-d', strtotime($u['created_at'])) ?></span><br>
                            <i class="fa-solid fa-phone me-1"></i> Phone: <span class="meta-val"><?= htmlspecialchars($u['phone']) ?></span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="manifest-footer">
        <div class="legal-notice">
            This document contains sensitive administrative data. Unauthorized distribution or disclosure is strictly prohibited under StayHub Security Protocols. All entities listed are subject to ongoing cryptographic verification.
        </div>
        <div class="brand-fine">
            StayHub Management Suite &copy; <?= date('Y') ?> &bull; Identity Protection Group
        </div>
    </div>

    <div class="print-btn-container no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Execute Print
        </button>
    </div>

    <script>
        // Auto-trigger print window if needed, but the button is more controlled
        // window.print();
    </script>
</body>
</html>
