<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: upload_mangas.php");
    exit();
}

$chapter_id = (int) $_GET['id'];

// --- Procesar borrar página ---
if (isset($_GET['delete_page_id'])) {
    $delete_page_id = (int) $_GET['delete_page_id'];

    // Primero obtener la imagen y page_number para borrar archivo físico y renumerar
    $stmt = $conn->prepare("SELECT image_path, page_number FROM pages WHERE id = ? AND chapter_id = ?");
    $stmt->bind_param("ii", $delete_page_id, $chapter_id);
    $stmt->execute();
    $stmt->bind_result($image_path, $deleted_page_number);
    $stmt->fetch();
    $stmt->close();

    if ($image_path) {
        $file_path = __DIR__ . '/../uploads/pages/' . $image_path;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Borrar registro de BD
        $stmt = $conn->prepare("DELETE FROM pages WHERE id = ? AND chapter_id = ?");
        $stmt->bind_param("ii", $delete_page_id, $chapter_id);
        $stmt->execute();
        $stmt->close();

        // Renumerar páginas siguientes para mantener secuencia
        $stmt = $conn->prepare("UPDATE pages SET page_number = page_number - 1 WHERE chapter_id = ? AND page_number > ?");
        $stmt->bind_param("ii", $chapter_id, $deleted_page_number);
        $stmt->execute();
        $stmt->close();

        // Redirigir para evitar resubmission
        header("Location: edit_chapter.php?id=$chapter_id");
        exit();
    }
}

// Obtener capítulo actual
$stmt = $conn->prepare("SELECT * FROM chapters WHERE id = ?");
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();
$chapter = $result->fetch_assoc();

if (!$chapter) {
    echo "Capítulo no encontrado.";
    exit();
}

// --- Procesar edición título y número capítulo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['new_images'])) {
    $chapter_number = trim($_POST['chapter_number']);
    $title = trim($_POST['title']);

    $stmt = $conn->prepare("UPDATE chapters SET chapter_number = ?, title = ? WHERE id = ?");
    $stmt->bind_param("isi", $chapter_number, $title, $chapter_id);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_chapter.php?id=$chapter_id");
    exit();
}

// --- Procesar subida de nuevas páginas ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_images'])) {
    // Obtener último page_number actual
    $stmt = $conn->prepare("SELECT MAX(page_number) FROM pages WHERE chapter_id = ?");
    $stmt->bind_param("i", $chapter_id);
    $stmt->execute();
    $stmt->bind_result($max_page_number);
    $stmt->fetch();
    $stmt->close();

    $start_page_number = $max_page_number ? $max_page_number + 1 : 1;

    $page_number = $start_page_number;
    foreach ($_FILES['new_images']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['new_images']['error'][$index] === UPLOAD_ERR_OK) {
            $originalName = $_FILES['new_images']['name'][$index];
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $newFileName = uniqid('page_') . '.' . $ext;
            $destination = __DIR__ . '/../uploads/pages/' . $newFileName;

            if (move_uploaded_file($tmpName, $destination)) {
                $stmt = $conn->prepare("INSERT INTO pages (chapter_id, image_path, page_number) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $chapter_id, $newFileName, $page_number);
                $stmt->execute();
                $stmt->close();
                $page_number++;
            }
        }
    }
    header("Location: edit_chapter.php?id=$chapter_id");
    exit();
}

// Obtener páginas del capítulo actual ordenadas por page_number
$stmt_pages = $conn->prepare("SELECT id, image_path, page_number FROM pages WHERE chapter_id = ? ORDER BY page_number ASC");
$stmt_pages->bind_param("i", $chapter_id);
$stmt_pages->execute();
$result_pages = $stmt_pages->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Capítulo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<div class="container py-5">
    <h2 class="text-warning mb-4">Editar capítulo</h2>

    <!-- Formulario para editar título y número -->
    <form method="post" class="mb-5">
        <div class="mb-3">
            <label class="form-label">Número de capítulo</label>
            <input type="number" name="chapter_number" class="form-control" required value="<?= htmlspecialchars($chapter['chapter_number']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($chapter['title']) ?>">
        </div>

        <button type="submit" class="btn btn-warning">Guardar cambios</button>
        <a href="add_chapter.php?manga_id=<?= $chapter['manga_id'] ?>" class="btn btn-secondary">Volver</a>
    </form>

    <!-- Formulario para añadir páginas nuevas -->
    <h3 class="text-warning mb-3">Añadir páginas nuevas al capítulo</h3>
    <form method="post" enctype="multipart/form-data" class="mb-5">
        <div class="mb-3">
            <label for="new_images" class="form-label">Selecciona imágenes nuevas</label>
            <input type="file" name="new_images[]" id="new_images" multiple accept="image/*" class="form-control" required>
            <small class="form-text text-light">Puedes seleccionar varias imágenes para añadir nuevas páginas.</small>
        </div>
        <button type="submit" class="btn btn-warning">Subir nuevas páginas</button>
    </form>

    <!-- Listado de páginas -->
    <h3 class="text-warning mt-5 mb-3">Páginas del capítulo</h3>
    <?php if ($result_pages->num_rows === 0): ?>
        <p>No hay páginas subidas para este capítulo aún.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-4 g-3">
            <?php while ($page = $result_pages->fetch_assoc()): ?>
                <div class="col">
                    <div class="card bg-secondary text-light h-100">
                        <img src="../uploads/pages/<?= htmlspecialchars($page['image_path']) ?>" class="card-img-top" alt="Página <?= $page['page_number'] ?>">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title">Página <?= $page['page_number'] ?></h5>
                            <div>
                                <a href="edit_page.php?page_id=<?= $page['id'] ?>" class="btn btn-sm btn-warning me-2">Editar página</a>
                                <a href="edit_chapter.php?id=<?= $chapter_id ?>&delete_page_id=<?= $page['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que quieres borrar esta página?')">Borrar página</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="mt-3">
            <a href="reorder_pages.php?chapter_id=<?= $chapter_id ?>" class="btn btn-info">Reordenar páginas</a>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
