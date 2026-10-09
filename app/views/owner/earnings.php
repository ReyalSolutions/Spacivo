<?php require __DIR__ . '/../layouts/admin_header.php'; ?>

<style>
/* Elite Financial Dashboard Design */
:root {
    --glass-bg: rgba(255, 255, 255, 0.75);
    --glass-border: rgba(255, 255, 255, 0.4);
    --accent-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
    --accent-blue: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    --accent-slate: linear-gradient(135deg, #64748b 0%, #334155 100%);
}

.earnings-container {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Bento Stats */
.bento-card {
    background: #ffffff;
    border: 1px solid rgba(241, 245, 249, 0.8);
    border-radius: 32px;
    padding: 35px;
    box-shadow: 0 4px 30px rgba(0,0,0,0.02);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    height: 100%;
}
.bento-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
    border-color: rgba(37, 99, 235, 0.2);
}

.hero-stats {
    background: var(--accent-slate);
    color: white;
}

.icon-box-lg {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    margin-bottom: 25px;
}

.property-row {
    padding: 24px;
    background: #f8fafc;
    border-radius: 24px;
    margin-bottom: 16px;
    border: 1px solid #f1f5f9;
    transition: all 0.3s;
}
.property-row:hover {
    background: #ffffff;
    border-color: #2563eb;
    transform: translateX(8px);
    box-shadow: 0 10px 30px rgba(37, 99, 235, 0.1);
}

.revenue-pill {
    padding: 10px 20px;
    background: #ecfdf5;
    color: #047857;
    border-radius: 100px;
    font-weight: 800;
    font-size: 0.85rem;
}
</style>

<div class="container-fluid px-4 py-5 earnings-container">
    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between mb-5 gap-4">
        <div class="w-100">
            <h1 class="display-4 fw-900 text-dark mb-2 letter-spacing--2">Total Earnings</h1>
            <p class="text-muted fs-5 mb-0 fw-500">Comprehensive financial performance across your property portfolio.</p>
        </div>
        <div class="d-flex w-100 w-lg-auto mt-2 mt-lg-0">
            <a href="/tenant/?url=owner/payments/transactions" class="btn btn-outline-dark rounded-pill px-4 py-3 py-sm-2 fw-800 d-flex justify-content-center align-items-center gap-2 shadow-sm w-100">
                <i class="fa-solid fa-receipt"></i>
                <span>VIEW ALL TRANSACTIONS</span>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Main Portfolio Stat -->
        <div class="col-xl-4 col-lg-5">
            <div class="bento-card hero-stats shadow-lg position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <!-- Decorative background elements -->
                <div class="position-absolute" style="top: -20px; right: -20px; opacity: 0.05; pointer-events: none; z-index: 0;">
                    <i class="fa-solid fa-shield-halved" style="font-size: 10rem; transform: rotate(15deg);"></i>
                </div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="icon-box-lg shadow-sm mb-4" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <i class="fa-solid fa-sack-dollar fa-2xl text-white"></i>
                    </div>
                    <div class="text-white-50 small fw-900 uppercase ls-2 mb-2">Portfolio Lifetime Revenue</div>
                    <div class="display-4 fw-950 mb-4 ls--2" style="letter-spacing: -3px;">₱<?= number_format($totalRevenue, 2) ?></div>
                </div>
                
                <div class="pt-4 border-top border-white-10" style="position: relative; z-index: 1;">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-3">
                        <div>
                            <span class="d-block text-white-50 extra-small fw-800 uppercase ls-1 mb-1">Managed Assets</span>
                            <span class="fw-900 h6 mb-0 text-white d-block"><?= count($houses) ?> Registered Properties</span>
                        </div>
                        <div class="badge bg-white text-dark rounded-pill px-4 py-2 fw-900 fs-7 shadow-sm">VERIFIED</div>
                    </div>
                    <div class="progress" style="height: 10px; background: rgba(255,255,255,0.08); border-radius: 100px;">
                        <div class="progress-bar bg-white shadow-sm" style="width: 100%; border-radius: 100px;"></div>
                    </div>
                    <p class="text-white-50 extra-small fw-700 mt-3 mb-0">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> Auditor Grade: 100% Precision Data
                    </p>
                </div>
            </div>
        </div>

        <!-- Property Breakdown -->
        <div class="col-xl-8 col-lg-7">
            <div class="bento-card">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
                    <div class="w-100">
                        <h3 class="h4 fw-900 mb-0">Earnings by Asset</h3>
                        <p class="text-muted small fw-600 mb-0 mt-1">Select a property to view specific transaction ledgers.</p>
                    </div>
                    <div class="badge bg-light text-dark rounded-pill px-4 py-2 border fw-800 fs-7 ls-1 text-center w-100 w-md-auto">AUDITED FIGURES</div>
                </div>

                <div class="property-list mt-2">
                    <?php if (empty($houses)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-building-circle-exclamation text-muted opacity-20 mb-3" style="font-size: 4rem;"></i>
                            <p class="text-muted fw-800">No properties registered under this profile.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($houses as $h): ?>
                            <div class="property-row d-flex flex-column align-items-start gap-3 cursor-pointer" onclick="redirectToAssetTransactions(<?= $h['id'] ?>)">
                                <div class="d-flex align-items-center gap-3 w-100 overflow-hidden">
                                    <div class="icon-box bg-white border shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; border-radius: 16px;">
                                        <i class="fa-solid fa-house-lock text-primary fs-5"></i>
                                    </div>
                                    <div class="text-wrap ps-1 text-break">
                                        <h5 class="mb-0 fw-900 text-dark text-wrap text-break"><?= htmlspecialchars($h['name']) ?></h5>
                                        <div class="text-muted small fw-700 opacity-75 text-wrap text-break"><?= htmlspecialchars($h['address']) ?></div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between w-100 pt-3 border-top gap-4 mt-1" style="border-color: rgba(37,99,235,0.1) !important;">
                                    <div class="text-start">
                                        <div class="revenue-pill mb-1 d-inline-block">₱<?= number_format($h['revenue'], 2) ?></div>
                                        <div class="text-muted extra-small fw-800 text-uppercase d-block mt-1">Total Lifetime Revenue</div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-muted opacity-25"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Redirection -->
    <form id="assetRedirectForm" action="/tenant/?url=owner/payments/transactions" method="POST">
        <input type="hidden" name="house_id" id="redirectHouseId">
    </form>

    <script>
    function redirectToAssetTransactions(houseId) {
        document.getElementById('redirectHouseId').value = houseId;
        document.getElementById('assetRedirectForm').submit();
    }
    </script>

    <!-- Additional Insights Row -->
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="bento-card p-5 h-100" style="background: #f8fafc;">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="icon-box bg-primary text-white" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h4 class="fw-900 text-dark mb-0">Financial Health</h4>
                </div>
                <p class="text-muted mb-4 fs-6 fw-500">Your portfolio is currently generating consistent revenue across multiple units. Monitor individual transaction details for granular audits.</p>
                <div class="d-flex flex-column flex-sm-row gap-3 w-100">
                    <a href="/tenant/?url=owner/payments/transactions" class="btn btn-primary rounded-pill px-4 fw-800 py-3 shadow-sm border-0 d-flex justify-content-center align-items-center flex-grow-1 w-100" style="background: #2563eb; transition: all 0.3s;" data-elite-tooltip="View Comprehensive Audit">
                        <i class="fa-solid fa-shield-check me-2"></i>AUDIT ALL TRANSACTIONS
                    </a>
                    <a href="/tenant/?url=owner/payments/earnings" class="btn btn-outline-dark rounded-pill px-4 fw-800 py-3 shadow-sm d-flex justify-content-center align-items-center flex-grow-1 w-100" style="transition: all 0.3s; border-width: 2px;" data-elite-tooltip="Sync Live Revenue Flow">
                        <i class="fa-solid fa-rotate me-2"></i>REFRESH DATA
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="row g-4 h-100">
                <div class="col-md-6">
                    <div class="bento-card p-4 h-100 bg-white border border-dashed border-2 text-center d-flex flex-column justify-content-center">
                        <i class="fa-solid fa-file-invoice-dollar text-primary opacity-20 mb-3" style="font-size: 3rem;"></i>
                        <h6 class="fw-900 text-dark mb-1">Fiscal Integrity</h6>
                        <p class="text-muted extra-small mb-0 fw-700">All data verified via StayHub Ledger v2.0</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bento-card p-4 h-100 bg-white border border-dashed border-2 text-center d-flex flex-column justify-content-center">
                        <i class="fa-solid fa-shield-halved text-success opacity-20 mb-3" style="font-size: 3rem;"></i>
                        <h6 class="fw-900 text-dark mb-1">Secure Transactions</h6>
                        <p class="text-muted extra-small mb-0 fw-700">Encrypted financial trail for all assets</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
