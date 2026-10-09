<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<style>
/* Elite Archival Design System */
:root {
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.3);
    --accent-slate: linear-gradient(135deg, #64748b 0%, #334155 100%);
    --accent-indigo: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    --accent-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.history-container {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Glass Stats Cards */
.stats-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.icon-box {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* History Cards */
.history-card {
    border: none;
    border-radius: 32px;
    background: #ffffff;
    box-shadow: 0 4px 30px rgba(0,0,0,0.02);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(241, 245, 249, 0.8);
    position: relative;
    overflow: hidden;
}
.history-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    background: var(--accent-slate);
    opacity: 0.1;
    transition: opacity 0.3s;
}
.history-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(100, 116, 139, 0.1);
    border-color: rgba(100, 116, 139, 0.2);
}

.history-avatar {
    width: 72px;
    height: 72px;
    border-radius: 22px;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 850;
    font-size: 1.75rem;
    border: 2px solid #e2e8f0;
}

.status-badge {
    padding: 8px 18px;
    border-radius: 100px;
    font-size: 0.65rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.badge-archived { background: #f8fafc; color: #64748b; border: 1px solid rgba(100, 116, 139, 0.2); }

.detail-item {
    padding: 16px;
    background: #f8fafc;
    border-radius: 20px;
    border: 1px solid #f1f5f9;
}

.action-btn {
    padding: 12px 24px;
    border-radius: 16px;
    font-weight: 800;
    font-size: 0.8rem;
    transition: all 0.3s;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
}
.action-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
    transform: translateY(-2px);
}

.breadcrumb-custom {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #64748b;
}
</style>

<div class="container-fluid px-4 py-5 history-container">
    <!-- Header -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-8">
            <nav class="breadcrumb-custom mb-3 d-flex align-items-center gap-2">
                <a href="/tenant/?url=owner/houses" class="text-decoration-none text-muted">Portfolio</a>
                <i class="fa-solid fa-chevron-right small opacity-50"></i>
                <a href="/tenant/?url=owner/bookings" class="text-decoration-none text-muted">Tenants</a>
                <i class="fa-solid fa-chevron-right small opacity-50"></i>
                <span class="text-primary">Archival History</span>
            </nav>
            <h1 class="display-5 fw-900 text-dark mb-2 letter-spacing--2">Residency History</h1>
            <p class="text-muted fs-5 mb-0 fw-500">
                <?= $house ? 'Historical residency logs and former tenants for ' . htmlspecialchars($house['name']) : 'Comprehensive archive of all completed tenancies across your portfolio.' ?>
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 d-flex justify-content-lg-end align-items-center gap-3">
            <!-- Property Filter -->
            <div class="property-filter-wrapper me-2">
                <select class="form-select rounded-pill border-2 px-4 py-2 fw-700 text-dark bg-white shadow-sm" style="min-width: 200px; cursor: pointer;" onchange="location.href='/tenant/?url=owner/bookings/history&house_id=' + this.value">
                    <option value="">All My Properties</option>
                    <?php foreach ($houses as $h): ?>
                        <option value="<?= $h['id'] ?>" <?= (isset($house) && $house['id'] == $h['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($h['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <form method="POST" action="/tenant/?url=owner/bookings/print_history" target="_blank" class="d-inline">
                <?php if ($house): ?>
                    <input type="hidden" name="house_id" value="<?= (int)$house['id'] ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-800 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-print"></i>
                    <span>PRINT</span>
                </button>
            </form>
            <a href="/tenant/?url=owner/bookings" class="btn btn-dark rounded-pill px-4 py-2 fw-800 d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-users"></i>
                <span>ACTIVE</span>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100">
                <div class="icon-box bg-slate-50 text-slate-600" style="background:#f1f5f9; color:#475569;">
                    <i class="fa-solid fa-box-archive fa-xl"></i>
                </div>
                <div class="text-muted small fw-800 uppercase ls-1 mt-3 mb-1">Former Residents</div>
                <div class="h2 mb-0 fw-900"><?= count($history) ?> <small class="text-muted fw-500 h6">Records</small></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100">
                <div class="icon-box bg-indigo-50 text-indigo-600" style="background:#eef2ff; color:#4f46e5;">
                    <i class="fa-solid fa-file-invoice-dollar fa-xl"></i>
                </div>
                <div class="text-muted small fw-800 uppercase ls-1 mt-3 mb-1">Historical Volume</div>
                <div class="h2 mb-0 fw-900">
                    <?php 
                        $vol = array_reduce($history, fn($c, $b) => $c + (float)$b['total_amount'], 0);
                        echo '₱' . number_format($vol, 0);
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- History Grid -->
    <div class="row g-4">
        <?php if (empty($history)): ?>
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-5 border border-dashed border-2">
                    <i class="fa-solid fa-clock-rotate-left text-muted opacity-20 mb-4" style="font-size: 5rem;"></i>
                    <h2 class="fw-900 text-dark mb-2">No Historical Data</h2>
                    <p class="text-muted mx-auto" style="max-width: 400px;">When residents move out or contracts are finalized, their archival records will be preserved here.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($history as $b): ?>
                <div class="col-xl-6">
                    <div class="history-card p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="history-avatar">
                                    <?= substr($b['tenant_name'], 0, 1) ?>
                                </div>
                                <div>
                                    <h3 class="h5 fw-900 text-dark mb-0"><?= htmlspecialchars($b['tenant_name']) ?></h3>
                                    <div class="status-badge badge-archived mt-1">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        COMPLETED RESIDENCY
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small fw-800 uppercase mb-1">Total Revenue</div>
                                <div class="h4 fw-900 text-dark mb-0">₱<?= number_format((float)$b['total_amount'], 2) ?></div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <div class="text-muted small fw-800 mb-1">PREVIOUS UNIT</div>
                                    <div class="fw-700 text-dark"><?= htmlspecialchars($b['room_name']) ?></div>
                                    <div class="text-muted small fw-600"><?= htmlspecialchars($b['boarding_house_name']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <div class="text-muted small fw-800 mb-1">TENANCY LIFECYCLE</div>
                                    <div class="fw-700 text-dark">
                                        <?= date('M d, Y', strtotime($b['start_date'])) ?>
                                        <i class="fa-solid fa-arrow-right mx-1 text-muted small"></i>
                                        <?= date('M d, Y', strtotime($b['end_date'])) ?>
                                    </div>
                                    <div class="text-muted small fw-600 mt-1">
                                        <i class="fa-solid fa-clock me-1"></i>
                                        <?php 
                                            $days = (strtotime($b['end_date']) - strtotime($b['start_date'])) / (60*60*24);
                                            echo round($days) . ' days duration';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <span class="text-muted small fw-600"><i class="fa-solid fa-box-archive me-1"></i> Permanent Archival Log</span>
                            <form method="POST" action="/tenant/?url=owner/payments">
                                <input type="hidden" name="tenancy_id" value="<?= (int)$b['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                <button type="submit" class="action-btn">
                                    <i class="fa-solid fa-receipt me-2"></i>VIEW LEDGER
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
