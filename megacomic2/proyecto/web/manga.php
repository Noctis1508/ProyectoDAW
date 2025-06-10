<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    die("Manga no especificado.");
}

$manga_id = (int)$_GET['id'];

// Procesar nuevo comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $name = trim($_POST['name']) ?: 'Anónimo';
    $comment = trim($_POST['comment_content']);

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (manga_id, name, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $manga_id, $name, $comment);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: manga.php?id=" . $manga_id);
    exit();
}

// Obtener datos del manga
$order = 'ASC';
$iconClass = 'icon-sort-amount-asc';
if (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') {
    $order = 'DESC';
    $iconClass = 'icon-sort-amount-desc';
}
$newOrder = ($order === 'ASC') ? 'desc' : 'asc';

$stmt = $conn->prepare("SELECT * FROM mangas WHERE id = ?");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result = $stmt->get_result();
$manga = $result->fetch_assoc();
$stmt->close();

// Obtener categorías asociadas
$stmt = $conn->prepare("
    SELECT c.nombre 
    FROM categorias c
    INNER JOIN manga_categoria mc ON mc.categoria_id = c.id
    WHERE mc.manga_id = ?
");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result_cats = $stmt->get_result();
$categorias = [];
while ($cat = $result_cats->fetch_assoc()) {
    $categorias[] = $cat['nombre'];
}
$stmt->close();


if (!$manga) {
    die("Manga no encontrado.");
}

// Obtener capítulos
$stmt = $conn->prepare("SELECT id, chapter_number, title FROM chapters WHERE manga_id = ? ORDER BY chapter_number $order");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result_chapters = $stmt->get_result();
$chapters = [];
while ($row = $result_chapters->fetch_assoc()) {
    $chapters[] = $row;
}
$stmt->close();

// Obtener comentarios
$stmt = $conn->prepare("SELECT name, content, created_at FROM comments WHERE manga_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result_comments = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.png" type="image/png">
    <title><?= htmlspecialchars($manga['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-dark text-light">

<?php include "includes/header.php"; ?>

<div class="container my-5">
    <div class="manga-header d-flex flex-md-row align-items-start gap-4">
        <?php if (!empty($manga['cover_image'])): ?>
            <img src="uploads/<?= htmlspecialchars($manga['cover_image']) ?>" alt="<?= htmlspecialchars($manga['title']) ?>" class="manga-cover img-fluid">
        <?php endif; ?>

        <div class="manga-info">
            <h1 class="text-warning"><?= htmlspecialchars($manga['title']) ?></h1>
            <p class="manga-description"><?= nl2br(htmlspecialchars($manga['description'])) ?></p>
            <?php
                $statusClass = '';
                switch (strtolower($manga['status'] ?? '')) {
                    case 'terminado': $statusClass = 'text-danger'; break;
                    case 'publicandose': $statusClass = 'text-success'; break;
                    case 'pausado': $statusClass = 'text-warning'; break;
                    default: $statusClass = 'text-light';
                }
            ?>
            <p class="manga-status"><strong>Estado:</strong> <span class="<?= $statusClass ?>"><?= htmlspecialchars($manga['status'] ?? 'Desconocido') ?></span></p>
            <?php if (!empty($categorias)): ?>
                <p class="manga-categorias"><strong>Categorías:</strong> <?= implode(', ', array_map('htmlspecialchars', $categorias)) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <h3 class="section-title d-flex align-items-center gap-2">
        Capítulos
        <a href="?id=<?= $manga_id ?>&order=<?= $newOrder ?>" class="text-decoration-none text-light fs-5" title="Ordenar capítulos <?= strtoupper($newOrder) ?>">
            <i class="<?= $iconClass ?>"></i>
        </a>
    </h3>

    <?php if (count($chapters) === 0): ?>
        <p>No hay capítulos disponibles.</p>
    <?php else: ?>
        <div class="chapters-container">
            <ul class="chapters-list">
                <?php foreach ($chapters as $chapter): ?>
                    <li>
                        <a href="reader.php?id=<?= $chapter['id'] ?>&page=1" class="chapter-item">
                            <span class="chapter-number">Capítulo <?= htmlspecialchars($chapter['chapter_number']) ?>:</span>
                            <span class="chapter-title"><?= htmlspecialchars($chapter['title']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <hr class="my-4 border-light">

    <h3 class="text-warning">Comentarios</h3>

    <!-- Formulario para nuevo comentario -->
    <form method="post" class="mb-4">
        <div class="mb-2">
            <label class="form-label">Nombre (opcional)</label>
            <input type="text" name="name" class="form-control" maxlength="100">
        </div>
        <div class="mb-2">
            <label class="form-label">Comentario</label>
            <textarea name="comment_content" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-warning">Enviar comentario</button>
    </form>

    <!-- Lista de comentarios -->
    <?php if ($result_comments->num_rows === 0): ?>
        <p>No hay comentarios todavía.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php while ($comment = $result_comments->fetch_assoc()): ?>
                <li class="list-group-item bg-secondary text-light mb-2">
                    <strong><?= htmlspecialchars($comment['name']) ?></strong>
                    <small class="text-muted float-end"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
