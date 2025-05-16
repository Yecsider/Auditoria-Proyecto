<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';
redirectIfNotLoggedIn();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['post_id'])) {
    echo "ID de publicación no proporcionado.";
    exit;
}

$post_id = $_GET['post_id'];

// Procesar nuevo comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
    $stmt->execute([
        ':post_id' => $post_id,
        ':user_id' => $_SESSION['user_id'],
        ':content' => $_POST['content']
    ]);
}

// Obtener publicación
$stmt = $conn->prepare("SELECT p.content, u.first_name, u.last_name, p.created_at
                        FROM posts p
                        JOIN users u ON p.user_id = u.user_id
                        WHERE p.post_id = :post_id");
$stmt->execute([':post_id' => $post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener comentarios
$stmt = $conn->prepare("SELECT c.content, u.first_name, u.last_name, c.created_at
                        FROM comments c
                        JOIN users u ON c.user_id = u.user_id
                        WHERE c.post_id = :post_id
                        ORDER BY c.created_at ASC");
$stmt->execute([':post_id' => $post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comentarios</title>
    <link rel="stylesheet" href="../../assets/css/inventory.css">
</head>
<body>
    <h2>Publicación</h2>
    <p><strong><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>:</strong> <?= htmlspecialchars($post['content']) ?></p>
    <p><em><?= $post['created_at'] ?></em></p>

    <hr>

    <h3>Comentarios</h3>
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
            <div style="margin-bottom: 15px;">
                <strong><?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?></strong>
                <p><?= htmlspecialchars($comment['content']) ?></p>
                <small><em><?= $comment['created_at'] ?></em></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aún no hay comentarios.</p>
    <?php endif; ?>

    <hr>

    <h3>Agregar comentario</h3>
    <form method="POST">
        <textarea name="content" rows="4" cols="60" placeholder="Escribe tu comentario..." required></textarea><br>
        <button type="submit">Comentar</button>
    </form>

    <a href="../../index.php" class="btn-back">← Volver al inicio</a>
</body>
</html>
