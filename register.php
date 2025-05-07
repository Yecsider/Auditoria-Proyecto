<?php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';
require_once 'includes/db_connection.php';

// Solo administradores pueden registrar nuevos usuarios
redirectIfNotAdmin();

$page_title = "Registrar Nuevo Usuario";
include 'includes/header.php';
include 'includes/navigation.php';

// Obtener roles disponibles
$stmt = $conn->prepare("SELECT role_id, role_name FROM roles ORDER BY role_name");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role_id = (int)$_POST['role_id'];
    
    // Validaciones
    if ($password !== $confirm_password) {
        $error_message = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8) {
        $error_message = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        try {
            // Verificar si el usuario ya existe
            $check_stmt = $conn->prepare("
                SELECT user_id FROM users WHERE username = :username OR email = :email
            ");
            $check_stmt->bindParam(':username', $username);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();
            
            if ($check_stmt->fetch()) {
                $error_message = "El nombre de usuario o email ya están en uso.";
            } else {
                // Hash de la contraseña
                $password_plain = $password;
                
                // Insertar nuevo usuario
                $insert_stmt = $conn->prepare("
                    INSERT INTO users 
                    (username, email, password_hash, first_name, last_name, role_id)
                    VALUES 
                    (:username, :email, :password_plain, :first_name, :last_name, :role_id)
                ");
                $insert_stmt->bindParam(':username', $username);
                $insert_stmt->bindParam(':email', $email);
                $insert_stmt->bindParam(':password_plain', $password_plain);
                $insert_stmt->bindParam(':first_name', $first_name);
                $insert_stmt->bindParam(':last_name', $last_name);
                $insert_stmt->bindParam(':role_id', $role_id);
                $insert_stmt->execute();
                
                // Registrar la acción en logs
                $log_stmt = $conn->prepare("
                    INSERT INTO logs (user_id, action, details)
                    VALUES (:user_id, 'user_register', :details)
                ");
                $log_stmt->bindParam(':user_id', $_SESSION['user_id']);
                $log_stmt->bindValue(':details', "Nuevo usuario: $username");
                $log_stmt->execute();
                
                $success_message = "Usuario registrado exitosamente!";
            }
        } catch (PDOException $e) {
            $error_message = "Error al registrar el usuario: " . $e->getMessage();
        }
    }
}
?>

<main class="content">
    <h2>Registrar Nuevo Usuario</h2>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="form">
        <div class="form-row">
            <div class="form-group">
                <label for="username">Nombre de Usuario*:</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email*:</label>
                <input type="email" name="email" id="email" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">Nombres*:</label>
                <input type="text" name="first_name" id="first_name" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Apellidos*:</label>
                <input type="text" name="last_name" id="last_name" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="password">Contraseña* (mínimo 8 caracteres):</label>
                <input type="password" name="password" id="password" required minlength="8">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña*:</label>
                <input type="password" name="confirm_password" id="confirm_password" required minlength="8">
            </div>
        </div>
        
        <div class="form-group">
            <label for="role_id">Rol*:</label>
            <select name="role_id" id="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['role_id']; ?>">
                        <?php echo htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn">Registrar Usuario</button>
            <a href="modules/admin/manage_users.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</main>

<?php include 'includes/footer.php'; ?>

