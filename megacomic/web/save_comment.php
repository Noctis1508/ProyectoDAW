<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manga_id'], $_POST['comment'])) {
    $manga_id = (int) $_POST['manga_id'];
    $user_id = (int) $_SESSION['user_id'];
    $comment = trim($_POST['comment']);

    if ($comment !== '') {
        $stmt = $conn->prepare("INSERT INTO comments (manga_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $manga_id, $user_id, $comment);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: manga.php?id=" . $_POST['manga_id']);
exit();
