    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Maickel Okereke</h3>
                    <p>Professional Accountant & Web Developer</p>
                    <p>Transforming businesses through expert financial management and cutting-edge web solutions.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Services</h3>
                    <p><a href="#services">Accounting & Bookkeeping</a></p>
                    <p><a href="#services">Tax Preparation & Planning</a></p>
                    <p><a href="#services">Web Development</a></p>
                    <p><a href="#services">Business Consulting</a></p>
                    <p><a href="#services">Financial Analysis</a></p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="<?php echo SITE_URL; ?>">Home</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/blog.php">Blog</a></p>
                    <p><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></p>
                    <p><a href="<?php echo SITE_URL; ?>#portfolio">Portfolio</a></p>
                    <p><a href="<?php echo SITE_URL; ?>#about">About</a></p>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-envelope"></i> <?php echo ADMIN_EMAIL; ?></p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-map-marker-alt"></i> Your City, State</p>
                    <p><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM</p>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="social-links">
                <a href="https://linkedin.com/in/maickelokereke" target="_blank" rel="noopener noreferrer" class="social-link" title="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="https://github.com/maickelokereke" target="_blank" rel="noopener noreferrer" class="social-link" title="GitHub">
                    <i class="fab fa-github"></i>
                </a>
                <a href="https://twitter.com/maickelokereke" target="_blank" rel="noopener noreferrer" class="social-link" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://facebook.com/maickelokereke" target="_blank" rel="noopener noreferrer" class="social-link" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://instagram.com/maickelokereke" target="_blank" rel="noopener noreferrer" class="social-link" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
            
            <!-- Copyright -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Maickel Okereke. All rights reserved.</p>
                <p>
                    <a href="<?php echo SITE_URL; ?>/privacy-policy.php" style="margin-right: 1rem;">Privacy Policy</a>
                    <a href="<?php echo SITE_URL; ?>/terms-of-service.php">Terms of Service</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Additional page-specific scripts can be included here -->
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
    
    <!-- Font Awesome (if not already included) -->
    <script>
        // Check if Font Awesome is loaded, if not load it
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
            document.head.appendChild(link);
        }
    </script>
    
    <!-- Performance and analytics scripts -->
    <script>
        // Page performance monitoring
        window.addEventListener('load', function() {
            // Remove page loader
            const loader = document.querySelector('.page-loader');
            if (loader) {
                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => {
                        loader.remove();
                    }, 300);
                }, 500);
            }
            
            // Log performance metrics (optional)
            if ('performance' in window) {
                const perfData = window.performance.timing;
                const loadTime = perfData.loadEventEnd - perfData.navigationStart;
                console.log('Page load time:', loadTime + 'ms');
            }
        });
        
        // Service Worker registration (for PWA features)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo SITE_URL; ?>/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed');
                    });
            });
        }
        
        // Lazy loading fallback for older browsers
        if (!('IntersectionObserver' in window)) {
            const script = document.createElement('script');
            script.src = 'https://polyfill.io/v3/polyfill.min.js?features=IntersectionObserver';
            document.head.appendChild(script);
        }
        
        // Add mobile menu functionality for smaller screens
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navMenu = document.querySelector('.nav-menu');
            
            if (mobileMenuBtn && navMenu) {
                // Create mobile menu if it doesn't exist
                if (window.innerWidth <= 768) {
                    navMenu.style.display = 'none';
                    navMenu.style.position = 'absolute';
                    navMenu.style.top = '100%';
                    navMenu.style.left = '0';
                    navMenu.style.right = '0';
                    navMenu.style.background = 'rgba(255, 255, 255, 0.1)';
                    navMenu.style.backdropFilter = 'blur(20px)';
                    navMenu.style.flexDirection = 'column';
                    navMenu.style.padding = '1rem';
                    navMenu.style.border = '1px solid rgba(255, 255, 255, 0.2)';
                    navMenu.style.borderRadius = '0 0 12px 12px';
                }
                
                mobileMenuBtn.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        if (navMenu.style.display === 'none') {
                            navMenu.style.display = 'flex';
                        } else {
                            navMenu.style.display = 'none';
                        }
                    }
                });
                
                // Close mobile menu when clicking outside or on a link
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && !mobileMenuBtn.contains(e.target) && !navMenu.contains(e.target)) {
                        navMenu.style.display = 'none';
                    }
                });
                
                // Handle window resize
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        navMenu.style.display = 'flex';
                        navMenu.style.position = 'static';
                        navMenu.style.flexDirection = 'row';
                        navMenu.style.background = 'none';
                        navMenu.style.backdropFilter = 'none';
                        navMenu.style.border = 'none';
                        navMenu.style.borderRadius = '0';
                        navMenu.style.padding = '0';
                    } else {
                        navMenu.style.display = 'none';
                        navMenu.style.position = 'absolute';
                        navMenu.style.flexDirection = 'column';
                        navMenu.style.background = 'rgba(255, 255, 255, 0.1)';
                        navMenu.style.backdropFilter = 'blur(20px)';
                        navMenu.style.border = '1px solid rgba(255, 255, 255, 0.2)';
                        navMenu.style.borderRadius = '0 0 12px 12px';
                        navMenu.style.padding = '1rem';
                    }
                });
            }
        });
    </script>
    
    <!-- Cookie consent (optional) -->
    <script>
        // Simple cookie consent
        function checkCookieConsent() {
            if (!localStorage.getItem('cookieConsent')) {
                const cookieBanner = document.createElement('div');
                cookieBanner.style.cssText = `
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: rgba(0, 0, 0, 0.9);
                    color: white;
                    padding: 1rem;
                    text-align: center;
                    z-index: 10000;
                    backdrop-filter: blur(10px);
                `;
                cookieBanner.innerHTML = `
                    <p style="margin: 0 0 1rem;">This website uses cookies to enhance your experience. 
                    <a href="${'<?php echo SITE_URL; ?>'}/privacy-policy.php" style="color: #4facfe;">Learn more</a></p>
                    <button onclick="acceptCookies()" style="background: #4facfe; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">Accept</button>
                    <button onclick="declineCookies()" style="background: transparent; color: white; border: 1px solid white; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; margin-left: 0.5rem;">Decline</button>
                `;
                document.body.appendChild(cookieBanner);
            }
        }
        
        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'accepted');
            document.querySelector('[style*="position: fixed"][style*="bottom: 0"]').remove();
        }
        
        function declineCookies() {
            localStorage.setItem('cookieConsent', 'declined');
            document.querySelector('[style*="position: fixed"][style*="bottom: 0"]').remove();
        }
        
        // Check cookie consent on page load
        setTimeout(checkCookieConsent, 2000);
    </script>
</body>
</html>