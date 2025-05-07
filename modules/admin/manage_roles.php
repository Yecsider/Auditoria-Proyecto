<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotAdmin();

$page_title = "Gestionar Roles";
include '../../includes/header.php';
include '../../includes/navigation.php';

// Obtener todos los roles
$roles = $conn->query("SELECT * FROM roles ORDER BY role_name")->fetchAll(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';

// Procesar creación de nuevo rol
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_role'])) {
    $role_name = trim($_POST['role_name']);
    $description = trim($_POST['description']);
    
    if (empty($role_name)) {
        $error_message = "El nombre del rol es requerido";
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO roles (role_name, description)
                VALUES (:role_name, :description)
            ");
            $stmt->bindParam(':role_name', $role_name);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
            
            $success_message = "Rol creado exitosamente!";
            header("Refresh:2");
        } catch (PDOException $e) {
            if ($e->getCode() == 23505) { // Violación de unique constraint
                $error_message = "El nombre del rol ya existe";
            } else {
                $error_message = "Error al crear el rol: " . $e->getMessage();
            }
        }
    }
}

// Procesar eliminación de rol
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_role'])) {
    $role_id = (int)$_POST['role_id'];
    
    try {
        // Verificar si hay usuarios con este rol
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role_id = :role_id");
        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();
        $user_count = $stmt->fetchColumn();
        
        if ($user_count > 0) {
            $error_message = "No se puede eliminar el rol porque hay usuarios asignados a él";
        } else {
            $stmt = $conn->prepare("DELETE FROM roles WHERE role_id = :role_id");
            $stmt->bindParam(':role_id', $role_id);
            $stmt->execute();
            
            $success_message = "Rol eliminado exitosamente!";
            header("Refresh:2");
        }
    } catch (PDOException $e) {
        $error_message = "Error al eliminar el rol: " . $e->getMessage();
    }
}
?>

<main class="content">
    <div class="toolbar">
        <h2>Gestionar Roles</h2>
    </div>

    <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <div class="admin-sections">
        <section class="create-role">
            <h3>Crear Nuevo Rol</h3>
            
            <form method="POST" class="form">
                <div class="form-group">
                    <label for="role_name">Nombre del Rol*</label>
                    <input type="text" name="role_name" id="role_name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea name="description" id="description" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="create_role" class="btn btn-primary">Crear Rol</button>
                </div>
            </form>
        </section>

        <section class="roles-list">
            <h3>Roles Existentes</h3>
            
            <?php if (empty($roles)): ?>
                <p>No hay roles registrados.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Usuarios</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?php echo $role['role_id']; ?></td>
                            <td><?php echo htmlspecialchars($role['role_name']); ?></td>
                            <td><?php echo htmlspecialchars($role['description']); ?></td>
                            <td>
                                <?php 
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role_id = :role_id");
                                $stmt->bindParam(':role_id', $role['role_id']);
                                $stmt->execute();
                                echo $stmt->fetchColumn();
                                ?>
                            </td>
                            <td class="actions-cell">
                                <a href="edit_role.php?id=<?php echo $role['role_id']; ?>" 
                                   class="btn btn-small" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="role_id" value="<?php echo $role['role_id']; ?>">
                                    <button type="submit" name="delete_role" class="btn btn-small btn-danger" 
                                            title="Eliminar" 
                                            onclick="return confirm('¿Estás seguro de eliminar este rol?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>