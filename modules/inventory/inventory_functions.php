<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';
redirectIfNotLoggedIn();

if (!isDigitador()) {
    header('Location: ../../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'manual') {
    try {
        $stmt = $conn->prepare("INSERT INTO inventario 
            (id_inventario, marca, modelo, serial, categoria, estado, id_persona)
            VALUES (:id_inventario, :marca, :modelo, :serial, :categoria, :estado, :id_persona)");

        $stmt->execute([
            ':id_inventario' => $_POST['id_inventario'],
            ':marca' => $_POST['marca'],
            ':modelo' => $_POST['modelo'],
            ':serial' => $_POST['serial'],
            ':categoria' => $_POST['categoria'],
            ':estado' => $_POST['estado'],
            ':id_persona' => $_POST['id_persona'],
        ]);

        header("Location: add_inventory.php?success=1");
    } catch (PDOException $e) {
        echo "Error al guardar el inventario: " . $e->getMessage();
    }
}
