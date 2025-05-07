<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotAdmin();

$page_title = "Gestionar Usuarios";
include '../../includes/header.php';
include '../../includes/navigation.php';

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? (int)$_GET['role'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Consulta base
$query = "
    SELECT u.user_id, u.username, u.email, u.first_name, u.last_name, 
           u.is_active, u.created_at, r.role_name
    FROM users u
    JOIN roles r ON u.role_id = r.role_id
";

$where = [];
$params = [];

// Aplicar filtros
if (!empty($search)) {
    $where[] = "(u.username LIKE :search OR u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($role_filter > 0) {
    $where[] = "u.role_id = :role_id";
    $params[':role_id'] = $role_filter;
}

if ($status_filter !== 'all') {
    $where[] = "u.is_active = :is_active";
    $params[':is_active'] = $status_filter === 'active' ? 1 : 0;
}

// Construir consulta final
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";

// Obtener usuarios
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener total de usuarios para paginación
$count_query = "SELECT COUNT(*) FROM users u";
if (!empty($where)) {
    $count_query .= " WHERE " . implode(" AND ", $where);
}
$total_users = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    $total_users->bindValue($key, $value);
}
$total_users->execute();
$total_users = $total_users->fetchColumn();
$total_pages = ceil($total_users / $per_page);

// Obtener roles para filtro
$roles = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="content">
    <div class="toolbar">
        <h2>Gestionar Usuarios</h2>
        <div class="actions">
            <a href="create_user.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </a>
        </div>
    </div>

    <div class="filters">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <input type="text" name="search" placeholder="Buscar..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="form-group">
                <select name="role">
                    <option value="0">Todos los roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['role_id']; ?>"
                            <?php echo $role_filter == $role['role_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <select name="status">
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Todos los estados</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Activos</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
                </select>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="manage_users.php" class="btn">
                <i class="fas fa-times"></i> Limpiar
            </a>
        </form>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    <td class="actions-cell">
                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" 
                           class="btn btn-small" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        <?php if ($user['is_active']): ?>
                            <a href="deactivate_user.php?id=<?php echo $user['user_id']; ?>" 
                               class="btn btn-small btn-warning" title="Desactivar">
                                <i class="fas fa-user-times"></i>
                            </a>
                        <?php else: ?>
                            <a href="activate_user.php?id=<?php echo $user['user_id']; ?>" 
                               class="btn btn-small btn-success" title="Activar">
                                <i class="fas fa-user-check"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" 
                           class="btn btn-small btn-danger" 
                           title="Eliminar"
                           onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
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