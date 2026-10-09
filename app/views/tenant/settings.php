<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="dash-container">
    <div class="welcome-header">
        <h1>Account Settings</h1>
        <p>Manage your notification preferences, security, and account configuration.</p>
    </div>

    <!-- Security Settings -->
    <div class="premium-stat-card" style="margin-top: 24px; padding: 32px;">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #fff1f2, #ffe4e6); display: flex; align-items: center; justify-content: center; color: #e11d48; font-size: 1.2rem; flex-shrink: 0;">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 4px; font-size: 1.1rem; font-weight: 800; color: var(--dash-text-main);">Security & Login</h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--dash-text-muted);">Update your password and secure your account.</p>
            </div>
        </div>

        <form>
            <div style="display: grid; gap: 20px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Current Password</label>
                    <input type="password" class="auth-input" placeholder="Enter current password" style="width: 100%;" disabled value="********">
                </div>
                <!-- Placeholder for future functionality -->
                <div>
                    <button type="button" class="btn-res-action btn-res-primary" style="opacity: 0.6; cursor: not-allowed;"><i class="fas fa-key"></i> Update Password</button>
                    <span style="font-size: 0.8rem; color: var(--dash-text-muted); margin-left: 12px; font-style: italic;">Feature locked in MVP</span>
                </div>
            </div>
        </form>
    </div>

    <!-- Notifications Settings -->
    <div class="premium-stat-card" style="margin-top: 24px; padding: 32px;">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #eff6ff, #dbeafe); display: flex; align-items: center; justify-content: center; color: #3b82f6; font-size: 1.2rem; flex-shrink: 0;">
                <i class="fas fa-bell"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 4px; font-size: 1.1rem; font-weight: 800; color: var(--dash-text-main);">Notifications</h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--dash-text-muted);">Control how you receive alerts and updates.</p>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <div style="font-weight: 700; color: var(--dash-text-main); margin-bottom: 4px;">Email Alerts</div>
                    <div style="font-size: 0.85rem; color: var(--dash-text-muted);">Receive booking updates and payment receipts to <?= htmlspecialchars($user['email'] ?? 'your email', ENT_QUOTES, 'UTF-8') ?>.</div>
                </div>
                <div style="background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">Enabled</div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; color: var(--dash-text-main); margin-bottom: 4px;">SMS Notifications</div>
                    <div style="font-size: 0.85rem; color: var(--dash-text-muted);">Get urgent alerts sent directly to your phone.</div>
                </div>
                <div style="background: #e2e8f0; color: #64748b; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;">Disabled</div>
            </div>
        </div>
    </div>

    <!-- Appearance -->
    <div class="premium-stat-card" style="margin-top: 24px; padding: 32px;">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #f3e8ff, #e9d5ff); display: flex; align-items: center; justify-content: center; color: #a855f7; font-size: 1.2rem; flex-shrink: 0;">
                <i class="fas fa-palette"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 4px; font-size: 1.1rem; font-weight: 800; color: var(--dash-text-main);">Appearance</h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--dash-text-muted);">Customize the look and feel of your dashboard.</p>
            </div>
        </div>

        <div style="display: flex; gap: 16px;">
            <div style="flex: 1; padding: 16px; border: 2px solid var(--dash-primary); border-radius: 16px; background: #f8fafc; cursor: pointer; text-align: center;">
                <i class="fas fa-sun" style="font-size: 1.5rem; color: #f59e0b; margin-bottom: 8px;"></i>
                <div style="font-weight: 800; color: var(--dash-primary);">Light Mode</div>
            </div>
            <div style="flex: 1; padding: 16px; border: 2px solid transparent; border-radius: 16px; background: #1e293b; cursor: not-allowed; text-align: center; opacity: 0.5;">
                <i class="fas fa-moon" style="font-size: 1.5rem; color: #cbd5e1; margin-bottom: 8px;"></i>
                <div style="font-weight: 800; color: #cbd5e1;">Dark Mode (Pro)</div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
