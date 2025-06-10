<?php
// Ruta base del proyecto en el navegador
define('BASE_URL', '/');

// Conexión a la base de datos
$host = 'localhost';
$user = 'megacomic';
$pass = 'megacomic';
$dbname = 'megacomic';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
