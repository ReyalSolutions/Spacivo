<style>
/* --- Premium Landing Footer (Reusable Component) --- */
.landing-footer {
    background: #0b1220; /* Premium deep dark navy */
    color: #cbd5e1;
    font-family: 'Outfit', sans-serif;
    position: relative;
    overflow: hidden;
    padding-top: 5rem;
    padding-bottom: 2rem;
    margin-top: auto; /* Pushes footer to bottom of flex container */
}

.landing-footer .footer-heading {
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.5px;
    margin-bottom: 1.5rem;
}

.landing-footer .brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    margin-bottom: 1.5rem;
}

.landing-footer .brand .text-dark {
    color: #ffffff !important;
}

.landing-footer .footer-links {
    padding-left: 0;
    list-style: none;
}

.landing-footer .footer-links li {
    margin-bottom: 12px;
}

.landing-footer .footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.landing-footer .footer-links a:hover {
    color: #ffffff;
    transform: translateX(5px);
}

.landing-footer .social-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.05);
    color: #f8fafc;
    border-radius: 50%;
    font-size: 1.1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    border: 1px solid rgba(255,255,255,0.1);
}

.landing-footer .social-icon:hover {
    background: #4f46e5;
    color: white;
    transform: translateY(-3px);
    border-color: #4f46e5;
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
}

.landing-footer .newsletter-group {
    background: rgba(255,255,255,0.05);
    border-radius: 100px;
    padding: 4px;
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
    display: flex;
}

.landing-footer .newsletter-group:focus-within {
    border-color: #4f46e5;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
}

.landing-footer .newsletter-group .form-control {
    border: none;
    background: transparent;
    color: white;
    box-shadow: none;
    padding-left: 16px;
    font-size: 0.95rem;
}

.landing-footer .newsletter-group .form-control::placeholder {
    color: #64748b;
}

.landing-footer .newsletter-group .form-control:focus {
    box-shadow: none;
}

.landing-footer .newsletter-group .btn {
    border-radius: 100px !important;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: #4f46e5;
    color: white;
    border: none;
}

.landing-footer .newsletter-group .btn:hover {
    background: #4338ca;
}

.landing-footer .footer-bottom {
    padding-top: 1.5rem;
    margin-top: 4rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.landing-footer .footer-legal a {
    color: #64748b;
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.2s ease;
}

.landing-footer .footer-legal a:hover {
    color: #ffffff;
}

/* Optional: hide the minimal dashboard footer if it exists below */
.app-footer {
    display: none !important;
}

/* Visibility Variant: Desktop Only */
.landing-footer.desktop-only {
    display: none !important;
}

@media (min-width: 992px) {
    .landing-footer.desktop-only {
        display: block !important;
    }
}
</style>

<footer class="landing-footer <?= isset($footerVariant) ? $footerVariant : '' ?>">
    <div class="container-fluid px-lg-5">
        <div class="row g-5">
            <div class="col-lg-4 pe-lg-5">
                <a class="brand" href="/tenant/">
                    <i class="fa-solid fa-house-chimney-window fs-2 text-primary"></i>
                    <span class="fs-3 fw-bold text-dark">StayHub</span>
                </a>
                <p class="text-white pe-md-4 mb-4" style="line-height: 1.6; font-size: 0.95rem;">
                    The ultimate ecosystem for discovering, managing, and experiencing premium boarding houses seamlessly across the Philippines.
                </p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="social-icon"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="col-6 col-lg-2 offset-lg-1">
                <h5 class="footer-heading">Discover</h5>
                <ul class="footer-links">
                    <li><a href="/tenant/?url=boarding/explore">Find a Room</a></li>
                    <li><a href="#featured">Featured Stays</a></li>
                    <li><a href="#how-it-works">How it Works</a></li>
                    <li><a href="#pricing">Pricing Plans</a></li>
                </ul>
            </div>
            
            <div class="col-6 col-lg-2">
                <h5 class="footer-heading">Platform</h5>
                <ul class="footer-links">
                    <li><a href="/tenant/?url=auth/login">Tenant Portal</a></li>
                    <li><a href="/tenant/?url=auth/register">Sign Up</a></li>
                    <li><a href="#">Owner Dashboard</a></li>
                    <li><a href="#">Help Center</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3">
                <h5 class="footer-heading">Stay In The Loop</h5>
                <p class="mb-3" style="color: #94a3b8; font-size: 0.9rem;">Subscribe to get the latest premium listings and offers directly to your inbox.</p>
                <div class="newsletter-group">
                    <input type="email" class="form-control" placeholder="Email Address" aria-label="Email Address">
                    <button class="btn" type="button"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="m-0 fw-medium" style="color: #94a3b8; font-size: 0.85rem;">© <?= date('Y') ?> Reyal Solutions, Inc. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline m-0 footer-legal">
                        <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
                        <li class="list-inline-item ms-3"><a href="#">Terms of Service</a></li>
                        <li class="list-inline-item ms-3"><a href="#">Cookie settings</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
