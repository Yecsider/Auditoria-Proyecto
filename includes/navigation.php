<?php
// navigation.php - Navegación principal corregida
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(APP_NAME); ?> - <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Inicio'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<nav class="main-nav">
    <div class="nav-container">
        <!-- Logo alineado a la izquierda -->
        <div class="logo">
            <a href="<?php echo BASE_URL; ?>index.php">
                <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="<?php echo htmlspecialchars(APP_NAME); ?>">
                <span class="logo-text"><?php echo htmlspecialchars(APP_NAME); ?></span>
            </a>
        </div>

        <!-- Menú principal centrado -->
        <div class="nav-links">
            <ul class="nav-menu">
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>index.php"><i class="fas fa-home"></i> Inicio</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_posts.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>modules/posts/view_posts.php"><i class="fas fa-newspaper"></i> Publicaciones</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'inbox.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>modules/messages/inbox.php"><i class="fas fa-envelope"></i> Mensajes</a>
                </li>
                
                <?php if (isLoggedIn()): ?>
                <li class="dropdown <?php echo strpos($_SERVER['PHP_SELF'], 'profile/') !== false ? 'active' : ''; ?>">
                    <a href="#"><i class="fas fa-user"></i> Mi Perfil <i class="fas fa-caret-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo BASE_URL; ?>modules/profile/view_profile.php"><i class="fas fa-id-card"></i> Ver Perfil</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modules/profile/edit_profile.php"><i class="fas fa-user-edit"></i> Editar Perfil</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modules/profile/change_password.php"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if (isAdmin()): ?>
                <li class="dropdown <?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? 'active' : ''; ?>">
                    <a href="#"><i class="fas fa-cog"></i> Admin <i class="fas fa-caret-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo BASE_URL; ?>modules/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Panel</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modules/admin/manage_users.php"><i class="fas fa-users-cog"></i> Usuarios</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modules/admin/manage_roles.php"><i class="fas fa-user-tag"></i> Roles</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modules/admin/view_logs.php"><i class="fas fa-clipboard-list"></i> Registros</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php if (isDigitador()): ?>
        <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'inventory') !== false ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>modules/inventory/add_inventory.php"><i class="fas fa-box"></i> Inventario</a>
        </li>
        <?php endif; ?>

        <!-- Acciones de usuario alineadas a la derecha -->
        <div class="user-actions">
            <?php if (isLoggedIn()): ?>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                <a href="<?php echo BASE_URL; ?>logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </a>
            <?php endif; ?>
        </div>


        <!-- Botón para menú móvil -->
        <button class="mobile-menu-toggle" aria-label="Menú">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<!-- Contenido principal -->
<main class="main-content">