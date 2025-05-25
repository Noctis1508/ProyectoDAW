<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['chapter_id'])) {
    die("Capítulo no especificado.");
}

$chapter_id = intval($_GET['chapter_id']);

// Obtener capítulo y manga para enlaces y título
$stmt = $conn->prepare("SELECT c.chapter_number, c.title, m.title AS manga_title, c.manga_id FROM chapters c JOIN mangas m ON c.manga_id = m.id WHERE c.id = ?");
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$stmt->bind_result($chapter_number, $chapter_title, $manga_title, $manga_id);
if (!$stmt->fetch()) {
    die("Capítulo no encontrado.");
}
$stmt->close();

// Manejar cambio de orden
if (isset($_GET['action'], $_GET['page_id'])) {
    $action = $_GET['action'];
    $page_id = intval($_GET['page_id']);

    // Obtener página actual
    $stmt = $conn->prepare("SELECT page_number FROM pages WHERE id = ? AND chapter_id = ?");
    $stmt->bind_param("ii", $page_id, $chapter_id);
    $stmt->execute();
    $stmt->bind_result($current_pos);
    if (!$stmt->fetch()) {
        $stmt->close();
        header("Location: reorder_pages.php?chapter_id=$chapter_id");
        exit();
    }
    $stmt->close();

    if ($action === 'up') {
        // Buscar página anterior con page_number menor (la más cercana)
        $stmt = $conn->prepare("SELECT id, page_number FROM pages WHERE chapter_id = ? AND page_number < ? ORDER BY page_number DESC LIMIT 1");
        $stmt->bind_param("ii", $chapter_id, $current_pos);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($swap = $result->fetch_assoc()) {
            // Intercambiar page_number
            $stmt1 = $conn->prepare("UPDATE pages SET page_number = ? WHERE id = ?");
            $stmt2 = $conn->prepare("UPDATE pages SET page_number = ? WHERE id = ?");
            $stmt1->bind_param("ii", $swap['page_number'], $page_id);
            $stmt2->bind_param("ii", $current_pos, $swap['id']);
            $stmt1->execute();
            $stmt2->execute();
            $stmt1->close();
            $stmt2->close();
        }
        $stmt->close();
    } elseif ($action === 'down') {
        // Buscar página siguiente con page_number mayor (la más cercana)
        $stmt = $conn->prepare("SELECT id, page_number FROM pages WHERE chapter_id = ? AND page_number > ? ORDER BY page_number ASC LIMIT 1");
        $stmt->bind_param("ii", $chapter_id, $current_pos);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($swap = $result->fetch_assoc()) {
            // Intercambiar page_number
            $stmt1 = $conn->prepare("UPDATE pages SET page_number = ? WHERE id = ?");
            $stmt2 = $conn->prepare("UPDATE pages SET page_number = ? WHERE id = ?");
            $stmt1->bind_param("ii", $swap['page_number'], $page_id);
            $stmt2->bind_param("ii", $current_pos, $swap['id']);
            $stmt1->execute();
            $stmt2->execute();
            $stmt1->close();
            $stmt2->close();
        }
        $stmt->close();
    }

    header("Location: reorder_pages.php?chapter_id=$chapter_id");
    exit();
}

// Obtener páginas ordenadas
$pages = [];
$stmt = $conn->prepare("SELECT id, image_path, page_number FROM pages WHERE chapter_id = ? ORDER BY page_number ASC");
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pages[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reordenar páginas - Capítulo <?= htmlspecialchars($chapter_number) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        img.page-thumb {
            max-height: 150px;
            border: 2px solid #ffc107;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-dark text-light">

<div class="container py-5">
    <h2 class="text-warning mb-4">Reordenar páginas del capítulo <?= htmlspecialchars($chapter_number) ?> - <?= htmlspecialchars($chapter_title) ?></h2>
    <h4 class="mb-4">Manga: <?= htmlspecialchars($manga_title) ?></h4>

    <?php if (empty($pages)): ?>
        <p>No hay páginas para este capítulo.</p>
    <?php else: ?>
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Vista previa</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $index => $page): ?>
                    <tr>
                        <td><?= htmlspecialchars($page['page_number']) ?></td>
                        <td>
                            <img src="../uploads/pages/<?= htmlspecialchars($page['image_path']) ?>" alt="Página <?= $page['page_number'] ?>" class="page-thumb">
                        </td>
                        <td>
                            <?php if ($index > 0): ?>
                                <a href="?chapter_id=<?= $chapter_id ?>&page_id=<?= $page['id'] ?>&action=up" class="btn btn-sm btn-outline-warning">↑ Subir</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled>↑</button>
                            <?php endif; ?>

                            <?php if ($index < count($pages) - 1): ?>
                                <a href="?chapter_id=<?= $chapter_id ?>&page_id=<?= $page['id'] ?>&action=down" class="btn btn-sm btn-outline-warning">↓ Bajar</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled>↓</button>
                            <?php endif; ?>

                            <a href="edit_page.php?page_id=<?= $page['id'] ?>" class="btn btn-sm btn-outline-info ms-2">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="add_chapter.php?manga_id=<?= $manga_id ?>" class="btn btn-secondary mt-4">Volver al manga</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
