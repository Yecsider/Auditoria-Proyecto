<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotLoggedIn();

$page_title = "Bandeja de Entrada";
include '../../includes/header.php';
include '../../includes/navigation.php';

// Obtener mensajes recibidos
$stmt = $conn->prepare("
    SELECT m.*, u.username as sender_username, 
           u.first_name as sender_first_name, u.last_name as sender_last_name
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.receiver_id = :user_id
    ORDER BY m.created_at DESC
");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marcar mensajes como leídos al abrir la bandeja
$update_stmt = $conn->prepare("
    UPDATE messages 
    SET is_read = TRUE 
    WHERE receiver_id = :user_id AND is_read = FALSE
");
$update_stmt->bindParam(':user_id', $_SESSION['user_id']);
$update_stmt->execute();
?>

<main class="content">
    <h2>Bandeja de Entrada</h2>
    
    <a href="send_message.php" class="btn">Nuevo Mensaje</a>
    
    <div class="messages-container">
        <?php if (empty($messages)): ?>
            <p>No tienes mensajes.</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
            <div class="message <?php echo $message['is_read'] ? 'read' : 'unread'; ?>">
                <div class="message-header">
                    <h3>De: <?php echo htmlspecialchars($message['sender_first_name'] . ' ' . htmlspecialchars($message['sender_last_name'])); ?></h3>
                    <small>@<?php echo htmlspecialchars($message['sender_username']); ?> - <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></small>
                </div>
                <div class="message-content">
                    <p><?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                </div>
                <div class="message-actions">
                    <a href="send_message.php?reply_to=<?php echo $message['sender_id']; ?>" class="btn btn-small">Responder</a>
                    <a href="delete_message.php?message_id=<?php echo $message['message_id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('¿Eliminar este mensaje?')">Eliminar</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>