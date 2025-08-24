<?php
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not permitted');
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Administration - <?php echo SITE_NAME; ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Meta -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/favicon.ico">
</head>
<body class="admin-body">
    
    <!-- Admin Topbar -->
    <nav class="admin-topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="admin-logo">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">
                    <i class="fas fa-shield-alt"></i>
                    <span>Admin Panel</span>
                </a>
            </div>
        </div>
        
        <div class="topbar-center">
            <div class="admin-search">
                <input type="text" placeholder="Rechercher..." id="adminSearch">
                <i class="fas fa-search"></i>
            </div>
        </div>
        
        <div class="topbar-right">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="<?php echo SITE_URL; ?>" target="_blank" class="quick-action" title="Voir le site">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                
                <button class="quick-action" id="newPostBtn" title="Nouvel article">
                    <i class="fas fa-plus"></i>
                </button>
                
                <div class="quick-action notifications" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
            </div>
            
            <!-- Admin Profile -->
            <div class="admin-profile dropdown">
                <button class="profile-toggle" id="profileToggle">
                    <img src="https://via.placeholder.com/40x40/667eea/ffffff?text=<?php echo substr($_SESSION['username'], 0, 2); ?>" 
                         alt="Profile" class="profile-avatar">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                
                <div class="dropdown-menu" id="profileDropdown">
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        Mon profil
                    </a>
                    <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Paramètres
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Admin Layout -->
    <div class="admin-layout">
        
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-content">
                
                <!-- Dashboard -->
                <div class="sidebar-section">
                    <h6 class="sidebar-heading">Tableau de bord</h6>
                    <ul class="sidebar-menu">
                        <li class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                            <a href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Accueil</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'analytics' ? 'active' : ''; ?>">
                            <a href="analytics.php">
                                <i class="fas fa-chart-line"></i>
                                <span>Statistiques</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Content Management -->
                <div class="sidebar-section">
                    <h6 class="sidebar-heading">Contenu</h6>
                    <ul class="sidebar-menu">
                        <li class="<?php echo in_array($current_page, ['posts', 'post-create', 'post-edit']) ? 'active' : ''; ?>">
                            <a href="posts.php">
                                <i class="fas fa-newspaper"></i>
                                <span>Articles</span>
                                <span class="menu-badge"><?php echo $post->getTotalCount(); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'comments' ? 'active' : ''; ?>">
                            <a href="comments.php">
                                <i class="fas fa-comments"></i>
                                <span>Commentaires</span>
                                <?php 
                                $pending_comments = $comment->getPendingCount();
                                if ($pending_comments > 0): 
                                ?>
                                <span class="menu-badge pending"><?php echo $pending_comments; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'media' ? 'active' : ''; ?>">
                            <a href="media.php">
                                <i class="fas fa-images"></i>
                                <span>Médias</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Communications -->
                <div class="sidebar-section">
                    <h6 class="sidebar-heading">Communications</h6>
                    <ul class="sidebar-menu">
                        <li class="<?php echo $current_page === 'messages' ? 'active' : ''; ?>">
                            <a href="messages.php">
                                <i class="fas fa-envelope"></i>
                                <span>Messages</span>
                                <?php 
                                $unread_messages = $message->getUnreadCount();
                                if ($unread_messages > 0): 
                                ?>
                                <span class="menu-badge unread"><?php echo $unread_messages; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'newsletter' ? 'active' : ''; ?>">
                            <a href="newsletter.php">
                                <i class="fas fa-mail-bulk"></i>
                                <span>Newsletter</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- System -->
                <div class="sidebar-section">
                    <h6 class="sidebar-heading">Système</h6>
                    <ul class="sidebar-menu">
                        <li class="<?php echo $current_page === 'users' ? 'active' : ''; ?>">
                            <a href="users.php">
                                <i class="fas fa-users"></i>
                                <span>Utilisateurs</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                            <a href="settings.php">
                                <i class="fas fa-cog"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'backup' ? 'active' : ''; ?>">
                            <a href="backup.php">
                                <i class="fas fa-database"></i>
                                <span>Sauvegardes</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Quick Stats -->
                <div class="sidebar-stats">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-number"><?php echo number_format($post->getTotalViews()); ?></span>
                            <span class="stat-label">Vues totales</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-number"><?php echo $like->getTotalCount(); ?></span>
                            <span class="stat-label">Likes totaux</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-content">
                
                <?php if (isset($_SESSION['admin_message'])): ?>
                <div class="admin-alert alert-<?php echo $_SESSION['admin_message']['type']; ?>">
                    <i class="fas fa-<?php echo $_SESSION['admin_message']['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <span><?php echo $_SESSION['admin_message']['text']; ?></span>
                    <button class="alert-close">&times;</button>
                </div>
                <?php 
                unset($_SESSION['admin_message']);
                endif; 
                ?>