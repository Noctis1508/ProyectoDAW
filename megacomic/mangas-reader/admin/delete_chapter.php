<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['manga_id'])) {
    header("Location: upload_mangas.php");
    exit();
}

$chapter_id = (int) $_GET['id'];
$manga_id = (int) $_GET['manga_id'];

// Eliminar imágenes del capítulo
$imgStmt = $conn->prepare("SELECT image_path FROM pages WHERE chapter_id = ?");
$imgStmt->bind_param("i", $chapter_id);
$imgStmt->execute();
$images = $imgStmt->get_result();

while ($img = $images->fetch_assoc()) {
    $file = __DIR__ . '/../uploads/pages/' . $img['image_path'];
    if (file_exists($file)) {
        unlink($file);
    }
}

$conn->query("DELETE FROM pages WHERE chapter_id = $chapter_id");
$conn->query("DELETE FROM chapters WHERE id = $chapter_id");

header("Location: add_chapter.php?manga_id=$manga_id");
exit();
