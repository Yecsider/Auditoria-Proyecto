<?php
// logout.php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';
require_once 'includes/db_connection.php';

// Registrar el logout en logs si hay una sesión activa
if (isset($_SESSION['user_id'])) {
    $log_stmt = $conn->prepare("
        INSERT INTO logs (user_id, action, ip_address)
        VALUES (:user_id, 'logout', :ip_address)
    ");
    $log_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $log_stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR']);
    $log_stmt->execute();
}

// Destruir la sesión y redirigir
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>