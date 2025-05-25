<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: upload_manga.php");
    exit();
}

$manga_id = (int)$_GET['id'];

// Obtener datos actuales del manga
$stmt = $conn->prepare("SELECT title, description, cover_image FROM mangas WHERE id = ?");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: upload_manga.php");
    exit();
}

$manga = $result->fetch_assoc();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if ($title === '') {
        $errors[] = "El título es obligatorio.";
    }

    // Manejar subida de imagen (opcional)
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['cover_image']['type'], $allowed_types)) {
            $errors[] = "El archivo debe ser una imagen JPG, PNG o GIF.";
        } else {
            $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'cover_' . uniqid() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/';
            $upload_path = $upload_dir . $new_filename;

            if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                $errors[] = "Error al subir la imagen.";
            } else {
                // Si se subió bien, elimina la imagen vieja si existe
                if ($manga['cover_image'] && file_exists($upload_dir . $manga['cover_image'])) {
                    unlink($upload_dir . $manga['cover_image']);
                }
                $manga['cover_image'] = $new_filename;
            }
        }
    }

    if (empty($errors)) {
        // Actualizar en BD
        $stmt = $conn->prepare("UPDATE mangas SET title = ?, description = ?, cover_image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $manga['cover_image'], $manga_id);
        $stmt->execute();
        $success = true;

        // Refrescar datos
        $manga['title'] = $title;
        $manga['description'] = $description;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Editar Manga - MegaComic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-dark text-light">

<?php include '../includes/header.php'; ?>

<div class="container py-4">
  <h1>Editar Manga</h1>

  <?php if ($success): ?>
    <div class="alert alert-success">Manga actualizado correctamente.</div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="edit_manga.php?id=<?= $manga_id ?>" method="post" enctype="multipart/form-data" class="text-dark">
    <div class="mb-3">
      <label for="title" class="form-label">Título</label>
      <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($manga['title']) ?>" required>
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Descripción</label>
      <textarea name="description" id="description" class="form-control" rows="5"><?= htmlspecialchars($manga['description']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Imagen de portada actual</label><br>
      <?php if ($manga['cover_image'] && file_exists(__DIR__ . '/../uploads/' . $manga['cover_image'])): ?>
        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($manga['cover_image']) ?>" alt="Portada" style="max-width: 200px;">
      <?php else: ?>
        <div class="bg-secondary text-center py-5">Sin imagen</div>
      <?php endif; ?>
    </div>

    <div class="mb-3">
      <label for="cover_image" class="form-label">Cambiar imagen de portada (opcional)</label>
      <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/*">
    </div>

    <button type="submit" class="btn btn-warning">Guardar cambios</button>
    <a href="upload_manga.php" class="btn btn-secondary">Volver</a>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
