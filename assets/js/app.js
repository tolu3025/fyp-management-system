// assets/js/app.js
// Custom interactions, notification updates via Fetch API, and email logger drawer toggling

document.addEventListener('DOMContentLoaded', () => {
    // 1. Notification Dropdown Toggle
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!notifDropdown.contains(e.target) && e.target !== notifBtn) {
                notifDropdown.classList.remove('show');
            }
        });
    }

    // 2. Mark Notification as Read via Fetch API
    const notifItems = document.querySelectorAll('.notif-item.unread');
    notifItems.forEach(item => {
        item.addEventListener('click', function() {
            const notifId = this.getAttribute('data-id');
            if (!notifId) return;

            fetch('api/mark_notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${notifId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI locally
                    this.classList.remove('unread');
                    
                    // Decrease unread count badge
                    const badge = document.querySelector('.badge-count');
                    if (badge) {
                        let count = parseInt(badge.textContent) || 0;
                        if (count > 1) {
                            badge.textContent = count - 1;
                        } else {
                            badge.remove();
                        }
                    }
                }
            })
            .catch(err => console.error('Error marking notification:', err));
        });
    });

    // Mark All as Read button
    const markAllBtn = document.getElementById('markAllNotif');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            fetch('api/mark_notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'all=true'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mark all visual items as read
                    document.querySelectorAll('.notif-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                    // Remove badge
                    const badge = document.querySelector('.badge-count');
                    if (badge) badge.remove();
                }
            })
            .catch(err => console.error('Error marking all notifications:', err));
        });
    }

    // 3. Email Console Toggle
    const devMailHeader = document.getElementById('devMailHeader');
    const devMailContent = document.getElementById('devMailContent');
    
    if (devMailHeader && devMailContent) {
        devMailHeader.addEventListener('click', () => {
            devMailContent.classList.toggle('open');
            const indicator = devMailHeader.querySelector('.toggle-indicator');
            if (indicator) {
                indicator.innerHTML = devMailContent.classList.contains('open') ? '<i class="fa-solid fa-chevron-down"></i>' : '<i class="fa-solid fa-chevron-up"></i>';
            }
        });
    }

    // 4. Global Button Loading States
    const buttons = document.querySelectorAll('button, .btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Let form submit event handle submit buttons
            if (btn.type === 'submit' || (btn.tagName === 'INPUT' && btn.type === 'submit')) {
                return;
            }
            
            // If already loading or disabled
            if (btn.classList.contains('btn-loading') || btn.disabled) {
                e.preventDefault();
                return;
            }
            
            // Ignore print trigger
            if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes('window.print')) {
                return;
            }
            
            // Activate loading
            btn.classList.add('btn-loading');
            if (btn.tagName === 'BUTTON') {
                btn.disabled = true;
            } else if (btn.tagName === 'A') {
                btn.style.pointerEvents = 'none';
            }
            
            const originalHtml = btn.innerHTML;
            btn.setAttribute('data-original-html', originalHtml);
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
            
            // Revert after timeout for non-form actions
            setTimeout(() => {
                btn.classList.remove('btn-loading');
                if (btn.tagName === 'BUTTON') {
                    btn.disabled = false;
                } else if (btn.tagName === 'A') {
                    btn.style.pointerEvents = 'auto';
                }
                btn.innerHTML = originalHtml;
            }, 3000);
        });
    });

    // Handle Form Submissions Loading State
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                if (submitBtn.classList.contains('btn-loading') || submitBtn.disabled) {
                    e.preventDefault();
                    return;
                }
                
                // Add loading class and update innerHTML
                submitBtn.classList.add('btn-loading');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.setAttribute('data-original-html', originalHtml);
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Please wait...';
                
                // Defer disabling the button so that the browser successfully initiates
                // form submission and serializes all values before the button becomes disabled.
                setTimeout(() => {
                    if (submitBtn.tagName === 'BUTTON') {
                        submitBtn.disabled = true;
                    }
                }, 10);
            }
        });
    });

    // 5. Password Viewer Toggle (Wrap all password fields)
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const wrapper = document.createElement('div');
        wrapper.className = 'password-wrapper';
        
        // Wrap input
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        // Add eye icon
        const toggleIcon = document.createElement('i');
        toggleIcon.className = 'fa-solid fa-eye password-toggle-icon';
        wrapper.appendChild(toggleIcon);
        
        toggleIcon.addEventListener('click', () => {
            if (input.type === 'password') {
                input.type = 'text';
                toggleIcon.className = 'fa-solid fa-eye-slash password-toggle-icon';
            } else {
                input.type = 'password';
                toggleIcon.className = 'fa-solid fa-eye password-toggle-icon';
            }
        });
    });
});
