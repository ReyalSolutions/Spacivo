<?php 
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/admin_header.php'; 
?>

<div class="animate-fade-up">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-5 p-4 bg-white rounded-4 shadow-sm border">
        <div>
            <h2 class="fw-bold m-0 text-dark fs-4">System Orchestration</h2>
            <p class="text-muted mb-0 small fw-600 mt-1">Unified gateway for global parameters and feature architectures</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 header-actions w-100 w-md-auto">
            <button class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold transition-all w-100 w-md-auto btn-premium" style="height: 48px;" form="settingsForm">
                <i class="fa-solid fa-floppy-disk me-2"></i>Apply Changes
            </button>
        </div>
    </div>

    <form id="settingsForm" action="/tenant/?url=admin/save_settings" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="context" value="general">

        <div class="row g-5">
            <!-- Left Column: Core Settings -->
            <div class="col-lg-8">
                <!-- Branding Section -->
                <div class="premium-stat-card p-5 shadow-lg border-0 bg-white mb-5" style="border-radius: 32px;" id="branding">
                    <h4 class="fw-800 mb-4 d-flex align-items-center gap-3 text-primary">
                        <i class="fa-solid fa-shield-halved fs-3 me-2"></i> Company Branding
                    </h4>
                    <div class="row align-items-center g-4">
                        <div class="col-md-4 text-center">
                            <div class="position-relative d-inline-block">
                                <div id="logoWrapper" class="rounded-4 border-2 border-dashed d-flex align-items-center justify-content-center overflow-hidden bg-light" style="width: 150px; height: 150px; cursor: pointer;">
                                    <?php if (!empty($settings['company_logo'])): ?>
                                        <img src="/tenant/<?= htmlspecialchars($settings['company_logo']) ?>" id="logoPreview" class="w-100 h-100 object-fit-contain p-2">
                                    <?php else: ?>
                                        <div id="logoPlaceholder" class="text-muted text-center p-3">
                                            <i class="fa-solid fa-image fs-1 mb-2"></i>
                                            <p class="small mb-0">Click to Upload Logo</p>
                                        </div>
                                        <img id="logoPreview" class="w-100 h-100 object-fit-contain p-2 d-none">
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="company_logo" id="logoInput" class="d-none" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <p class="text-muted small mb-3">Your company logo will be displayed on the sidebar, reports, and administrative headers. Recommended size: 512x512px (PNG/WebP with transparency).</p>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="$('#logoInput').click()">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i>Upload New Identity
                            </button>
                        </div>
                    </div>
                </div>

                <!-- General Manifest Section -->
                <div class="premium-stat-card p-5 shadow-lg border-0 bg-white mb-5" style="border-radius: 32px;" id="general">
                    <h4 class="fw-800 mb-4 d-flex align-items-center gap-3 text-primary">
                        <i class="fa-solid fa-earth-asia fs-3 me-2"></i> Global Site Manifest
                    </h4>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Application Identity</label>
                            <input type="text" name="site_name" class="form-control rounded-pill px-4 border-2" value="<?= htmlspecialchars($settings['site_name'] ?? 'BNSC MIS') ?>" placeholder="Enter site name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Administrative Email</label>
                            <input type="email" name="admin_email" class="form-control rounded-pill px-4 border-2" value="<?= htmlspecialchars($settings['admin_email'] ?? 'admin@bnsc.edu.ph') ?>" placeholder="System alerts recipient">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Platform Meta Description</label>
                            <textarea name="site_desc" class="form-control rounded-4 border-2" rows="3"><?= htmlspecialchars($settings['site_desc'] ?? 'A high-fidelity management system for tenant and housing orchestration.') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Timezone Synchronicity</label>
                            <select name="timezone" class="form-select rounded-pill px-4 border-2">
                                <option value="Asia/Manila" <?= ($settings['timezone'] ?? 'Asia/Manila') === 'Asia/Manila' ? 'selected' : '' ?>>Asia/Manila (UTC+08:00)</option>
                                <option value="UTC" <?= ($settings['timezone'] ?? 'Asia/Manila') === 'UTC' ? 'selected' : '' ?>>Universal Coordinated Time (UTC)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Feature Manifest Section -->
                <div class="premium-stat-card p-5 shadow-lg border-0 bg-white mb-5" style="border-radius: 32px;" id="features">
                    <h4 class="fw-800 mb-4 d-flex align-items-center gap-3 text-primary">
                        <i class="fa-solid fa-vial-circle-check fs-3 me-2"></i> Experimental Feature Manifest
                    </h4>
                    <div class="bg-light p-4 rounded-4 border">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="debug_mode" <?= ($settings['debug_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-dark">System Debug Overlays</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="owner_self_reg" <?= ($settings['owner_self_reg'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-dark">Owner Self-Registration</label>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-check form-switch custom-switch p-3 border-danger border rounded-4 bg-danger bg-opacity-10 d-flex align-items-start gap-3">
                                    <input class="form-check-input flex-shrink-0 mt-1 border-danger" type="checkbox" name="maintenance_mode" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?> style="transform: scale(1.3); margin-left: 0;">
                                    <div>
                                        <label class="form-check-label fw-900 text-danger mb-1">Strict Maintenance Mode</label>
                                        <div class="text-danger small fw-700 opacity-75">Locks all public endpoints, landing pages, and tenant interfaces. Only administrators can bypass this lock via /tenant/?url=auth/login.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Navigation & Quick Links -->
            <div class="col-lg-4">
                <!-- Payment Gateway Card -->
                <div class="premium-stat-card p-4 shadow-lg border-0 text-white mb-4" style="border-radius: 24px; background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px;">
                            <i class="fa-solid fa-money-bill-transfer fs-2 text-white" style="color: white !important;"></i>
                        </div>
                        <h5 class="fw-800 m-0 ls-1">Payment Protocols</h5>
                    </div>
                    <p class="small opacity-90 mb-4 fw-medium">Configure GCash numbers, PayMongo API keys, and automated collection gateways.</p>
                    <a href="/tenant/?url=admin/payment_settings" class="btn btn-white w-100 rounded-pill fw-bold py-2 shadow-sm transition-all hover-translate-y">
                        Configure Gateways <i class="fa-solid fa-arrow-right ms-2 animate-bounce-x"></i>
                    </a>
                </div>

                <!-- Integrity Audit Card -->
                <div class="premium-stat-card p-4 shadow-sm border bg-white mb-4" style="border-radius: 24px;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center border shadow-sm" style="width: 52px; height: 52px;">
                            <i class="fa-solid fa-clock-rotate-left fs-3"></i>
                        </div>
                        <h5 class="fw-800 m-0 text-dark">Integrity Audit</h5>
                    </div>
                    <p class="small text-muted mb-4 fw-medium">Review all administrative orchestrations and system-wide state changes.</p>
                    <a href="/tenant/?url=admin/logs" class="btn btn-outline-primary w-100 rounded-pill fw-bold py-2 shadow-sm transition-all hover-translate-y">
                        View Audit Logs <i class="fa-solid fa-database ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.premium-stat-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.premium-stat-card:hover {
    transform: translateY(-5px);
}

.custom-switch .form-check-input {
    width: 3.2em;
    height: 1.6em;
    cursor: pointer;
    margin-right: 12px;
}

.btn-white {
    background: white;
    color: #2563eb;
    border: none;
}

.btn-white:hover {
    background: #f8fafc;
    color: #1d4ed8;
    transform: translateY(-2px);
}

.ls-1 { letter-spacing: 0.5px; }

.hover-translate-y:hover {
    transform: translateY(-2px);
}

.animate-bounce-x {
    animation: bounceX 1s infinite;
}

@keyframes bounceX {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(3px); }
}
</style>

<script>
$(document).ready(function() {
    // Logo Preview Logic
    $('#logoWrapper').on('click', function() {
        $('#logoInput').click();
    });

    $('#logoInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').attr('src', e.target.result).removeClass('d-none');
                $('#logoPlaceholder').addClass('d-none');
            }
            reader.readAsDataURL(file);
        }
    });

    $("#settingsForm").on("submit", function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalHtml = submitBtn.html();
        
        submitBtn.prop("disabled", true).html('<i class="fa-solid fa-circle-notch fa-spin me-2"></i>Persisting Protocols...');

        // Use FormData for file uploads
        const formData = new FormData(this);

        $.ajax({
            url: form.attr("action"),
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Orchestration Success",
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                        background: 'rgba(255,255,255,0.95)',
                        backdrop: `rgba(0,123,255,0.05)`
                    });

                    // Global UI Synchronicity
                    const newLogo = response.logo;
                    const siteName = form.find('[name="site_name"]').val();

                    // Update Browser Title
                    document.title = siteName + ' Admin';

                    // Update Sidebar Branding
                    const $header = $('.admin-sidebar-header');
                    if (newLogo) {
                        const logoSrc = '/tenant/' + newLogo;
                        if ($header.find('img.sidebar-logo').length) {
                            $header.find('img.sidebar-logo').attr('src', logoSrc);
                        } else {
                            $header.find('i.fa-solid').replaceWith('<img src="' + logoSrc + '" class="sidebar-logo">');
                        }
                    }
                    if (siteName) {
                        $header.find('span').text(siteName).attr('title', siteName);
                    }
                } else {
                    Swal.fire({ icon: "error", title: "Persistence Failure", text: response.message });
                }
            },
            error: function() {
                Swal.fire({ icon: "error", title: "System Boundary Error", text: "Communication failed." });
            },
            complete: function() {
                submitBtn.prop("disabled", false).html(originalHtml);
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
