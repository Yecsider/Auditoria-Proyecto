<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotLoggedIn();

// Obtener información del perfil
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT u.*, r.role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.role_id
    WHERE u.user_id = :user_id
");
$stmt->bindParam(':user_id', $profile_id);
$stmt->execute();
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    header("Location: index.php");
    exit();
}

$page_title = "Perfil de " . htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']);
include '../../includes/header.php';
include '../../includes/navigation.php';
?>

<main class="content">
    <div class="profile-header">
        <h2><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h2>
        <p class="username">@<?php echo htmlspecialchars($profile['username']); ?></p>
        <p class="role"><?php echo htmlspecialchars($profile['role_name']); ?></p>
        
        <?php if ($profile['user_id'] == $_SESSION['user_id']): ?>
            <a href="edit_profile.php" class="btn">Editar Perfil</a>
            <a href="change_password.php" class="btn">Cambiar Contraseña</a>
        <?php else: ?>
            <a href="send_message.php?reply_to=<?php echo $profile['user_id']; ?>" class="btn">Enviar Mensaje</a>
        <?php endif; ?>
    </div>
    
    <div class="profile-details">
        <h3>Información de Contacto</h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
        <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($profile['created_at'])); ?></p>
        <p><strong>Último acceso:</strong> <?php echo $profile['last_login'] ? date('d/m/Y H:i', strtotime($profile['last_login'])) : 'Nunca'; ?></p>
    </div>
    
    <div class="profile-posts">
        <h3>Últimas Publicaciones</h3>
        <?php
        $stmt = $conn->prepare("
            SELECT p.* 
            FROM posts p 
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        $stmt->bindParam(':user_id', $profile_id);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (empty($posts)): ?>
            <p>No hay publicaciones recientes.</p>
        <?php else: ?>
            <div class="posts-list">
                <?php foreach ($posts as $post): ?>
                <div class="post">
                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <small><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></small>
                    <a href="../posts/comments.php?post_id=<?php echo $post['post_id']; ?>" class="btn btn-small">Ver comentarios</a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>