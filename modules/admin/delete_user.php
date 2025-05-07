<?php
// modules/admin/delete_user.php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de usuario no válido";
    header("Location: manage_users.php");
    exit();
}

$user_id = (int)$_GET['id'];

// Verificar que no sea el mismo usuario
if ($user_id === $_SESSION['user_id']) {
    $_SESSION['error_message'] = "No puedes eliminarte a ti mismo";
    header("Location: manage_users.php");
    exit();
}

try {
    $conn->beginTransaction();
    
    // 1. Eliminar dependencias primero (ajusta según tu esquema de BD)
    $conn->exec("DELETE FROM messages WHERE sender_id = $user_id OR receiver_id = $user_id");
    $conn->exec("DELETE FROM comments WHERE user_id = $user_id");
    $conn->exec("DELETE FROM posts WHERE user_id = $user_id");
    
    // 2. Eliminar al usuario
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    // Registrar la acción
    $log_stmt = $conn->prepare("
        INSERT INTO logs (user_id, action, details)
        VALUES (:admin_id, 'user_delete', :details)
    ");
    $details = "Usuario eliminado ID: $user_id";
    $log_stmt->bindParam(':admin_id', $_SESSION['user_id']);
    $log_stmt->bindParam(':details', $details);
    $log_stmt->execute();
    
    $conn->commit();
    
    $_SESSION['success_message'] = "Usuario eliminado correctamente";
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Error al eliminar usuario: " . $e->getMessage();
}

header("Location: manage_users.php");
exit();
?>