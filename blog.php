<?php
require_once 'includes/init.php';

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Calculate offset
$limit = POSTS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get posts with search
$posts = $post->getAll($limit, $offset, 'published', $search);
$total_posts = $post->getTotalCount('published', $search);

// Calculate pagination info
$total_pages = ceil($total_posts / $limit);

// Page metadata
$page_title = !empty($search) ? "Search: $search" : 'Blog';
$page_description = 'Latest insights on accounting, web development, and business strategy from Maickel Okereke.';
$page_keywords = 'blog, accounting tips, web development, business strategy, financial planning, technology insights';

includeHeader($page_title, $page_description, $page_keywords);
?>

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <a href="<?php echo SITE_URL; ?>" class="logo">Maickel Okereke</a>
        <ul class="nav-menu">
            <li><a href="<?php echo SITE_URL; ?>" class="nav-link">Home</a></li>
            <li><a href="<?php echo SITE_URL; ?>/blog.php" class="nav-link active">Blog</a></li>
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
    <div class="hero-content">
        <h1 class="hero-title">Blog</h1>
        <p class="hero-subtitle">
            Insights on accounting, web development, and business strategy
        </p>
        
        <!-- Search Form -->
        <div style="max-width: 500px; margin: 2rem auto;">
            <form method="GET" class="search-form" style="display: flex; gap: 1rem;">
                <div style="flex: 1; position: relative;">
                    <input 
                        type="text" 
                        name="search" 
                        value="<?php echo htmlspecialchars($search); ?>" 
                        placeholder="Search articles..." 
                        class="form-input search-input"
                        style="padding-left: 3rem;"
                    >
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.6);"></i>
                </div>
                <button type="submit" class="btn btn-glass">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        <?php if (!empty($search)): ?>
        <p style="color: rgba(255, 255, 255, 0.8); margin-top: 1rem;">
            <?php echo $total_posts; ?> result<?php echo $total_posts !== 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($search); ?>"
            <a href="blog.php" style="color: #4facfe; margin-left: 1rem;">Clear search</a>
        </p>
        <?php endif; ?>
    </div>
</section>

<!-- Blog Posts Section -->
<section class="section">
    <div class="container">
        <?php if (empty($posts)): ?>
            <div class="glass-card" style="padding: 3rem; text-align: center;">
                <i class="fas fa-search" style="font-size: 3rem; color: rgba(255, 255, 255, 0.3); margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 1rem;">No posts found</h3>
                <p style="color: rgba(255, 255, 255, 0.7);">
                    <?php if (!empty($search)): ?>
                        No articles match your search criteria. Try different keywords or browse all posts.
                    <?php else: ?>
                        There are no published posts yet. Check back soon for new content!
                    <?php endif; ?>
                </p>
                <?php if (!empty($search)): ?>
                <a href="blog.php" class="btn btn-primary" style="margin-top: 1rem;">View All Posts</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="blog-posts">
                <?php foreach ($posts as $blog_post): 
                    // Get like status for current user (by IP)
                    $user_ip = get_client_ip();
                    $has_liked = $like->hasLiked($blog_post['id'], $user_ip);
                    $like_count = isset($blog_post['like_count']) ? $blog_post['like_count'] : $like->getCountByPostId($blog_post['id']);
                    $comment_count = isset($blog_post['comment_count']) ? $blog_post['comment_count'] : $comment->getCountByPostId($blog_post['id']);
                ?>
                <article class="glass-card blog-post" data-post-id="<?php echo $blog_post['id']; ?>">
                    <!-- Post Header -->
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; padding: 1.5rem 1.5rem 0;">
                        <img 
                            src="https://via.placeholder.com/50x50/667eea/ffffff?text=MO" 
                            alt="Maickel Okereke" 
                            style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"
                        >
                        <div>
                            <h4 style="color: var(--text-white); margin-bottom: 0.25rem;"><?php echo htmlspecialchars($blog_post['author_name']); ?></h4>
                            <div class="blog-meta">
                                <span><?php echo time_ago($blog_post['created_at']); ?></span>
                                <span>•</span>
                                <span><?php echo $blog_post['view_count']; ?> views</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Post Content -->
                    <div style="padding: 0 1.5rem;">
                        <h3 class="blog-title">
                            <a href="blog-post.php?slug=<?php echo $blog_post['slug']; ?>">
                                <?php echo htmlspecialchars($blog_post['title']); ?>
                            </a>
                        </h3>
                        
                        <p class="blog-excerpt">
                            <?php echo htmlspecialchars($blog_post['excerpt']); ?>
                        </p>
                    </div>
                    
                    <!-- Featured Image -->
                    <?php if ($blog_post['featured_image']): ?>
                    <div style="margin: 1rem 0;">
                        <img 
                            src="<?php echo UPLOAD_URL . $blog_post['featured_image']; ?>" 
                            alt="<?php echo htmlspecialchars($blog_post['title']); ?>" 
                            style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 12px;"
                        >
                    </div>
                    <?php endif; ?>
                    
                    <!-- Post Actions -->
                    <div class="blog-actions" style="padding: 0 1.5rem 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 1rem; padding-top: 1rem;">
                        <button 
                            class="like-btn <?php echo $has_liked ? 'liked' : ''; ?>" 
                            data-post-id="<?php echo $blog_post['id']; ?>"
                            title="<?php echo $has_liked ? 'Unlike' : 'Like'; ?> this post"
                        >
                            <i class="fas fa-heart"></i>
                            <span class="like-count"><?php echo $like_count; ?></span>
                        </button>
                        
                        <a href="blog-post.php?slug=<?php echo $blog_post['slug']; ?>#comments" class="comment-btn">
                            <i class="fas fa-comment"></i>
                            <span class="comment-count"><?php echo $comment_count; ?></span>
                        </a>
                        
                        <button class="share-btn" onclick="sharePost('<?php echo $blog_post['slug']; ?>', '<?php echo addslashes($blog_post['title']); ?>')">
                            <i class="fas fa-share"></i>
                            <span>Share</span>
                        </button>
                        
                        <a href="blog-post.php?slug=<?php echo $blog_post['slug']; ?>" class="btn btn-glass" style="margin-left: auto; padding: 8px 16px; font-size: 0.875rem;">
                            Read More
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div style="margin-top: 3rem;">
                <?php 
                $base_url = 'blog.php';
                if (!empty($search)) {
                    $base_url .= '?search=' . urlencode($search);
                }
                echo paginate($total_posts, $limit, $page, $base_url); 
                ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Sidebar with Recent Posts and Categories (if desired) -->
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="grid grid-2" style="align-items: start;">
            <!-- Recent Posts -->
            <div class="glass-card">
                <div style="padding: 2rem;">
                    <h3 style="margin-bottom: 1.5rem;">
                        <i class="fas fa-clock" style="margin-right: 0.5rem; color: #4facfe;"></i>
                        Recent Posts
                    </h3>
                    
                    <?php 
                    $recent_posts = $post->getRecent(5);
                    if (!empty($recent_posts)): 
                    ?>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($recent_posts as $recent_post): ?>
                        <div style="padding-bottom: 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">
                                <a href="blog-post.php?slug=<?php echo $recent_post['slug']; ?>" style="color: var(--text-white); text-decoration: none;">
                                    <?php echo htmlspecialchars($recent_post['title']); ?>
                                </a>
                            </h4>
                            <p style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.6);">
                                <?php echo time_ago($recent_post['created_at']); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p style="color: rgba(255, 255, 255, 0.7);">No recent posts available.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Popular Posts -->
            <div class="glass-card">
                <div style="padding: 2rem;">
                    <h3 style="margin-bottom: 1.5rem;">
                        <i class="fas fa-fire" style="margin-right: 0.5rem; color: #f093fb;"></i>
                        Popular Posts
                    </h3>
                    
                    <?php 
                    $popular_posts = $post->getPopular(5);
                    if (!empty($popular_posts)): 
                    ?>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($popular_posts as $popular_post): ?>
                        <div style="padding-bottom: 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">
                                <a href="blog-post.php?slug=<?php echo $popular_post['slug']; ?>" style="color: var(--text-white); text-decoration: none;">
                                    <?php echo htmlspecialchars($popular_post['title']); ?>
                                </a>
                            </h4>
                            <p style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.6);">
                                <?php echo $popular_post['view_count']; ?> views
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p style="color: rgba(255, 255, 255, 0.7);">No popular posts available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php includeFooter(); ?>

<script>
// Share functionality
function sharePost(slug, title) {
    const url = `${window.location.origin}/blog-post.php?slug=${slug}`;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).catch(console.error);
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            showMessage('Link copied to clipboard!', 'success');
        }).catch(() => {
            // Fallback: show URL in prompt
            prompt('Copy this link:', url);
        });
    }
}

// Initialize search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.querySelector('.search-form');
    
    // Auto-submit search form after typing stops
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    searchForm.submit();
                }
            }, 500);
        });
    }
});
</script>