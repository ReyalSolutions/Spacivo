<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden Access | StayHub</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --danger-gradient: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            --glass-bg: rgba(255, 255, 255, 0.7);
        }
        
        body {
            font-family: 'Plus+Jakarta+Sans', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0, transparent 50%), 
                radial-gradient(at 100% 0%, rgba(244, 63, 94, 0.05) 0, transparent 50%);
        }

        .shadow-premium {
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 10px 10px -5px rgba(0,0,0,0.02);
        }

        .hover-scale { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .hover-scale:hover { transform: translateY(-3px) scale(1.02); }

        .fw-900 { font-weight: 900; }
        .fw-800 { font-weight: 800; }
        
        .error-code {
            background: var(--danger-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 8rem;
            line-height: 1;
        }

        .bg-danger-soft { background: #fff1f2; }
        .text-rose { color: #e11d48; }
        
        .pulse-anim { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(244, 63, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0); }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 text-center animate__animated animate__fadeInUp">
            <div class="mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-danger-soft text-rose rounded-circle shadow-sm pulse-anim" style="width: 140px; height: 140px; font-size: 4rem;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
            
            <h1 class="error-code fw-900 mb-2">403</h1>
            <h2 class="fw-800 text-dark mb-4 fs-1">Access Restricted</h2>
            
            <div class="card border-0 shadow-premium p-5 mb-5" style="border-radius: 32px; background: var(--glass-bg); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.5) !important;">
                <p class="text-secondary fs-5 mb-4 px-lg-5">Your administrative identity does not currently possess the required authorization tokens to access this module.</p>
                <div class="d-inline-block">
                    <span class="badge bg-dark rounded-pill px-4 py-3 fw-bold shadow-sm" style="letter-spacing: 0.5px;">
                        <i class="fa-solid fa-key me-2 text-warning"></i>REQUIRED TOKEN: <span class="font-monospace text-warning"><?= htmlspecialchars($permission ?? 'UNDEFINED_PRIVILEGE') ?></span>
                    </span>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-4">
                <a href="/tenant/?url=admin/index" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg hover-scale" style="background: var(--primary-gradient); border: none;">
                    <i class="fa-solid fa-house-chimney me-2"></i>Dashboard Home
                </a>
                <button onclick="history.back()" class="btn btn-white bg-white text-dark rounded-pill px-5 py-3 fw-bold border shadow-sm hover-scale">
                    <i class="fa-solid fa-arrow-left-long me-2"></i>Go Back
                </button>
            </div>
            
            <div class="mt-5 pt-4">
                <p class="text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>Administrative Security Enforcement Interface
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
