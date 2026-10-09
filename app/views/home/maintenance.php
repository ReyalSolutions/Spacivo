<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance | Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --dark: #020617;
            --darker: #080f23;
            --primary: #3b82f6;
            --primary-glow: rgba(59, 130, 246, 0.35);
            --emerald: #10b981;
            --amber: #f59e0b;
            --slate: #94a3b8;
            --glass: rgba(255,255,255,0.04);
            --border: rgba(255,255,255,0.07);
        }

        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark);
            color: white;
            overflow: hidden;
        }

        /* ── Animated Background ── */
        .bg-scene {
            position: fixed;
            inset: 0;
            z-index: 0;
        }

        /* Radial spotlight that drifts */
        .bg-scene::before {
            content: '';
            position: absolute;
            top: -30%;
            left: -20%;
            width: 80vw;
            height: 80vw;
            background: radial-gradient(circle, rgba(59,130,246,0.12) 0%, transparent 65%);
            animation: orb-drift 18s ease-in-out infinite alternate;
        }
        .bg-scene::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: -15%;
            width: 70vw;
            height: 70vw;
            background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 65%);
            animation: orb-drift 22s ease-in-out infinite alternate-reverse;
        }

        @keyframes orb-drift {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(6vw, 8vw) scale(1.1); }
        }

        /* Grid Lines */
        .grid-bg {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
        }

        /* ── Layout ── */
        .stage {
            position: relative;
            z-index: 10;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px;
            gap: 0;
            overflow: hidden;
        }

        /* ── Main Glass Card ── */
        .glass-card {
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 32px 36px;
            max-width: 580px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.05),
                0 40px 80px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.08);
            animation: card-appear 1s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes card-appear {
            from { opacity: 0; transform: translateY(40px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Icon ── */
        .icon-ring {
            position: relative;
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
        }

        .icon-ring-inner {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            border-radius: 20px;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 0 40px var(--primary-glow), 0 8px 24px rgba(0,0,0,0.4);
            animation: icon-float 5s ease-in-out infinite;
        }

        @keyframes icon-float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50%  { transform: translateY(-10px) rotate(3deg); }
        }

        /* Rotating ring */
        .icon-ring::before {
            content: '';
            position: absolute;
            inset: -12px;
            border-radius: 36px;
            border: 2px solid transparent;
            border-top-color: rgba(59,130,246,0.6);
            border-right-color: rgba(59,130,246,0.2);
            animation: ring-spin 3s linear infinite;
        }
        @keyframes ring-spin { to { transform: rotate(360deg); } }

        /* ── Typography ── */
        h1 {
            font-size: clamp(1.5rem, 4vw, 2.2rem);
            font-weight: 900;
            letter-spacing: -1.5px;
            line-height: 1.1;
            background: linear-gradient(135deg, #ffffff 30%, rgba(255,255,255,0.55) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .subtitle {
            color: var(--slate);
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.55;
            max-width: 440px;
            margin: 0 auto 20px;
        }

        /* ── Live Status Box ── */
        .status-box {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .status-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--slate);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 14px;
            border-radius: 100px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .badge-offline { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.25); }
        .badge-active  { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
        .badge-warn    { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }

        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }
        .pulse-dot.red     { background: #ef4444; box-shadow: 0 0 8px #ef4444; animation: pulse 2s ease infinite; }
        .pulse-dot.green   { background: #10b981; box-shadow: 0 0 8px #10b981; animation: pulse 2s ease infinite 0.3s; }
        .pulse-dot.amber   { background: #f59e0b; box-shadow: 0 0 8px #f59e0b; animation: pulse 2s ease infinite 0.6s; }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%  { opacity: 0.4; transform: scale(0.75); }
        }

        .divider {
            height: 1px;
            background: var(--border);
        }

        /* ── ETA Progress Bar ── */
        .eta-bar {
            width: 100%;
            height: 4px;
            background: rgba(255,255,255,0.06);
            border-radius: 100px;
            overflow: hidden;
            margin-top: 4px;
        }
        .eta-fill {
            height: 100%;
            width: 72%;
            background: linear-gradient(90deg, #2563eb, #38bdf8);
            border-radius: 100px;
            animation: eta-anim 3s ease-in-out infinite alternate;
        }
        @keyframes eta-anim {
            0%   { width: 60%; }
            100% { width: 85%; }
        }

        /* ── Footer Link ── */
        .admin-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 100px;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .admin-link:hover {
            background: rgba(59,130,246,0.15);
            border-color: rgba(59,130,246,0.4);
            color: #93c5fd;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59,130,246,0.15);
        }

        .bottom-tag {
            margin-top: 14px;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.18);
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* ── Floating Particles ── */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }
        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.5);
            animation: particle-rise linear infinite;
        }
        @keyframes particle-rise {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.6; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        @media (max-width: 540px) {
            .glass-card { padding: 40px 24px; }
            .status-row { flex-direction: column; align-items: flex-start; gap: 6px; }
        }
    </style>
</head>
<body>

    <!-- Background Layers -->
    <div class="bg-scene"></div>
    <div class="grid-bg"></div>

    <!-- Particles -->
    <div class="particles" id="particles"></div>

    <!-- Main Content -->
    <div class="stage">
        <div class="glass-card">

            <!-- Animated Icon -->
            <div class="icon-ring">
                <div class="icon-ring-inner">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
            </div>

            <!-- Heading -->
            <h1>System Under Maintenance</h1>
            <p class="subtitle">
                Our engineers are actively deploying critical infrastructure upgrades. We'll be back online shortly with improved performance and new features.
            </p>

            <!-- Live System Status -->
            <div class="status-box">
                <div class="status-row">
                    <span class="status-label"><i class="fa-solid fa-server me-2 opacity-50"></i> Core Services</span>
                    <span class="status-badge badge-offline">
                        <span class="pulse-dot red"></span> Offline
                    </span>
                </div>
                <div class="divider"></div>
                <div class="status-row">
                    <span class="status-label"><i class="fa-solid fa-database me-2 opacity-50"></i> Database Cluster</span>
                    <span class="status-badge badge-active">
                        <span class="pulse-dot green"></span> Preserved
                    </span>
                </div>
                <div class="divider"></div>
                <div class="status-row">
                    <span class="status-label"><i class="fa-solid fa-shield-halved me-2 opacity-50"></i> Data Integrity</span>
                    <span class="status-badge badge-active">
                        <span class="pulse-dot green"></span> Secured
                    </span>
                </div>
                <div class="divider"></div>
                <div class="status-row">
                    <span class="status-label" style="flex: 1;"><i class="fa-solid fa-hourglass-half me-2 opacity-50"></i> Deployment Progress</span>
                </div>
                <div class="eta-bar"><div class="eta-fill"></div></div>
            </div>

            <!-- Admin Link -->
            <a href="/tenant/?url=auth/login" class="admin-link">
                <i class="fa-solid fa-fingerprint"></i>
                Authorized Personnel Access
            </a>
        </div>

        <p class="bottom-tag">Powered by StayHub Infrastructure&nbsp;&mdash;&nbsp;All systems are monitored 24/7</p>
    </div>

    <script>
    // Generate drifting particles
    (function() {
        const container = document.getElementById('particles');
        const count = 30;
        const colors = ['rgba(59,130,246,0.4)', 'rgba(16,185,129,0.3)', 'rgba(245,158,11,0.25)'];

        for (let i = 0; i < count; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + 'vw';
            p.style.width  = (Math.random() * 3 + 1) + 'px';
            p.style.height = (Math.random() * 3 + 1) + 'px';
            p.style.background = colors[Math.floor(Math.random() * colors.length)];
            p.style.animationDuration = (Math.random() * 18 + 10) + 's';
            p.style.animationDelay = '-' + (Math.random() * 20) + 's';
            container.appendChild(p);
        }
    })();
    </script>

</body>
</html>
