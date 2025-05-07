<?php
// modules/posts/view_posts.php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotLoggedIn();

$page_title = "Publicaciones";
include '../../includes/header.php';
include '../../includes/navigation.php';

// Obtener publicaciones
$stmt = $conn->prepare("
    SELECT p.*, u.username, u.first_name, u.last_name 
    FROM posts p 
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.created_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="content">
    <h2>Publicaciones Recientes</h2>
    
    <a href="create_post.php" class="btn">Nueva Publicación</a>
    
    <div class="posts-container">
        <?php foreach ($posts as $post): ?>
        <div class="post">
            <div class="post-header">
                <h3><?php echo htmlspecialchars($post['first_name'] . ' ' . htmlspecialchars($post['last_name'])); ?></h3>
                <small>@<?php echo htmlspecialchars($post['username']); ?> - <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></small>
            </div>
            <div class="post-content">
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            </div>
            <div class="post-actions">
                <a href="comments.php?post_id=<?php echo $post['post_id']; ?>" class="btn btn-small">Comentar</a>
                <?php if ($post['user_id'] == $_SESSION['user_id'] || isAdmin()): ?>
                    <a href="edit_post.php?post_id=<?php echo $post['post_id']; ?>" class="btn btn-small">Editar</a>
                    <a href="delete_post.php?post_id=<?php echo $post['post_id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('¿Eliminar esta publicación?')">Eliminar</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>