<?php
$host = 'localhost';
$dbname = 'megacomic';
$user = 'megacomic';  // Cambia si usas otro usuario
$pass = 'megacomic';      // Cambia si tienes contraseña

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
