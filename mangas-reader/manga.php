<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    die("Manga no especificado.");
}

$manga_id = (int)$_GET['id'];

// Obtener info del manga
$stmt = $conn->prepare("SELECT * FROM mangas WHERE id = ?");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result = $stmt->get_result();
$manga = $result->fetch_assoc();
$stmt->close();

if (!$manga) {
    die("Manga no encontrado.");
}

// Obtener capítulos del manga ordenados por número
$stmt = $conn->prepare("SELECT id, chapter_number, title FROM chapters WHERE manga_id = ? ORDER BY chapter_number ASC");
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result_chapters = $stmt->get_result();
$chapters = [];
while ($row = $result_chapters->fetch_assoc()) {
    $chapters[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($manga['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #121212;
            color: #eee;
        }
        .manga-header {
            margin-top: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .chapters-scroll {
            display: flex;
            overflow-x: auto;
            gap: 1rem;
            padding: 1rem 0;
            scroll-behavior: smooth;
        }
        .chapter-card {
            background: #222;
            border-radius: 8px;
            padding: 1rem;
            min-width: 140px;
            flex-shrink: 0;
            cursor: pointer;
            color: #ffc107;
            text-align: center;
            transition: background-color 0.3s ease;
            user-select: none;
        }
        .chapter-card:hover {
            background-color: #333;
        }
        .manga-description {
            max-width: 800px;
            margin: 0 auto 40px;
            font-size: 1.1rem;
            line-height: 1.5;
        }
        /* Scrollbar estilizado (opcional) */
        .chapters-scroll::-webkit-scrollbar {
            height: 8px;
        }
        .chapters-scroll::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 4px;
        }
        .chapters-scroll::-webkit-scrollbar-track {
            background: #222;
        }
    </style>
</head>
<body>

<?php include "includes/header.php"; ?>

<div class="container">
    <div class="manga-header">
        <h1><?= htmlspecialchars($manga['title']) ?></h1>
        <?php if (!empty($manga['cover_image'])): ?>
            <img src="uploads/<?= htmlspecialchars($manga['cover_image']) ?>" alt="<?= htmlspecialchars($manga['title']) ?>" class="img-fluid rounded my-3" style="max-height: 400px;">
        <?php endif; ?>
        <p class="manga-description"><?= nl2br(htmlspecialchars($manga['description'])) ?></p>
    </div>

    <h3>Capítulos</h3>
    <?php if (count($chapters) === 0): ?>
        <p>No hay capítulos disponibles.</p>
    <?php else: ?>
        <div class="chapters-scroll">
            <?php foreach ($chapters as $chapter): ?>
                <a href="reader.php?id=<?= $chapter['id'] ?>&page=1" class="chapter-card" title="<?= htmlspecialchars($chapter['title']) ?>">
                    <div>Capítulo <?= htmlspecialchars($chapter['chapter_number']) ?></div>
                    <div><?= htmlspecialchars($chapter['title']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="my-4 text-center">
        <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
