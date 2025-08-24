<?php
define('ADMIN_ACCESS', true);
require_once '../includes/init.php';

// Check admin authentication
requireAdmin();

// Page title
$page_title = 'Gestion des messages';

// Handle bulk actions
if ($_POST && isset($_POST['bulk_action']) && isset($_POST['selected_messages'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Token CSRF invalide.'];
    } else {
        $action = $_POST['bulk_action'];
        $selected_messages = $_POST['selected_messages'];
        $success_count = 0;
        
        foreach ($selected_messages as $message_id) {
            switch ($action) {
                case 'read':
                    if ($message->updateStatus($message_id, 'read')) {
                        $success_count++;
                    }
                    break;
                case 'unread':
                    if ($message->updateStatus($message_id, 'unread')) {
                        $success_count++;
                    }
                    break;
                case 'archive':
                    if ($message->updateStatus($message_id, 'archived')) {
                        $success_count++;
                    }
                    break;
                case 'delete':
                    if ($message->delete($message_id)) {
                        $success_count++;
                    }
                    break;
            }
        }
        
        if ($success_count > 0) {
            $action_text = [
                'read' => 'marqué(s) comme lu(s)',
                'unread' => 'marqué(s) comme non lu(s)',
                'archive' => 'archivé(s)',
                'delete' => 'supprimé(s)'
            ];
            $_SESSION['admin_message'] = [
                'type' => 'success', 
                'text' => "$success_count message(s) {$action_text[$action]} avec succès."
            ];
        }
    }
    
    redirect($_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
}

// Handle single message actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Token CSRF invalide.'];
    } else {
        switch ($action) {
            case 'read':
                if ($message->updateStatus($message_id, 'read')) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Message marqué comme lu.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors de la mise à jour.'];
                }
                break;
            case 'archive':
                if ($message->updateStatus($message_id, 'archived')) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Message archivé avec succès.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'archivage.'];
                }
                break;
            case 'delete':
                if ($message->delete($message_id)) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Message supprimé avec succès.'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Erreur lors de la suppression.'];
                }
                break;
        }
    }
    
    redirect('messages.php');
}

// Get filters and search
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build where conditions
$where_conditions = ['1=1'];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get messages
$query = "SELECT * FROM messages $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM messages $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_messages = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_messages / $per_page);

// Get message statistics
$stats = [
    'total' => $message->getTotalCount(),
    'unread' => $message->getUnreadCount(),
    'read' => $message->getReadCount(),
    'archived' => $message->getArchivedCount(),
    'today' => $message->getTodayCount(),
    'this_week' => $message->getThisWeekCount()
];

include 'templates/header.php';
?>

<!-- Messages Header -->
<div class="d-flex justify-between align-center mb-4">
    <div>
        <h1 class="mb-1">Gestion des messages</h1>
        <p class="text-muted mb-0"><?php echo $total_messages; ?> message(s) trouvé(s)</p>
    </div>
    
    <div class="d-flex gap-2">
        <button class="admin-btn admin-btn-outline" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i>
            Actualiser
        </button>
        <a href="../contact.php" target="_blank" class="admin-btn admin-btn-primary">
            <i class="fas fa-external-link-alt"></i>
            Page contact
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="admin-stats mb-4">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Total</h3>
            <div class="stat-card-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--admin-primary);">
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['total']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Non lus</h3>
            <div class="stat-card-icon" style="background: rgba(220, 38, 38, 0.1); color: var(--admin-danger);">
                <i class="fas fa-envelope-open"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['unread']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Lus</h3>
            <div class="stat-card-icon" style="background: rgba(5, 150, 105, 0.1); color: var(--admin-secondary);">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['read']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Archivés</h3>
            <div class="stat-card-icon" style="background: rgba(217, 119, 6, 0.1); color: var(--admin-warning);">
                <i class="fas fa-archive"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['archived']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Cette semaine</h3>
            <div class="stat-card-icon" style="background: rgba(2, 132, 199, 0.1); color: var(--admin-info);">
                <i class="fas fa-calendar-week"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo $stats['this_week']; ?></div>
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
                    placeholder="Rechercher dans les messages..." 
                    class="form-input"
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <!-- Status Filter -->
            <select name="status" class="form-input">
                <option value="">Tous les statuts</option>
                <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Non lus</option>
                <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Lus</option>
                <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Archivés</option>
            </select>
            
            <!-- Filter Button -->
            <button type="submit" class="admin-btn admin-btn-primary">
                <i class="fas fa-search"></i>
                Filtrer
            </button>
            
            <!-- Reset Button -->
            <?php if (!empty($search) || !empty($status_filter)): ?>
            <a href="messages.php" class="admin-btn admin-btn-outline">
                <i class="fas fa-times"></i>
                Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Messages Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex justify-between align-center">
            <h3 class="admin-card-title">Messages</h3>
            
            <!-- Bulk Actions -->
            <div class="d-flex gap-2 align-center" id="bulkActions" style="display: none !important;">
                <select id="bulkActionSelect" class="form-input">
                    <option value="">Actions groupées</option>
                    <option value="read">Marquer comme lu</option>
                    <option value="unread">Marquer comme non lu</option>
                    <option value="archive">Archiver</option>
                    <option value="delete">Supprimer</option>
                </select>
                <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick="executeBulkAction()">
                    Appliquer
                </button>
            </div>
        </div>
    </div>
    
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($messages)): ?>
            <div style="padding: 3rem; text-align: center;">
                <i class="fas fa-envelope" style="font-size: 3rem; color: var(--admin-text-muted); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--admin-text-muted); margin-bottom: 0.5rem;">Aucun message trouvé</h3>
                <p style="color: var(--admin-text-muted); margin-bottom: 1.5rem;">
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        Aucun message ne correspond à vos critères de recherche.
                    <?php else: ?>
                        Aucun message n'a encore été reçu.
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
                                <th>Expéditeur</th>
                                <th>Sujet</th>
                                <th>Message</th>
                                <th style="width: 100px;">Statut</th>
                                <th style="width: 140px;">Date</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                            <tr class="<?php echo $msg['status'] === 'unread' ? 'unread-message' : ''; ?>">
                                <td>
                                    <input 
                                        type="checkbox" 
                                        name="selected_messages[]" 
                                        value="<?php echo $msg['id']; ?>"
                                        class="message-checkbox"
                                        onchange="updateBulkActions()"
                                    >
                                </td>
                                <td>
                                    <div class="d-flex align-center gap-1">
                                        <div style="width: 40px; height: 40px; background: var(--admin-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.875rem;">
                                            <?php echo strtoupper(substr($msg['name'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($msg['name']); ?>
                                                <?php if ($msg['status'] === 'unread'): ?>
                                                    <span style="width: 8px; height: 8px; background: var(--admin-danger); border-radius: 50%; display: inline-block; margin-left: 0.5rem;"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: var(--admin-primary); text-decoration: none;">
                                                    <?php echo htmlspecialchars($msg['email']); ?>
                                                </a>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                                IP: <?php echo htmlspecialchars($msg['ip_address']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 200px;">
                                        <p style="margin: 0; font-size: 0.875rem; font-weight: 600; color: var(--admin-text-primary);">
                                            <?php echo htmlspecialchars($msg['subject']); ?>
                                        </p>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        <p style="margin: 0; font-size: 0.875rem; line-height: 1.4; color: var(--admin-text-secondary);">
                                            <?php echo htmlspecialchars(substr($msg['message'], 0, 100) . (strlen($msg['message']) > 100 ? '...' : '')); ?>
                                        </p>
                                        <button 
                                            class="btn-link" 
                                            style="font-size: 0.75rem; color: var(--admin-primary); background: none; border: none; padding: 0; margin-top: 0.25rem; cursor: pointer;"
                                            onclick="showFullMessage(<?php echo $msg['id']; ?>)"
                                        >
                                            Lire plus
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $msg['status']; ?>">
                                        <?php 
                                        $status_labels = [
                                            'unread' => 'Non lu',
                                            'read' => 'Lu',
                                            'archived' => 'Archivé'
                                        ];
                                        echo $status_labels[$msg['status']] ?? ucfirst($msg['status']);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.75rem;">
                                        <div><?php echo date('d/m/Y', strtotime($msg['created_at'])); ?></div>
                                        <div class="text-muted"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                                        <div class="text-muted"><?php echo time_ago($msg['created_at']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($msg['status'] === 'unread'): ?>
                                            <!-- Mark as Read -->
                                            <a 
                                                href="?action=read&id=<?php echo $msg['id']; ?>&token=<?php echo generate_csrf_token(); ?>"
                                                class="admin-btn admin-btn-secondary admin-btn-sm"
                                                title="Marquer comme lu"
                                            >
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Reply -->
                                        <a 
                                            href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>"
                                            class="admin-btn admin-btn-primary admin-btn-sm"
                                            title="Répondre"
                                        >
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        
                                        <!-- More Actions Dropdown -->
                                        <div class="dropdown">
                                            <button class="admin-btn admin-btn-outline admin-btn-sm dropdown-toggle" title="Plus d'actions">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <button onclick="showFullMessage(<?php echo $msg['id']; ?>)" class="dropdown-item">
                                                    <i class="fas fa-eye"></i>
                                                    Voir le message
                                                </button>
                                                
                                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="dropdown-item">
                                                    <i class="fas fa-reply"></i>
                                                    Répondre par email
                                                </a>
                                                
                                                <div class="dropdown-divider"></div>
                                                
                                                <?php if ($msg['status'] !== 'archived'): ?>
                                                    <a href="?action=archive&id=<?php echo $msg['id']; ?>&token=<?php echo generate_csrf_token(); ?>" class="dropdown-item">
                                                        <i class="fas fa-archive"></i>
                                                        Archiver
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a 
                                                    href="?action=delete&id=<?php echo $msg['id']; ?>&token=<?php echo generate_csrf_token(); ?>" 
                                                    class="dropdown-item text-danger"
                                                    onclick="return confirmDelete('Êtes-vous sûr de vouloir supprimer ce message ?')"
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
        Affichage de <?php echo (($page - 1) * $per_page) + 1; ?> à <?php echo min($page * $per_page, $total_messages); ?> sur <?php echo $total_messages; ?> messages
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

<!-- Message Modal -->
<div id="messageModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div class="admin-card" style="max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <div class="admin-card-header">
            <div class="d-flex justify-between align-center">
                <h3 class="admin-card-title">Message complet</h3>
                <button onclick="closeMessageModal()" style="background: none; border: none; color: var(--admin-text-muted); cursor: pointer; font-size: 1.5rem;">
                    &times;
                </button>
            </div>
        </div>
        <div class="admin-card-body" id="messageModalContent">
            <!-- Message content will be loaded here -->
        </div>
    </div>
</div>

<!-- Additional CSS -->
<style>
.unread-message {
    background: rgba(79, 70, 229, 0.05);
    border-left: 3px solid var(--admin-primary);
}

.status-archived {
    background: rgba(217, 119, 6, 0.1);
    color: var(--admin-warning);
}

.btn-link {
    color: var(--admin-primary);
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}
</style>

<script>
// Messages data for modal
const messagesData = <?php echo json_encode($messages); ?>;

// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.message-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.message-checkbox:checked');
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
    const checkboxes = document.querySelectorAll('.message-checkbox:checked');
    
    if (!action) {
        alert('Veuillez sélectionner une action.');
        return;
    }
    
    if (checkboxes.length === 0) {
        alert('Veuillez sélectionner au moins un message.');
        return;
    }
    
    let confirmMessage = '';
    switch (action) {
        case 'read':
            confirmMessage = `Êtes-vous sûr de vouloir marquer ${checkboxes.length} message(s) comme lu(s) ?`;
            break;
        case 'unread':
            confirmMessage = `Êtes-vous sûr de vouloir marquer ${checkboxes.length} message(s) comme non lu(s) ?`;
            break;
        case 'archive':
            confirmMessage = `Êtes-vous sûr de vouloir archiver ${checkboxes.length} message(s) ?`;
            break;
        case 'delete':
            confirmMessage = `Êtes-vous sûr de vouloir supprimer ${checkboxes.length} message(s) ? Cette action est irréversible.`;
            break;
    }
    
    if (confirm(confirmMessage)) {
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkForm').submit();
    }
}

// Show full message modal
function showFullMessage(messageId) {
    const message = messagesData.find(m => m.id == messageId);
    if (!message) return;
    
    const modalContent = document.getElementById('messageModalContent');
    modalContent.innerHTML = `
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div style="width: 50px; height: 50px; background: var(--admin-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                    ${message.name.substring(0, 2).toUpperCase()}
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 1.125rem;">${escapeHtml(message.name)}</h4>
                    <p style="margin: 0; color: var(--admin-text-muted); font-size: 0.875rem;">
                        <a href="mailto:${escapeHtml(message.email)}" style="color: var(--admin-primary); text-decoration: none;">
                            ${escapeHtml(message.email)}
                        </a>
                    </p>
                    <p style="margin: 0; color: var(--admin-text-muted); font-size: 0.75rem;">
                        ${formatDate(message.created_at)} • IP: ${escapeHtml(message.ip_address)}
                    </p>
                </div>
            </div>
            
            <div style="background: var(--admin-border-light); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <h5 style="margin: 0 0 0.5rem 0; color: var(--admin-text-primary);">Sujet:</h5>
                <p style="margin: 0; font-weight: 600;">${escapeHtml(message.subject)}</p>
            </div>
            
            <div>
                <h5 style="margin: 0 0 1rem 0; color: var(--admin-text-primary);">Message:</h5>
                <div style="background: var(--admin-border-light); padding: 1.5rem; border-radius: 8px; line-height: 1.6;">
                    ${escapeHtml(message.message).replace(/\n/g, '<br>')}
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2 justify-between">
            <div class="d-flex gap-2">
                <a 
                    href="mailto:${escapeHtml(message.email)}?subject=Re: ${encodeURIComponent(message.subject)}" 
                    class="admin-btn admin-btn-primary"
                >
                    <i class="fas fa-reply"></i>
                    Répondre par email
                </a>
                
                ${message.status !== 'read' ? `
                    <a 
                        href="?action=read&id=${message.id}&token=${getCSRFToken()}" 
                        class="admin-btn admin-btn-secondary"
                    >
                        <i class="fas fa-check"></i>
                        Marquer comme lu
                    </a>
                ` : ''}
            </div>
            
            <div class="d-flex gap-2">
                ${message.status !== 'archived' ? `
                    <a 
                        href="?action=archive&id=${message.id}&token=${getCSRFToken()}" 
                        class="admin-btn admin-btn-warning"
                    >
                        <i class="fas fa-archive"></i>
                        Archiver
                    </a>
                ` : ''}
                
                <button onclick="closeMessageModal()" class="admin-btn admin-btn-outline">
                    Fermer
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('messageModal').style.display = 'flex';
}

// Close message modal
function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleString('fr-FR');
}

// Close modal on outside click
document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMessageModal();
    }
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    updateBulkActions();
});
</script>

<?php include 'templates/footer.php'; ?>