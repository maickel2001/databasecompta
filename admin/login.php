<?php
require_once '../includes/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$error_message = '';
$info_message = '';

// Handle logout message
if (isset($_GET['logout'])) {
    $info_message = 'Vous avez été déconnecté avec succès.';
}

// Handle timeout message
if (isset($_GET['timeout'])) {
    $error_message = 'Votre session a expiré. Veuillez vous reconnecter.';
}

// Handle login form submission
if ($_POST && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_ip = get_client_ip();
    
    // Rate limiting for login attempts
    if (!rate_limit($user_ip . '_login', MAX_LOGIN_ATTEMPTS, LOCKOUT_TIME)) {
        $error_message = 'Trop de tentatives de connexion. Veuillez attendre ' . (LOCKOUT_TIME/60) . ' minutes.';
    } else {
        // Validate input
        if (empty($username) || empty($password)) {
            $error_message = 'Veuillez remplir tous les champs.';
        } else {
            // Attempt login
            if ($user->login($username, $password)) {
                // Set session variables
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['last_activity'] = time();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to dashboard
                redirect(SITE_URL . '/admin/dashboard.php');
            } else {
                $error_message = 'Nom d\'utilisateur ou mot de passe incorrect.';
            }
        }
    }
}

$page_title = 'Connexion Administrateur';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <!-- Login Container -->
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div class="glass-card" style="width: 100%; max-width: 400px;">
            <div style="padding: 2rem;">
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-shield" style="font-size: 2rem; color: white;"></i>
                    </div>
                    <h1 style="color: white; margin-bottom: 0.5rem;">Connexion Admin</h1>
                    <p style="color: rgba(255, 255, 255, 0.7);">Panneau d'administration</p>
                </div>

                <!-- Messages -->
                <?php if ($error_message): ?>
                <div style="background: rgba(245, 101, 101, 0.2); border: 1px solid rgba(245, 101, 101, 0.5); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: white;">
                    <i class="fas fa-exclamation-triangle" style="color: #f56565; margin-right: 0.5rem;"></i>
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>

                <?php if ($info_message): ?>
                <div style="background: rgba(72, 187, 120, 0.2); border: 1px solid rgba(72, 187, 120, 0.5); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: white;">
                    <i class="fas fa-info-circle" style="color: #48bb78; margin-right: 0.5rem;"></i>
                    <?php echo $info_message; ?>
                </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user" style="margin-right: 0.5rem;"></i>
                            Nom d'utilisateur
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Votre nom d'utilisateur"
                            value="<?php echo htmlspecialchars($username ?? ''); ?>"
                            required
                            autocomplete="username"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock" style="margin-right: 0.5rem;"></i>
                            Mot de passe
                        </label>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Votre mot de passe"
                                required
                                autocomplete="current-password"
                                style="padding-right: 3rem;"
                            >
                            <button 
                                type="button" 
                                id="togglePassword" 
                                style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255, 255, 255, 0.6); cursor: pointer;"
                                title="Afficher/Masquer le mot de passe"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: flex; align-items: center; color: rgba(255, 255, 255, 0.8); font-size: 0.875rem;">
                            <input type="checkbox" name="remember" style="margin-right: 0.5rem;">
                            Se souvenir de moi
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;" id="loginBtn">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                        Se connecter
                    </button>
                </form>

                <!-- Footer -->
                <div style="text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.875rem;">
                        <a href="<?php echo SITE_URL; ?>" style="color: #4facfe; text-decoration: none;">
                            <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>
                            Retour au site
                        </a>
                    </p>
                    
                    <div style="margin-top: 1rem;">
                        <p style="color: rgba(255, 255, 255, 0.5); font-size: 0.75rem;">
                            Accès par défaut:<br>
                            <code>admin / admin123</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');
        
        // Password visibility toggle
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                showMessage('Veuillez remplir tous les champs.', 'error');
                return;
            }
            
            // Show loading state
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 0.5rem;"></i>Connexion...';
            loginBtn.disabled = true;
        });
        
        // Auto-focus on username field
        document.getElementById('username').focus();
        
        // Clear form on browser back
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                form.reset();
                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>Se connecter';
                loginBtn.disabled = false;
            }
        });
    });
    
    // Message display function
    function showMessage(message, type = 'info') {
        const messageEl = document.createElement('div');
        messageEl.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            color: white;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        if (type === 'error') {
            messageEl.style.borderColor = 'rgba(245, 101, 101, 0.5)';
        }
        
        messageEl.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            messageEl.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (messageEl.parentNode) {
                    messageEl.parentNode.removeChild(messageEl);
                }
            }, 300);
        }, 5000);
    }
    </script>
</body>
</html>