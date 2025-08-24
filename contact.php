<?php
require_once 'includes/init.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_POST && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $msg = sanitize_input($_POST['message'] ?? '');
    $user_ip = get_client_ip();
    
    // Validation
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Le nom doit contenir au moins 2 caractères.';
    }
    
    if (!validate_email($email)) {
        $errors[] = 'Veuillez entrer une adresse email valide.';
    }
    
    if (empty($subject) || strlen($subject) < 5) {
        $errors[] = 'Le sujet doit contenir au moins 5 caractères.';
    }
    
    if (empty($msg) || strlen($msg) < 10) {
        $errors[] = 'Le message doit contenir au moins 10 caractères.';
    }
    
    // Rate limiting
    if (!rate_limit($user_ip . '_contact', 3, 3600)) {
        $errors[] = 'Trop de messages envoyés. Veuillez attendre avant de renvoyer.';
    }
    
    if (empty($errors)) {
        // Create message object
        $message->name = $name;
        $message->email = $email;
        $message->subject = $subject;
        $message->message = $msg;
        $message->ip_address = $user_ip;
        
        // Check for spam
        if ($message->isSpam()) {
            $error_message = 'Votre message a été identifié comme spam. Veuillez réessayer.';
        } else {
            // Save to database
            if ($message->create()) {
                // Send email notifications
                $message->sendEmailNotification();
                $message->sendAutoReply();
                
                $success_message = 'Votre message a été envoyé avec succès ! Je vous répondrai dans les plus brefs délais.';
                
                // Clear form data
                $name = $email = $subject = $msg = '';
            } else {
                $error_message = 'Erreur lors de l\'envoi du message. Veuillez réessayer.';
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Page metadata
$page_title = 'Contact';
$page_description = 'Contactez Maickel Okereke pour vos besoins en comptabilité et développement web. Consultation gratuite disponible.';
$page_keywords = 'contact, consultation, comptable, développeur web, services financiers';

includeHeader($page_title, $page_description, $page_keywords);
?>

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <a href="<?php echo SITE_URL; ?>" class="logo">Maickel Okereke</a>
        <ul class="nav-menu">
            <li><a href="<?php echo SITE_URL; ?>" class="nav-link">Accueil</a></li>
            <li><a href="<?php echo SITE_URL; ?>/blog.php" class="nav-link">Blog</a></li>
            <li><a href="<?php echo SITE_URL; ?>/contact.php" class="nav-link active">Contact</a></li>
        </ul>
        <button class="mobile-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero" style="min-height: 60vh; padding-top: 120px;">
    <div class="hero-content">
        <h1 class="hero-title">Contactez-moi</h1>
        <p class="hero-subtitle">
            Prêt à transformer votre entreprise ? Discutons de vos besoins
        </p>
    </div>
</section>

<!-- Contact Section -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="align-items: start; gap: 3rem;">
            <!-- Contact Form -->
            <div class="glass-card">
                <div style="padding: 2rem;">
                    <h2 style="margin-bottom: 1.5rem;">
                        <i class="fas fa-envelope" style="margin-right: 0.5rem; color: #4facfe;"></i>
                        Envoyez-moi un message
                    </h2>
                    
                    <?php if ($success_message): ?>
                    <div style="background: rgba(72, 187, 120, 0.2); border: 1px solid rgba(72, 187, 120, 0.5); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: white;">
                        <i class="fas fa-check-circle" style="color: #48bb78; margin-right: 0.5rem;"></i>
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div style="background: rgba(245, 101, 101, 0.2); border: 1px solid rgba(245, 101, 101, 0.5); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: white;">
                        <i class="fas fa-exclamation-triangle" style="color: #f56565; margin-right: 0.5rem;"></i>
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="contact-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="form-group">
                            <label for="name" class="form-label">Nom complet *</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-input" 
                                placeholder="Votre nom complet"
                                value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Adresse email *</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="votre@email.com"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="subject" class="form-label">Sujet *</label>
                            <select id="subject" name="subject" class="form-input" required>
                                <option value="">Choisissez un sujet</option>
                                <option value="Consultation comptable" <?php echo (isset($subject) && $subject === 'Consultation comptable') ? 'selected' : ''; ?>>Consultation comptable</option>
                                <option value="Développement web" <?php echo (isset($subject) && $subject === 'Développement web') ? 'selected' : ''; ?>>Développement web</option>
                                <option value="Services de tenue de livres" <?php echo (isset($subject) && $subject === 'Services de tenue de livres') ? 'selected' : ''; ?>>Services de tenue de livres</option>
                                <option value="Préparation fiscale" <?php echo (isset($subject) && $subject === 'Préparation fiscale') ? 'selected' : ''; ?>>Préparation fiscale</option>
                                <option value="Consultation d'affaires" <?php echo (isset($subject) && $subject === 'Consultation d\'affaires') ? 'selected' : ''; ?>>Consultation d'affaires</option>
                                <option value="Autre" <?php echo (isset($subject) && $subject === 'Autre') ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label">Message *</label>
                            <textarea 
                                id="message" 
                                name="message" 
                                class="form-textarea" 
                                placeholder="Décrivez votre projet ou vos besoins..."
                                required
                            ><?php echo htmlspecialchars($msg ?? ''); ?></textarea>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: flex; align-items: center; color: rgba(255, 255, 255, 0.8); font-size: 0.875rem;">
                                <input type="checkbox" required style="margin-right: 0.5rem;">
                                J'accepte d'être contacté concernant ma demande
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                            Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div>
                <!-- Contact Details -->
                <div class="glass-card">
                    <div style="padding: 2rem;">
                        <h3 style="margin-bottom: 1.5rem;">
                            <i class="fas fa-address-card" style="margin-right: 0.5rem; color: #f093fb;"></i>
                            Informations de contact
                        </h3>
                        
                        <div style="display: grid; gap: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: rgba(79, 172, 254, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-envelope" style="color: #4facfe;"></i>
                                </div>
                                <div>
                                    <h4 style="color: white; margin-bottom: 0.25rem;">Email</h4>
                                    <a href="mailto:<?php echo ADMIN_EMAIL; ?>" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">
                                        <?php echo ADMIN_EMAIL; ?>
                                    </a>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: rgba(240, 147, 251, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-phone" style="color: #f093fb;"></i>
                                </div>
                                <div>
                                    <h4 style="color: white; margin-bottom: 0.25rem;">Téléphone</h4>
                                    <a href="tel:+1234567890" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">
                                        +1 (234) 567-8900
                                    </a>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: rgba(0, 242, 254, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-map-marker-alt" style="color: #00f2fe;"></i>
                                </div>
                                <div>
                                    <h4 style="color: white; margin-bottom: 0.25rem;">Localisation</h4>
                                    <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">
                                        Votre Ville, Province<br>
                                        Consultations à distance disponibles
                                    </p>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 50px; height: 50px; background: rgba(102, 126, 234, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-clock" style="color: #667eea;"></i>
                                </div>
                                <div>
                                    <h4 style="color: white; margin-bottom: 0.25rem;">Heures d'ouverture</h4>
                                    <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">
                                        Lun-Ven: 9h00 - 18h00<br>
                                        Sam: 10h00 - 14h00
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Services Overview -->
                <div class="glass-card" style="margin-top: 2rem;">
                    <div style="padding: 2rem;">
                        <h3 style="margin-bottom: 1.5rem;">
                            <i class="fas fa-handshake" style="margin-right: 0.5rem; color: #4facfe;"></i>
                            Consultation gratuite
                        </h3>
                        
                        <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.6; margin-bottom: 1.5rem;">
                            Je propose une consultation gratuite de 30 minutes pour discuter de vos besoins 
                            et voir comment je peux vous aider à atteindre vos objectifs.
                        </p>
                        
                        <div style="display: grid; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-check" style="color: #48bb78;"></i>
                                <span style="color: rgba(255, 255, 255, 0.8); font-size: 0.875rem;">Analyse de vos besoins</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-check" style="color: #48bb78;"></i>
                                <span style="color: rgba(255, 255, 255, 0.8); font-size: 0.875rem;">Recommandations personnalisées</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-check" style="color: #48bb78;"></i>
                                <span style="color: rgba(255, 255, 255, 0.8); font-size: 0.875rem;">Devis détaillé</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-check" style="color: #48bb78;"></i>
                                <span style="color: rgba(255, 255, 255, 0.8); font-size: 0.875rem;">Plan d'action</span>
                            </div>
                        </div>
                        
                        <a href="tel:+1234567890" class="btn btn-secondary" style="width: 100%; margin-top: 1.5rem;">
                            <i class="fas fa-phone" style="margin-right: 0.5rem;"></i>
                            Appelez maintenant
                        </a>
                    </div>
                </div>
                
                <!-- Social Links -->
                <div class="glass-card" style="margin-top: 2rem;">
                    <div style="padding: 2rem;">
                        <h3 style="margin-bottom: 1.5rem; text-align: center;">
                            <i class="fas fa-share-alt" style="margin-right: 0.5rem; color: #f093fb;"></i>
                            Suivez-moi
                        </h3>
                        
                        <div class="social-links" style="margin: 0;">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Questions fréquentes</h2>
            <p class="section-subtitle">
                Réponses aux questions les plus courantes
            </p>
        </div>
        
        <div class="grid grid-2">
            <div class="glass-card">
                <div style="padding: 1.5rem;">
                    <h4 style="color: white; margin-bottom: 1rem;">
                        <i class="fas fa-question-circle" style="color: #4facfe; margin-right: 0.5rem;"></i>
                        Quels services proposez-vous ?
                    </h4>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.6;">
                        Je propose des services de comptabilité (tenue de livres, préparation fiscale), 
                        de développement web (sites vitrines, e-commerce) et de consultation d'affaires.
                    </p>
                </div>
            </div>
            
            <div class="glass-card">
                <div style="padding: 1.5rem;">
                    <h4 style="color: white; margin-bottom: 1rem;">
                        <i class="fas fa-clock" style="color: #f093fb; margin-right: 0.5rem;"></i>
                        Quel est le délai de réponse ?
                    </h4>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.6;">
                        Je réponds généralement dans les 24-48 heures. Pour les urgences, 
                        n'hésitez pas à m'appeler directement.
                    </p>
                </div>
            </div>
            
            <div class="glass-card">
                <div style="padding: 1.5rem;">
                    <h4 style="color: white; margin-bottom: 1rem;">
                        <i class="fas fa-money-bill-wave" style="color: #00f2fe; margin-right: 0.5rem;"></i>
                        Comment fonctionnent vos tarifs ?
                    </h4>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.6;">
                        Mes tarifs dépendent du projet et de sa complexité. La consultation initiale 
                        est gratuite et je fournis un devis détaillé avant tout engagement.
                    </p>
                </div>
            </div>
            
            <div class="glass-card">
                <div style="padding: 1.5rem;">
                    <h4 style="color: white; margin-bottom: 1rem;">
                        <i class="fas fa-globe" style="color: #667eea; margin-right: 0.5rem;"></i>
                        Travaillez-vous à distance ?
                    </h4>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.6;">
                        Oui, je travaille avec des clients partout au Canada et à l'international. 
                        Les consultations se font par vidéoconférence ou téléphone.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php includeFooter(); ?>

<script>
// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.contact-form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Real-time validation
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearError);
    });
    
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        
        // Remove existing error styling
        field.style.borderColor = '';
        
        let isValid = true;
        
        switch(field.name) {
            case 'name':
                if (value.length < 2) {
                    showFieldError(field, 'Le nom doit contenir au moins 2 caractères');
                    isValid = false;
                }
                break;
                
            case 'email':
                if (!isValidEmail(value)) {
                    showFieldError(field, 'Veuillez entrer une adresse email valide');
                    isValid = false;
                }
                break;
                
            case 'subject':
                if (value === '') {
                    showFieldError(field, 'Veuillez choisir un sujet');
                    isValid = false;
                }
                break;
                
            case 'message':
                if (value.length < 10) {
                    showFieldError(field, 'Le message doit contenir au moins 10 caractères');
                    isValid = false;
                }
                break;
        }
        
        if (isValid) {
            showFieldSuccess(field);
        }
    }
    
    function showFieldError(field, message) {
        field.style.borderColor = '#f56565';
        // Remove existing error message
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.cssText = 'color: #f56565; font-size: 0.875rem; margin-top: 0.25rem;';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function showFieldSuccess(field) {
        field.style.borderColor = '#48bb78';
        // Remove error message
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    function clearError(e) {
        const field = e.target;
        field.style.borderColor = '';
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        // Validate all fields before submission
        let isFormValid = true;
        inputs.forEach(input => {
            if (input.hasAttribute('required')) {
                const event = { target: input };
                validateField(event);
                if (input.style.borderColor === '#f56565') {
                    isFormValid = false;
                }
            }
        });
        
        if (!isFormValid) {
            e.preventDefault();
            showMessage('Veuillez corriger les erreurs avant de soumettre le formulaire.', 'error');
        }
    });
});
</script>