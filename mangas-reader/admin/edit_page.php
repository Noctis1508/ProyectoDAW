<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['page_id'])) {
    die("Página no especificada.");
}

$page_id = intval($_GET['page_id']);

// Obtener datos página
$stmt = $conn->prepare("SELECT p.id, p.image_path, p.page_number, c.id AS chapter_id, c.manga_id FROM pages p JOIN chapters c ON p.chapter_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $page_id);
$stmt->execute();
$result = $stmt->get_result();
$page = $result->fetch_assoc();
$stmt->close();

if (!$page) {
    die("Página no encontrada.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Eliminar archivo físico
        $image_path = __DIR__ . '/../uploads/pages/' . $page['image_path'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Eliminar registro BD
        $stmt = $conn->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->bind_param("i", $page_id);
        $stmt->execute();
        $stmt->close();

        header("Location: reorder_pages.php?chapter_id=" . $page['chapter_id']);
        exit();
    }

    if (isset($_POST['replace_image'])) {
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_ext = ['jpg','jpeg','png','gif'];
            $max_size = 5 * 1024 * 1024;

            $originalName = $_FILES['new_image']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $size = $_FILES['new_image']['size'];
            $tmpName = $_FILES['new_image']['tmp_name'];

            if (!in_array($ext, $allowed_ext)) {
                $errors[] = "Archivo no permitido.";
            } elseif ($size > $max_size) {
                $errors[] = "Archivo demasiado grande.";
            } else {
                $newFileName = uniqid('page_') . '.' . $ext;
                $destination = __DIR__ . '/../uploads/pages/' . $newFileName;

                if (move_uploaded_file($tmpName, $destination)) {
                    // Borrar archivo anterior
                    $oldFile = __DIR__ . '/../uploads/pages/' . $page['image_path'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }

                    // Actualizar BD
                    $stmt = $conn->prepare("UPDATE pages SET image_path = ? WHERE id = ?");
                    $stmt->bind_param("si", $newFileName, $page_id);
                    $stmt->execute();
                    $stmt->close();

                    $success = "Imagen reemplazada correctamente.";
                    $page['image_path'] = $newFileName; // actualizar variable para mostrar nueva imagen
                } else {
                    $errors[] = "Error al subir la nueva imagen.";
                }
            }
        } else {
            $errors[] = "No se ha seleccionado ninguna imagen.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar página #<?= $page['page_number'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<div class="container py-5">
    <h2 class="text-warning mb-4">Editar página #<?= $page['page_number'] ?></h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="mb-4">
        <img src="../uploads/pages/<?= htmlspecialchars($page['image_path']) ?>" alt="Página <?= $page['page_number'] ?>" class="img-fluid border border-warning rounded">
    </div>

    <form method="post" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label for="new_image" class="form-label">Subir nueva imagen para reemplazar</label>
            <input type="file" name="new_image" id="new_image" accept="image/*" class="form-control" required>
        </div>
        <button type="submit" name="replace_image" class="btn btn-warning">Reemplazar imagen</button>
    </form>

    <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar esta página?')">
        <button type="submit" name="delete" class="btn btn-danger">Eliminar página</button>
        <a href="reorder_pages.php?chapter_id=<?= $page['chapter_id'] ?>" class="btn btn-secondary ms-2">Volver al capítulo</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
