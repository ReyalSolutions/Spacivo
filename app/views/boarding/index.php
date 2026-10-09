<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Google Fonts: Inter & Outfit -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/tenant/public/assets/css/boarding.css">



<!-- Immersive Background Support -->
<div class="bg-blobs">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<div class="container overflow-visible">
    <!-- Hero Section -->
    <section class="hero-container">
        <div class="hero-content">
            <div class="hero-badge" data-aos="fade-down" data-aos-delay="100">
                <i class="fa-solid fa-crown"></i>
                <span>Philippines' #1 Boarding Network</span>
            </div>
            <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                Find your <span>perfect stay</span> without the friction.
            </h1>
            <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="300">
                The most intelligent way to discover, book, and live in premium boarding houses. Zero hidden fees, 100% verified listings.
            </p>
            <div class="hero-cta-group" data-aos="fade-up" data-aos-delay="400">
                <a href="/tenant/?url=boarding/explore" class="btn btn-hero btn-primary-hero">
                    <i class="fa-solid fa-key me-2"></i>
                    <span>Rent Now</span>
                    <i class="fa-solid fa-arrow-right ms-3"></i>
                </a>
                <a href="#how-it-works" class="btn btn-hero btn-secondary-hero">
                    <i class="fa-solid fa-circle-play me-2 text-primary"></i>
                    How it Works
                </a>
            </div>
        </div>

        <div class="hero-visual" data-aos="fade-left" data-aos-delay="200">
            <div class="hero-image-container">
                <div class="hero-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1522770179533-24471fcdba45?ixlib=rb-4.0.3&auto=format&fit=crop&w=1400&q=80" alt="Modern Room" class="hero-image">
                </div>
            </div>
            
            <!-- Floating Social Proof -->
            <div class="floating-card card-1">
                <div class="bg-primary text-white p-2 rounded-circle">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <h5 class="m-0 fw-bold">Verified</h5>
                    <p class="m-0 text-muted small">Quality Guaranteed</p>
                </div>
            </div>

            <div class="floating-card card-2">
                <div class="avatar-group d-flex me-2">
                    <img src="https://ui-avatars.com/api/?name=A&background=random" class="rounded-circle border border-white" width="30" alt="">
                    <img src="https://ui-avatars.com/api/?name=B&background=random" class="rounded-circle border border-white ms-n2" width="30" alt="">
                    <img src="https://ui-avatars.com/api/?name=C&background=random" class="rounded-circle border border-white ms-n2" width="30" alt="">
                </div>
                <div>
                    <h5 class="m-0 fw-bold">5k+</h5>
                    <p class="m-0 text-muted small">Happy Tenants</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Box Stats Section -->
    <section class="stats-section">
        <div class="stats-bento-grid">
            <!-- Properties Bento Card -->
            <div class="bento-card bento-large" data-aos="fade-right">
                <i class="fa-solid fa-hotel stat-visual" style="font-size: 8rem; top: 40px; right: -20px; opacity: 0.05;"></i>
                <span class="bento-value counter" data-target="500">0</span>
                <span class="bento-label">
                    Total Properties <span class="stat-trend">+15% Monthy</span>
                </span>
            </div>

            <!-- Retention Bento Card -->
            <div class="bento-card bento-medium" data-aos="fade-down" data-aos-delay="100">
                <div class="stat-visual">
                    <svg class="progress-ring">
                        <circle class="progress-ring-circle" r="28" cx="30" cy="30" style="stroke-dasharray: 175.9; stroke-dashoffset: 3.5;"></circle>
                    </svg>
                </div>
                <span class="bento-value counter" data-target="98">0</span>
                <span class="bento-label">Retention</span>
            </div>

            <!-- Rating Bento Card -->
            <div class="bento-card bento-medium" data-aos="fade-down" data-aos-delay="200">
                <div class="text-warning mb-2" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                </div>
                <span class="bento-value counter" data-target="4.9" data-decimals="1">0</span>
                <span class="bento-label">Customer Satisfaction</span>
            </div>

            <!-- Visits Bento Card -->
            <div class="bento-card bento-wide" data-aos="fade-left" data-aos-delay="300">
                <div class="stat-visual" style="width: 200px; right: 0;">
                    <svg viewBox="0 0 200 80" width="100%" height="100%" preserveAspectRatio="none">
                        <path d="M0,70 Q40,60 80,40 T160,20 T200,10" fill="none" stroke="#4f46e5" stroke-width="2" opacity="0.3"></path>
                    </svg>
                </div>
                <span class="bento-value counter" data-target="2">0</span>
                <span class="bento-label">Verified Annual Visits (M) <span class="stat-trend">Growing</span></span>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="section-header text-center" data-aos="fade-up">
            <h5 class="text-primary fw-bold text-uppercase mb-3" style="letter-spacing: 3px;">The Journey</h5>
            <h2 class="display-4 fw-black">Seamless Rental Workflow</h2>
        </div>
        
        <div class="step-grid">
            <div class="step-connector"></div>
            
            <div class="step-card" data-aos="fade-up" data-aos-delay="100">
                <div class="step-number">01</div>
                <div class="step-icon">
                    <i class="fa-solid fa-compass-drafting"></i>
                </div>
                <h3 class="step-title">Discover & Explore</h3>
                <p class="step-desc">Utilize dynamic smart filters to find verified boarding houses near your target destination with real-time maps.</p>
            </div>
            
            <div class="step-card" data-aos="fade-up" data-aos-delay="200">
                <div class="step-number">02</div>
                <div class="step-icon">
                    <i class="fa-solid fa-bolt-lightning"></i>
                </div>
                <h3 class="step-title">Instant Reservation</h3>
                <p class="step-desc">Secure your premium space in less than 60 seconds with our integrated and safe payment gateway. Zero manual delays.</p>
            </div>
            
            <div class="step-card" data-aos="fade-up" data-aos-delay="300">
                <div class="step-number">03</div>
                <div class="step-icon">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3 class="step-title">Seamless Check-in</h3>
                <p class="step-desc">Receive your high-speed digital check-in guide and immediately move into your verified, community-driven residence.</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section pb-20" id="features">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <h5 class="text-primary fw-bold text-uppercase mb-3" style="letter-spacing: 3px;">Capabilities</h5>
            <h2 class="display-4 fw-black">Engineered for Excellence</h2>
        </div>
        <div class="row g-4 mt-2">
            <!-- Feature 1: AI Matching (Large) -->
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card h-100">
                    <i class="fa-solid fa-wand-magic-sparkles feature-bg-icon"></i>
                    <div class="feature-icon-wrapper">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <div class="position-relative z-3">
                        <h3 class="feature-title display-6">Expert AI Matching</h3>
                        <p class="feature-text fs-5">Our proprietary neural engine analyzes thousands of data points—from commute patterns to lifestyle preferences—to match you with the perfect community, not just a room.</p>
                        <div class="mt-4">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Neural Engine 2.0</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill ms-2">99.8% Match Rate</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Feature 2: Verified Community (Small) -->
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card h-100">
                    <i class="fa-solid fa-shield-halved feature-bg-icon"></i>
                    <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #ec4899 0%, #d946ef 100%);">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h3 class="feature-title">Elite Vetting</h3>
                    <p class="feature-text">Every tenant and owner undergoes a rigorous 5-point verification process, ensuring a secure and prestigious environment for all members.</p>
                </div>
            </div>

            <!-- Feature 3: Automated Billing (Small) -->
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card h-100">
                    <i class="fa-solid fa-receipt feature-bg-icon"></i>
                    <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #3b82f6 0%, #2ecc71 100%);">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h3 class="feature-title">Smart Billing</h3>
                    <p class="feature-text">Automated recurring payments with built-in credit score building for on-time payments. Finance meets lifestyle.</p>
                </div>
            </div>

            <!-- Feature 4: Premium Analytics (Large) -->
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card h-100">
                    <i class="fa-solid fa-chart-line feature-bg-icon"></i>
                    <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);">
                        <i class="fa-solid fa-gem"></i>
                    </div>
                    <div class="position-relative z-3">
                        <h3 class="feature-title display-6">Advanced Yield Analytics</h3>
                        <p class="feature-text fs-5">For owners, StayHub provides deep-learning insights into occupancy trends, market pricing optimization, and automated ROI forecasting tools.</p>
                        <div class="mt-4">
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Owner Exclusive</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill ms-2">Real-time Forecast</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section py-20" id="pricing">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <h5 class="text-primary fw-bold text-uppercase mb-3" style="letter-spacing: 3px;">Economics</h5>
                <h2 class="display-4 fw-black">Simple, Tiered Pricing</h2>
                
            <div class="pricing-toggle-wrapper mt-5" data-aos="fade-up">
                <div class="segmented-control" id="pricing-control">
                    <div class="segment active" data-value="monthly">Monthly</div>
                    <div class="segment" data-value="yearly">
                        Yearly 
                        <span class="save-badge-mini">Save 17%</span>
                    </div>
                    <div class="segment-slider"></div>
                    <input type="checkbox" id="pricing-toggle" style="display: none;">
                </div>
            </div>
            </div>

            <div class="row g-4 justify-content-center">
                <?php foreach ($plans as $plan): 
                    $features = json_decode($plan['features'], true) ?: [];
                    $isPopular = ((int)$plan['id'] === ($popularPlanId ?? 0));
                    
                    // Style mapping based on plan name
                    $badgeStyle = '';
                    $badgeTextSnippet = '';
                    $cardClass = 'pricing-card h-100 text-center';
                    $btnClass = 'btn btn-outline-primary w-100 py-3 rounded-pill fw-bold';
                    $priceColor = '';

                    switch(strtolower($plan['name'])) {
                        case 'starter':
                            $badgeStyle = 'bg-light text-muted';
                            $badgeTextSnippet = 'Free';
                            break;
                        case 'plus':
                            $badgeStyle = 'style="background: rgba(236,72,153,0.1); color: #ec4899;"';
                            $badgeTextSnippet = 'Essential';
                            break;
                        case 'pro':
                            $badgeStyle = 'style="background: rgba(79, 70, 229, 0.1); color: #4f46e5;"';
                            $badgeTextSnippet = 'Professional';
                            $btnClass = 'btn btn-outline-primary w-100 py-3 rounded-pill fw-bold';
                            $priceColor = 'style="color: #4f46e5;"';
                            break;
                        case 'elite':
                            $badgeStyle = 'style="background: rgba(15, 23, 42, 0.1); color: #0f172a;"';
                            $badgeTextSnippet = 'Ultimate';
                            $btnClass = 'btn btn-outline-dark w-100 py-3 rounded-pill fw-bold';
                            break;
                    }

                    if ($isPopular) {
                        $cardClass .= ' pricing-popular';
                        $btnClass = 'btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-lg';
                    }
                ?>
                    <div class="col-lg-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="<?= $cardClass ?>">
                            <?php if ($isPopular): ?>
                                <div class="position-absolute top-0 start-50 translate-middle">
                                    <span class="badge bg-primary px-3 py-2 rounded-pill shadow-lg">Most Popular</span>
                                </div>
                            <?php endif; ?>

                            <span class="plan-badge <?= strpos($badgeStyle, 'style=') === false ? $badgeStyle : '' ?>" <?= strpos($badgeStyle, 'style=') !== false ? $badgeStyle : '' ?>>
                                <?= $badgeTextSnippet ?>
                            </span>
                            <h3 class="h4 fw-bold mb-4"><?= htmlspecialchars($plan['name']) ?></h3>
                            
                            <div class="price mb-4">
                                <div class="price-monthly">
                                    <span class="price-amount" <?= $priceColor ?>><span class="price-currency">₱</span><?= number_format((float)$plan['price_monthly'], 0) ?></span>
                                    <span class="price-period">/month</span>
                                </div>
                                <div class="price-yearly">
                                    <span class="price-amount" <?= $priceColor ?>><span class="price-currency">₱</span><?= number_format((float)$plan['price_yearly'], 0) ?></span>
                                    <span class="price-period">/year</span>
                                </div>
                            </div>

                            <?php if ((int)$plan['length_free'] > 0): ?>
                                <div class="mb-4">
                                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-bold border border-info border-opacity-25 ripple-info" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                                        <i class="fa-solid fa-gift me-1"></i><?= $plan['length_free'] ?>-DAY FREE TRIAL
                                    </span>
                                </div>
                            <?php endif; ?>

                            <ul class="list-unstyled pricing-features mb-4 text-start">
                                <?php 
                                    $allFeatures = [];
                                    $allFeatures[] = ((int)$plan['bhouse_limit'] >= 9999 ? 'Unlimited Properties' : 'Up to ' . htmlspecialchars((string)$plan['bhouse_limit']) . ' Properties');
                                    $allFeatures[] = ((int)$plan['room_limit'] >= 9999 ? 'Unlimited Rooms' : 'Up to ' . htmlspecialchars((string)$plan['room_limit']) . ' Rooms');
                                    foreach ($features as $f) { $allFeatures[] = htmlspecialchars((string)$f); }
                                    
                                    $limit = (strtolower($plan['name']) === 'starter') ? 4 : 5;
                                    $displayItems = array_slice($allFeatures, 0, $limit);
                                    $remainingCount = count($allFeatures) - $limit;
                                ?>
                                
                                <?php foreach ($displayItems as $item): ?>
                                    <li><i class="fa-solid fa-circle-check"></i> <?= $item ?></li>
                                <?php endforeach; ?>

                                <?php if ($remainingCount > 0): ?>
                                    <li class="mt-3">
                                        <a href="javascript:void(0)" class="text-primary fw-bold small text-decoration-none view-all-features" 
                                           data-plan="<?= htmlspecialchars($plan['name']) ?>" 
                                           data-features='<?= json_encode($allFeatures) ?>'>
                                            <i class="fa-solid fa-plus-circle me-1"></i> See <?= $remainingCount ?> more features
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <div class="mt-auto">
                                <a href="/tenant/?url=auth/register" class="<?= $btnClass ?>">
                                    <?= strtolower($plan['name']) === 'pro' ? 'Go Professional' : (strtolower($plan['name']) === 'elite' ? 'Get Elite' : (strtolower($plan['name']) === 'plus' ? 'Select Plus' : 'Get Started')) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section" id="testimonials">
        <div class="container-fluid">
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <h5 class="text-primary fw-bold text-uppercase mb-3" style="letter-spacing: 3px;">Social Proof</h5>
                <h2 class="display-4 fw-black">Trust by Design</h2>
            </div>

            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-aos="fade-up" data-aos-delay="200">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                </div>

                <div class="carousel-inner">
                    <!-- Testimonial 1 -->
                    <div class="carousel-item active">
                        <div class="testimonial-card">
                            <i class="fa-solid fa-quote-right quote-icon-bg"></i>
                            <p class="testimonial-text">"The design and flow of StayHub is what sold me. It doesn't just look good, it actually works seamlessly when you're looking for a place to stay."</p>
                            <div class="testimonial-author">
                                <img src="https://ui-avatars.com/api/?name=Marco+V&background=8b5cf6&color=fff" class="author-avatar" alt="Marco Valeri">
                                <div>
                                    <h5 class="fw-bold mb-0">Marco Valeri</h5>
                                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Creative Director</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="carousel-item">
                        <div class="testimonial-card">
                            <i class="fa-solid fa-quote-right quote-icon-bg"></i>
                            <p class="testimonial-text">"I've listed my property on several platforms, but the tenant quality and management tools on StayHub are unmatched."</p>
                            <div class="testimonial-author">
                                <img src="https://ui-avatars.com/api/?name=Liza+M&background=ec4899&color=fff" class="author-avatar" alt="Liza Magsino">
                                <div>
                                    <h5 class="fw-bold mb-0">Liza Magsino</h5>
                                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Multi-unit Owner</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="carousel-item">
                        <div class="testimonial-card">
                            <i class="fa-solid fa-quote-right quote-icon-bg"></i>
                            <p class="testimonial-text">"As a student, finding a verified place near campus was stressful until I used StayHub. The digital check-in guide was a lifesaver."</p>
                            <div class="testimonial-author">
                                <img src="https://ui-avatars.com/api/?name=Rafael+S&background=3b82f6&color=fff" class="author-avatar" alt="Rafael Santos">
                                <div>
                                    <h5 class="fw-bold mb-0">Rafael Santos</h5>
                                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Graduate Student</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 4 -->
                    <div class="carousel-item">
                        <div class="testimonial-card">
                            <i class="fa-solid fa-quote-right quote-icon-bg"></i>
                            <p class="testimonial-text">"Managing payments used to be a headache. Now, automated billing handles everything, and I can focus on expanding my business."</p>
                            <div class="testimonial-author">
                                <img src="https://ui-avatars.com/api/?name=Elena+D&background=10b981&color=fff" class="author-avatar" alt="Elena Dimas">
                                <div>
                                    <h5 class="fw-bold mb-0">Elena Dimas</h5>
                                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Property Mogul</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section" id="faq" data-aos="fade-up">
        <div class="container" style="max-width: 1000px;">
            <div class="section-header text-center mb-10">
                <div class="faq-badge mb-4 mx-auto" style="width: fit-content; background: rgba(79,70,229,0.1); color: #4f46e5; padding: 10px 25px; border-radius: 100px; font-weight: 900; letter-spacing: 2px;">
                    Common Questions
                </div>
                <h2 class="display-3 fw-black mb-4">You've got questions.<br>We have the answers.</h2>
                <p class="fs-5 text-muted max-width-600 mx-auto">Everything you need to know about the Philippines' most premium boarding house network.</p>
            </div>
            
            <div class="accordion" id="faqAccordion">
                <!-- FAQ Item 1 -->
                <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            <div class="faq-icon"><i class="fa-solid fa-hotel"></i></div>
                            How do I verify a property?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Every property on StayHub undergoes a rigorous 5-point verification process by our ground team, including legal permit checks, amenity audits, and owner identity vetting to ensure 100% peace of mind.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            <div class="faq-icon"><i class="fa-solid fa-credit-card"></i></div>
                            Are there any hidden booking fees?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Absolutely not. What you see is what you pay. We believe in 100% transparency. Our platform service fee is clearly displayed before you initiate any reservation.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            <div class="faq-icon"><i class="fa-solid fa-door-open"></i></div>
                            What happens if I need to move out early?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We offer flexible move-out policies adapted to modern lifetsyles. Simply notify your property manager via the digital StayHub dashboard at least 30 days in advance to initiate a seamless check-out.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="accordion-item" data-aos="fade-up" data-aos-delay="400">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            <div class="faq-icon"><i class="fa-solid fa-shield-heart"></i></div>
                            Is my security deposit safe?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, all security deposits are managed through our secure escrow-inspired digital vault and are returned automatically within 7 business days after a successful move-out inspection.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Area -->
    <section class="container mb-5" data-aos="zoom-in">
        <div class="final-cta">
            <div class="cta-glow-1"></div>
            <div class="cta-glow-2"></div>
            
            <!-- Floating Decor icons -->
            <i class="fa-solid fa-door-open cta-floating-icon" style="top: 15%; left: 10%; animation-delay: 0s;"></i>
            <i class="fa-solid fa-star cta-floating-icon" style="top: 60%; left: 5%; animation-delay: 1s; font-size: 2rem;"></i>
            <i class="fa-solid fa-bolt cta-floating-icon" style="top: 20%; right: 10%; animation-delay: 2s; font-size: 4rem;"></i>
            <i class="fa-solid fa-gem cta-floating-icon" style="bottom: 15%; right: 15%; animation-delay: 3s;"></i>

            <div class="cta-content">
                <div class="badge bg-white bg-opacity-10 mb-4 px-4 py-2 rounded-pill" style="backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); letter-spacing: 2px;">
                    READY TO UPGRADE?
                </div>
                <h2 class="cta-title">Start your stay with<br>StayHub today.</h2>
                <p class="cta-subtitle">Join thousands of verified tenants and premium owners across the Philippines in the future of boarding.</p>
                
                <div class="cta-btn-group">
                    <a href="/tenant/?url=auth/register" class="btn btn-cta-primary">
                        Get Started Now
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="/tenant/?url=boarding/explore" class="btn btn-cta-secondary">
                        Explore Listings
                    </a>
                </div>

                <div class="cta-social-proof">
                    <div class="avatar-group d-flex mb-2">
                        <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle border border-2 border-white" width="35" style="z-index: 3;">
                        <img src="https://ui-avatars.com/api/?name=Smith&background=random" class="rounded-circle border border-2 border-white ms-n2" width="35" style="z-index: 2;">
                        <img src="https://ui-avatars.com/api/?name=Doe&background=random" class="rounded-circle border border-2 border-white ms-n2" width="35" style="z-index: 1;">
                    </div>
                    <span class="small fw-bold letter-spacing-1">JOIN 5,000+ HAPPY TENANTS</span>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // Intersection Observer for AOS
    document.addEventListener('DOMContentLoaded', () => {
        const observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('aos-animate');
                }
            });
        }, observerOptions);

        document.querySelectorAll('[data-aos]').forEach(el => observer.observe(el));

        // Expert Stats Counting Animation
        const statCounters = document.querySelectorAll('.counter');
        const countObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const targetVal = parseFloat(target.getAttribute('data-target'));
                    const decimals = parseInt(target.getAttribute('data-decimals') || 0);
                    const duration = 2000;
                    let startTime = null;

                    const animate = (currentTime) => {
                        if (!startTime) startTime = currentTime;
                        const progress = Math.min((currentTime - startTime) / duration, 1);
                        const currentVal = progress * targetVal;
                        
                        let displayVal = currentVal.toFixed(decimals);
                        if (targetVal === 500) displayVal += '+';
                        if (targetVal === 98) displayVal += '%';
                        if (targetVal === 4.9) displayVal += '/5';
                        if (targetVal === 2) displayVal += 'M+';
                        
                        target.innerText = displayVal;
                        if (progress < 1) requestAnimationFrame(animate);
                    };
                    requestAnimationFrame(animate);
                    countObserver.unobserve(target);
                }
            });
        }, { threshold: 0.5 });
        statCounters.forEach(counter => countObserver.observe(counter));
        
        // Parallax Effect for Hero Visual
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const heroVisual = document.querySelector('.hero-image');
            if (heroVisual) {
                heroVisual.style.transform = `translateY(${scrolled * 0.1}px) scale(${1 + scrolled * 0.0001})`;
            }
        });

        // Magnetic Button Effect
        document.querySelectorAll('.btn-hero').forEach(btn => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                btn.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = `translate(0, 0)`;
            });
        });

        // Pricing Toggle - Segmented
        const pricingControl = document.getElementById('pricing-control');
        const pricingToggle = document.getElementById('pricing-toggle');
        const segments = document.querySelectorAll('.segment');
        const pricingSectionObj = document.getElementById('pricing');

        if (pricingControl && pricingSectionObj) {
            pricingControl.addEventListener('click', function() {
                pricingToggle.checked = !pricingToggle.checked;
                
                if (pricingToggle.checked) {
                    pricingSectionObj.classList.add('show-yearly');
                    segments[0].classList.remove('active');
                    segments[1].classList.add('active');
                } else {
                    pricingSectionObj.classList.remove('show-yearly');
                    segments[0].classList.add('active');
                    segments[1].classList.remove('active');
                }
            });
        }

        // --- Nav ScrollSpy Logic ---
        const navLinksSpy = document.querySelectorAll('.app-nav a[href*="#"]');
        const sectionsSpy = document.querySelectorAll('section[id]');
        
        const scrollSpyOptions = {
            threshold: [0.2, 0.5, 0.8],
            rootMargin: '-20% 0px -20% 0px'
        };

        const scrollSpyObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    navLinksSpy.forEach(link => {
                        const href = link.getAttribute('href');
                        if (href.endsWith('#' + id)) {
                            link.classList.add('active');
                        } else {
                            link.classList.remove('active');
                        }
                    });
                }
            });
        }, scrollSpyOptions);

        // Features Modal Logic
        const featuresModal = new bootstrap.Modal(document.getElementById('featuresModal'));
        const modalPlanName = document.getElementById('modalPlanName');
        const modalFeaturesList = document.getElementById('modalFeaturesList');

        document.querySelectorAll('.view-all-features').forEach(trigger => {
            trigger.addEventListener('click', function() {
                const planName = this.getAttribute('data-plan');
                const features = JSON.parse(this.getAttribute('data-features'));
                
                modalPlanName.textContent = planName + ' Plan Features';
                modalFeaturesList.innerHTML = '';
                
                features.forEach(feature => {
                    const item = document.createElement('div');
                    item.className = 'd-flex align-items-center gap-3 p-3 rounded-3 bg-light border border-opacity-10';
                    item.innerHTML = `
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                            <i class="fa-solid fa-circle-check" style="font-size: 0.9rem;"></i>
                        </div>
                        <span class="text-dark fw-bold small">${feature}</span>
                    `;
                    modalFeaturesList.appendChild(item);
                });
                
                featuresModal.show();
            });
        });

        sectionsSpy.forEach(section => scrollSpyObserver.observe(section));
    });
</script>
    <!-- Premium Landing Footer (Standalone Component) -->
    <?php require __DIR__ . '/../layouts/landing_footer.php'; ?>

    <!-- Features Modal (Relocated to avoid backdrop issues) -->
    <div class="modal fade" id="featuresModal" tabindex="-1" aria-hidden="true" style="z-index: 9999 !important;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-black h4" id="modalPlanName">Plan Features</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="modalFeaturesList" class="d-flex flex-column gap-3">
                        <!-- Features will be injected here -->
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold w-100" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
