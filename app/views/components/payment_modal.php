<!-- Reusable Payment Selection Modal -->
<style>
#paymentGatewayModal { z-index: 10001 !important; }
#paymentGatewayModal .modal-content {
    border-radius: 32px;
    border: none;
    box-shadow: 0 40px 100px rgba(0,0,0,0.25);
}
#paymentGatewayModal .payment-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    padding: 30px 40px;
    border-radius: 32px 32px 0 0;
}
.payment-method-option {
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.payment-method-option .card-body {
    border: 2px solid #f1f5f9;
    border-radius: 20px;
    padding: 20px;
}
.payment-method-option:hover .card-body {
    border-color: #c7d2fe;
    background: #f8faff;
    transform: translateY(-3px);
}
.payment-method-option.selected .card-body {
    border-color: #6366f1;
    background: #f0f7ff;
    box-shadow: 0 10px 25px rgba(99,102,241,0.1);
}
.payment-method-option.selected .check-mark {
    opacity: 1 !important;
}
.payment-icon-bg {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    color: #6366f1;
    font-size: 1.25rem;
}
</style>

<div class="modal fade" id="paymentGatewayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="payment-header text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-900 mb-1">Select Payment Protocol</h4>
                        <p class="text-white text-opacity-75 mb-0 small">Secure financial orchestration via verified gateways</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-white text-opacity-50" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark fs-4"></i>
                    </button>
                </div>
            </div>
            
            <div class="modal-body p-5 bg-white">
                <!-- Summary Card with Tax Breakdown -->
                <div class="bg-light p-4 rounded-4 mb-5 shadow-sm border">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted smaller fw-800 text-uppercase ls-1 d-block mb-1">Service Selection</span>
                            <h5 class="fw-900 text-dark mb-3" id="paymentModalPlanName">Plan Name</h5>
                            
                            <div id="taxBreakdownSection" style="display: none;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small fw-bold">Subtotal:</span>
                                    <span class="text-dark small fw-bold" id="paymentModalSubtotal">₱0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small fw-bold">Service Fee (<span id="paymentModalTaxRate">0</span>%):</span>
                                    <span class="text-dark small fw-bold" id="paymentModalTaxAmount">₱0.00</span>
                                </div>
                                <div class="border-top mt-2 pt-2 d-flex justify-content-between">
                                    <span class="text-dark fw-800 smaller text-uppercase">Grand Total Due:</span>
                                    <h4 class="fw-900 text-primary mb-0" id="paymentModalPrice">₱0.00</h4>
                                </div>
                            </div>
                            
                            <div id="simpleTotalSection">
                                <span class="text-muted small fw-800 text-uppercase ls-1 d-block mb-1">Total Due</span>
                                <h3 class="fw-900 text-primary mb-0" id="paymentModalPriceSimple">₱0.00</h3>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="rounded-4 bg-white p-3 border d-inline-block shadow-sm">
                                <i class="fa-solid fa-receipt fs-2 text-primary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="text-dark fw-800 small text-uppercase ls-1 mb-4">Available Gateways</h6>
                
                <div class="row g-3" id="paymentMethodsContainer">
                    <?php
                        $paymongoActive = ($paymentSettings['paymongo_enabled'] ?? '0') === '1';
                        $paymongoMethods = json_decode($paymentSettings['paymongo_methods'] ?? '[]', true);
                        $taxEnabled = ($paymentSettings['payment_tax_enabled'] ?? '0') === '1';
                        $taxPercent = (float)($paymentSettings['payment_tax_percent'] ?? 0);
                        $availableAny = false;

                        if ($paymongoActive && !empty($paymongoMethods)):
                            $availableAny = true;
                            $allPossible = [
                                'card' => ['name' => 'Credit/Debit Card', 'icon' => 'fa-solid fa-credit-card', 'desc' => 'Visa, Mastercard, JCB'],
                                'gcash' => ['name' => 'GCash', 'icon' => 'fa-solid fa-bolt', 'desc' => 'Instant API Clearance'],
                                'grab_pay' => ['name' => 'GrabPay', 'icon' => 'fa-solid fa-wallet', 'desc' => 'Grab-E Digital Wallet'],
                                'paymaya' => ['name' => 'Maya', 'icon' => 'fa-solid fa-money-bill-wave', 'desc' => 'Maya / PayMaya'],
                                'qrph' => ['name' => 'QRPh', 'icon' => 'fa-solid fa-qrcode', 'desc' => 'Universal PH QR Standard'],
                                'billease' => ['name' => 'BillEase', 'icon' => 'fa-solid fa-calendar-check', 'desc' => 'Buy Now, Pay Later']
                            ];

                            foreach ($paymongoMethods as $methodId):
                                if (!isset($allPossible[$methodId])) continue;
                                $m = $allPossible[$methodId];
                    ?>
                        <div class="col-md-6">
                            <div class="payment-method-option h-100" onclick="initiatePayMongoTransaction('<?= $methodId ?>', this)">
                                <div class="card-body bg-white shadow-sm position-relative">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="payment-icon-bg">
                                            <i class="<?= $m['icon'] ?>"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-800 mb-0"><?= $m['name'] ?></h6>
                                            <p class="text-muted smaller mb-0"><?= $m['desc'] ?></p>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-muted ms-auto opacity-50 payment-method-chevron"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!$availableAny): ?>
                        <div class="col-12 text-center py-4">
                            <div class="alert alert-warning border-0 rounded-4">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                No active payment protocols configured. Please contact support.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-5 text-center">
                    <p class="text-muted smaller mb-0">
                        <i class="fa-solid fa-lock me-1"></i> SSL Encrypted · PCI-DSS Compliant PayMongo Integration
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let paymentData = {
    planId: null,
    amount: 0,
    cycle: 'monthly',
    subId: null
};

const taxConfig = {
    enabled: <?= $taxEnabled ? 'true' : 'false' ?>,
    percent: <?= $taxPercent ?>
};

/**
 * Public method to trigger the payment modal.
 */
function openPaymentSelection(data) {
    paymentData = data;
    const subtotal = data.amount;
    let grandTotal = subtotal;
    
    document.getElementById('paymentModalPlanName').textContent = data.planName + (data.cycle ? ' (' + data.cycle.charAt(0).toUpperCase() + data.cycle.slice(1) + ')' : '');
    
    if (taxConfig.enabled && taxConfig.percent > 0) {
        const taxAmount = subtotal * (taxConfig.percent / 100);
        grandTotal = subtotal + taxAmount;
        
        document.getElementById('taxBreakdownSection').style.display = 'block';
        document.getElementById('simpleTotalSection').style.display = 'none';
        
        document.getElementById('paymentModalSubtotal').textContent = '₱' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('paymentModalTaxRate').textContent = taxConfig.percent;
        document.getElementById('paymentModalTaxAmount').textContent = '₱' + taxAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('paymentModalPrice').textContent = '₱' + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    } else {
        document.getElementById('taxBreakdownSection').style.display = 'none';
        document.getElementById('simpleTotalSection').style.display = 'block';
        document.getElementById('paymentModalPriceSimple').textContent = '₱' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    // Reset selection UI if any
    document.querySelectorAll('.payment-method-option').forEach(el => el.classList.remove('selected', 'loading'));

    // Show modal
    const payModalElement = document.getElementById('paymentGatewayModal');
    let payModal = bootstrap.Modal.getInstance(payModalElement);
    if (!payModal) payModal = new bootstrap.Modal(payModalElement);
    payModal.show();
}

function initiatePayMongoTransaction(method, element) {
    // Visual feedback
    document.querySelectorAll('.payment-method-option').forEach(el => el.style.opacity = '0.5');
    element.style.opacity = '1';
    element.classList.add('loading');
    element.querySelector('.card-body').style.borderColor = '#6366f1';
    const chevron = element.querySelector('.payment-method-chevron');
    if (chevron) {
        chevron.className = 'fa-solid fa-circle-notch fa-spin text-primary ms-auto payment-method-chevron';
    }

    submitPaymentReference('paymongo', method);
}

function submitPaymentReference(gateway, method = '') {
    Swal.fire({
        title: 'Initializing Secure Checkout',
        text: 'Generating your PayMongo verification link...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const formData = new URLSearchParams();
    
    // Support either subscription upgrade or generic payment
    if (paymentData.subId !== undefined && paymentData.subId !== null) formData.append('subscription_id', paymentData.subId);
    if (paymentData.planId) formData.append('new_plan_id', paymentData.planId);
    if (paymentData.cycle) formData.append('billing_cycle', paymentData.cycle);
    if (paymentData.paymentId) formData.append('payment_id', paymentData.paymentId);
    
    formData.append('payment_gateway', gateway);
    formData.append('payment_method', method);
    formData.append('csrf_token', '<?= Csrf::token() ?>');

    const initiateUrl = paymentData.initiateUrl || '/tenant/?url=admin/upgrade_plan';

    fetch(initiateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.redirect_url) {
            // Redirect immediately to PayMongo
            window.location.href = data.redirect_url;
        } else if (data.success) {
            // This should ideally not happen for PayMongo if success is true but redirect_url is missing
            Swal.fire({
                icon: 'warning',
                title: 'Partial Success',
                text: data.message || 'Subscription protocols initialized, but no redirect URL was provided. Please contact support.',
                confirmButtonColor: '#3b82f6'
            }).then(() => window.location.reload());
        } else {
            // Explicit failure from server
            let errorDetail = data.message || 'Payment initialization failed.';
            if (data.debug_info && data.debug_info.error) {
                errorDetail += `\n\nDetail: ${data.debug_info.error}`;
            }
            Swal.fire({
                icon: 'error',
                title: 'Payment Error',
                text: errorDetail,
                confirmButtonColor: '#ef4444'
            });
            resetModalUI();
        }
    })
    .catch(error => {
        console.error('Payment Error:', error);
        Swal.fire('Error', 'Gateway timeout. Please check your connection.', 'error');
        resetModalUI();
    });
}

function resetModalUI() {
    document.querySelectorAll('.payment-method-option').forEach(el => {
        el.style.opacity = '1';
        el.classList.remove('loading');
        el.querySelector('.card-body').style.borderColor = '#f1f5f9';
        const chevron = el.querySelector('.payment-method-chevron');
        if (chevron) {
            chevron.className = 'fa-solid fa-chevron-right text-muted ms-auto opacity-50 payment-method-chevron';
        }
    });
}
</script>
