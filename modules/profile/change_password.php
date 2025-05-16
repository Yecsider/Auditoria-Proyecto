<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';
redirectIfNotLoggedIn();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Las nuevas contraseñas no coinciden.";
    } else {
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $current_password === $user['password_hash']) {
            $update = $conn->prepare("UPDATE users SET password_hash = :new_password WHERE user_id = :user_id");
            $update->execute([
                ':new_password' => $new_password,
                ':user_id' => $_SESSION['user_id']
            ]);
            $message = "Contraseña actualizada correctamente.";
        } else {
            $message = "La contraseña actual es incorrecta.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar contraseña</title>
    <link rel="stylesheet" href="../../assets/css/inventory.css">
</head>
<body>
    <h2>Cambiar contraseña</h2>

    <?php if ($message): ?>
        <p><strong><?= $message ?></strong></p>
    <?php endif; ?>

    <form method="POST">
        <label>Contraseña actual:
            <input type="password" name="current_password" required>
        </label><br>
        <label>Nueva contraseña:
            <input type="password" name="new_password" required>
        </label><br>
        <label>Confirmar nueva contraseña:
            <input type="password" name="confirm_password" required>
        </label><br>
        <button type="submit">Actualizar</button>
    </form>

    <a href="../../index.php" class="btn-back">← Volver al inicio</a>
</body>
</html>
