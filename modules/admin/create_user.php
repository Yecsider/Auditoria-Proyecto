<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotAdmin();

$page_title = "Crear Nuevo Usuario";
include '../../includes/header.php';
include '../../includes/navigation.php';

// Obtener roles disponibles
$roles = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$form_data = [
    'username' => '',
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'role_id' => 1 // Valor por defecto
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'role_id' => (int)$_POST['role_id']
    ];
    
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if (empty($form_data['username'])) {
        $errors['username'] = "El nombre de usuario es requerido";
    } elseif (strlen($form_data['username']) < 4) {
        $errors['username'] = "El nombre de usuario debe tener al menos 4 caracteres";
    }
    
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Ingrese un email válido";
    }
    
    if (empty($password)) {
        $errors['password'] = "La contraseña es requerida";
    } elseif (strlen($password) < 8) {
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
    }
    
    // Verificar si el usuario o email ya existen
    if (empty($errors)) {
        $stmt = $conn->prepare("
            SELECT user_id FROM users 
            WHERE username = :username OR email = :email
        ");
        $stmt->bindParam(':username', $form_data['username']);
        $stmt->bindParam(':email', $form_data['email']);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $errors['general'] = "El nombre de usuario o email ya están registrados";
        }
    }
    
    // Crear usuario si no hay errores
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Eliminar el hash y guardar la contraseña en texto plano
            $password_plain = $_POST['password'];
            
            $stmt = $conn->prepare("
                INSERT INTO users 
                (username, email, password_hash, first_name, last_name, role_id)
                VALUES 
                (:username, :email, :password, :first_name, :last_name, :role_id)
            ");
            $stmt->bindParam(':username', $form_data['username']);
            $stmt->bindParam(':email', $form_data['email']);
            $stmt->bindParam(':password', $password_plain); // Texto plano
            $stmt->bindParam(':first_name', $form_data['first_name']);
            $stmt->bindParam(':last_name', $form_data['last_name']);
            $stmt->bindParam(':role_id', $form_data['role_id']);
            $stmt->execute();
            
            // Registrar la acción
            $log_stmt = $conn->prepare("
                INSERT INTO logs (user_id, action, details)
                VALUES (:user_id, 'user_create', :details)
            ");
            $log_details = "Nuevo usuario: " . $form_data['username'];
            $log_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $log_stmt->bindParam(':details', $log_details);
            $log_stmt->execute();
            
            $conn->commit();
            
            $_SESSION['success_message'] = "Usuario creado exitosamente!";
            header("Location: manage_users.php");
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors['general'] = "Error al crear el usuario: " . $e->getMessage();
        }
    }
}
?>

<main class="content">
    <h2>Crear Nuevo Usuario</h2>
    
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?php echo $errors['general']; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="form">
        <div class="form-row">
            <div class="form-group <?php echo isset($errors['username']) ? 'has-error' : ''; ?>">
                <label for="username">Nombre de Usuario*</label>
                <input type="text" name="username" id="username" 
                       value="<?php echo htmlspecialchars($form_data['username']); ?>" required>
                <?php if (isset($errors['username'])): ?>
                    <span class="error-message"><?php echo $errors['username']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                <label for="email">Email*</label>
                <input type="email" name="email" id="email" 
                       value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">Nombres*</label>
                <input type="text" name="first_name" id="first_name" 
                       value="<?php echo htmlspecialchars($form_data['first_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Apellidos*</label>
                <input type="text" name="last_name" id="last_name" 
                       value="<?php echo htmlspecialchars($form_data['last_name']); ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
                <label for="password">Contraseña*</label>
                <input type="password" name="password" id="password" required>
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?php echo isset($errors['confirm_password']) ? 'has-error' : ''; ?>">
                <label for="confirm_password">Confirmar Contraseña*</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error-message"><?php echo $errors['confirm_password']; ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="role_id">Rol*</label>
            <select name="role_id" id="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['role_id']; ?>" 
                        <?php echo $role['role_id'] == $form_data['role_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
            <a href="manage_users.php" class="btn">Cancelar</a>
        </div>
    </form>
</main>

<?php include '../../includes/footer.php'; ?>