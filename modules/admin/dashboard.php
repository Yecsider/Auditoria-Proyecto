<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotAdmin();

$page_title = "Panel de Administración";
include '../../includes/header.php';
include '../../includes/navigation.php';

// Obtener estadísticas
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'active_users' => $conn->query("SELECT COUNT(*) FROM users WHERE is_active = TRUE")->fetchColumn(),
    'inactive_users' => $conn->query("SELECT COUNT(*) FROM users WHERE is_active = FALSE")->fetchColumn(),
    'today_logins' => $conn->query("SELECT COUNT(*) FROM logs WHERE action = 'login' AND created_at::date = CURRENT_DATE")->fetchColumn()
];

// Usuarios recientes
$recent_users = $conn->query("
    SELECT u.user_id, u.username, u.first_name, u.last_name, u.created_at, r.role_name 
    FROM users u
    JOIN roles r ON u.role_id = r.role_id
    ORDER BY u.created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Actividad reciente
$recent_activity = $conn->query("
    SELECT l.*, u.username 
    FROM logs l
    JOIN users u ON l.user_id = u.user_id
    ORDER BY l.created_at DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="content">
    <div class="dashboard-header">
        <h2>Panel de Administración</h2>
        <div class="current-time"><?php echo date('d/m/Y H:i'); ?></div>
    </div>

    <div class="quick-actions">
        <a href="create_user.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </a>
        <a href="manage_users.php" class="btn">
            <i class="fas fa-users-cog"></i> Gestionar Usuarios
        </a>
        <a href="manage_roles.php" class="btn">
            <i class="fas fa-user-tag"></i> Gestionar Roles
        </a>
        <a href="view_logs.php" class="btn">
            <i class="fas fa-clipboard-list"></i> Ver Registros
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">Usuarios Totales</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-value"><?php echo $stats['active_users']; ?></div>
            <div class="stat-label">Usuarios Activos</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-times"></i></div>
            <div class="stat-value"><?php echo $stats['inactive_users']; ?></div>
            <div class="stat-label">Usuarios Inactivos</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
            <div class="stat-value"><?php echo $stats['today_logins']; ?></div>
            <div class="stat-label">Ingresos Hoy</div>
        </div>
    </div>

    <div class="dashboard-sections">
        <section class="recent-section">
            <h3><i class="fas fa-users"></i> Usuarios Recientes</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="recent-section">
            <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
            <div class="activity-list">
                <?php foreach ($recent_activity as $log): ?>
                <div class="activity-item">
                    <div class="activity-header">
                        <span class="activity-user"><?php echo htmlspecialchars($log['username']); ?></span>
                        <span class="activity-time"><?php echo date('H:i', strtotime($log['created_at'])); ?></span>
                    </div>
                    <div class="activity-action">
                        <span class="action-type"><?php echo htmlspecialchars($log['action']); ?></span>
                        <?php if (!empty($log['details'])): ?>
                        <span class="action-details">- <?php echo htmlspecialchars($log['details']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>