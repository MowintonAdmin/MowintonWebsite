        </main>
    </div>
</div>

<!-- Global Modal: Change Current User Password -->
<div class="admin-modal-backdrop" id="changeOwnPasswordModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <div>
                <h3 style="margin-bottom: 2px;">Change Your Password</h3>
                <span style="font-size: 13px; color: var(--admin-text-muted);">Logged in as <strong>@<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?></strong></span>
            </div>
            <button type="button" class="admin-modal-close" onclick="closeModal('changeOwnPasswordModal')">✕</button>
        </div>

        <form method="POST" action="/admin/change-password.php">
            <?php echo admin_csrf_field(); ?>
            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/admin/'); ?>">

            <div class="admin-form-group">
                <label for="modal_curr_password">Current Password</label>
                <div class="password-input-wrap">
                    <input type="password" id="modal_curr_password" name="current_password" class="admin-form-input" required placeholder="Enter current password">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('modal_curr_password', this)" aria-label="Show password" title="Show password">
                        <svg class="eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="eye-closed" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="admin-form-group">
                <label for="modal_new_password">New Password (Minimum 8 Characters)</label>
                <div class="password-input-wrap">
                    <input type="password" id="modal_new_password" name="new_password" class="admin-form-input" minlength="8" required placeholder="Enter new password">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('modal_new_password', this)" aria-label="Show password" title="Show password">
                        <svg class="eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="eye-closed" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="admin-form-group">
                <label for="modal_confirm_password">Confirm New Password</label>
                <div class="password-input-wrap">
                    <input type="password" id="modal_confirm_password" name="confirm_password" class="admin-form-input" minlength="8" required placeholder="Re-enter new password">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('modal_confirm_password', this)" aria-label="Show password" title="Show password">
                        <svg class="eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="eye-closed" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeModal('changeOwnPasswordModal')">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary" style="width: auto;">Update Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Admin Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    const sidebar = document.getElementById('adminSidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', function () {
            sidebar.classList.remove('open');
        });
    }

    // Modal helpers
    window.openModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('open');
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('open');
    };

    // Toggle password visibility helper
    window.togglePasswordVisibility = function (inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const eyeOpen = btn.querySelector('.eye-open');
        const eyeClosed = btn.querySelector('.eye-closed');
        if (input.type === 'password') {
            input.type = 'text';
            if (eyeOpen) eyeOpen.style.display = 'none';
            if (eyeClosed) eyeClosed.style.display = 'block';
            btn.setAttribute('aria-label', 'Hide password');
            btn.setAttribute('title', 'Hide password');
        } else {
            input.type = 'password';
            if (eyeOpen) eyeOpen.style.display = 'block';
            if (eyeClosed) eyeClosed.style.display = 'none';
            btn.setAttribute('aria-label', 'Show password');
            btn.setAttribute('title', 'Show password');
        }
    };

    // Close on backdrop click
    document.querySelectorAll('.admin-modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) {
                backdrop.classList.remove('open');
            }
        });
    });

    // --- Real-time Session Inactivity Auto-Logout ---
    (function () {
        const timeoutSeconds = <?php echo function_exists('get_session_timeout_seconds') ? get_session_timeout_seconds() : 1800; ?>;
        let secondsInactive = 0;
        let lastPingTime = Date.now();

        function resetInactivity() {
            secondsInactive = 0;
            // Send keepalive ping at most once every 60 seconds while user is actively interacting
            const now = Date.now();
            if (now - lastPingTime > 60000) {
                lastPingTime = now;
                fetch('/admin/session-ping.php', { method: 'GET', credentials: 'same-origin' })
                    .catch(function () {});
            }
        }

        // Listen for user interactions across the dashboard
        const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
        activityEvents.forEach(function (evt) {
            window.addEventListener(evt, resetInactivity, { passive: true });
        });

        // Inactivity ticker every second
        setInterval(function () {
            secondsInactive += 1;
            if (secondsInactive >= timeoutSeconds) {
                // Instantly redirect to login with expired notice
                window.location.href = '/admin/login.php?expired=1';
            }
        }, 1000);
    })();
});
</script>
</body>
</html>
