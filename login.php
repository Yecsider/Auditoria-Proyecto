<?php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';
require_once 'includes/db_connection.php';

// Si el usuario ya está logueado, redirigir según su rol
if (isLoggedIn()) {
    redirectByRole();
}

$error_message = '';

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (loginUser($username, $password)) {
        // Registrar el login en logs
        $log_stmt = $conn->prepare("
            INSERT INTO logs (user_id, action, ip_address)
            VALUES (:user_id, 'login', :ip_address)
        ");
        $log_stmt->bindParam(':user_id', $_SESSION['user_id']);
        $log_stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR']);
        $log_stmt->execute();
        
        redirectByRole();
    // Después del if (loginUser($username, $password)) {...} añade:
} else {
    // Depuración
    error_log("Intento de login fallido para usuario: $username");
    
    // Verificar si el usuario existe
    $check_user = $conn->prepare("SELECT user_id FROM users WHERE username = :username");
    $check_user->bindParam(':username', $username);
    $check_user->execute();
    
    if (!$check_user->fetch()) {
        error_log("El usuario no existe en la base de datos");
        $error_message = "El usuario no existe.";
    } else {
        error_log("El usuario existe pero la contraseña no coincide");
        $error_message = "Contraseña incorrecta.";
    }
}
}

$page_title = "Iniciar Sesión";
include 'includes/header.php';
?>

<main class="content auth-content">
    <div class="auth-container">
        <h2>Iniciar Sesión</h2>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-block">Ingresar</button>
            </div>
        </form>
        
        <div class="auth-links">
            <p>¿Problemas para ingresar? Contacta al administrador.</p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>