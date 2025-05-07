<?php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';
require_once 'includes/db_connection.php';

redirectIfNotLoggedIn();

$page_title = "Inicio";
include 'includes/header.php';
include 'includes/navigation.php';
?>

<main class="content">
    <section class="welcome-section">
        <h2>Bienvenido a la red social interna</h2>
        <p>Conéctate con tus colegas, comparte ideas y mantente informado.</p>
    </section>
    
    <section class="quick-actions">
        <div class="action-card">
            <h3>Crear publicación</h3>
            <p>Comparte tus ideas con el equipo.</p>
            <a href="modules/posts/create_post.php" class="btn">Nueva publicación</a>
        </div>
        
        <div class="action-card">
            <h3>Mensajes</h3>
            <p>Revisa tus mensajes privados.</p>
            <a href="modules/messages/inbox.php" class="btn">Ir a mensajes</a>
        </div>
        
        <div class="action-card">
            <h3>Mi perfil</h3>
            <p>Actualiza tu información personal.</p>
            <a href="modules/profile/view_profile.php" class="btn">Ver perfil</a>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>