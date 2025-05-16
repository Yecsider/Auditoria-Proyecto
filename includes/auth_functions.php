<?php
require_once 'db_connection.php';

function isAdminOrManager() {
    return $_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente';
}

function isDigitador() {
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 8;
}

// includes/auth_functions.php

function loginUser($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND is_active = TRUE");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Cambiar password_verify por comparación directa
    if ($user && $user['password_hash'] === $password) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Actualizar último login
        $update_stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = :user_id");
        $update_stmt->bindParam(':user_id', $user['user_id']);
        $update_stmt->execute();
        
        return true;
    }
    
    return false;
}
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role_id'] == 1; // Asumiendo que el rol 1 es Admin
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<?php
// ... (funciones anteriores existentes)

/**
 * Redirige al usuario según su rol
 */
function redirectByRole() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    
    // Redirigir según el rol del usuario
    switch ($_SESSION['role_id']) {
        case 1: // Admin
            header("Location: modules/admin/dashboard.php");
            break;
        case 2: // RRHH
            header("Location: modules/hr/dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}

/**
 * Genera un token CSRF
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida un token CSRF
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


?>

