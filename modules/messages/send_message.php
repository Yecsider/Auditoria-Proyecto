<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';

redirectIfNotLoggedIn();

$page_title = "Enviar Mensaje";
include '../../includes/header.php';
include '../../includes/navigation.php';

// Obtener lista de usuarios para seleccionar destinatario
$stmt = $conn->prepare("
    SELECT user_id, username, first_name, last_name 
    FROM users 
    WHERE user_id != :current_user AND is_active = TRUE
    ORDER BY first_name, last_name
");
$stmt->bindParam(':current_user', $_SESSION['user_id']);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar envío de mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $content = trim($_POST['content']);
    
    if (!empty($content)) {
        try {
            $insert_stmt = $conn->prepare("
                INSERT INTO messages (sender_id, receiver_id, content)
                VALUES (:sender_id, :receiver_id, :content)
            ");
            $insert_stmt->bindParam(':sender_id', $_SESSION['user_id']);
            $insert_stmt->bindParam(':receiver_id', $receiver_id);
            $insert_stmt->bindParam(':content', $content);
            $insert_stmt->execute();
            
            $_SESSION['success_message'] = "Mensaje enviado correctamente.";
            header("Location: inbox.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error al enviar el mensaje: " . $e->getMessage();
        }
    } else {
        $error_message = "El contenido del mensaje no puede estar vacío.";
    }
}

// Si es una respuesta, obtener el destinatario
$reply_to = isset($_GET['reply_to']) ? (int)$_GET['reply_to'] : null;
$receiver = null;

if ($reply_to) {
    $stmt = $conn->prepare("
        SELECT user_id, username, first_name, last_name 
        FROM users 
        WHERE user_id = :user_id AND is_active = TRUE
    ");
    $stmt->bindParam(':user_id', $reply_to);
    $stmt->execute();
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<main class="content">
    <h2><?php echo $receiver ? "Responder a " . htmlspecialchars($receiver['first_name']) : "Nuevo Mensaje"; ?></h2>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="message-form">
        <div class="form-group">
            <label for="receiver_id">Destinatario:</label>
            <select name="receiver_id" id="receiver_id" required>
                <?php if ($receiver): ?>
                    <option value="<?php echo $receiver['user_id']; ?>" selected>
                        <?php echo htmlspecialchars($receiver['first_name'] . ' ' . $receiver['last_name'] . ' (@' . $receiver['username'] . ')'); ?>
                    </option>
                <?php else: ?>
                    <option value="">Selecciona un destinatario</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (@' . $user['username'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="content">Mensaje:</label>
            <textarea name="content" id="content" rows="5" required></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn">Enviar Mensaje</button>
            <a href="inbox.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</main>

<?php include '../../includes/footer.php'; ?>