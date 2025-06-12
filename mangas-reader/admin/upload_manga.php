<?php
session_start();
require_once __DIR__ . '/../config.php'; // si estás en admin/
?>
<?php

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php'; // Cambia esta ruta si tu conexión está en otro lado

$stmt = $conn->prepare("SELECT id, title, cover_image, created_at FROM mangas ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mis Mangas - MegaComic</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-dark text-light">

<?php include '../includes/header.php'; ?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Tus mangas</h1>
    <a href="add_manga.php" class="btn btn-warning"><span class="icon-plus" style="font-size: 1rem;"></span></a>
  </div>

  <?php if ($result->num_rows === 0): ?>
    <p>No has subido ningún manga todavía.</p>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <?php while ($manga = $result->fetch_assoc()): ?>
        <div class="col">
          <div class="card bg-secondary text-light h-100">
           <?php if ($manga['cover_image'] && file_exists(__DIR__ . "/../uploads/" . $manga['cover_image'])): ?>
            <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($manga['cover_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($manga['title']) ?>">
            <?php else: ?>
            <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height:200px; color:gray;">
                Sin imagen
            </div>
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($manga['title']) ?></h5>
              <p class="card-text"><small>Subido el <?= date("d/m/Y", strtotime($manga['created_at'])) ?></small></p>
              <a href="edit_manga.php?id=<?= $manga['id'] ?>" class="btn btn-sm btn-outline-warning"><span class="icon-pencil2" style="font-size: 0.85rem;"></span> Editar</a>
              <a href="add_chapter.php?manga_id=<?= $manga['id'] ?>" class="btn btn-sm btn-outline-info"><span class="icon-plus" style="font-size: 0.8rem;"></span> capítulo</a>
              <form action="delete_manga.php" method="post" onsubmit="return confirm('¿Eliminar este manga?');" style="display:inline;">
                <input type="hidden" name="id" value="<?= $manga['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"><span class="icon-trash" style="font-size: 1rem;"></span></button>
                </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
