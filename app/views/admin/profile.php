<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<!-- Zero-Embedded PHP Architecture: Data Orchestrated via AJAX -->
<div class="container-fluid px-4 py-4 animate-fade-up">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-light">
        <div>
            <h3 class="fw-bold text-dark mb-1">Account Profile</h3>
            <p class="text-muted mb-0 small uppercase fw-semibold tracking-wider">Identity & Security Orchestration</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-light bg-opacity-50 px-3 py-2 rounded-pill shadow-sm">
                <li class="breadcrumb-item"><a href="/tenant/?url=admin/index" class="text-decoration-none text-primary fw-medium">Dashboard</a></li>
                <li class="breadcrumb-item active fw-bold">Profile</li>
            </ol>
        </nav>
    </div>

    <!-- Main Container: Populated via loadProfileData() -->
    <div id="profileLoading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted fw-semibold">Synchronizing with identity server...</p>
    </div>

    <form id="profileForm" action="/tenant/?url=admin/update_profile" method="POST" enctype="multipart/form-data" class="row g-4 d-none">
        <input type="hidden" name="csrf_token" value="">
        
        <!-- Left Column: Interactive Avatar & Quick Links -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-top" style="top: 100px; z-index: 10;">
                <div class="card-body text-center py-5">
                    <div class="position-relative d-inline-block mb-4 group">
                        <div id="avatarWrapper" class="avatar-luxe position-relative overflow-hidden rounded-circle border border-4 border-white shadow-lg mx-auto" style="width: 160px; height: 160px; cursor: pointer;">
                            <img src="" id="avatarPreview" class="w-100 h-100 object-fit-cover transition-all d-none" alt="Profile">
                            <div id="avatarPlaceholder" class="w-100 h-100 d-flex align-items-center justify-content-center bg-gradient-primary text-white">
                                <span class="display-2 fw-bold" id="initialsDisplay"></span>
                            </div>
                            <div class="avatar-overlay position-absolute inset-0 bg-dark bg-opacity-50 d-flex flex-column align-items-center justify-content-center text-white opacity-0 transition-all">
                                <i class="fa-solid fa-camera mb-2 fs-4"></i>
                                <span class="small fw-bold">Change Photo</span>
                            </div>
                        </div>
                        <input type="file" name="profile_image" id="profileImageInput" class="d-none" accept="image/*">
                        <div class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-3 border-white p-2 shadow-sm" style="margin-right: 5px; margin-bottom: 5px;">
                            <i class="fa-solid fa-check text-white fs-6"></i>
                        </div>
                    </div>

                    <h4 class="fw-extrabold text-dark mb-1" id="nameDisplay">---</h4>
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-4">
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3" id="roleBadge">
                            <i class="fa-solid fa-crown me-1 small"></i>
                        </span>
                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3">
                            <i class="fa-solid fa-circle me-1 small"></i>Active
                        </span>
                    </div>
                    
                    <div class="text-start mt-4 pt-4 border-top border-light">
                        <div class="p-3 rounded-4 bg-light bg-opacity-50 mb-3 transition-all hover-translate-x">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-shape bg-white shadow-sm rounded-3 p-2"><i class="fa-solid fa-envelope text-primary"></i></div>
                                <div class="overflow-hidden">
                                    <small class="text-muted d-block text-uppercase fw-bold ls-1">Email Connection</small>
                                    <span class="text-dark fw-bold text-truncate d-block" id="emailDisplay">---</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 rounded-4 bg-light bg-opacity-50 mb-3 transition-all hover-translate-x">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-shape bg-white shadow-sm rounded-3 p-2"><i class="fa-solid fa-phone text-primary"></i></div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold ls-1">Direct Line</small>
                                    <span class="text-dark fw-bold" id="phoneDisplay">---</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 rounded-4 bg-light bg-opacity-50 transition-all hover-translate-x">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-shape bg-white shadow-sm rounded-3 p-2"><i class="fa-solid fa-clock-rotate-left text-primary"></i></div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold ls-1">Onboarded Since</small>
                                    <span class="text-dark fw-bold" id="joinedDateDisplay">---</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Form Modules -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-4 d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                        <i class="fa-solid fa-user-pen fs-5"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">Personal Information</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="form-floating mb-0">
                                <input type="text" name="first_name" class="form-control bg-light border-0 rounded-4" id="floatingFirstName" placeholder="First Name" required>
                                <label for="floatingFirstName">First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-0">
                                <input type="text" name="middle_name" class="form-control bg-light border-0 rounded-4" id="floatingMiddleName" placeholder="Middle Name">
                                <label for="floatingMiddleName">Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-0">
                                <input type="text" name="last_name" class="form-control bg-light border-0 rounded-4" id="floatingLastName" placeholder="Last Name" required>
                                <label for="floatingLastName">Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-0">
                                <input type="text" name="username" class="form-control bg-light border-0 rounded-4" id="floatingUsername" placeholder="Username" required>
                                <label for="floatingUsername">User Identity (@)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-0">
                                <input type="text" name="phone" class="form-control bg-light border-0 rounded-4" id="floatingPhone" placeholder="Phone">
                                <label for="floatingPhone">Contact Phone</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating mb-0">
                                <input type="email" name="email" class="form-control bg-light border-0 rounded-4" id="floatingEmail" placeholder="Email" required>
                                <label for="floatingEmail">Email Address</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Architecture Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-3 text-warning">
                        <i class="fa-solid fa-shield-virus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Security Credentials</h5>
                        <small class="text-muted">Updated via secure cryptographic protocols</small>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="alert alert-warning border-0 rounded-4 bg-warning bg-opacity-10 py-3 d-flex align-items-start gap-3 mb-4">
                        <i class="fa-solid fa-circle-info mt-1"></i>
                        <span class="small">Leave both fields empty if you do NOT wish to modify your current encrypted password.</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" name="password" class="form-control bg-light border-0 rounded-4" id="floatingNewPass" placeholder="New Password">
                                <label for="floatingNewPass">New Password</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" name="confirm_password" class="form-control bg-light border-0 rounded-4" id="floatingConfirmPass" placeholder="Confirm Password">
                                <label for="floatingConfirmPass">Confirm New Password</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission Footer -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-5">
                <div class="d-flex align-items-center justify-content-between px-2">
                    <button type="reset" class="btn btn-outline-light border-0 text-muted fw-bold rounded-pill px-4 py-2">
                        <i class="fa-solid fa-undo me-2"></i>Discard Transitions
                    </button>
                    <button type="submit" id="submitBtn" class="btn btn-primary rounded-pill px-5 py-3 fw-extrabold shadow-lg-primary transition-all">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Synchronize Identity
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
    }

    .form-control {
        transition: all 0.3s ease;
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        color: #1e293b !important;
        font-weight: 500;
    }

    .form-control:focus {
        background-color: #fff !important;
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
    }

    .form-floating > .form-control {
        height: 64px !important;
        padding-top: 1.7rem !important;
        padding-bottom: 0.6rem !important;
    }

    .form-floating > label {
        padding-top: 1.2rem !important;
        padding-left: 1rem !important;
        color: #64748b !important;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        transform: scale(0.8) translateY(-1rem) translateX(0.15rem) !important;
        color: #6366f1 !important;
        opacity: 0.9 !important;
    }

    .card {
        border: 1px solid rgba(0,0,0,0.05) !important;
        backdrop-filter: blur(10px);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    }

    .avatar-luxe {
        border: 5px solid #fff !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .hover-translate-x:hover {
        transform: translateX(8px);
        background-color: rgba(99, 102, 241, 0.04) !important;
    }

    .ls-1 { letter-spacing: 0.5px; }

    .avatar-overlay {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(4px);
    }

    .avatar-luxe:hover .avatar-overlay {
        opacity: 1 !important;
    }

    .avatar-luxe:hover img {
        transform: scale(1.1);
    }

    .avatar-luxe img, .avatar-luxe div {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
        border: none;
        letter-spacing: 0.5px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
$(document).ready(function() {
    loadProfileData();

    // Avatar Selection
    $('#avatarWrapper').on('click', function() {
        $('#profileImageInput').click();
    });

    $('#profileImageInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#avatarPreview').attr('src', e.target.result).removeClass('d-none');
                $('#avatarPlaceholder').addClass('d-none');
            }
            reader.readAsDataURL(file);
        }
    });

    // Form Submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $btn = $('#submitBtn');
        const originalBtnHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner-third fa-spin me-2"></i>Synchronizing Identity...');
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Synchronized',
                        text: res.message,
                        timer: 2500,
                        showConfirmButton: false,
                        background: '#ffffff',
                        iconColor: '#22c55e'
                    });
                    
                    // Trigger UI Refresh
                    loadProfileData();
                    
                    // Clear security fields
                    $form.find('[name="password"], [name="confirm_password"]').val('');
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sync Failed',
                        text: res.message,
                        background: '#ffffff',
                        iconColor: '#f43f5e'
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = 'Architectural transit error occured.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'System Error', text: errorMsg });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    function loadProfileData() {
        // Fetch CSRF from meta
        const csrf = $('meta[name="csrf-token"]').attr('content');
        $('input[name="csrf_token"]').val(csrf);

        $.ajax({
            url: '/tenant/?url=admin/get_profile_async',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const user = res.user;
                    
                    // Populate Form
                    $('[name="first_name"]').val(user.first_name);
                    $('[name="middle_name"]').val(user.middle_name);
                    $('[name="last_name"]').val(user.last_name);
                    $('[name="username"]').val(user.username);
                    $('[name="phone"]').val(user.phone);
                    $('[name="email"]').val(user.email);
                    
                    // Update Displays
                    $('#nameDisplay').text(user.first_name + ' ' + (user.last_name || ''));
                    $('#initialsDisplay').text(user.first_name ? user.first_name.charAt(0).toUpperCase() : 'A');
                    $('#emailDisplay').text(user.email);
                    $('#phoneDisplay').text(user.phone || 'N/A');
                    $('#roleBadge').html('<i class="fa-solid fa-crown me-1 small"></i>' + res.roleLabel);
                    
                    const joined = new Date(user.created_at);
                    $('#joinedDateDisplay').text(joined.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }));

                    // Handle Avatar
                    if (user.image) {
                        $('#avatarPreview').attr('src', '/tenant/' + user.image).removeClass('d-none');
                        $('#avatarPlaceholder').addClass('d-none');
                        
                        // Global update
                        $('.admin-user-avatar, .header-avatar, .user-img').each(function() {
                            if ($(this).is('img')) {
                                $(this).attr('src', '/tenant/' + user.image);
                            } else {
                                $(this).html('<img src="/tenant/' + user.image + '" class="w-100 h-100 object-fit-cover rounded-circle">');
                            }
                        });
                    } else {
                        $('#avatarPreview').addClass('d-none');
                        $('#avatarPlaceholder').removeClass('d-none');
                        $('.admin-user-avatar, .header-avatar, .user-img').text(user.first_name.charAt(0).toUpperCase());
                    }

                    $('.admin-user-name, .header-name, .u-name').text(user.first_name + ' ' + user.last_name);

                    // Reveal Form
                    $('#profileLoading').addClass('d-none');
                    $('#profileForm').removeClass('d-none').addClass('animate-fade-up');
                }
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
