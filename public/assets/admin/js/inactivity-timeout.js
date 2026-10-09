/**
 * inactivity-timeout.js
 * Tracks user inactivity and shows a countdown warning before auto-logout.
 *
 * Config (set window.REYAL_TIMEOUT_CONFIG before loading this script):
 *   pingUrl        — URL to session_ping.php  (default: 'actions/session_ping.php')
 *   timeoutSeconds — 0 = auto-detect from server; else set manually in seconds
 *   warningBefore  — seconds before expiry to show warning (default: 120 = 2 min)
 *   pingInterval   — how often to ping server in ms (default: 240000 = 4 min)
 *   logoutUrl      — where to redirect on logout (default: 'logout.php')
 */
(function () {
  'use strict';

  const cfg = Object.assign({
    pingUrl:        'actions/session_ping.php',
    timeoutSeconds: 0,
    warningBefore:  120,
    pingInterval:   4 * 60 * 1000,
    logoutUrl:      'logout.php',
  }, window.REYAL_TIMEOUT_CONFIG || {});

  let warningTimer   = null;
  let countdownTimer = null;
  let warningShown   = false;
  let resolvedTimeout = cfg.timeoutSeconds; // will be set from server if 0

  let lastActivity = Date.now();
  let pingTimer    = null;

  /* ─── Activity tracking ─────────────────────────────────────────── */
  const ACTIVITY_EVENTS = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
  ACTIVITY_EVENTS.forEach(evt => document.addEventListener(evt, onActivity, { passive: true }));

  function onActivity() {
    lastActivity = Date.now();
    if (warningShown) {
      dismissWarning();
    }
    resetWarningTimer();
  }

  /* ─── Warning timer ──────────────────────────────────────────────── */
  function resetWarningTimer() {
    clearTimeout(warningTimer);
    if (!resolvedTimeout) return; // wait until server confirms timeout
    const showAfterMs = (resolvedTimeout - cfg.warningBefore) * 1000;
    if (showAfterMs > 0) {
      warningTimer = setTimeout(showWarning, showAfterMs);
    }
  }

  function showWarning() {
    warningShown = true;
    let secondsLeft = cfg.warningBefore;

    // Build modal if not already present
    if (!document.getElementById('sessionTimeoutModal')) {
      document.body.insertAdjacentHTML('beforeend', `
        <div id="sessionTimeoutModal" style="
          display:none; position:fixed; inset:0; z-index:99999;
          background:rgba(10,15,30,0.72); backdrop-filter:blur(6px);
          align-items:center; justify-content:center;">
          <div style="
            background:#fff; border-radius:14px; padding:32px 36px;
            max-width:400px; width:90%; text-align:center;
            box-shadow:0 24px 64px rgba(0,0,0,0.22);
            font-family:'Plus Jakarta Sans',sans-serif;">
            <div style="font-size:42px; margin-bottom:12px;">⏱️</div>
            <h4 style="margin:0 0 8px; font-size:18px; color:#0f172a; font-weight:700;">
              Session Expiring Soon
            </h4>
            <p style="color:#64748b; font-size:14px; margin:0 0 20px; line-height:1.6;">
              You've been inactive. Your session will expire in
              <strong id="sessionCountdown" style="color:#0ea5e9; font-size:17px;"></strong>
            </p>
            <div style="display:flex; gap:10px; justify-content:center;">
              <button id="sessionStayBtn" style="
                flex:1; padding:10px; border:none; border-radius:8px; cursor:pointer;
                background:linear-gradient(135deg,#0ea5e9,#0d9488); color:#fff;
                font-weight:600; font-size:14px;">
                Stay Logged In
              </button>
              <button id="sessionLogoutBtn" style="
                flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px;
                cursor:pointer; background:#f8fafc; color:#475569;
                font-weight:600; font-size:14px;">
                Logout Now
              </button>
            </div>
          </div>
        </div>
      `);

      document.getElementById('sessionStayBtn').addEventListener('click', function () {
        dismissWarning();
        pingServer();
      });

      document.getElementById('sessionLogoutBtn').addEventListener('click', function () {
        window.location.href = cfg.logoutUrl;
      });
    }

    const modal = document.getElementById('sessionTimeoutModal');
    const countdownEl = document.getElementById('sessionCountdown');
    modal.style.display = 'flex';

    function updateCountdown() {
      if (secondsLeft <= 0) {
        clearInterval(countdownTimer);
        modal.style.display = 'none';
        window.location.href = cfg.logoutUrl + '?reason=timeout';
        return;
      }
      const m = Math.floor(secondsLeft / 60);
      const s = secondsLeft % 60;
      countdownEl.textContent = m > 0
        ? `${m}m ${String(s).padStart(2, '0')}s`
        : `${s}s`;
      secondsLeft--;
    }

    updateCountdown();
    countdownTimer = setInterval(updateCountdown, 1000);
  }

  function dismissWarning() {
    warningShown = false;
    clearInterval(countdownTimer);
    const modal = document.getElementById('sessionTimeoutModal');
    if (modal) modal.style.display = 'none';
  }

  /* ─── Server ping ───────────────────────────────────────────────── */
  function pingServer() {
    fetch(cfg.pingUrl, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
      .then(r => r.json())
      .then(function (data) {
        if (data.expired) {
          clearInterval(countdownTimer);
          clearTimeout(warningTimer);
          window.location.href = cfg.logoutUrl + '?reason=timeout';
          return;
        }
        // Auto-detect timeout from server (first ping or when resolvedTimeout is 0)
        if (data.timeout && (!resolvedTimeout || resolvedTimeout !== data.timeout)) {
          resolvedTimeout = data.timeout;
          resetWarningTimer(); // restart timer with correct server timeout
        }
      })
      .catch(function () {
        // Silently ignore; server enforces on next page load
      });
  }

  /* ─── Init ──────────────────────────────────────────────────────── */
  // Ping immediately on load to get the DB timeout, then set interval
  pingServer();
  pingTimer = setInterval(pingServer, cfg.pingInterval);

})();

