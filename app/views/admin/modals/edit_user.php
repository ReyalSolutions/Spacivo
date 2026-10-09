<!-- Edit User Modal (Sapphire Blue Edition + Advanced Security) -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl overflow-hidden animate-scale-in" style="border-radius: 28px; background: rgba(255, 255, 255, 0.99); backdrop-filter: blur(30px);">
            <!-- Modal Header with Sapphire Blue Gradient -->
            <div class="modal-header border-0 p-4" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white/10 p-3 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner text-white">
                        <i class="fa-solid fa-user-pen fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-white mb-0" id="editUserModalLabel">Modify System Identity</h5>
                        <p class="text-white small mb-0 fw-500">Authorized administrative override for existing records</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <form id="editUserForm" action="/tenant/?url=admin/update_user" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                    <input type="hidden" name="user_id" id="editUserId">
                    
                    <div class="row g-4">
                        <!-- Identity Block (3 Columns) -->
                        <div class="col-md-4">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">First Name</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-signature"></i></span>
                                <input type="text" name="first_name" id="editFirst" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="Juan" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">First name is mandatory.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Middle Name</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-font"></i></span>
                                <input type="text" name="middle_name" id="editMiddle" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="Optional" style="border-radius: 0 16px 16px 0 !important;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Last Name</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" name="last_name" id="editLast" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="Dela Cruz" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">Last name is mandatory.</div>
                            </div>
                        </div>

                        <!-- Technical & Communication -->
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Username</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-at"></i></span>
                                <input type="text" name="username" id="editUser" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="juan_dc" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">Unique handle required.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Email Address</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" id="editEmail" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="juan.dc@stayhub.ph" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">Valid email needed.</div>
                            </div>
                        </div>

                        <!-- Role & Contact -->
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Phone Number</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-mobile-screen"></i></span>
                                <input type="tel" name="phone" id="editPhone" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="+63 9XX XXX XXXX" required style="border-radius: 0 16px 16px 0 !important;">
                                <div class="invalid-feedback ps-1 fw-bold">Contact info mandatory.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Access Privilege</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-user-lock"></i></span>
                                <select name="role" id="editRole" class="form-select border-0 bg-slate-50 fs-6 ps-1 fw-bold text-blue" required style="border-radius: 0 16px 16px 0 !important;">
                                    <option value="tenant">TENANT (Base)</option>
                                    <option value="owner">OWNER (Management)</option>
                                    <option value="admin">ADMIN (Root)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Security Credentials Override -->
                        <div class="col-md-6 mt-2">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">New Password (Override)</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-key"></i></span>
                                <input type="password" id="editPass" name="password" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="Leave blank to keep" style="border-radius: 0 !important;">
                                <button type="button" class="input-group-text bg-slate-50 border-0 text-slate-400 toggle-pass" data-target="editPass" style="border-radius: 0 16px 16px 0 !important;">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                            <div class="mt-2 px-1">
                                <div class="strength-meter-container">
                                    <div id="editStrengthMeter" class="strength-meter-bar"></div>
                                </div>
                                <span id="editStrengthText" class="smaller fw-700 text-muted mt-1 d-block">System entropy analysis...</span>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label small fw-800 text-slate-500 text-uppercase letter-spacing-wider">Confirm Override Key</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-slate-50 border-0 text-slate-400"><i class="fa-solid fa-check-double"></i></span>
                                <input type="password" id="editConfirm" name="confirm_password" class="form-control border-0 bg-slate-50 fs-6 ps-1" placeholder="••••••••" style="border-radius: 0 !important;">
                                <button type="button" class="input-group-text bg-slate-50 border-0 text-slate-400 toggle-pass" data-target="editConfirm" style="border-radius: 0 16px 16px 0 !important;">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                            <div id="editMatchNotice" class="smaller fw-800 text-rose mt-2 d-none pulse-match">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Keys do not match!
                            </div>
                        </div>

                        <!-- Update Logic Note -->
                        <div class="col-12 mt-2">
                            <div class="p-3 rounded-2xl bg-slate-100 border-0 d-flex gap-3 align-items-center">
                                <i class="fa-solid fa-info-circle text-blue fs-5"></i>
                                <p class="smaller text-muted mb-0 fw-500">
                                    Performing an identity override will update all demographic data instantly. Password changes will propagate across all verified sessions upon next entry.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden Submit -->
                    <button type="submit" id="hiddenEditSubmit" class="d-none"></button>
                </form>
            </div>

            <div class="modal-footer border-0 p-4 bg-slate-50 d-flex justify-content-between">
                <button type="button" class="btn btn-link text-slate-400 fw-700 text-decoration-none px-0" data-bs-dismiss="modal">CANCEL OVERRIDE</button>
                <button type="button" id="updateUserBtn" class="btn btn-blue-accent rounded-pill px-5 py-3 fw-800 shadow-xl transition-all">
                    SAVE IDENTITY UPDATE <i class="fa-solid fa-check-double ms-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Form Submission & Validation Fix
    const $editForm = $('#editUserForm');
    
    $('#updateUserBtn').on('click', function() {
        if (!$editForm[0].checkValidity()) {
            $editForm.addClass('was-validated');
            $('.modal-content').addClass('shake-effect');
            setTimeout(() => $('.modal-content').removeClass('shake-effect'), 500);
            return;
        }
        $(this).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> SAVING...').attr('disabled', true);
        $editForm.submit();
    });

    // Password Strength
    const $ePass = $('#editPass');
    const $eConfirm = $('#editConfirm');
    const $eMeter = $('#editStrengthMeter');
    const $eText = $('#editStrengthText');
    const $eNotice = $('#editMatchNotice');
    const $uBtn = $('#updateUserBtn');

    $ePass.on('input', function() {
        const val = $(this).val();
        if (val === '') {
            $eMeter.css('width', '0%');
            $eText.text('System entropy analysis...').css('color', '#64748b');
            return;
        }
        let strength = 0;
        if (val.length >= 8) strength += 25;
        if (/[A-Z]/.test(val)) strength += 25;
        if (/[0-9]/.test(val)) strength += 25;
        if (/[^A-Za-z0-9]/.test(val)) strength += 25;

        $eMeter.css('width', strength + '%');
        if (strength <= 25) { $eMeter.css('background', '#ef4444'); $eText.text('Vulnerable (Weak)').css('color', '#ef4444'); }
        else if (strength <= 50) { $eMeter.css('background', '#f59e0b'); $eText.text('Balanced (Medium)').css('color', '#f59e0b'); }
        else { $eMeter.css('background', '#10b981'); $eText.text('Fortified (Secure)').css('color', '#10b981'); }
    });

    function checkEditMatch() {
        if ($ePass.val() !== $eConfirm.val() && $eConfirm.val().length > 0) {
            $eNotice.removeClass('d-none');
            $uBtn.attr('disabled', true).css('opacity', '0.5');
        } else {
            $eNotice.addClass('d-none');
            $uBtn.attr('disabled', false).css('opacity', '1');
        }
    }

    $ePass.on('keyup', checkEditMatch);
    $eConfirm.on('keyup', checkEditMatch);
});
</script>
