<?php
define('ADMIN_ACCESS', true);
require_once '../includes/init.php';

// Check admin authentication
requireAdmin();

// Page title
$page_title = 'Tableau de bord';

// Get dashboard statistics
$stats = [
    'total_posts' => $post->getTotalCount(),
    'published_posts' => $post->getPublishedCount(),
    'draft_posts' => $post->getDraftCount(),
    'total_comments' => $comment->getTotalCount(),
    'pending_comments' => $comment->getPendingCount(),
    'total_messages' => $message->getTotalCount(),
    'unread_messages' => $message->getUnreadCount(),
    'total_views' => $post->getTotalViews(),
    'total_likes' => $like->getTotalCount(),
    'today_views' => $post->getTodayViews(),
    'this_month_posts' => $post->getThisMonthCount(),
    'this_month_comments' => $comment->getThisMonthCount()
];

// Get recent activities
$recent_posts = $post->getRecent(5);
$recent_comments = $comment->getRecent(5);
$recent_messages = $message->getRecent(5);

// Get popular posts
$popular_posts = $post->getPopular(5);

// Chart data for last 30 days
$chart_data = [
    'views' => $post->getViewsChart(30),
    'posts' => $post->getPostsChart(30),
    'comments' => $comment->getCommentsChart(30)
];

include 'templates/header.php';
?>

<!-- Dashboard Header -->
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h1 class="mb-1">Tableau de bord</h1>
        <p class="text-muted mb-0">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !</p>
    </div>
    
    <div class="d-flex gap-2">
        <a href="post-create.php" class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i>
            Nouvel article
        </a>
        <button class="admin-btn admin-btn-outline" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt"></i>
            Actualiser
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="admin-stats">
    <!-- Posts Stats -->
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Articles</h3>
            <div class="stat-card-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--admin-primary);">
                <i class="fas fa-newspaper"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['total_posts']; ?></div>
        <div class="stat-card-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>+<?php echo $stats['this_month_posts']; ?> ce mois</span>
        </div>
    </div>
    
    <!-- Views Stats -->
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Vues totales</h3>
            <div class="stat-card-icon" style="background: rgba(5, 150, 105, 0.1); color: var(--admin-secondary);">
                <i class="fas fa-eye"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['total_views']); ?></div>
        <div class="stat-card-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>+<?php echo $stats['today_views']; ?> aujourd'hui</span>
        </div>
    </div>
    
    <!-- Comments Stats -->
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Commentaires</h3>
            <div class="stat-card-icon" style="background: rgba(217, 119, 6, 0.1); color: var(--admin-warning);">
                <i class="fas fa-comments"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['total_comments']; ?></div>
        <div class="stat-card-change <?php echo $stats['pending_comments'] > 0 ? 'negative' : 'positive'; ?>">
            <?php if ($stats['pending_comments'] > 0): ?>
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo $stats['pending_comments']; ?> en attente</span>
            <?php else: ?>
                <i class="fas fa-check"></i>
                <span>Tous approuvés</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Messages Stats -->
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Messages</h3>
            <div class="stat-card-icon" style="background: rgba(2, 132, 199, 0.1); color: var(--admin-info);">
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['total_messages']; ?></div>
        <div class="stat-card-change <?php echo $stats['unread_messages'] > 0 ? 'negative' : 'positive'; ?>">
            <?php if ($stats['unread_messages'] > 0): ?>
                <i class="fas fa-envelope-open"></i>
                <span><?php echo $stats['unread_messages']; ?> non lus</span>
            <?php else: ?>
                <i class="fas fa-check"></i>
                <span>Tous lus</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Views Chart -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Vues des 30 derniers jours</h3>
        </div>
        <div class="admin-card-body">
            <canvas id="viewsChart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Statistiques rapides</h3>
        </div>
        <div class="admin-card-body">
            <div style="display: grid; gap: 1rem;">
                <div class="d-flex justify-between align-center">
                    <span class="text-muted">Articles publiés</span>
                    <strong><?php echo $stats['published_posts']; ?></strong>
                </div>
                <div class="d-flex justify-between align-center">
                    <span class="text-muted">Brouillons</span>
                    <strong><?php echo $stats['draft_posts']; ?></strong>
                </div>
                <div class="d-flex justify-between align-center">
                    <span class="text-muted">Likes totaux</span>
                    <strong><?php echo $stats['total_likes']; ?></strong>
                </div>
                <div class="d-flex justify-between align-center">
                    <span class="text-muted">Moyenne vues/article</span>
                    <strong><?php echo $stats['total_posts'] > 0 ? round($stats['total_views'] / $stats['total_posts']) : 0; ?></strong>
                </div>
            </div>
            
            <hr style="border: none; height: 1px; background: var(--admin-border); margin: 1.5rem 0;">
            
            <div>
                <h4 style="font-size: 0.875rem; margin-bottom: 1rem; color: var(--admin-text-secondary);">Actions rapides</h4>
                <div style="display: grid; gap: 0.5rem;">
                    <a href="post-create.php" class="admin-btn admin-btn-outline admin-btn-sm">
                        <i class="fas fa-plus"></i>
                        Créer un article
                    </a>
                    <a href="comments.php" class="admin-btn admin-btn-outline admin-btn-sm">
                        <i class="fas fa-comments"></i>
                        Modérer commentaires
                    </a>
                    <a href="messages.php" class="admin-btn admin-btn-outline admin-btn-sm">
                        <i class="fas fa-envelope"></i>
                        Voir messages
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Recent Posts -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="d-flex justify-between align-center">
                <h3 class="admin-card-title">Articles récents</h3>
                <a href="posts.php" class="admin-btn admin-btn-outline admin-btn-sm">Voir tout</a>
            </div>
        </div>
        <div class="admin-card-body">
            <?php if (empty($recent_posts)): ?>
                <p class="text-muted text-center">Aucun article trouvé.</p>
            <?php else: ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($recent_posts as $recent_post): ?>
                    <div class="d-flex gap-1 align-center">
                        <div style="flex: 1;">
                            <h5 style="font-size: 0.875rem; margin-bottom: 0.25rem;">
                                <a href="post-edit.php?id=<?php echo $recent_post['id']; ?>" style="color: var(--admin-text-primary); text-decoration: none;">
                                    <?php echo htmlspecialchars($recent_post['title']); ?>
                                </a>
                            </h5>
                            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.75rem; color: var(--admin-text-muted);">
                                <span><?php echo time_ago($recent_post['created_at']); ?></span>
                                <span><?php echo $recent_post['view_count']; ?> vues</span>
                                <span class="status-badge status-<?php echo $recent_post['status']; ?>">
                                    <?php echo ucfirst($recent_post['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Comments -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="d-flex justify-between align-center">
                <h3 class="admin-card-title">Commentaires récents</h3>
                <a href="comments.php" class="admin-btn admin-btn-outline admin-btn-sm">Voir tout</a>
            </div>
        </div>
        <div class="admin-card-body">
            <?php if (empty($recent_comments)): ?>
                <p class="text-muted text-center">Aucun commentaire trouvé.</p>
            <?php else: ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($recent_comments as $recent_comment): ?>
                    <div class="d-flex gap-1 align-center">
                        <img 
                            src="<?php echo $comment->getAvatarUrl($recent_comment['author_avatar'], $recent_comment['author_email'], 32); ?>" 
                            alt="<?php echo htmlspecialchars($recent_comment['author_name']); ?>"
                            style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;"
                        >
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <strong style="font-size: 0.875rem;"><?php echo htmlspecialchars($recent_comment['author_name']); ?></strong>
                                <span class="status-badge status-<?php echo $recent_comment['status']; ?>">
                                    <?php echo ucfirst($recent_comment['status']); ?>
                                </span>
                            </div>
                            <p style="margin: 0; font-size: 0.75rem; color: var(--admin-text-secondary); line-height: 1.4;">
                                <?php echo htmlspecialchars(substr($recent_comment['content'], 0, 80) . (strlen($recent_comment['content']) > 80 ? '...' : '')); ?>
                            </p>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 0.25rem;">
                                <?php echo time_ago($recent_comment['created_at']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Popular Posts & Recent Messages -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Popular Posts -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Articles populaires</h3>
        </div>
        <div class="admin-card-body">
            <?php if (empty($popular_posts)): ?>
                <p class="text-muted text-center">Aucun article populaire trouvé.</p>
            <?php else: ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($popular_posts as $i => $popular_post): ?>
                    <div class="d-flex gap-1 align-center">
                        <div style="width: 24px; height: 24px; background: var(--admin-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.75rem; font-weight: 600;">
                            <?php echo $i + 1; ?>
                        </div>
                        <div style="flex: 1;">
                            <h5 style="font-size: 0.875rem; margin-bottom: 0.25rem;">
                                <a href="../blog-post.php?slug=<?php echo $popular_post['slug']; ?>" target="_blank" style="color: var(--admin-text-primary); text-decoration: none;">
                                    <?php echo htmlspecialchars($popular_post['title']); ?>
                                </a>
                            </h5>
                            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.75rem; color: var(--admin-text-muted);">
                                <span><?php echo $popular_post['view_count']; ?> vues</span>
                                <span><?php echo $popular_post['like_count'] ?? 0; ?> likes</span>
                                <span><?php echo $popular_post['comment_count'] ?? 0; ?> commentaires</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Messages -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="d-flex justify-between align-center">
                <h3 class="admin-card-title">Messages récents</h3>
                <a href="messages.php" class="admin-btn admin-btn-outline admin-btn-sm">Voir tout</a>
            </div>
        </div>
        <div class="admin-card-body">
            <?php if (empty($recent_messages)): ?>
                <p class="text-muted text-center">Aucun message trouvé.</p>
            <?php else: ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($recent_messages as $recent_message): ?>
                    <div class="d-flex gap-1 align-center">
                        <div style="width: 40px; height: 40px; background: var(--admin-info); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                            <?php echo strtoupper(substr($recent_message['name'], 0, 2)); ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <strong style="font-size: 0.875rem;"><?php echo htmlspecialchars($recent_message['name']); ?></strong>
                                <?php if ($recent_message['status'] === 'unread'): ?>
                                    <span class="status-badge status-unread">Non lu</span>
                                <?php endif; ?>
                            </div>
                            <p style="margin: 0; font-size: 0.875rem; font-weight: 500; color: var(--admin-text-primary);">
                                <?php echo htmlspecialchars($recent_message['subject']); ?>
                            </p>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 0.25rem;">
                                <?php echo htmlspecialchars($recent_message['email']); ?> • <?php echo time_ago($recent_message['created_at']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Badges CSS -->
<style>
.status-badge {
    font-size: 0.7rem;
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-published {
    background: rgba(5, 150, 105, 0.1);
    color: var(--admin-secondary);
}

.status-draft {
    background: rgba(217, 119, 6, 0.1);
    color: var(--admin-warning);
}

.status-approved {
    background: rgba(5, 150, 105, 0.1);
    color: var(--admin-secondary);
}

.status-pending {
    background: rgba(217, 119, 6, 0.1);
    color: var(--admin-warning);
}

.status-unread {
    background: rgba(220, 38, 38, 0.1);
    color: var(--admin-danger);
}

.status-read {
    background: rgba(5, 150, 105, 0.1);
    color: var(--admin-secondary);
}
</style>

<?php
// Add Chart.js scripts
$page_scripts = ['https://cdn.jsdelivr.net/npm/chart.js'];

// Inline scripts for charts
$inline_scripts = "
// Views Chart
const viewsCtx = document.getElementById('viewsChart').getContext('2d');
const viewsChart = new Chart(viewsCtx, {
    type: 'line',
    data: {
        labels: " . json_encode(array_keys($chart_data['views'])) . ",
        datasets: [{
            label: 'Vues',
            data: " . json_encode(array_values($chart_data['views'])) . ",
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#e2e8f0'
                }
            },
            x: {
                grid: {
                    color: '#e2e8f0'
                }
            }
        }
    }
});

// Refresh dashboard function
function refreshDashboard() {
    showLoading(true);
    location.reload();
}
";

include 'templates/footer.php';
?>