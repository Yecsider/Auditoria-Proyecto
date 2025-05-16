<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_functions.php';
require_once '../../includes/db_connection.php';
redirectIfNotLoggedIn();

if (!isDigitador()) {
    header('Location: ../../index.php');
    exit;
}

$stmt = $conn->prepare("SELECT user_id, first_name, last_name FROM users WHERE is_active = TRUE ORDER BY first_name");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Inventario</title>
</head>
<body>
    <h2>Registro Manual de Inventario</h2>
    <form action="inventory_functions.php" method="POST">
        <input type="hidden" name="action" value="manual">
        <label>ID Inventario: <input type="number" name="id_inventario" required></label><br>
        <label>Marca: <input type="text" name="marca" required></label><br>
        <label>Modelo: <input type="text" name="modelo" required></label><br>
        <label>Serial: <input type="text" name="serial" required></label><br>
        <label>Categoría: <input type="text" name="categoria" required></label><br>
        <label>Estado:
            <select name="estado" required>
                <option value="Operativo">Operativo</option>
                <option value="Dañado">Dañado</option>
            </select>
        </label><br>
        <label>Responsable:
            <select name="id_persona" required>
                <?php foreach ($users as $row): ?>
                    <option value="<?= $row['user_id'] ?>"><?= $row['first_name'] . ' ' . $row['last_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <button type="submit">Guardar</button>
    </form>
    <hr>
    <h2>Cargar desde CSV</h2>
    <form action="upload_inventory.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Cargar CSV</button>
    </form>
</body>
</html>
