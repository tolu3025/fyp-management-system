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
                indicator.textContent = devMailContent.classList.contains('open') ? '▼' : '▲';
            }
        });
    }
});
