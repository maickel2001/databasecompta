<?php
define('ADMIN_ACCESS', true);
require_once '../includes/init.php';

// Check admin authentication
requireAdmin();

// Page title
$page_title = 'Gestion des articles';

// Handle bulk actions
if ($_POST && isset($_POST['bulk_action']) && isset($_POST['selected_posts'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Token CSRF invalide.'];
    } else {
        $action = $_POST['bulk_action'];
        $selected_posts = $_POST['selected_posts'];
        $success_count = 0;
        
        foreach ($selected_posts as $post_id) {
            switch ($action) {
                case 'delete':
                    if ($post->delete($post_id)) {
                        $success_count++;
                    }
                    break;
                case 'publish':
                    if ($post->updateStatus($post_id, 'published')) {
                        $success_count++;
                    }
                    break;
                case 'draft':
                    if ($post->updateStatus($post_id, 'draft')) {
                        $success_count++;
                    }
                    break;
            }
        }
        
        if ($success_count > 0) {
            $action_text = [
                'delete' => 'supprimé(s)',
                'publish' => 'publié(s)',
                'draft' => 'mis en brouillon'
            ];
            $_SESSION['admin_message'] = [
                'type' => 'success', 
                'text' => "$success_count article(s) {$action_text[$action]} avec succès."
            ];
        }
    }
    
    redirect($_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
}

// Handle single post actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $post_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Token CSRF invalide.'];
    } else {
        switch ($action) {
            case 'delete':
                if ($post->delete($post_id)) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Article supprimé avec succès.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors de la suppression.'];
                }
                break;
            case 'publish':
                if ($post->updateStatus($post_id, 'published')) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Article publié avec succès.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors de la publication.'];
                }
                break;
            case 'draft':
                if ($post->updateStatus($post_id, 'draft')) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Article mis en brouillon.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors de la mise en brouillon.'];
                }
                break;
        }
    }
    
    redirect('posts.php');
}

// Get filters and search
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Get posts with filters
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get posts
$posts = $post->getAll($per_page, $offset, $status_filter, $search);
$total_posts = $post->getTotalCount($where_clause, $params);
$total_pages = ceil($total_posts / $per_page);

// Get post statistics
$stats = [
    'total' => $post->getTotalCount(),
    'published' => $post->getPublishedCount(),
    'draft' => $post->getDraftCount(),
    'this_month' => $post->getThisMonthCount()
];

include 'templates/header.php';
?>

<!-- Posts Header -->
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h1 class="mb-1">Gestion des articles</h1>
        <p class="text-muted mb-0"><?php echo $total_posts; ?> article(s) trouvé(s)</p>
    </div>
    
    <div class="d-flex gap-2">
        <a href="post-create.php" class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i>
            Nouvel article
        </a>
        <button class="admin-btn admin-btn-outline" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i>
            Actualiser
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="admin-stats mb-4">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total</h3>
            <div class="stat-card-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--admin-primary);">
                <i class="fas fa-newspaper"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['total']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Publiés</h3>
            <div class="stat-card-icon" style="background: rgba(5, 150, 105, 0.1); color: var(--admin-secondary);">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['published']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Brouillons</h3>
            <div class="stat-card-icon" style="background: rgba(217, 119, 6, 0.1); color: var(--admin-warning);">
                <i class="fas fa-edit"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['draft']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Ce mois</h3>
            <div class="stat-card-icon" style="background: rgba(2, 132, 199, 0.1); color: var(--admin-info);">
                <i class="fas fa-calendar"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['this_month']; ?></div>
    </div>
</div>

<!-- Filters and Search -->
<div class="admin-card mb-4">
    <div class="admin-card-body">
        <form method="GET" class="d-flex gap-2 align-center">
            <!-- Search -->
            <div style="flex: 1; max-width: 300px;">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Rechercher dans les articles..." 
                    class="form-input"
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <!-- Status Filter -->
            <select name="status" class="form-input">
                <option value="">Tous les statuts</option>
                <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Publiés</option>
                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Brouillons</option>
            </select>
            
            <!-- Filter Button -->
            <button type="submit" class="admin-btn admin-btn-primary">
                <i class="fas fa-search"></i>
                Filtrer
            </button>
            
            <!-- Reset Button -->
            <?php if (!empty($search) || !empty($status_filter)): ?>
            <a href="posts.php" class="admin-btn admin-btn-outline">
                <i class="fas fa-times"></i>
                Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Posts Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex justify-between align-center">
            <h3 class="admin-card-title">Articles</h3>
            
            <!-- Bulk Actions -->
            <div class="d-flex gap-2 align-center" id="bulkActions" style="display: none !important;">
                <select id="bulkActionSelect" class="form-input">
                    <option value="">Actions groupées</option>
                    <option value="publish">Publier</option>
                    <option value="draft">Mettre en brouillon</option>
                    <option value="delete">Supprimer</option>
                </select>
                <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick="executeBulkAction()">
                    Appliquer
                </button>
            </div>
        </div>
    </div>
    
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($posts)): ?>
            <div style="padding: 3rem; text-align: center;">
                <i class="fas fa-newspaper" style="font-size: 3rem; color: var(--admin-text-muted); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--admin-text-muted); margin-bottom: 0.5rem;">Aucun article trouvé</h3>
                <p style="color: var(--admin-text-muted); margin-bottom: 1.5rem;">
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        Aucun article ne correspond à vos critères de recherche.
                    <?php else: ?>
                        Commencez par créer votre premier article.
                    <?php endif; ?>
                </p>
                <a href="post-create.php" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i>
                    Créer un article
                </a>
            </div>
        <?php else: ?>
            <form id="bulkForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="bulk_action" id="bulkActionInput">
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Titre</th>
                            <th style="width: 100px;">Statut</th>
                            <th style="width: 120px;">Vues</th>
                            <th style="width: 100px;">Likes</th>
                            <th style="width: 120px;">Commentaires</th>
                            <th style="width: 140px;">Date</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post_item): ?>
                        <tr>
                            <td>
                                <input 
                                    type="checkbox" 
                                    name="selected_posts[]" 
                                    value="<?php echo $post_item['id']; ?>"
                                    class="post-checkbox"
                                    onchange="updateBulkActions()"
                                >
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <?php if ($post_item['featured_image']): ?>
                                        <img 
                                            src="<?php echo UPLOAD_URL . $post_item['featured_image']; ?>" 
                                            alt="<?php echo htmlspecialchars($post_item['title']); ?>"
                                            style="width: 50px; height: 35px; object-fit: cover; border-radius: 4px;"
                                        >
                                    <?php else: ?>
                                        <div style="width: 50px; height: 35px; background: var(--admin-border-light); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <h5 style="margin: 0; font-size: 0.875rem; font-weight: 600;">
                                            <a href="post-edit.php?id=<?php echo $post_item['id']; ?>" style="color: var(--admin-text-primary); text-decoration: none;">
                                                <?php echo htmlspecialchars($post_item['title']); ?>
                                            </a>
                                        </h5>
                                        <p style="margin: 0; font-size: 0.75rem; color: var(--admin-text-muted);">
                                            <?php echo htmlspecialchars(substr($post_item['excerpt'], 0, 60) . (strlen($post_item['excerpt']) > 60 ? '...' : '')); ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $post_item['status']; ?>">
                                    <?php echo ucfirst($post_item['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-eye text-muted"></i>
                                    <span><?php echo number_format($post_item['view_count']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-heart text-muted"></i>
                                    <span><?php echo $like->getCountByPostId($post_item['id']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-comments text-muted"></i>
                                    <span><?php echo $comment->getCountByPostId($post_item['id']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.75rem;">
                                    <div><?php echo date('d/m/Y', strtotime($post_item['created_at'])); ?></div>
                                    <div class="text-muted"><?php echo date('H:i', strtotime($post_item['created_at'])); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <!-- View Post -->
                                    <a 
                                        href="../blog-post.php?slug=<?php echo $post_item['slug']; ?>" 
                                        target="_blank"
                                        class="admin-btn admin-btn-outline admin-btn-sm"
                                        title="Voir l'article"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <!-- Edit Post -->
                                    <a 
                                        href="post-edit.php?id=<?php echo $post_item['id']; ?>"
                                        class="admin-btn admin-btn-outline admin-btn-sm"
                                        title="Modifier"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <!-- Quick Actions Dropdown -->
                                    <div class="dropdown">
                                        <button class="admin-btn admin-btn-outline admin-btn-sm dropdown-toggle" title="Plus d'actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <?php if ($post_item['status'] === 'draft'): ?>
                                                <a href="?action=publish&id=<?php echo $post_item['id']; ?>&token=<?php echo generate_csrf_token(); ?>" class="dropdown-item">
                                                    <i class="fas fa-check"></i>
                                                    Publier
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=draft&id=<?php echo $post_item['id']; ?>&token=<?php echo generate_csrf_token(); ?>" class="dropdown-item">
                                                    <i class="fas fa-edit"></i>
                                                    Mettre en brouillon
                                                </a>
                                            <?php endif; ?>
                                            
                                            <div class="dropdown-divider"></div>
                                            
                                            <a 
                                                href="?action=delete&id=<?php echo $post_item['id']; ?>&token=<?php echo generate_csrf_token(); ?>" 
                                                class="dropdown-item text-danger"
                                                onclick="return confirmDelete('Êtes-vous sûr de vouloir supprimer cet article ?')"
                                            >
                                                <i class="fas fa-trash"></i>
                                                Supprimer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="d-flex justify-between align-center mt-4">
    <div class="text-muted">
        Affichage de <?php echo (($page - 1) * $per_page) + 1; ?> à <?php echo min($page * $per_page, $total_posts); ?> sur <?php echo $total_posts; ?> articles
    </div>
    
    <div class="d-flex gap-1">
        <?php
        $query_params = $_GET;
        
        // Previous page
        if ($page > 1):
            $query_params['page'] = $page - 1;
        ?>
            <a href="?<?php echo http_build_query($query_params); ?>" class="admin-btn admin-btn-outline admin-btn-sm">
                <i class="fas fa-chevron-left"></i>
                Précédent
            </a>
        <?php endif; ?>
        
        <?php
        // Page numbers
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        
        if ($start > 1):
        ?>
            <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => 1])); ?>" class="admin-btn admin-btn-outline admin-btn-sm">1</a>
            <?php if ($start > 2): ?>
                <span class="admin-btn admin-btn-outline admin-btn-sm" style="pointer-events: none;">...</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php $query_params['page'] = $i; ?>
            <a 
                href="?<?php echo http_build_query($query_params); ?>" 
                class="admin-btn admin-btn-<?php echo $i === $page ? 'primary' : 'outline'; ?> admin-btn-sm"
            >
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php
        if ($end < $total_pages):
            if ($end < $total_pages - 1):
        ?>
                <span class="admin-btn admin-btn-outline admin-btn-sm" style="pointer-events: none;">...</span>
            <?php endif; ?>
            <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $total_pages])); ?>" class="admin-btn admin-btn-outline admin-btn-sm"><?php echo $total_pages; ?></a>
        <?php endif; ?>
        
        <?php
        // Next page
        if ($page < $total_pages):
            $query_params['page'] = $page + 1;
        ?>
            <a href="?<?php echo http_build_query($query_params); ?>" class="admin-btn admin-btn-outline admin-btn-sm">
                Suivant
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.post-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.post-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkboxes.length > 0) {
        bulkActions.style.display = 'flex';
    } else {
        bulkActions.style.display = 'none';
        document.getElementById('selectAll').checked = false;
    }
}

// Execute bulk action
function executeBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    const checkboxes = document.querySelectorAll('.post-checkbox:checked');
    
    if (!action) {
        alert('Veuillez sélectionner une action.');
        return;
    }
    
    if (checkboxes.length === 0) {
        alert('Veuillez sélectionner au moins un article.');
        return;
    }
    
    let confirmMessage = '';
    switch (action) {
        case 'delete':
            confirmMessage = `Êtes-vous sûr de vouloir supprimer ${checkboxes.length} article(s) ?`;
            break;
        case 'publish':
            confirmMessage = `Êtes-vous sûr de vouloir publier ${checkboxes.length} article(s) ?`;
            break;
        case 'draft':
            confirmMessage = `Êtes-vous sûr de vouloir mettre ${checkboxes.length} article(s) en brouillon ?`;
            break;
    }
    
    if (confirm(confirmMessage)) {
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkForm').submit();
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    updateBulkActions();
});
</script>

<?php include 'templates/footer.php'; ?>