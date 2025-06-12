<?php
require_once __DIR__ . '/config.php';

$query = trim($_GET['q'] ?? '');
$categoriaId = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;

$results = [];

if ($query !== '' || $categoriaId !== null) {

    // Construimos la consulta dinámicamente
    $sql = "SELECT DISTINCT m.id, m.title, m.cover_image 
            FROM mangas m";

    $params = [];
    $types = "";
    $where = [];

    if ($categoriaId !== null) {
        $sql .= " INNER JOIN manga_categoria mc ON m.id = mc.manga_id";
        $where[] = "mc.categoria_id = ?";
        $types .= "i";
        $params[] = $categoriaId;
    }

    if ($query !== '') {
        $where[] = "m.title LIKE ?";
        $types .= "s";
        $params[] = "%$query%";
    }

    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY m.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Buscar mangas - MegaComic</title>
  <link href="<?= BASE_URL ?>assets/css/styles.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .manga-card {
      cursor: pointer;
      transition: transform 0.2s ease;
    }
    .manga-card:hover {
      transform: scale(1.03);
    }
    .manga-card .card-img-top {
      height: 300px;
      object-fit: cover;
    }
  </style>
</head>
<body class="bg-dark text-light">
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="container py-5">
    <h1 class="text-warning mb-4">Resultados de búsqueda</h1>

    <?php if ($categoriaId !== null): ?>
      <?php
      $stmtCat = $conn->prepare("SELECT nombre FROM categorias WHERE id = ?");
      $stmtCat->bind_param('i', $categoriaId);
      $stmtCat->execute();
      $resCat = $stmtCat->get_result();
      $catNombre = $resCat->fetch_assoc()['nombre'] ?? 'Categoría';
      $stmtCat->close();
      ?>
      <p>Mostrando mangas en la categoría: <strong><?= htmlspecialchars($catNombre) ?></strong></p>
    <?php endif; ?>

    <?php if ($query !== ''): ?>
      <p>Filtrando por título que contenga: <strong><?= htmlspecialchars($query) ?></strong></p>
    <?php endif; ?>

    <?php if ($query === '' && $categoriaId === null): ?>
      <p>Ingresa un término de búsqueda o selecciona una categoría.</p>
    <?php elseif (empty($results)): ?>
      <p>No se encontraron mangas con los criterios indicados.</p>
    <?php else: ?>
      <div class="row">
        <?php foreach ($results as $manga): ?>
          <div class="col-md-4 col-lg-3 mb-4">
            <a href="<?= BASE_URL ?>manga.php?id=<?= $manga['id'] ?>" class="text-decoration-none text-light">
              <div class="card bg-secondary text-light manga-card h-100">
                <?php if ($manga['cover_image']): ?>
                  <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($manga['cover_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($manga['title']) ?>">
                <?php else: ?>
                  <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height:300px;">
                    Sin imagen
                  </div>
                <?php endif; ?>
                <div class="card-body text-center">
                  <h5 class="card-title"><?= htmlspecialchars($manga['title']) ?></h5>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
