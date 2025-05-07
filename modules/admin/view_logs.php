<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotAdmin();

$page_title = "Registros del Sistema";
include '../../includes/header.php';
include '../../includes/navigation.php';

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filtros
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Consulta base
$query = "
    SELECT l.*, u.username 
    FROM logs l
    LEFT JOIN users u ON l.user_id = u.user_id
";

$where = [];
$params = [];

// Aplicar filtros
if (!empty($action_filter)) {
    $where[] = "l.action = :action";
    $params[':action'] = $action_filter;
}

if ($user_filter > 0) {
    $where[] = "l.user_id = :user_id";
    $params[':user_id'] = $user_filter;
}

if (!empty($date_from)) {
    $where[] = "l.created_at >= :date_from";
    $params[':date_from'] = $date_from . ' 00:00:00';
}

if (!empty($date_to)) {
    $where[] = "l.created_at <= :date_to";
    $params[':date_to'] = $date_to . ' 23:59:59';
}

// Construir consulta final
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";

// Obtener registros
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener total de registros para paginación
$count_query = "SELECT COUNT(*) FROM logs l";
if (!empty($where)) {
    $count_query .= " WHERE " . implode(" AND ", $where);
}
$total_logs = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    $total_logs->bindValue($key, $value);
}
$total_logs->execute();
$total_logs = $total_logs->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

// Obtener acciones únicas para filtro
$actions = $conn->query("SELECT DISTINCT action FROM logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

// Obtener usuarios para filtro
$users = $conn->query("SELECT user_id, username FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="content">
    <div class="toolbar">
        <h2>Registros del Sistema</h2>
    </div>

    <div class="filters">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <select name="action">
                    <option value="">Todas las acciones</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?php echo htmlspecialchars($action); ?>"
                            <?php echo $action_filter == $action ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($action); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <select name="user_id">
                    <option value="0">Todos los usuarios</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>"
                            <?php echo $user_filter == $user['user_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from">Desde:</label>
                <input type="date" name="date_from" id="date_from" 
                       value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            
            <div class="form-group">
                <label for="date_to">Hasta:</label>
                <input type="date" name="date_to" id="date_to" 
                       value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="view_logs.php" class="btn">
                <i class="fas fa-times"></i> Limpiar
            </a>
        </form>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha/Hora</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Detalles</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo $log['log_id']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($log['username'] ?? 'Sistema'); ?></td>
                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                    <td><?php echo htmlspecialchars($log['details'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" 
               class="page-link">
                <i class="fas fa-angle-double-left"></i>
            </a>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
               class="page-link">
                <i class="fas fa-angle-left"></i>
            </a>
        <?php endif; ?>
        
        <?php 
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        
        for ($i = $start; $i <= $end; $i++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
               class="page-link">
                <i class="fas fa-angle-right"></i>
            </a>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" 
               class="page-link">
                <i class="fas fa-angle-double-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<?php include '../../includes/footer.php'; ?>