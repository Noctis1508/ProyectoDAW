<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['manga_id'])) {
    die("Manga no especificado.");
}

$manga_id = intval($_GET['manga_id']);

// Obtener nombre del manga
$manga_title = '';
$stmt = $conn->prepare("SELECT title FROM mangas WHERE id = ?");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$stmt->bind_result($manga_title);
$stmt->fetch();
$stmt->close();

if (!$manga_title) {
    die("Manga no encontrado.");
}

$errors = [];

// Subir capítulo
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $chapter_number = $_POST['chapter_number'];
    $title = $_POST['title'];

    // Insertar capítulo
    $stmt = $conn->prepare("INSERT INTO chapters (manga_id, chapter_number, title, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $manga_id, $chapter_number, $title);
    if (!$stmt->execute()) {
        $errors[] = "Error al insertar el capítulo en la base de datos.";
    }
    $chapter_id = $stmt->insert_id;
    $stmt->close();

    // Preparar carpeta de imágenes
    $pages_folder = __DIR__ . '/../uploads/pages/';
    if (!is_dir($pages_folder)) {
        mkdir($pages_folder, 0777, true);
    }

    $allowed_ext = ['jpg','jpeg','png','gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $page_number = 1;
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
            $originalName = $_FILES['images']['name'][$index];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $size = $_FILES['images']['size'][$index];

            if (!in_array($ext, $allowed_ext)) {
                $errors[] = "Archivo no permitido: $originalName";
                continue;
            }
            if ($size > $max_size) {
                $errors[] = "Archivo demasiado grande: $originalName";
                continue;
            }

            $newFileName = uniqid('page_') . '.' . $ext;
            $destination = $pages_folder . $newFileName;

            if (move_uploaded_file($tmpName, $destination)) {
                $stmt = $conn->prepare("INSERT INTO pages (chapter_id, image_path, page_number) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $chapter_id, $newFileName, $page_number);
                if (!$stmt->execute()) {
                    $errors[] = "Error al guardar la página en la base de datos: $originalName";
                }
                $stmt->close();
                $page_number++;
            } else {
                $errors[] = "Error al subir la imagen: $originalName";
            }
        }
    }

    // Si no hay errores, redirigir a la misma página para evitar reenvío POST
    if (empty($errors)) {
        header("Location: add_chapter.php?manga_id=" . $manga_id);
        exit();
    }
}

// Obtener capítulos existentes
$capitulos = [];
$stmt = $conn->prepare("SELECT id, chapter_number, title, created_at FROM chapters WHERE manga_id = ? ORDER BY chapter_number ASC");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $capitulos[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir capítulo - <?= htmlspecialchars($manga_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <h2 class="mb-4 text-warning">Añadir capítulo al manga: <span class="text-white"><?= htmlspecialchars($manga_title) ?></span></h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="bg-secondary p-4 rounded mb-5">
        <div class="mb-3">
            <label for="chapter_number" class="form-label">Número de capítulo</label>
            <input type="number" name="chapter_number" id="chapter_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Título del capítulo</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Opcional">
        </div>
        <div class="mb-3">
            <label for="images" class="form-label">Imágenes del capítulo</label>
            <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*" required>
            <small class="form-text text-light">Puedes seleccionar varias imágenes manteniendo Ctrl (o Cmd en Mac).</small>
        </div>
        <button type="submit" class="btn btn-warning">Subir capítulo</button>
    </form>

    <h3 class="text-warning mb-3">Capítulos ya subidos</h3>
    <?php if (empty($capitulos)): ?>
        <p>No hay capítulos todavía.</p>
    <?php else: ?>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Subido el</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($capitulos as $cap): ?>
                    <tr>
                        <td><?= htmlspecialchars($cap['chapter_number']) ?></td>
                        <td><?= htmlspecialchars($cap['title']) ?></td>
                        <td><?= date("d/m/Y", strtotime($cap['created_at'])) ?></td>
                        <td>
                            <a href="edit_chapter.php?id=<?= $cap['id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                            <a href="delete_chapter.php?id=<?= $cap['id'] ?>&manga_id=<?= $manga_id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar este capítulo?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
