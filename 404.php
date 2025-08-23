<?php
require_once 'includes/init.php';

// Set 404 header
http_response_code(404);

$page_title = 'Page non trouvée';
$page_description = 'La page que vous recherchez n\'existe pas ou a été déplacée.';

includeHeader($page_title, $page_description);
?>

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <a href="<?php echo SITE_URL; ?>" class="logo">Maickel Okereke</a>
        <ul class="nav-menu">
            <li><a href="<?php echo SITE_URL; ?>" class="nav-link">Accueil</a></li>
            <li><a href="<?php echo SITE_URL; ?>/blog.php" class="nav-link">Blog</a></li>
            <li><a href="<?php echo SITE_URL; ?>/contact.php" class="nav-link">Contact</a></li>
        </ul>
    </div>
</nav>

<!-- 404 Section -->
<section class="hero">
    <div class="hero-content">
        <div class="glass-card" style="padding: 3rem; text-align: center; max-width: 600px; margin: 0 auto;">
            <div style="font-size: 6rem; font-weight: 700; color: rgba(255, 255, 255, 0.3); margin-bottom: 1rem;">
                404
            </div>
            
            <h1 style="font-size: 2rem; margin-bottom: 1rem; color: white;">
                Page non trouvée
            </h1>
            
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.125rem; margin-bottom: 2rem; line-height: 1.6;">
                Désolé, la page que vous recherchez n'existe pas ou a été déplacée. 
                Vérifiez l'URL ou retournez à l'accueil.
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
                    <i class="fas fa-home" style="margin-right: 0.5rem;"></i>
                    Retour à l'accueil
                </a>
                
                <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-glass">
                    <i class="fas fa-newspaper" style="margin-right: 0.5rem;"></i>
                    Voir le blog
                </a>
            </div>
            
            <!-- Quick Links -->
            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <h3 style="color: white; margin-bottom: 1.5rem;">Liens utiles</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; text-align: left;">
                    <div>
                        <h4 style="color: rgba(255, 255, 255, 0.9); font-size: 0.875rem; margin-bottom: 0.5rem;">Services</h4>
                        <ul style="list-style: none; padding: 0; color: rgba(255, 255, 255, 0.7); font-size: 0.875rem;">
                            <li style="margin-bottom: 0.25rem;">• Comptabilité</li>
                            <li style="margin-bottom: 0.25rem;">• Développement web</li>
                            <li style="margin-bottom: 0.25rem;">• Consultation</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 style="color: rgba(255, 255, 255, 0.9); font-size: 0.875rem; margin-bottom: 0.5rem;">Contact</h4>
                        <ul style="list-style: none; padding: 0; color: rgba(255, 255, 255, 0.7); font-size: 0.875rem;">
                            <li style="margin-bottom: 0.25rem;">
                                <a href="mailto:<?php echo ADMIN_EMAIL; ?>" style="color: #4facfe; text-decoration: none;">
                                    <?php echo ADMIN_EMAIL; ?>
                                </a>
                            </li>
                            <li style="margin-bottom: 0.25rem;">+1 (234) 567-8900</li>
                            <li style="margin-bottom: 0.25rem;">
                                <a href="<?php echo SITE_URL; ?>/contact.php" style="color: #4facfe; text-decoration: none;">
                                    Formulaire de contact
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php includeFooter(); ?>

<script>
// Add some interactive elements
document.addEventListener('DOMContentLoaded', function() {
    // Animate the 404 number
    const number404 = document.querySelector('.hero-content .glass-card div:first-child');
    if (number404) {
        let opacity = 0.1;
        const animate = () => {
            opacity += 0.01;
            if (opacity >= 0.3) opacity = 0.1;
            number404.style.color = `rgba(255, 255, 255, ${opacity})`;
            requestAnimationFrame(animate);
        };
        animate();
    }
    
    // Track 404 errors (optional analytics)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            page_title: '404 - Page Not Found',
            page_location: window.location.href
        });
    }
});
</script>