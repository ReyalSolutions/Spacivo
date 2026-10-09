<!-- Add User Modal (Sapphire Blue Edition + Advanced Security) -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl overflow-hidden animate-scale-in" style="border-radius: 28px; background: rgba(255, 255, 255, 0.99); backdrop-filter: blur(30px);">
            <!-- Modal Header with Sapphire Blue Gradient -->
            <div class="modal-header border-0 p-4" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white/10 p-3 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner text-white">
                        <i class="fa-solid fa-user-shield fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-white mb-0" id="addUserModalLabel">Secure Identity Provisioning</h5>
                        <p class="text-white small mb-0 fw-500">Manual entry mode with encrypted credential injection</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <form id="addUserForm" action="/tenant/?url=admin/store_user" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                    
                    <div class="row g-4">
                        <!-- Identity Block (3 Columns) -->
                        <div class="col-md-4">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">First Name</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-signature"></i></span>
                                <input type="text" name="first_name" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="Juan" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">Identification requires a first name.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Middle Name</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-font"></i></span>
                                <input type="text" name="middle_name" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="Optional" style="border-radius: 0 16px 16px 0 !important;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Last Name</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" name="last_name" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="Dela Cruz" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">Family name is mandatory for records.</div>
                            </div>
                        </div>

                        <!-- Technical & Communication -->
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Username</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-at"></i></span>
                                <input type="text" name="username" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="juan_dc" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">System unique handle required.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Email Address</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="juan.dc@stayhub.ph" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">Valid digital address needed.</div>
                            </div>
                        </div>

                        <!-- Role & Contact -->
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Phone Number</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-mobile-screen"></i></span>
                                <input type="tel" name="phone" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="+63 9XX XXX XXXX" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">Critical for identity verification.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Assign Access Level</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-user-lock"></i></span>
                                <select name="role" class="form-select border-0 bg-slate-50 fs-6 ps-1 fw-bold text-blue" required style="border-radius: 0 16px 16px 0 !important;">
                                    <option value="tenant" selected>TENANT (Base)</option>
                                    <option value="owner">OWNER (Management)</option>
                                    <option value="admin">ADMIN (Root)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Secure Credentials -->
                        <div class="col-md-6 mt-2">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">System Password</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-key"></i></span>
                                <input type="password" id="adminPass" name="password" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="••••••••" required style="border-radius: 0 !important;">
                                <button type="button" class="input-group-text bg-slate-50 border-0 text-slate-400 toggle-pass" data-target="adminPass" style="border-radius: 0 16px 16px 0 !important;">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                            <div class="mt-2 px-1">
                                <div class="strength-meter-container">
                                    <div id="strengthMeter" class="strength-meter-bar"></div>
                                </div>
                                <span id="strengthText" class="smaller fw-700 text-muted mt-1 d-block">Awaiting complexity...</span>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Confirm Identity Key</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-check-double"></i></span>
                                <input type="password" id="adminConfirm" name="confirm_password" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="••••••••" required style="border-radius: 0 !important;">
                                <button type="button" class="input-group-text bg-slate-50 border-0 text-slate-400 toggle-pass" data-target="adminConfirm" style="border-radius: 0 16px 16px 0 !important;">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                            <div id="matchNotice" class="smaller fw-800 text-rose mt-2 d-none pulse-match">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Keys do not match!
                            </div>
                        </div>

                        <!-- Security Integrity Footer -->
                        <div class="col-12">
                            <div class="p-4 rounded-3xl bg-blue-50 border border-blue-100 d-flex gap-3 align-items-center mt-2">
                                <div class="bg-blue-600 p-2 rounded-xl text-white shadow-lg animate-pulse">
                                    <i class="fa-solid fa-vault fs-5"></i>
                                </div>
                                <div>
                                    <p class="small text-blue-900 mb-0 fw-800">Advanced Encryption Standard (AES)</p>
                                    <p class="smaller text-blue-600 mb-0 fw-500">Credentials will be salted and hashed using BCrypt before database persistence.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Submit for Form Association -->
                    <button type="submit" id="hiddenSubmit" class="d-none"></button>
                </form>
            </div>

            <div class="modal-footer border-0 p-4 bg-slate-50 d-flex justify-content-between">
                <button type="button" class="btn btn-link text-slate-400 fw-700 text-decoration-none px-0" data-bs-dismiss="modal">DISCARD IDENTITY</button>
                <button type="button" id="provisionBtn" class="btn btn-blue-accent rounded-pill px-5 py-3 fw-800 shadow-xl transition-all">
                    PROVISION IDENTITY <i class="fa-solid fa-shield-check ms-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Password Toggle
    $('.toggle-pass').on('click', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    // Form Submission & Validation Fix
    const $form = $('#addUserForm');
    
    $('#provisionBtn').on('click', function() {
        if (!$form[0].checkValidity()) {
            $form.addClass('was-validated');
            // Animate effect for emphasizing validation
            $('.modal-content').addClass('shake-effect');
            setTimeout(() => $('.modal-content').removeClass('shake-effect'), 500);
            return;
        }
        $(this).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> PROVISIONING...').attr('disabled', true);
        $form.submit();
    });

    // Password Strength Meter & Matching Logic
    const $pass = $('#adminPass');
    const $confirm = $('#adminConfirm');
    const $meter = $('#strengthMeter');
    const $text = $('#strengthText');
    const $notice = $('#matchNotice');
    const $btn = $('#provisionBtn');

    $pass.on('input', function() {
        const val = $(this).val();
        let strength = 0;
        
        if (val.length >= 8) strength += 25;
        if (/[A-Z]/.test(val)) strength += 25;
        if (/[0-9]/.test(val)) strength += 25;
        if (/[^A-Za-z0-9]/.test(val)) strength += 25;

        $meter.css('width', strength + '%');
        
        if (strength <= 25) { $meter.css('background', '#ef4444'); $text.text('Critical Danger (Too Weak)').css('color', '#ef4444'); }
        else if (strength <= 50) { $meter.css('background', '#f59e0b'); $text.text('Standard Risk (Medium)').css('color', '#f59e0b'); }
        else if (strength <= 75) { $meter.css('background', '#10b981'); $text.text('Secure Identity (Strong)').css('color', '#10b981'); }
        else { $meter.css('background', '#3b82f6'); $text.text('Maximum Entropy (Elite)').css('color', '#3b82f6'); }
    });

    function checkMatch() {
        if ($pass.val() !== $confirm.val() && $confirm.val().length > 0) {
            $notice.removeClass('d-none');
            $btn.attr('disabled', true).css('opacity', '0.5');
        } else {
            $notice.addClass('d-none');
            $btn.attr('disabled', false).css('opacity', '1');
        }
    }

    $pass.on('keyup', checkMatch);
    $confirm.on('keyup', checkMatch);
});
</script>

<style>
.bg-slate-50 { background-color: #f8fafc !important; }
.text-slate-400 { color: #94a3b8 !important; }
.text-slate-500 { color: #64748b !important; }
.bg-blue-50 { background-color: #eff6ff !important; }
.border-blue-100 { border-color: #dbeafe !important; }
.text-blue-900 { color: #1e3a8a !important; }
.text-blue-600 { color: #2563eb !important; }
.text-blue { color: #3b82f6 !important; }
.text-rose { color: #f43f5e !important; }
.btn-blue-accent { background: #2563eb; color: white; border: none; }
.btn-blue-accent:hover { background: #1d4ed8; color: white; transform: translateY(-3px); box-shadow: 0 12px 30px rgba(37, 99, 235, 0.4) !important; }
.shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important; }
.rounded-3xl { border-radius: 24px !important; }
.rounded-2xl { border-radius: 18px !important; }
.fw-800 { font-weight: 800; }
.smaller { font-size: 0.75rem; }

.strength-meter-container { height: 6px; width: 100%; background: #f1f5f9; border-radius: 100px; overflow: hidden; }
.strength-meter-bar { height: 100%; width: 0; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

.pulse-match { animation: pulseMatch 1.5s infinite; }
@keyframes pulseMatch { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }

.shake-effect { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>
