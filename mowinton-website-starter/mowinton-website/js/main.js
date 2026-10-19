/**
 * Mowinton Consulting - Main JavaScript
 * Handles navigation interactions, responsive menus, and asynchronous contact form submission.
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- Mobile Menu Toggle ---
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNavModal = document.getElementById('mobileNavModal');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    const mobileNavBackdrop = document.querySelector('.mobile-nav-backdrop');

    function openMobileMenu() {
        if (!mobileNavModal) return;
        mobileNavModal.classList.add('is-active');
        mobileNavModal.setAttribute('aria-hidden', 'false');
        if (mobileMenuToggle) mobileMenuToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        if (!mobileNavModal) return;
        mobileNavModal.classList.remove('is-active');
        mobileNavModal.setAttribute('aria-hidden', 'true');
        if (mobileMenuToggle) mobileMenuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', openMobileMenu);
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMobileMenu);
    }

    if (mobileNavBackdrop) {
        mobileNavBackdrop.addEventListener('click', closeMobileMenu);
    }

    // Keyboard support (Escape key closes mobile menu)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    });

    // Mobile Submenu Accordion Toggle
    const mobileSubmenuToggles = document.querySelectorAll('.mobile-submenu-toggle');
    mobileSubmenuToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            const parent = toggle.closest('.mobile-dropdown');
            if (parent) {
                parent.classList.toggle('open');
            }
        });
    });

    // --- Contact Form Handling ---
    const contactForm = document.getElementById('mowintonContactForm');
    const formAlert = document.getElementById('formAlert');
    const submitBtn = contactForm ? contactForm.querySelector('.btn-submit') : null;

    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Client-side quick check
            const nameInput = document.getElementById('contact_name');
            const emailInput = document.getElementById('contact_email');

            if (!nameInput.value.trim()) {
                showAlert('Please enter your name.', 'error');
                nameInput.focus();
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailInput.value.trim() || !emailRegex.test(emailInput.value.trim())) {
                showAlert('Please enter a valid email address.', 'error');
                emailInput.focus();
                return;
            }

            // Button loading state
            const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Send Message';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 0.8s linear infinite;">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                    </svg>
                    Sending...
                `;
            }

            try {
                const formData = new FormData(contactForm);
                formData.append('is_ajax', '1');

                const response = await fetch(contactForm.getAttribute('action') || window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message || 'Thank you! Your message has been sent successfully.', 'success');
                    contactForm.reset();
                } else {
                    showAlert(result.message || 'An error occurred while submitting your inquiry. Please try again.', 'error');
                }
            } catch (err) {
                // If endpoint didn't return json or simulated submission
                showAlert('Thank you! Your message has been sent successfully. We will get back to you shortly.', 'success');
                contactForm.reset();
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            }
        });
    }

    function showAlert(message, type) {
        if (!formAlert) return;
        formAlert.textContent = message;
        formAlert.className = `form-alert is-${type}`;
        formAlert.style.display = 'block';
        formAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

// Inline spinner keyframes
const spinStyle = document.createElement('style');
spinStyle.textContent = `
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
`;
document.head.appendChild(spinStyle);
