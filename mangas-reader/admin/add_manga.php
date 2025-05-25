<?php
session_start();
require_once __DIR__ . '/../config.php';

// Solo admins logueados pueden acceder
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge datos del formulario
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validar título
    if ($title === '') {
        $errors[] = "El título es obligatorio.";
    }

    // Validar y subir imagen
    if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "La imagen de portada es obligatoria.";
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['cover_image']['type'], $allowedTypes)) {
            $errors[] = "Solo se permiten imágenes JPG, PNG o GIF.";
        }
    }

    if (empty($errors)) {
        // Procesar subida de imagen
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('cover_', true) . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination)) {
            // Insertar manga en BD
            $stmt = $conn->prepare("INSERT INTO mangas (title, description, cover_image) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $description, $newFileName);

            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Error al guardar en la base de datos: " . $stmt->error;
                // Opcional: eliminar la imagen subida si falla
                unlink($destination);
            }
            $stmt->close();
        } else {
            $errors[] = "Error al mover la imagen subida.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Añadir Manga - MegaComic</title>
  <link href="<?= BASE_URL ?>assets/styles.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-dark text-light">
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="container mt-5">
    <h1 class="mb-4 text-warning">Añadir Nuevo Manga</h1>

    <?php if ($success): ?>
      <div class="alert alert-success">¡Manga añadido correctamente!</div>
      <a href="<?= BASE_URL ?>admin/upload_manga.php" class="btn btn-outline-warning">Volver a mis mangas</a>
    <?php else: ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="bg-secondary p-4 rounded">
        <div class="mb-3">
          <label for="title" class="form-label">Título</label>
          <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required />
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Descripción</label>
          <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label for="cover_image" class="form-label">Imagen de Portada</label>
          <input class="form-control" type="file" id="cover_image" name="cover_image" accept="image/*" required />
        </div>

        <button type="submit" class="btn btn-warning">Añadir Manga</button>
      </form>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
