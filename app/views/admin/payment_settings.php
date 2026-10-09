<?php 
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/management_header.php';
?>

<div class="animate-fade-up">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <a href="/tenant/?url=admin/settings" class="btn btn-light-primary btn-icon-only rounded-circle shadow-sm">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="fw-bold m-0 text-gradient-primary">Payment Gateway Protocols</h2>
            </div>
            <p class="text-muted mb-0 ms-5 ps-2">Secure configuration for manual and automated financial collection gateways</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold transition-all btn-lg" form="paymentSettingsForm">
                <i class="fa-solid fa-floppy-disk me-2"></i>Apply Changes
            </button>
        </div>
    </div>

    <form id="paymentSettingsForm" action="/tenant/?url=admin/save_settings" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="context" value="payments">

        <div class="row g-5">
            <div class="col-12">
                <div class="premium-stat-card p-5 shadow-lg border-0 bg-white" style="border-radius: 32px;">
                    <div class="row g-5">
                        <!-- GCash Column -->
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                                    <i class="fa-solid fa-mobile-screen-button fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-800 m-0">GCash Direct</h5>
                                    <span class="text-muted smaller fw-bold text-uppercase ls-1">Manual Verification Layer</span>
                                </div>
                            </div>
                            <div class="bg-light p-4 rounded-4 border">
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Registered Number</label>
                                    <input type="text" name="gcash_number" class="form-control rounded-pill px-4 border-2" value="<?= htmlspecialchars($settings['gcash_number'] ?? '09123456789') ?>" placeholder="09XX XXX XXXX">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Account Name</label>
                                    <input type="text" name="gcash_account_name" class="form-control rounded-pill px-4 border-2" value="<?= htmlspecialchars($settings['gcash_account_name'] ?? 'BNSC ADMIN') ?>" placeholder="Full Account Name">
                                </div>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="gcash_enabled" <?= ($settings['gcash_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label small fw-bold text-dark">Active for Subscription Payments</label>
                                </div>
                            </div>
                        </div>

                        <!-- PayMongo Column -->
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="rounded-circle bg-purple-subtle text-purple d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                                    <i class="fa-solid fa-link fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-800 m-0">PayMongo Integration</h5>
                                    <span class="text-muted smaller fw-bold text-uppercase ls-1">Automated API Orchestration</span>
                                </div>
                            </div>
                            <div class="bg-light p-4 rounded-4 border">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Public API Key</label>
                                    <input type="password" name="paymongo_pub" id="paymongo_pub" class="form-control rounded-pill px-4 border-2" value="<?= htmlspecialchars($settings['paymongo_pub'] ?? '') ?>" placeholder="pk_test_...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Secret API Key</label>
                                    <input type="password" name="paymongo_sec" id="paymongo_sec" class="form-control rounded-pill px-4 border-2" value="<?= htmlspecialchars($settings['paymongo_sec'] ?? '') ?>" placeholder="sk_test_...">
                                </div>

                                <button type="button" id="btnDiscoverMethods" class="btn btn-purple w-100 rounded-pill fw-bold mb-4 py-2 shadow-sm transition-all hover-translate-y">
                                    <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Discover Available Methods
                                </button>

                                <!-- Dynamic Payment Methods Grid -->
                                <div id="paymongo_methods_section" class="mb-3" style="display: none;">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-3 d-block border-bottom pb-2">Active Gateway Protocols</label>
                                    <div class="row g-3" id="payment_methods_grid">
                                        <?php 
                                            $activeMethods = json_decode($settings['paymongo_methods'] ?? '[]', true);
                                            $defaultMethods = [
                                                ['id' => 'card', 'name' => 'Credit / Debit Card', 'icon' => 'fa-solid fa-credit-card', 'desc' => 'Visa, Mastercard, JCB'],
                                                ['id' => 'gcash', 'name' => 'GCash', 'icon' => 'fa-solid fa-mobile-screen', 'desc' => 'Digital Wallet (Philippines)'],
                                                ['id' => 'grab_pay', 'name' => 'GrabPay', 'icon' => 'fa-solid fa-wallet', 'desc' => 'Grab Wallet Integration'],
                                                ['id' => 'paymaya', 'name' => 'Maya', 'icon' => 'fa-solid fa-money-bill-wave', 'desc' => 'PayMaya / Maya Digital'],
                                                ['id' => 'qrph', 'name' => 'QRPh', 'icon' => 'fa-solid fa-qrcode', 'desc' => 'Universal PH QR Standard'],
                                                ['id' => 'billease', 'name' => 'BillEase', 'icon' => 'fa-solid fa-calendar-check', 'desc' => 'Buy Now, Pay Later']
                                            ];
                                            foreach ($defaultMethods as $method):
                                        ?>
                                        <div class="col-sm-6">
                                            <label class="payment-method-card <?= in_array($method['id'], $activeMethods) ? 'active' : '' ?>" for="method_<?= $method['id'] ?>">
                                                <input type="checkbox" name="paymongo_methods[]" value="<?= $method['id'] ?>" id="method_<?= $method['id'] ?>" <?= in_array($method['id'], $activeMethods) ? 'checked' : '' ?> class="d-none">
                                                <div class="card-content p-3 rounded-4 border bg-white shadow-sm transition-all">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <div class="icon-avatar rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                            <i class="<?= $method['icon'] ?> text-primary small"></i>
                                                        </div>
                                                        <span class="fw-bold small text-dark"><?= $method['name'] ?></span>
                                                        <i class="fa-solid fa-circle-check ms-auto text-success check-icon" style="opacity: <?= in_array($method['id'], $activeMethods) ? '1' : '0' ?>;"></i>
                                                    </div>
                                                    <span class="text-secondary smaller d-block fw-medium" style="color: #64748b !important;"><?= $method['desc'] ?></span>
                                                </div>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="paymongo_enabled" <?= ($settings['paymongo_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label small fw-bold text-dark">Automated Transaction Processing</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Surcharges & Tax Protocols Section -->
                    <div class="row g-5 mt-4 pt-4 border-top">
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                                    <i class="fa-solid fa-percent fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-800 m-0">Surcharge & Tax Protocols</h5>
                                    <span class="text-muted smaller fw-bold text-uppercase ls-1">Dynamic Maintenance & Gateway Fees</span>
                                </div>
                            </div>
                            
                            <div class="bg-light p-4 rounded-4 border">
                                <div class="row align-items-center">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-2 d-block">Service Fee Percentage (%)</label>
                                        <div class="input-group" style="max-width: 250px;">
                                            <input type="number" step="0.01" name="payment_tax_percent" class="form-control rounded-start-pill px-4 border-2" value="<?= htmlspecialchars($settings['payment_tax_percent'] ?? '0.00') ?>" placeholder="0.00">
                                            <span class="input-group-text rounded-end-pill px-3 border-2 bg-white fw-bold">%</span>
                                        </div>
                                        <p class="smaller text-muted mt-2 mb-0">This fee will be automatically added to all payment gateways (e.g., PayMongo 4% processing fee).</p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="form-check form-switch custom-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="payment_tax_enabled" <?= ($settings['payment_tax_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                            <label class="form-check-label small fw-bold text-dark">Enable Global Surcharge Calculation</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.text-gradient-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.premium-stat-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-switch .form-check-input {
    width: 3.2em;
    height: 1.6em;
    cursor: pointer;
    margin-right: 12px;
}

.payment-method-card {
    cursor: pointer;
    display: block;
}

.payment-method-card .card-content {
    border: 2px solid transparent !important;
}

.payment-method-card.active .card-content {
    border-color: var(--bs-primary) !important;
    background: #f0f7ff !important;
}

.btn-purple {
    background: #8b5cf6;
    color: white;
}

.btn-purple:hover {
    background: #7c3aed;
    color: white;
    transform: translateY(-2px);
}

.hover-translate-y:hover {
    transform: translateY(-2px);
}

#payment_methods_grid .fw-bold {
    color: #0f172a !important; /* Slate 900 */
}

#payment_methods_grid .text-secondary, 
#payment_methods_grid .smaller {
    color: #475569 !important; /* Slate 600 - darker for better readability */
}
</style>

<script>
$(document).ready(function() {
    $(document).on("change", ".payment-method-card input", function() {
        const card = $(this).closest(".payment-method-card");
        const check = card.find(".check-icon");
        if (this.checked) {
            card.addClass("active");
            check.css("opacity", "1");
        } else {
            card.removeClass("active");
            check.css("opacity", "0");
        }
    });

    function togglePayMongoMethods() {
        const pub = $("#paymongo_pub").val().trim();
        const sec = $("#paymongo_sec").val().trim();
        if (pub && sec) {
            $("#paymongo_methods_section").slideDown();
        } else {
            $("#paymongo_methods_section").slideUp();
        }
    }

    $("#btnDiscoverMethods").on("click", function() {
        const pub = $("#paymongo_pub").val().trim();
        const sec = $("#paymongo_sec").val().trim();
        const btn = $(this);
        const originalHtml = btn.html();

        if (!pub || !sec) {
            Feedback.fire({ icon: 'warning', title: 'Keys Required', text: 'Specify protocol keys before discovery.' });
            return;
        }

        btn.prop("disabled", true).html('<i class="fa-solid fa-circle-notch fa-spin me-2"></i>Probing Gateway...');

        $.ajax({
            url: '/tenant/?url=admin/fetch_payment_methods',
            method: 'POST',
            data: { public_key: pub, secret_key: sec, csrf_token: '<?= $_SESSION['csrf_token'] ?>' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Feedback.fire({ icon: 'success', title: 'Discovery Complete', text: response.message, timer: 1500, showConfirmButton: false });
                    
                    // Re-render grid with discovered methods if they changed (in real app)
                    // For now, we just ensure the section is visible
                    togglePayMongoMethods();
                } else {
                    Feedback.fire({ icon: 'error', title: 'Discovery Failed', text: response.message });
                }
            },
            complete: function() {
                btn.prop("disabled", false).html(originalHtml);
            }
        });
    });

    $("#paymongo_pub, #paymongo_sec").on("keyup change", togglePayMongoMethods);
    togglePayMongoMethods(); // Initial check

    $("#paymentSettingsForm").on("submit", function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalHtml = submitBtn.html();
        
        submitBtn.prop("disabled", true).html('<i class="fa-solid fa-circle-notch fa-spin me-2"></i>Processing Protocols...');

        $.ajax({
            url: form.attr("action"),
            method: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Feedback.fire({
                        icon: "success",
                        title: "Gateways Refined",
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                        background: 'rgba(255,255,255,0.95)',
                        backdrop: `rgba(0,123,255,0.05)`
                    });
                } else {
                    Feedback.fire({ icon: "error", title: "Configuration Fault", text: response.message });
                }
            },
            error: function() {
                Feedback.fire({ icon: "error", title: "System Boundary Error", text: "Communication failed." });
            },
            complete: function() {
                submitBtn.prop("disabled", false).html(originalHtml);
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
