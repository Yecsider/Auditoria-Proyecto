<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';
redirectIfNotLoggedIn();

// Mostrar errores (solo para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Procesar publicación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (:user_id, :content)");
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':content' => $_POST['content']
        ]);
        header("Location: view_posts.php");
        exit;
    } catch (PDOException $e) {
        echo "Error al crear la publicación: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear publicación</title>
    <link rel="stylesheet" href="../../assets/css/inventory.css"> <!-- puedes reutilizar el CSS si deseas -->
</head>
<body>
    <h2>Nueva Publicación</h2>
    <form method="POST">
        <label for="content">Contenido:</label><br>
        <textarea name="content" id="content" rows="5" cols="60" placeholder="Escribe tu publicación..." required></textarea><br>
        <button type="submit">Publicar</button>
    </form>

    <a href="../../index.php" class="btn-back">← Volver al inicio</a>
</body>
</html>
