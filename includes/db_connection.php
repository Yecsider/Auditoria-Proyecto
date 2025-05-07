<?php
require_once 'config.php';

// Conexión a PostgreSQL
$db_host = 'localhost';
$db_port = '5432';
$db_name = 'red_social_empresa';
$db_user = 'postgres';
$db_pass = '123456789';

try {
    $conn = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>