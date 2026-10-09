<!-- High-Fidelity Identity Insight Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl overflow-hidden animate-zoom-in" style="border-radius: 24px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
            
            <!-- Sapphire Header with Glassmorphism -->
            <div class="modal-header border-0 p-4 position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e40af 100%);">
                <div class="header-content d-flex align-items-center gap-3">
                    <div class="header-icon bg-white bg-opacity-10 rounded-xl p-3 shadow-inner">
                        <i class="fa-solid fa-address-card text-white fs-3"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-800 text-white m-0">Identity Insight</h4>
                        <p class="text-white text-opacity-75 small m-0">Comprehensive access & profile telemetry</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-5">
                <div class="row g-4">
                    <!-- Identity Profile Section -->
                    <div class="col-lg-4 text-center border-end">
                        <div class="view-avatar-container mb-3">
                            <div id="viewAvatar" class="view-avatar mx-auto shadow-lg animate-float">
                                <span id="viewAvatarChar">U</span>
                            </div>
                        </div>
                        <h4 id="viewFullName" class="fw-bold text-dark m-0">Full Name</h4>
                        <p id="viewUsername" class="text-primary fw-semibold small mb-3">@username</p>
                        
                        <div id="viewStatusBadge" class="badge rounded-pill px-4 py-2 shadow-sm mb-3">
                            <i class="fa-solid fa-circle-check me-1"></i> ACTIVE
                        </div>
                    </div>

                    <!-- Meta Details Section -->
                    <div class="col-lg-8">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase mb-1"><i class="fa-solid fa-envelope me-2 text-primary"></i>Email Address</label>
                                <p id="viewEmail" class="fw-semibold text-dark mb-0">email@example.com</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase mb-1"><i class="fa-solid fa-phone me-2 text-primary"></i>Contact Number</label>
                                <p id="viewPhone" class="fw-semibold text-dark mb-0">+1 234 567 890</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase mb-1"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Account Role</label>
                                <p id="viewRole" class="fw-semibold text-dark mb-0 text-uppercase">TENANT</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase mb-1"><i class="fa-solid fa-calendar-day me-2 text-primary"></i>Registration Date</label>
                                <p id="viewJoined" class="fw-semibold text-dark mb-0">Oct 24, 2023</p>
                            </div>
                            <div class="col-12 mt-4 pt-3 border-top">
                                <div class="alert alert-info border-0 bg-primary bg-opacity-10 d-flex align-items-center gap-3" style="border-radius: 16px;">
                                    <i class="fa-solid fa-circle-info fs-4 text-primary"></i>
                                    <div class="small text-primary-dark fw-medium">
                                        This identity is under active cryptographic protection. All modifications are logged in the administrative audit trail.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Close Insight</button>
            </div>
        </div>
    </div>
</div>

<style>
.view-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #4f46e5 0%, #1e40af 100%);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
    font-weight: 800;
}

.view-avatar-container {
    padding: 10px;
    background: white;
    display: inline-block;
    border-radius: 40px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.badge-active { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.badge-restricted { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.animate-zoom-in { animation: zoomIn 0.3s ease-out forwards; }
</style>
