<?php
require_once 'includes/init.php';

// Get post slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('HTTP/1.1 404 Not Found');
    include '404.php';
    exit;
}

// Get post data
$post_data = $post->getBySlug($slug);

if (!$post_data) {
    header('HTTP/1.1 404 Not Found');
    include '404.php';
    exit;
}

// Get comments for this post
$comments = $comment->getByPostId($post_data['id']);

// Get like information
$user_ip = get_client_ip();
$has_liked = $like->hasLiked($post_data['id'], $user_ip);
$like_count = $like->getCountByPostId($post_data['id']);

// Handle comment submission
$comment_success = '';
$comment_error = '';

if ($_POST && isset($_POST['submit_comment']) && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $author_name = sanitize_input($_POST['author_name'] ?? '');
    $author_email = sanitize_input($_POST['author_email'] ?? '');
    $comment_content = sanitize_input($_POST['content'] ?? '');
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    // Handle avatar upload
    $avatar_filename = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_image($_FILES['avatar'], ['jpg', 'jpeg', 'png', 'gif']);
        if ($upload_result['success']) {
            $avatar_filename = $upload_result['filename'];
        }
    }
    
    // Validation
    $errors = [];
    
    if (empty($author_name) || strlen($author_name) < 2) {
        $errors[] = 'Le nom doit contenir au moins 2 caractères.';
    }
    
    if (!validate_email($author_email)) {
        $errors[] = 'Veuillez entrer une adresse email valide.';
    }
    
    if (empty($comment_content) || strlen($comment_content) < 5) {
        $errors[] = 'Le commentaire doit contenir au moins 5 caractères.';
    }
    
    // Rate limiting
    if (!rate_limit($user_ip . '_comment', 5, 3600)) {
        $errors[] = 'Trop de commentaires postés. Veuillez attendre avant de commenter à nouveau.';
    }
    
    if (empty($errors)) {
        // Create comment
        $comment->post_id = $post_data['id'];
        $comment->parent_id = $parent_id;
        $comment->author_name = $author_name;
        $comment->author_email = $author_email;
        $comment->author_avatar = $avatar_filename;
        $comment->content = $comment_content;
        $comment->ip_address = $user_ip;
        
        // Check for spam
        if ($comment->isSpam($comment_content, $author_email)) {
            $comment_error = 'Votre commentaire a été identifié comme spam.';
        } else {
            if ($comment->create()) {
                $comment_success = 'Votre commentaire a été publié avec succès !';
                // Refresh comments
                $comments = $comment->getByPostId($post_data['id']);
            } else {
                $comment_error = 'Erreur lors de la publication du commentaire.';
            }
        }
    } else {
        $comment_error = implode('<br>', $errors);
    }
}

// Page metadata
$page_title = $post_data['title'];
$page_description = $post_data['excerpt'];
$page_keywords = 'blog, article, ' . $post_data['title'];

includeHeader($page_title, $page_description, $page_keywords);
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
        <button class="mobile-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero" style="min-height: 60vh; padding-top: 120px;">
    <div class="hero-content" style="max-width: 800px;">
        <div style="margin-bottom: 1rem;">
            <a href="blog.php" style="color: #4facfe; text-decoration: none; font-size: 0.875rem;">
                <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>
                Retour au blog
            </a>
        </div>
        
        <h1 class="hero-title" style="text-align: left; font-size: 2.5rem;">
            <?php echo htmlspecialchars($post_data['title']); ?>
        </h1>
        
        <div style="display: flex; align-items: center; gap: 2rem; margin-top: 1.5rem; color: rgba(255, 255, 255, 0.8);">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <img 
                    src="https://via.placeholder.com/40x40/667eea/ffffff?text=MO" 
                    alt="<?php echo htmlspecialchars($post_data['author_name']); ?>"
                    style="width: 40px; height: 40px; border-radius: 50%;"
                >
                <span><?php echo htmlspecialchars($post_data['author_name']); ?></span>
            </div>
            
            <span>
                <i class="fas fa-calendar-alt" style="margin-right: 0.5rem;"></i>
                <?php echo date('j F Y', strtotime($post_data['created_at'])); ?>
            </span>
            
            <span>
                <i class="fas fa-eye" style="margin-right: 0.5rem;"></i>
                <?php echo $post_data['view_count']; ?> vues
            </span>
            
            <span>
                <i class="fas fa-heart" style="margin-right: 0.5rem;"></i>
                <?php echo $like_count; ?> likes
            </span>
        </div>
    </div>
</section>

<!-- Article Content -->
<section class="section">
    <div class="container">
        <div class="grid grid-3" style="grid-template-columns: 2fr 1fr; gap: 3rem; align-items: start;">
            <!-- Main Content -->
            <div>
                <article class="glass-card">
                    <?php if ($post_data['featured_image']): ?>
                    <img 
                        src="<?php echo UPLOAD_URL . $post_data['featured_image']; ?>" 
                        alt="<?php echo htmlspecialchars($post_data['title']); ?>"
                        style="width: 100%; height: 300px; object-fit: cover; border-radius: 16px 16px 0 0;"
                    >
                    <?php endif; ?>
                    
                    <div style="padding: 2rem;">
                        <div class="blog-content" style="color: rgba(255, 255, 255, 0.9); line-height: 1.8; font-size: 1.125rem;">
                            <?php echo nl2br(htmlspecialchars($post_data['content'])); ?>
                        </div>
                        
                        <!-- Article Actions -->
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                            <div class="blog-actions">
                                <button 
                                    class="like-btn <?php echo $has_liked ? 'liked' : ''; ?>" 
                                    data-post-id="<?php echo $post_data['id']; ?>"
                                    title="<?php echo $has_liked ? 'Unlike' : 'Like'; ?> cet article"
                                >
                                    <i class="fas fa-heart"></i>
                                    <span class="like-count"><?php echo $like_count; ?></span>
                                </button>
                                
                                <a href="#comments" class="comment-btn">
                                    <i class="fas fa-comment"></i>
                                    <span><?php echo count($comments); ?> commentaires</span>
                                </a>
                                
                                <button class="share-btn" onclick="sharePost('<?php echo $post_data['slug']; ?>', '<?php echo addslashes($post_data['title']); ?>')">
                                    <i class="fas fa-share"></i>
                                    <span>Partager</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
                
                <!-- Comments Section -->
                <section id="comments" class="comments-section" style="margin-top: 3rem;">
                    <div class="glass-card">
                        <div style="padding: 2rem;">
                            <h3 style="margin-bottom: 2rem;">
                                <i class="fas fa-comments" style="margin-right: 0.5rem; color: #4facfe;"></i>
                                Commentaires (<?php echo count($comments); ?>)
                            </h3>
                            
                            <!-- Comment Form -->
                            <div style="margin-bottom: 3rem;">
                                <h4 style="margin-bottom: 1.5rem;">Laissez un commentaire</h4>
                                
                                <?php if ($comment_success): ?>
                                <div style="background: rgba(72, 187, 120, 0.2); border: 1px solid rgba(72, 187, 120, 0.5); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: white;">
                                    <i class="fas fa-check-circle" style="color: #48bb78; margin-right: 0.5rem;"></i>
                                    <?php echo $comment_success; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($comment_error): ?>
                                <div style="background: rgba(245, 101, 101, 0.2); border: 1px solid rgba(245, 101, 101, 0.5); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: white;">
                                    <i class="fas fa-exclamation-triangle" style="color: #f56565; margin-right: 0.5rem;"></i>
                                    <?php echo $comment_error; ?>
                                </div>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data" class="comment-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="submit_comment" value="1">
                                    
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                        <div class="form-group">
                                            <label for="author_name" class="form-label">Nom *</label>
                                            <input 
                                                type="text" 
                                                id="author_name" 
                                                name="author_name" 
                                                class="form-input" 
                                                placeholder="Votre nom"
                                                required
                                            >
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="author_email" class="form-label">Email *</label>
                                            <input 
                                                type="email" 
                                                id="author_email" 
                                                name="author_email" 
                                                class="form-input" 
                                                placeholder="votre@email.com"
                                                required
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="avatar" class="form-label">Photo de profil (optionnel)</label>
                                        <input 
                                            type="file" 
                                            id="avatar" 
                                            name="avatar" 
                                            class="form-input"
                                            accept="image/*"
                                        >
                                        <small style="color: rgba(255, 255, 255, 0.6); font-size: 0.75rem;">
                                            Formats acceptés: JPG, PNG, GIF. Max 5MB. Si aucune image n'est uploadée, Gravatar sera utilisé.
                                        </small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="content" class="form-label">Commentaire *</label>
                                        <textarea 
                                            id="content" 
                                            name="content" 
                                            class="form-textarea" 
                                            placeholder="Votre commentaire..."
                                            required
                                        ></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                                        Publier le commentaire
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Comments List -->
                            <div class="comments-list">
                                <?php if (empty($comments)): ?>
                                <div style="text-align: center; padding: 2rem; color: rgba(255, 255, 255, 0.6);">
                                    <i class="fas fa-comments" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                    <p>Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
                                </div>
                                <?php else: ?>
                                    <?php echo renderComments($comments, $comment); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            
            <!-- Sidebar -->
            <div>
                <!-- Author Info -->
                <div class="glass-card">
                    <div style="padding: 2rem; text-align: center;">
                        <img 
                            src="https://via.placeholder.com/80x80/667eea/ffffff?text=MO" 
                            alt="Maickel Okereke"
                            style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 1rem;"
                        >
                        <h4 style="margin-bottom: 0.5rem;">Maickel Okereke</h4>
                        <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.875rem; margin-bottom: 1.5rem;">
                            Comptable professionnel & Développeur web
                        </p>
                        <p style="color: rgba(255, 255, 255, 0.8); font-size: 0.875rem; line-height: 1.6;">
                            Expert en gestion financière et développement web moderne. 
                            Je partage mes connaissances pour aider les entreprises à prospérer.
                        </p>
                        <a href="contact.php" class="btn btn-glass" style="margin-top: 1rem; width: 100%;">
                            Me contacter
                        </a>
                    </div>
                </div>
                
                <!-- Related Posts -->
                <?php 
                $related_posts = $post->getRecent(3);
                if (!empty($related_posts)): 
                ?>
                <div class="glass-card" style="margin-top: 2rem;">
                    <div style="padding: 2rem;">
                        <h4 style="margin-bottom: 1.5rem;">
                            <i class="fas fa-newspaper" style="margin-right: 0.5rem; color: #f093fb;"></i>
                            Articles récents
                        </h4>
                        
                        <div style="display: grid; gap: 1.5rem;">
                            <?php foreach ($related_posts as $related_post): ?>
                                <?php if ($related_post['id'] != $post_data['id']): ?>
                                <div>
                                    <h5 style="font-size: 0.875rem; margin-bottom: 0.5rem;">
                                        <a href="blog-post.php?slug=<?php echo $related_post['slug']; ?>" style="color: var(--text-white); text-decoration: none;">
                                            <?php echo htmlspecialchars($related_post['title']); ?>
                                        </a>
                                    </h5>
                                    <p style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.6);">
                                        <?php echo time_ago($related_post['created_at']); ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <a href="blog.php" class="btn btn-glass" style="margin-top: 1.5rem; width: 100%;">
                            Voir tous les articles
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Share Widget -->
                <div class="glass-card" style="margin-top: 2rem;">
                    <div style="padding: 2rem;">
                        <h4 style="margin-bottom: 1.5rem;">
                            <i class="fas fa-share-alt" style="margin-right: 0.5rem; color: #4facfe;"></i>
                            Partager cet article
                        </h4>
                        
                        <div style="display: grid; gap: 1rem;">
                            <button onclick="shareToFacebook()" class="btn btn-glass" style="justify-content: flex-start;">
                                <i class="fab fa-facebook-f" style="margin-right: 0.5rem; color: #4267B2;"></i>
                                Facebook
                            </button>
                            
                            <button onclick="shareToTwitter()" class="btn btn-glass" style="justify-content: flex-start;">
                                <i class="fab fa-twitter" style="margin-right: 0.5rem; color: #1DA1F2;"></i>
                                Twitter
                            </button>
                            
                            <button onclick="shareToLinkedIn()" class="btn btn-glass" style="justify-content: flex-start;">
                                <i class="fab fa-linkedin-in" style="margin-right: 0.5rem; color: #0077B5;"></i>
                                LinkedIn
                            </button>
                            
                            <button onclick="copyLink()" class="btn btn-glass" style="justify-content: flex-start;">
                                <i class="fas fa-link" style="margin-right: 0.5rem;"></i>
                                Copier le lien
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php includeFooter(); ?>

<?php
// Function to render comments recursively
function renderComments($comments, $comment_obj, $depth = 0) {
    $html = '';
    
    foreach ($comments as $comment_data) {
        $avatar_url = $comment_obj->getAvatarUrl($comment_data['author_avatar'], $comment_data['author_email']);
        $margin_left = $depth * 2 . 'rem';
        
        $html .= '<div class="comment" style="margin-left: ' . $margin_left . '; margin-bottom: 1.5rem;">';
        $html .= '<div class="comment-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">';
        $html .= '<img src="' . $avatar_url . '" alt="' . htmlspecialchars($comment_data['author_name']) . '" class="comment-avatar">';
        $html .= '<div>';
        $html .= '<div class="comment-author">' . htmlspecialchars($comment_data['author_name']) . '</div>';
        $html .= '<div class="comment-date">' . time_ago($comment_data['created_at']) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="comment-content" style="margin-bottom: 1rem;">' . nl2br(htmlspecialchars($comment_data['content'])) . '</div>';
        $html .= '<div class="comment-actions">';
        $html .= '<button class="reply-btn btn btn-glass" style="font-size: 0.75rem; padding: 0.25rem 0.75rem;" onclick="showReplyForm(' . $comment_data['id'] . ')">';
        $html .= '<i class="fas fa-reply" style="margin-right: 0.25rem;"></i>Répondre';
        $html .= '</button>';
        $html .= '</div>';
        
        // Reply form (hidden by default)
        $html .= '<div id="reply-form-' . $comment_data['id'] . '" style="display: none; margin-top: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">';
        $html .= '<form method="POST" class="reply-form">';
        $html .= '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
        $html .= '<input type="hidden" name="submit_comment" value="1">';
        $html .= '<input type="hidden" name="parent_id" value="' . $comment_data['id'] . '">';
        $html .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">';
        $html .= '<input type="text" name="author_name" placeholder="Votre nom" class="form-input" required>';
        $html .= '<input type="email" name="author_email" placeholder="votre@email.com" class="form-input" required>';
        $html .= '</div>';
        $html .= '<textarea name="content" placeholder="Votre réponse..." class="form-textarea" style="margin-bottom: 1rem;" required></textarea>';
        $html .= '<div>';
        $html .= '<button type="submit" class="btn btn-primary" style="margin-right: 0.5rem;">Répondre</button>';
        $html .= '<button type="button" onclick="hideReplyForm(' . $comment_data['id'] . ')" class="btn btn-glass">Annuler</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        
        // Render replies if any
        if (!empty($comment_data['replies'])) {
            $html .= renderComments($comment_data['replies'], $comment_obj, $depth + 1);
        }
        
        $html .= '</div>';
    }
    
    return $html;
}
?>

<script>
// Share functions
function sharePost(slug, title) {
    const url = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).catch(console.error);
    } else {
        copyLink();
    }
}

function shareToFacebook() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
}

function shareToTwitter() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(document.title);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
}

function shareToLinkedIn() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        showMessage('Lien copié dans le presse-papiers !', 'success');
    }).catch(() => {
        prompt('Copiez ce lien:', window.location.href);
    });
}

// Reply functionality
function showReplyForm(commentId) {
    const form = document.getElementById(`reply-form-${commentId}`);
    form.style.display = 'block';
}

function hideReplyForm(commentId) {
    const form = document.getElementById(`reply-form-${commentId}`);
    form.style.display = 'none';
}

// Smooth scroll to comments
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#comments') {
        setTimeout(() => {
            document.getElementById('comments').scrollIntoView({ behavior: 'smooth' });
        }, 500);
    }
});
</script>