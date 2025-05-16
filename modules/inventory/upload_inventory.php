<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';
redirectIfNotLoggedIn();

if (!isDigitador()) {
    header('Location: ../../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    try {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        fgetcsv($handle); // Saltar encabezado

        $stmt = $conn->prepare("INSERT INTO inventario (id_inventario, marca, modelo, serial, categoria, estado, id_persona)
                                VALUES (:id, :marca, :modelo, :serial, :categoria, :estado, :id_persona)");

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $stmt->execute([
                ':id' => $data[0],
                ':marca' => $data[1],
                ':modelo' => $data[2],
                ':serial' => $data[3],
                ':categoria' => $data[4],
                ':estado' => $data[5],
                ':id_persona' => $data[6],
            ]);
        }

        fclose($handle);
        echo "Inventario cargado exitosamente.";
    } catch (PDOException $e) {
        echo "Error al cargar inventario desde CSV: " . $e->getMessage();
    }
} else {
    echo "Archivo no válido.";
}
