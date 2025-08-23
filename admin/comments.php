<?php
define('ADMIN_ACCESS', true);
require_once '../includes/init.php';

// Check admin authentication
requireAdmin();

// Page title
$page_title = 'Gestion des commentaires';

// Handle bulk actions
if ($_POST && isset($_POST['bulk_action']) && isset($_POST['selected_comments'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Token CSRF invalide.'];
    } else {
        $action = $_POST['bulk_action'];
        $selected_comments = $_POST['selected_comments'];
        $success_count = 0;
        
        foreach ($selected_comments as $comment_id) {
            switch ($action) {
                case 'approve':
                    if ($comment->updateStatus($comment_id, 'approved')) {
                        $success_count++;
                    }
                    break;
                case 'reject':
                    if ($comment->updateStatus($comment_id, 'rejected')) {
                        $success_count++;
                    }
                    break;
                case 'spam':
                    if ($comment->updateStatus($comment_id, 'spam')) {
                        $success_count++;
                    }
                    break;
                case 'delete':
                    if ($comment->delete($comment_id)) {
                        $success_count++;
                    }
                    break;
            }
        }
        
        if ($success_count > 0) {
            $action_text = [
                'approve' => 'approuvé(s)',
                'reject' => 'rejeté(s)', 
                'spam' => 'marqué(s) comme spam',
                'delete' => 'supprimé(s)'
            ];
            $_SESSION['admin_message'] = [
                'type' => 'success', 
                'text' => "$success_count commentaire(s) {$action_text[$action]} avec succès."
            ];
        }
    }
    
    redirect($_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
}

// Handle single comment actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $comment_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Token CSRF invalide.'];
    } else {
        switch ($action) {
            case 'approve':
                if ($comment->updateStatus($comment_id, 'approved')) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Commentaire approuvé avec succès.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'approbation.'];
                }
                break;
            case 'reject':
                if ($comment->updateStatus($comment_id, 'rejected')) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Commentaire rejeté avec succès.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors du rejet.'];
                }
                break;
            case 'spam':
                if ($comment->updateStatus($comment_id, 'spam')) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Commentaire marqué comme spam.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors du marquage spam.'];
                }
                break;
            case 'delete':
                if ($comment->delete($comment_id)) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Commentaire supprimé avec succès.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors de la suppression.'];
                }
                break;
        }
    }
    
    redirect('comments.php');
}

// Get filters and search
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$post_filter = $_GET['post'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build where conditions
$where_conditions = ['1=1'];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(c.author_name LIKE ? OR c.author_email LIKE ? OR c.content LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($post_filter)) {
    $where_conditions[] = "c.post_id = ?";
    $params[] = $post_filter;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get comments with post information
$query = "SELECT c.*, p.title as post_title, p.slug as post_slug 
          FROM comments c 
          LEFT JOIN posts p ON c.post_id = p.id 
          $where_clause 
          ORDER BY c.created_at DESC 
          LIMIT $per_page OFFSET $offset";

$stmt = $db->prepare($query);
$stmt->execute($params);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM comments c LEFT JOIN posts p ON c.post_id = p.id $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_comments = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_comments / $per_page);

// Get comment statistics
$stats = [
    'total' => $comment->getTotalCount(),
    'approved' => $comment->getApprovedCount(),
    'pending' => $comment->getPendingCount(),
    'rejected' => $comment->getRejectedCount(),
    'spam' => $comment->getSpamCount(),
    'this_month' => $comment->getThisMonthCount()
];

// Get recent posts for filter dropdown
$recent_posts = $post->getRecent(20);

include 'templates/header.php';
?>

<!-- Comments Header -->
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h1 class="mb-1">Gestion des commentaires</h1>
        <p class="text-muted mb-0"><?php echo $total_comments; ?> commentaire(s) trouvé(s)</p>
    </div>
    
    <div class="d-flex gap-2">
        <button class="admin-btn admin-btn-outline" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i>
            Actualiser
        </button>
        <a href="../blog.php" target="_blank" class="admin-btn admin-btn-primary">
            <i class="fas fa-external-link-alt"></i>
            Voir le blog
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="admin-stats mb-4">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total</h3>
            <div class="stat-card-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--admin-primary);">
                <i class="fas fa-comments"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['total']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Approuvés</h3>
            <div class="stat-card-icon" style="background: rgba(5, 150, 105, 0.1); color: var(--admin-secondary);">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['approved']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">En attente</h3>
            <div class="stat-card-icon" style="background: rgba(217, 119, 6, 0.1); color: var(--admin-warning);">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['pending']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Spam</h3>
            <div class="stat-card-icon" style="background: rgba(220, 38, 38, 0.1); color: var(--admin-danger);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['spam']; ?></div>
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
                    placeholder="Rechercher dans les commentaires..." 
                    class="form-input"
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <!-- Status Filter -->
            <select name="status" class="form-input">
                <option value="">Tous les statuts</option>
                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approuvés</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejetés</option>
                <option value="spam" <?php echo $status_filter === 'spam' ? 'selected' : ''; ?>>Spam</option>
            </select>
            
            <!-- Post Filter -->
            <select name="post" class="form-input">
                <option value="">Tous les articles</option>
                <?php foreach ($recent_posts as $recent_post): ?>
                    <option value="<?php echo $recent_post['id']; ?>" <?php echo $post_filter == $recent_post['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(substr($recent_post['title'], 0, 50)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Filter Button -->
            <button type="submit" class="admin-btn admin-btn-primary">
                <i class="fas fa-search"></i>
                Filtrer
            </button>
            
            <!-- Reset Button -->
            <?php if (!empty($search) || !empty($status_filter) || !empty($post_filter)): ?>
            <a href="comments.php" class="admin-btn admin-btn-outline">
                <i class="fas fa-times"></i>
                Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Comments Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex justify-between align-center">
            <h3 class="admin-card-title">Commentaires</h3>
            
            <!-- Bulk Actions -->
            <div class="d-flex gap-2 align-center" id="bulkActions" style="display: none !important;">
                <select id="bulkActionSelect" class="form-input">
                    <option value="">Actions groupées</option>
                    <option value="approve">Approuver</option>
                    <option value="reject">Rejeter</option>
                    <option value="spam">Marquer comme spam</option>
                    <option value="delete">Supprimer</option>
                </select>
                <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick="executeBulkAction()">
                    Appliquer
                </button>
            </div>
        </div>
    </div>
    
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($comments)): ?>
            <div style="padding: 3rem; text-align: center;">
                <i class="fas fa-comments" style="font-size: 3rem; color: var(--admin-text-muted); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--admin-text-muted); margin-bottom: 0.5rem;">Aucun commentaire trouvé</h3>
                <p style="color: var(--admin-text-muted); margin-bottom: 1.5rem;">
                    <?php if (!empty($search) || !empty($status_filter) || !empty($post_filter)): ?>
                        Aucun commentaire ne correspond à vos critères de recherche.
                    <?php else: ?>
                        Aucun commentaire n'a encore été posté.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <form id="bulkForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="bulk_action" id="bulkActionInput">
                
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Auteur</th>
                                <th>Commentaire</th>
                                <th>Article</th>
                                <th style="width: 100px;">Statut</th>
                                <th style="width: 140px;">Date</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment_item): ?>
                            <tr>
                                <td>
                                    <input 
                                        type="checkbox" 
                                        name="selected_comments[]" 
                                        value="<?php echo $comment_item['id']; ?>"
                                        class="comment-checkbox"
                                        onchange="updateBulkActions()"
                                    >
                                </td>
                                <td>
                                    <div class="d-flex align-center gap-1">
                                        <img 
                                            src="<?php echo $comment->getAvatarUrl($comment_item['author_avatar'], $comment_item['author_email'], 40); ?>" 
                                            alt="<?php echo htmlspecialchars($comment_item['author_name']); ?>"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                                        >
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($comment_item['author_name']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                                <?php echo htmlspecialchars($comment_item['author_email']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                                IP: <?php echo htmlspecialchars($comment_item['ip_address']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        <p style="margin: 0; font-size: 0.875rem; line-height: 1.4;">
                                            <?php echo htmlspecialchars(substr($comment_item['content'], 0, 150) . (strlen($comment_item['content']) > 150 ? '...' : '')); ?>
                                        </p>
                                        <?php if ($comment_item['parent_id']): ?>
                                        <div style="margin-top: 0.5rem; padding: 0.5rem; background: var(--admin-border-light); border-radius: 4px; font-size: 0.75rem;">
                                            <i class="fas fa-reply" style="margin-right: 0.25rem;"></i>
                                            Réponse à un commentaire
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 200px;">
                                        <?php if ($comment_item['post_title']): ?>
                                            <a 
                                                href="../blog-post.php?slug=<?php echo $comment_item['post_slug']; ?>" 
                                                target="_blank" 
                                                style="color: var(--admin-primary); text-decoration: none; font-size: 0.875rem;"
                                                title="<?php echo htmlspecialchars($comment_item['post_title']); ?>"
                                            >
                                                <?php echo htmlspecialchars(substr($comment_item['post_title'], 0, 50) . (strlen($comment_item['post_title']) > 50 ? '...' : '')); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Article supprimé</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $comment_item['status']; ?>">
                                        <?php 
                                        $status_labels = [
                                            'approved' => 'Approuvé',
                                            'pending' => 'En attente',
                                            'rejected' => 'Rejeté',
                                            'spam' => 'Spam'
                                        ];
                                        echo $status_labels[$comment_item['status']] ?? ucfirst($comment_item['status']);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.75rem;">
                                        <div><?php echo date('d/m/Y', strtotime($comment_item['created_at'])); ?></div>
                                        <div class="text-muted"><?php echo date('H:i', strtotime($comment_item['created_at'])); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($comment_item['status'] === 'pending'): ?>
                                            <!-- Approve -->
                                            <a 
                                                href="?action=approve&id=<?php echo $comment_item['id']; ?>&token=<?php echo generate_csrf_token(); ?>"
                                                class="admin-btn admin-btn-secondary admin-btn-sm"
                                                title="Approuver"
                                            >
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($comment_item['status'] !== 'rejected'): ?>
                                            <!-- Reject -->
                                            <a 
                                                href="?action=reject&id=<?php echo $comment_item['id']; ?>&token=<?php echo generate_csrf_token(); ?>"
                                                class="admin-btn admin-btn-warning admin-btn-sm"
                                                title="Rejeter"
                                            >
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($comment_item['status'] !== 'spam'): ?>
                                            <!-- Mark as Spam -->
                                            <a 
                                                href="?action=spam&id=<?php echo $comment_item['id']; ?>&token=<?php echo generate_csrf_token(); ?>"
                                                class="admin-btn admin-btn-danger admin-btn-sm"
                                                title="Marquer comme spam"
                                            >
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- More Actions Dropdown -->
                                        <div class="dropdown">
                                            <button class="admin-btn admin-btn-outline admin-btn-sm dropdown-toggle" title="Plus d'actions">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <?php if ($comment_item['post_title']): ?>
                                                    <a href="../blog-post.php?slug=<?php echo $comment_item['post_slug']; ?>#comments" target="_blank" class="dropdown-item">
                                                        <i class="fas fa-external-link-alt"></i>
                                                        Voir sur le site
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                <?php endif; ?>
                                                
                                                <?php if ($comment_item['status'] !== 'approved'): ?>
                                                    <a href="?action=approve&id=<?php echo $comment_item['id']; ?>&token=<?php echo generate_csrf_token(); ?>" class="dropdown-item">
                                                        <i class="fas fa-check"></i>
                                                        Approuver
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a 
                                                    href="?action=delete&id=<?php echo $comment_item['id']; ?>&token=<?php echo generate_csrf_token(); ?>" 
                                                    class="dropdown-item text-danger"
                                                    onclick="return confirmDelete('Êtes-vous sûr de vouloir supprimer ce commentaire ?')"
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
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="d-flex justify-between align-center mt-4">
    <div class="text-muted">
        Affichage de <?php echo (($page - 1) * $per_page) + 1; ?> à <?php echo min($page * $per_page, $total_comments); ?> sur <?php echo $total_comments; ?> commentaires
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
        
        for ($i = $start; $i <= $end; $i++):
            $query_params['page'] = $i;
        ?>
            <a 
                href="?<?php echo http_build_query($query_params); ?>" 
                class="admin-btn admin-btn-<?php echo $i === $page ? 'primary' : 'outline'; ?> admin-btn-sm"
            >
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
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

<!-- Additional Status Badges CSS -->
<style>
.status-rejected {
    background: rgba(220, 38, 38, 0.1);
    color: var(--admin-danger);
}
</style>

<script>
// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.comment-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.comment-checkbox:checked');
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
    const checkboxes = document.querySelectorAll('.comment-checkbox:checked');
    
    if (!action) {
        alert('Veuillez sélectionner une action.');
        return;
    }
    
    if (checkboxes.length === 0) {
        alert('Veuillez sélectionner au moins un commentaire.');
        return;
    }
    
    let confirmMessage = '';
    switch (action) {
        case 'approve':
            confirmMessage = `Êtes-vous sûr de vouloir approuver ${checkboxes.length} commentaire(s) ?`;
            break;
        case 'reject':
            confirmMessage = `Êtes-vous sûr de vouloir rejeter ${checkboxes.length} commentaire(s) ?`;
            break;
        case 'spam':
            confirmMessage = `Êtes-vous sûr de vouloir marquer ${checkboxes.length} commentaire(s) comme spam ?`;
            break;
        case 'delete':
            confirmMessage = `Êtes-vous sûr de vouloir supprimer ${checkboxes.length} commentaire(s) ? Cette action est irréversible.`;
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