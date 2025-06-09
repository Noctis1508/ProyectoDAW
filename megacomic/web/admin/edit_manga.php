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
$stmt = $conn->prepare("SELECT title, description, cover_image, status FROM mangas WHERE id = ?");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: upload_manga.php");
    exit();
}

$manga = $result->fetch_assoc();

// Obtener todas las categorías
$categorias = $conn->query("SELECT * FROM categorias")->fetch_all(MYSQLI_ASSOC);

// Obtener categorías del manga actual
$stmt = $conn->prepare("SELECT categoria_id FROM manga_categoria WHERE manga_id = ?");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result = $stmt->get_result();
$categorias_manga = array_column($result->fetch_all(MYSQLI_ASSOC), 'categoria_id');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    if ($title === '') {
        $errors[] = "El título es obligatorio.";
    }

    if (!in_array($status, ['publicandose', 'pausado', 'terminado'])) {
        $errors[] = "Estado no válido.";
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
                // Eliminar imagen anterior si existe
                if ($manga['cover_image'] && file_exists($upload_dir . $manga['cover_image'])) {
                    unlink($upload_dir . $manga['cover_image']);
                }
                $manga['cover_image'] = $new_filename;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE mangas SET title = ?, description = ?, cover_image = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $description, $manga['cover_image'], $status, $manga_id);
        $stmt->execute();

        // Actualizar categorías
        $conn->query("DELETE FROM manga_categoria WHERE manga_id = $manga_id");
        if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
            $stmt = $conn->prepare("INSERT INTO manga_categoria (manga_id, categoria_id) VALUES (?, ?)");
            foreach ($_POST['categorias'] as $cat_id) {
                $cat_id = (int)$cat_id;
                $stmt->bind_param("ii", $manga_id, $cat_id);
                $stmt->execute();
            }
        }

        header("Location: edit_manga.php?id=" . $manga_id . "&success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Editar Manga - MegaComic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
</head>
<body class="bg-dark text-light">

<?php include '../includes/header.php'; ?>

<div class="container py-4">
  <h1>Editar Manga</h1>

  <?php if (isset($_GET['success'])): ?>
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
      <label for="status" class="form-label">Estado</label>
      <select name="status" id="status" class="form-select">
        <option value="publicandose" <?= $manga['status'] === 'publicandose' ? 'selected' : '' ?>>Publicándose</option>
        <option value="pausado" <?= $manga['status'] === 'pausado' ? 'selected' : '' ?>>Pausado</option>
        <option value="terminado" <?= $manga['status'] === 'terminado' ? 'selected' : '' ?>>Terminado</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="categorias" class="form-label">Categorías</label>
      <select name="categorias[]" id="categorias" class="form-select select2-tag" multiple>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $categorias_manga) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
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

    <button type="submit" class="btn btn-warning"><span class="icon-floppy-disk" style="font-size: 1rem;"></span> Guardar</button>
    <a href="upload_manga.php" class="btn btn-secondary"><span class="icon-undo2" style="font-size: 1rem;"></span></a>
  </form>
</div>

<!-- Librerías JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/script.js"></script>

</body>
</html>

