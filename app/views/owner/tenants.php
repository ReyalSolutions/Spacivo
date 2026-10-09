<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<style>
/* Elite Resident Directory Design */
.tenants-container {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.resident-card {
    background: #ffffff;
    border: 1px solid rgba(241, 245, 249, 0.8);
    border-radius: 28px;
    padding: 30px;
    box-shadow: 0 4px 30px rgba(0,0,0,0.02);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    height: 100%;
}

.resident-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
    border-color: rgba(37, 99, 235, 0.2);
}

.avatar-circle {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 900;
    margin-bottom: 20px;
}

.contact-action {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #f8fafc;
    color: #64748b;
    transition: all 0.2s;
    text-decoration: none;
}
.contact-action:hover {
    background: #2563eb;
    color: white;
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
}

.property-tag {
    font-size: 0.65rem;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: #2563eb;
    background: #eff6ff;
    padding: 4px 12px;
    border-radius: 100px;
    display: inline-block;
    margin-bottom: 15px;
}

.resident-status {
    width: 10px;
    height: 10px;
    background: #10b981;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
}
</style>

<div class="container-fluid px-4 py-5 tenants-container">
    <div class="row align-items-center mb-5">
        <div class="col-lg-8">
            <h1 class="display-4 fw-900 text-dark mb-2 letter-spacing--2">Resident Directory</h1>
            <p class="text-muted fs-5 mb-0 fw-500">Managing all verified occupants across your property portfolio.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
            <div class="d-flex justify-content-lg-end align-items-center gap-2">
                <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-800 py-2 border-0 shadow-sm" style="background: #2563eb;" data-elite-tooltip="Filtered Portfolio View">ALL ASSETS</button>
                </div>
                <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-800 py-2 shadow-sm" 
                        data-bs-toggle="modal" data-bs-target="#addTenantModal"
                        style="border-width: 2px;"
                        data-elite-tooltip="Provision New Resident Record">
                    <i class="fa-solid fa-user-plus me-2"></i>ADD TENANT
                </button>
            </div>
        </div>
    </div>

    <!-- Add Tenant Modal -->
    <div class="modal fade" id="addTenantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 py-4 px-4" style="background: linear-gradient(135deg, rgba(37,99,235,0.05), rgba(37,99,235,0.02));">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;border-radius:14px;background:rgba(37,99,235,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-user-shield" style="color:#2563eb;font-size:1.4rem;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Provision New Resident</h5>
                            <div class="small text-muted">Register a new tenant and assign residency.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTenantForm">
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Personal Details -->
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div style="width: 4px; height: 16px; background: #2563eb; border-radius: 10px;"></div>
                                    <h6 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.02em;">IDENTITY DETAILS</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">FIRST NAME</label>
                                        <input type="text" name="first_name" class="form-control rounded-3 border-light bg-light" placeholder="e.g. John" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">MIDDLE NAME</label>
                                        <input type="text" name="middle_name" class="form-control rounded-3 border-light bg-light" placeholder="Optional">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">LAST NAME</label>
                                        <input type="text" name="last_name" class="form-control rounded-3 border-light bg-light" placeholder="e.g. Doe" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Details -->
                            <div class="col-12">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                                        <div class="input-group">
                                            <span class="input-group-text border-0 bg-light rounded-start-3"><i class="fa-solid fa-envelope text-muted"></i></span>
                                            <input type="email" name="email" class="form-control border-light bg-light rounded-end-3" placeholder="john.doe@example.com" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">PHONE NUMBER</label>
                                        <div class="input-group">
                                            <span class="input-group-text border-0 bg-light rounded-start-3"><i class="fa-solid fa-phone text-muted"></i></span>
                                            <input type="tel" name="phone" class="form-control border-light bg-light rounded-end-3" placeholder="e.g. 09123456789" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Residency Selection -->
                            <div class="col-12 pt-2">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div style="width: 4px; height: 16px; background: #2563eb; border-radius: 10px;"></div>
                                    <h6 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.02em;">ASSIGN RESIDENCY</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">BOARDING HOUSE</label>
                                        <select name="house_id" id="houseSelect" class="form-select rounded-3 border-light bg-light" required>
                                            <option value="">Select a Property</option>
                                            <?php foreach ($houses as $house): ?>
                                                <option value="<?= $house['id'] ?>"><?= htmlspecialchars($house['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">ROOM ASSIGNMENT</label>
                                        <select name="room_id" id="roomSelect" class="form-select rounded-3 border-light bg-light" required disabled>
                                            <option value="">Select Property First</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="col-12">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">START DATE</label>
                                        <input type="date" name="start_date" class="form-control rounded-3 border-light bg-light" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">END DATE (OPTIONAL)</label>
                                        <input type="date" name="end_date" class="form-control rounded-3 border-light bg-light">
                                    </div>
                                </div>
                            </div>

                            <!-- Account Credentials -->
                            <div class="col-12 pt-2">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div style="width: 4px; height: 16px; background: #f59e0b; border-radius: 10px;"></div>
                                    <h6 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.02em;">ACCOUNT CREDENTIALS <span class="text-muted fw-normal">(Optional)</span></h6>
                                </div>
                                <div class="alert border-0 py-2 px-3 mb-3 rounded-3 small" style="background: #fffbeb; color: #92400e;">
                                    <i class="fa-solid fa-circle-info me-1"></i> If the email is already registered, these credentials will update that account.
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold text-muted">USERNAME</label>
                                        <div class="input-group">
                                            <span class="input-group-text border-0 bg-light rounded-start-3"><i class="fa-solid fa-at text-muted"></i></span>
                                            <input type="text" name="username" class="form-control border-light bg-light rounded-end-3" placeholder="e.g. johndoe" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label small fw-bold text-muted">PASSWORD</label>
                                        <div class="input-group">
                                            <span class="input-group-text border-0 bg-light rounded-start-3"><i class="fa-solid fa-lock text-muted"></i></span>
                                            <input type="password" name="password" class="form-control border-light bg-light" placeholder="Min. 8 characters" autocomplete="new-password">
                                            <button type="button" class="btn bg-light border-0 border-start px-3 toggle-pass" tabindex="-1"><i class="fa-solid fa-eye text-muted"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-4 px-4 rounded-bottom-4">
                        <button type="button" class="btn btn-link link-secondary text-decoration-none fw-bold small me-auto" data-bs-dismiss="modal">DISCARD CHANGES</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm py-2" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                            CONFIRM PROVISIONING
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($tenants)): ?>
            <div class="col-12 text-center py-5">
                <div class="mb-4">
                    <i class="fa-solid fa-user-slash text-muted opacity-10" style="font-size: 6rem;"></i>
                </div>
                <h3 class="fw-900 text-dark opacity-50">No active tenants found.</h3>
                <p class="text-muted fw-500">Residents will appear here once their bookings are approved and active.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tenants as $t): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="resident-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="avatar-circle">
                                <?= strtoupper(substr($t['tenant_name'], 0, 1)) ?>
                            </div>
                            <span class="badge bg-soft-success text-success fw-800 rounded-pill px-3 py-2 fs-7 border border-success opacity-75">
                                <span class="resident-status"></span> ACTIVE
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="property-tag"><?= htmlspecialchars($t['boarding_house_name']) ?></div>
                            <h4 class="fw-950 text-dark mb-1 h5"><?= htmlspecialchars($t['tenant_name']) ?></h4>
                            <div class="text-muted small fw-700">
                                <i class="fa-solid fa-door-open me-1"></i> Room: <?= htmlspecialchars($t['room_name']) ?>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top border-light">
                            <?php if (!empty($t['tenant_phone'])): ?>
                                <a href="tel:<?= $t['tenant_phone'] ?>" class="contact-action" data-elite-tooltip="Voice Call">
                                    <i class="fa-solid fa-phone"></i>
                                </a>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $t['tenant_phone']) ?>" target="_blank" class="contact-action" data-elite-tooltip="WhatsApp Message">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($t['tenant_email'])): ?>
                                <a href="mailto:<?= $t['tenant_email'] ?>" class="contact-action" data-elite-tooltip="Email Resident">
                                    <i class="fa-solid fa-envelope"></i>
                                </a>
                            <?php endif; ?>
                            <div class="ms-auto d-flex gap-2">
                                <a href="/tenant/?url=owner/payments&tenancy_id=<?= $t['id'] ?>" class="contact-action" data-elite-tooltip="View Financial Ledger">
                                    <i class="fa-solid fa-receipt"></i>
                                </a>
                                <button type="button" class="contact-action border-0 btn-edit-tenant"
                                    data-booking-id="<?= $t['id'] ?>"
                                    data-elite-tooltip="Edit Resident & Account">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Tenant Modal -->
<div class="modal fade" id="editTenantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 py-4 px-4" style="background: linear-gradient(135deg, rgba(16,185,129,0.06), rgba(16,185,129,0.02));">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;border-radius:14px;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-user-pen" style="color:#059669;font-size:1.4rem;"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Edit Resident Record</h5>
                        <div class="small text-muted">Update tenant details and account credentials.</div>
                    </div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTenantForm">
                <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                <input type="hidden" name="booking_id" id="edit_booking_id">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Identity -->
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div style="width: 4px; height: 16px; background: #059669; border-radius: 10px;"></div>
                                <h6 class="fw-bold mb-0 text-dark">IDENTITY DETAILS</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">FIRST NAME</label>
                                    <input type="text" name="first_name" id="edit_first_name" class="form-control rounded-3 border-light bg-light" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">MIDDLE NAME</label>
                                    <input type="text" name="middle_name" id="edit_middle_name" class="form-control rounded-3 border-light bg-light">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">LAST NAME</label>
                                    <input type="text" name="last_name" id="edit_last_name" class="form-control rounded-3 border-light bg-light" required>
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="col-12">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light rounded-start-3"><i class="fa-solid fa-envelope text-muted"></i></span>
                                        <input type="email" name="email" id="edit_email" class="form-control border-light bg-light rounded-end-3" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">PHONE NUMBER</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light rounded-start-3"><i class="fa-solid fa-phone text-muted"></i></span>
                                        <input type="tel" name="phone" id="edit_phone" class="form-control border-light bg-light rounded-end-3" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Residency Dates -->
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div style="width: 4px; height: 16px; background: #059669; border-radius: 10px;"></div>
                                <h6 class="fw-bold mb-0 text-dark">RESIDENCY TIMELINE</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 px-3 py-2">
                                        <div class="small text-muted mb-1 fw-bold">PROPERTY</div>
                                        <div class="fw-bold" id="edit_property_label" style="color:#059669;">—</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 px-3 py-2">
                                        <div class="small text-muted mb-1 fw-bold">ROOM</div>
                                        <div class="fw-bold" id="edit_room_label" style="color:#059669;">—</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">START DATE</label>
                                    <input type="date" name="start_date" id="edit_start_date" class="form-control rounded-3 border-light bg-light" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">END DATE (OPTIONAL)</label>
                                    <input type="date" name="end_date" id="edit_end_date" class="form-control rounded-3 border-light bg-light">
                                </div>
                            </div>
                        </div>

                        <!-- Account Credentials -->
                        <div class="col-12 pt-2">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div style="width: 4px; height: 16px; background: #f59e0b; border-radius: 10px;"></div>
                                <h6 class="fw-bold mb-0 text-dark">ACCOUNT CREDENTIALS</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold text-muted">USERNAME</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light rounded-start-3"><i class="fa-solid fa-at text-muted"></i></span>
                                        <input type="text" name="username" id="edit_username" class="form-control border-light bg-light rounded-end-3" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label small fw-bold text-muted">NEW PASSWORD <span class="text-muted fw-normal">(leave blank to keep current)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light rounded-start-3"><i class="fa-solid fa-lock text-muted"></i></span>
                                        <input type="password" name="password" class="form-control border-light bg-light" placeholder="Min. 8 characters" autocomplete="new-password">
                                        <button type="button" class="btn bg-light border-0 border-start px-3 toggle-pass" tabindex="-1"><i class="fa-solid fa-eye text-muted"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-4 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-link link-secondary text-decoration-none fw-bold small me-auto" data-bs-dismiss="modal">DISCARD CHANGES</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm py-2">
                        SAVE CHANGES
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // ─── Password Toggle ───
    $(document).on('click', '.toggle-pass', function() {
        const $input = $(this).closest('.input-group').find('input[type="password"], input[type="text"]').first();
        const isPass = $input.attr('type') === 'password';
        $input.attr('type', isPass ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // ─── Dynamic Room Selection (Add Modal) ───
    $('#houseSelect').on('change', function() {
        const houseId = $(this).val();
        const $roomSelect = $('#roomSelect');
        if (!houseId) {
            $roomSelect.html('<option value="">Select Property First</option>').attr('disabled', true);
            return;
        }
        $roomSelect.html('<option value="">Loading rooms...</option>').attr('disabled', true);
        $.getJSON('/tenant/?url=owner/get_available_rooms_json', { house_id: houseId }, function(res) {
            if (res.success && res.data.length > 0) {
                let opts = '<option value="">Choose a room</option>';
                res.data.forEach(r => {
                    opts += `<option value="${r.id}">${r.room_name} (₱${parseFloat(r.price).toLocaleString()} — ${r.available_slots} slots left)</option>`;
                });
                $roomSelect.html(opts).attr('disabled', false);
            } else {
                $roomSelect.html('<option value="">No available rooms</option>').attr('disabled', true);
            }
        }).fail(() => $roomSelect.html('<option value="">Error loading rooms</option>').attr('disabled', true));
    });

    // ─── Add Tenant Form ───
    $('#addTenantForm').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this), $btn = $form.find('button[type="submit"]'), orig = $btn.html();
        $btn.html('<i class="fa-solid fa-circle-notch fa-spin me-2"></i>PROVISIONING...').attr('disabled', true);
        $.post('/tenant/?url=owner/store_tenant', $form.serialize(), function(res) {
            if (res.success) {
                $('#addTenantModal').modal('hide');
                Feedback.fire({ icon: 'success', title: 'Provisioning Successful', text: res.message, confirmButtonColor: '#2563eb', timer: 3000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Feedback.fire({ icon: 'error', title: 'Verification Failed', text: res.message, confirmButtonColor: '#2563eb' });
                $btn.html(orig).attr('disabled', false);
            }
        }).fail(function(xhr) {
            let msg = 'Unexpected error occurred.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
            Feedback.fire({ icon: 'error', title: 'System Error', text: msg, confirmButtonColor: '#2563eb' });
            $btn.html(orig).attr('disabled', false);
        });
    });

    // ─── Open Edit Modal → Pre-fill data ───
    $(document).on('click', '.btn-edit-tenant', function() {
        const bookingId = $(this).data('booking-id');
        const $modal = $('#editTenantModal');
        const $form  = $('#editTenantForm');
        const $body  = $modal.find('.modal-body');

        // Disable form and show spinner overlay
        $form.find('input, select, button[type="submit"]').attr('disabled', true);
        $body.css('position', 'relative');
        if (!$body.find('.edit-loading-overlay').length) {
            $body.append('<div class="edit-loading-overlay" style="position:absolute;inset:0;background:rgba(255,255,255,0.85);display:flex;align-items:center;justify-content:center;z-index:10;border-radius:1rem;"><i class="fa-solid fa-circle-notch fa-spin fa-2x text-primary"></i></div>');
        }
        $modal.modal('show');

        $.getJSON('/tenant/?url=owner/get_tenant_json', { booking_id: bookingId }, function(res) {
            $body.find('.edit-loading-overlay').remove();
            $form.find('input, select, button[type="submit"]').attr('disabled', false);

            if (!res.success) {
                Feedback.fire({ icon: 'error', title: 'Load Failed', text: res.message, confirmButtonColor: '#2563eb' });
                $modal.modal('hide');
                return;
            }
            const d = res.data;
            $('#edit_booking_id').val(d.booking_id);
            $('#edit_first_name').val(d.first_name);
            $('#edit_middle_name').val(d.middle_name || '');
            $('#edit_last_name').val(d.last_name);
            $('#edit_email').val(d.email);
            $('#edit_phone').val(d.phone);
            $('#edit_username').val(d.username || '');
            $('#edit_start_date').val(d.start_date || '');
            $('#edit_end_date').val(d.end_date || '');
            $('#edit_property_label').text(d.boarding_house_name || '—');
            $('#edit_room_label').text(d.room_name || '—');
        }).fail(function() {
            $body.find('.edit-loading-overlay').remove();
            $form.find('input, select, button[type="submit"]').attr('disabled', false);
            Feedback.fire({ icon: 'error', title: 'Error', text: 'Failed to load resident data.', confirmButtonColor: '#2563eb' });
            $modal.modal('hide');
        });
    });

    // ─── Edit Tenant Form ───
    $('#editTenantModal').on('submit', '#editTenantForm', function(e) {
        e.preventDefault();
        const $form = $(this), $btn = $form.find('button[type="submit"]'), orig = $btn.html();
        $btn.html('<i class="fa-solid fa-circle-notch fa-spin me-2"></i>SAVING...').attr('disabled', true);
        $.post('/tenant/?url=owner/update_tenant', $form.serialize(), function(res) {
            if (res.success) {
                $('#editTenantModal').modal('hide');
                Feedback.fire({ icon: 'success', title: 'Record Updated', text: res.message, confirmButtonColor: '#059669', timer: 3000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Feedback.fire({ icon: 'error', title: 'Update Failed', text: res.message, confirmButtonColor: '#2563eb' });
                $btn.html(orig).attr('disabled', false);
            }
        }).fail(function(xhr) {
            let msg = 'Unexpected error occurred.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
            Feedback.fire({ icon: 'error', title: 'System Error', text: msg, confirmButtonColor: '#2563eb' });
            $btn.html(orig).attr('disabled', false);
        });
    });

});
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
