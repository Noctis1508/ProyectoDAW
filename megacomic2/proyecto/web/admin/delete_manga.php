<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

// Comprobar que se recibió POST con id numérico
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: upload_manga.php");
    exit();
}

$manga_id = (int)$_POST['id'];

// Obtener nombre imagen para borrarla
$stmt = $conn->prepare("SELECT cover_image FROM mangas WHERE id = ?");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: upload_manga.php");
    exit();
}

$manga = $result->fetch_assoc();

// Borrar imagen física si existe
if ($manga['cover_image'] && file_exists(__DIR__ . '/../uploads/' . $manga['cover_image'])) {
    unlink(__DIR__ . '/../uploads/' . $manga['cover_image']);
}

// Borrar registro BD
$stmt = $conn->prepare("DELETE FROM mangas WHERE id = ?");
$stmt->bind_param("i", $manga_id);
$stmt->execute();

header("Location: upload_manga.php");
exit();
?>
