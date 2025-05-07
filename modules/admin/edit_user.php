<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotAdmin();

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = (int)$_GET['id'];

// Obtener información del usuario
$stmt = $conn->prepare("
    SELECT user_id, username, email, first_name, last_name, role_id, is_active
    FROM users
    WHERE user_id = :user_id
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: manage_users.php");
    exit();
}

$page_title = "Editar Usuario: " . htmlspecialchars($user['username']);
include '../../includes/header.php';
include '../../includes/navigation.php';

// Obtener roles disponibles
$roles = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_data = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'role_id' => (int)$_POST['role_id'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Validaciones
    if (empty($user_data['first_name'])) {
        $errors['first_name'] = "El nombre es requerido";
    }
    
    if (empty($user_data['last_name'])) {
        $errors['last_name'] = "El apellido es requerido";
    }
    
    if (empty($user_data['email']) || !filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Ingrese un email válido";
    }
    
    // Verificar si el email ya existe (excepto para este usuario)
    if (empty($errors['email'])) {
        $stmt = $conn->prepare("
            SELECT user_id FROM users 
            WHERE email = :email AND user_id != :user_id
        ");
        $stmt->bindParam(':email', $user_data['email']);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $errors['email'] = "Este email ya está registrado por otro usuario";
        }
    }
    
    // Actualizar si no hay errores
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("
                UPDATE users SET
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                role_id = :role_id,
                is_active = :is_active,
                updated_at = CURRENT_TIMESTAMP
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':first_name', $user_data['first_name']);
            $stmt->bindParam(':last_name', $user_data['last_name']);
            $stmt->bindParam(':email', $user_data['email']);
            $stmt->bindParam(':role_id', $user_data['role_id']);
            $stmt->bindParam(':is_active', $user_data['is_active']);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            // Registrar la acción
            $log_stmt = $conn->prepare("
                INSERT INTO logs (user_id, action, details)
                VALUES (:user_id, 'user_edit', :details)
            ");
            $log_details = "Editado usuario ID: $user_id";
            $log_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $log_stmt->bindParam(':details', $log_details);
            $log_stmt->execute();
            
            $conn->commit();
            
            $success_message = "Usuario actualizado exitosamente!";
            
            // Actualizar datos del usuario en la variable
            $user = array_merge($user, $user_data);
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors['general'] = "Error al actualizar el usuario: " . $e->getMessage();
        }
    }
}

// Procesar cambio de contraseña si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $errors['password'] = "Las contraseñas no coinciden";
    } elseif (strlen($new_password) < 8) {
        $errors['password'] = "La contraseña debe tener al menos 8 caracteres";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = "La contraseña debe contener al menos una letra mayúscula";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = "La contraseña debe contener al menos una letra minúscula";
    } elseif (!preg_match('/\d/', $password)) {
        $errors['password'] = "La contraseña debe contener al menos un número";
    } elseif (!preg_match('/[\W_]/', $password)) {
        $errors['password'] = "La contraseña debe contener al menos un carácter especial";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Las contraseñas no coinciden";
        
    } else {
        try {
            // Guardar la contraseña en texto plano
            $stmt = $conn->prepare("
                UPDATE users SET
                password_hash = :password,
                updated_at = CURRENT_TIMESTAMP
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':password', $new_password); // Texto plano
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $success_message = $success_message ? $success_message . " y contraseña cambiada!" : "Contraseña cambiada exitosamente!";
        } catch (PDOException $e) {
            $errors['password'] = "Error al cambiar la contraseña: " . $e->getMessage();
        }
    }
}

?>

<main class="content">
    <h2>Editar Usuario: <?php echo htmlspecialchars($user['username']); ?></h2>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?php echo $errors['general']; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="form">
        <div class="form-row">
            <div class="form-group <?php echo isset($errors['first_name']) ? 'has-error' : ''; ?>">
                <label for="first_name">Nombres*</label>
                <input type="text" name="first_name" id="first_name" 
                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                <?php if (isset($errors['first_name'])): ?>
                    <span class="error-message"><?php echo $errors['first_name']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['last_name']) ? 'has-error' : ''; ?>">
                <label for="last_name">Apellidos*</label>
                <input type="text" name="last_name" id="last_name" 
                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                <?php if (isset($errors['last_name'])): ?>
                    <span class="error-message"><?php echo $errors['last_name']; ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
            <label for="email">Email*</label>
            <input type="email" name="email" id="email" 
                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <?php if (isset($errors['email'])): ?>
                <span class="error-message"><?php echo $errors['email']; ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="role_id">Rol*</label>
            <select name="role_id" id="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['role_id']; ?>"
                        <?php echo $user['role_id'] == $role['role_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" 
                       <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                Usuario activo
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="manage_users.php" class="btn">Cancelar</a>
        </div>
    </form>
    
    <div class="password-change-section">
        <h3>Cambiar Contraseña</h3>
        
        <?php if (isset($errors['password'])): ?>
            <div class="alert alert-error"><?php echo $errors['password']; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" name="new_password" id="new_password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Cambiar Contraseña</button>
            </div>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>