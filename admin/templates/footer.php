            </div>
        </main>
    </div>
    
    <!-- Admin Footer -->
    <footer class="admin-footer">
        <div class="footer-content">
            <div class="footer-left">
                <span>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tous droits réservés.</span>
            </div>
            <div class="footer-center">
                <span>Version 1.0.0</span>
            </div>
            <div class="footer-right">
                <span>Dernière connexion: <?php echo date('d/m/Y H:i', $_SESSION['last_activity'] ?? time()); ?></span>
            </div>
        </div>
    </footer>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Chargement...</span>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/admin/assets/js/admin.js"></script>
    
    <!-- Admin JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize admin features
        initAdminSidebar();
        initAdminDropdowns();
        initAdminAlerts();
        initAdminSearch();
        initQuickActions();
        
        // Auto-save functionality
        initAutoSave();
        
        // Real-time notifications
        initNotifications();
        
        // Charts and analytics
        if (typeof initCharts === 'function') {
            initCharts();
        }
    });
    
    // Admin Sidebar
    function initAdminSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
            
            // Restore sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
        }
    }
    
    // Admin Dropdowns
    function initAdminDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown');
        
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.profile-toggle, .dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (toggle && menu) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Close other dropdowns
                    dropdowns.forEach(other => {
                        if (other !== dropdown) {
                            other.classList.remove('active');
                        }
                    });
                    
                    dropdown.classList.toggle('active');
                });
            }
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        });
    }
    
    // Admin Alerts
    function initAdminAlerts() {
        const alerts = document.querySelectorAll('.admin-alert');
        
        alerts.forEach(alert => {
            const closeBtn = alert.querySelector('.alert-close');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                });
            }
            
            // Auto-hide success alerts
            if (alert.classList.contains('alert-success')) {
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-20px)';
                        setTimeout(() => {
                            alert.remove();
                        }, 300);
                    }
                }, 5000);
            }
        });
    }
    
    // Admin Search
    function initAdminSearch() {
        const searchInput = document.getElementById('adminSearch');
        
        if (searchInput) {
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        performAdminSearch(query);
                    }, 300);
                }
            });
        }
    }
    
    // Quick Actions
    function initQuickActions() {
        const newPostBtn = document.getElementById('newPostBtn');
        
        if (newPostBtn) {
            newPostBtn.addEventListener('click', function() {
                window.location.href = 'post-create.php';
            });
        }
    }
    
    // Auto-save functionality
    function initAutoSave() {
        const forms = document.querySelectorAll('.auto-save-form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('input', debounce(function() {
                    autoSaveForm(form);
                }, 2000));
            });
        });
    }
    
    // Real-time notifications
    function initNotifications() {
        // Check for new notifications every 30 seconds
        setInterval(checkNotifications, 30000);
    }
    
    // Utility Functions
    function performAdminSearch(query) {
        // Implement admin search functionality
        console.log('Searching for:', query);
    }
    
    function autoSaveForm(form) {
        const formData = new FormData(form);
        formData.append('action', 'auto_save');
        
        fetch('api/auto-save.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAdminMessage('Brouillon sauvegardé automatiquement', 'info', 2000);
            }
        })
        .catch(error => {
            console.error('Auto-save error:', error);
        });
    }
    
    function checkNotifications() {
        fetch('api/notifications.php')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.count);
        })
        .catch(error => {
            console.error('Notification check error:', error);
        });
    }
    
    function updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        }
    }
    
    function showAdminMessage(message, type = 'info', duration = 5000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `admin-alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button class="alert-close">&times;</button>
        `;
        
        const content = document.querySelector('.admin-content');
        content.insertBefore(alertDiv, content.firstChild);
        
        // Add close functionality
        const closeBtn = alertDiv.querySelector('.alert-close');
        closeBtn.addEventListener('click', function() {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                alertDiv.remove();
            }, 300);
        });
        
        // Auto-hide
        if (duration > 0) {
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.style.opacity = '0';
                    alertDiv.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 300);
                }
            }, duration);
        }
    }
    
    function showLoading(show = true) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = show ? 'flex' : 'none';
        }
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // CSRF Token helper
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
    
    // Confirm delete actions
    function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
        return confirm(message);
    }
    
    // Format numbers
    function formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    }
    
    // Format dates
    function formatDate(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }
    </script>
    
    <!-- Additional Page Scripts -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?php echo $inline_scripts; ?>
        </script>
    <?php endif; ?>
    
</body>
</html>