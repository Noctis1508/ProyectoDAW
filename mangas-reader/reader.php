<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    die("Capítulo no especificado.");
}

$chapter_id = (int)$_GET['id'];
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'single'; // 'single' o 'all'
$page_number = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Obtener info del capítulo y manga
$stmt = $conn->prepare("
    SELECT chapters.chapter_number, chapters.title AS chapter_title, mangas.title AS manga_title, mangas.id AS manga_id
    FROM chapters
    JOIN mangas ON chapters.manga_id = mangas.id
    WHERE chapters.id = ?
");
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();
$chapter = $result->fetch_assoc();
if (!$chapter) {
    die("Capítulo no encontrado.");
}

// Obtener todos los capítulos del manga para el select
$stmt = $conn->prepare("SELECT id, chapter_number, title FROM chapters WHERE manga_id = ? ORDER BY chapter_number ASC");
$stmt->bind_param("i", $chapter['manga_id']);
$stmt->execute();
$result_chapters = $stmt->get_result();
$chapters_list = [];
while ($row = $result_chapters->fetch_assoc()) {
    $chapters_list[] = $row;
}
$stmt->close();

// Obtener total páginas del capítulo
$stmt = $conn->prepare("SELECT COUNT(*) AS total_pages FROM pages WHERE chapter_id = ?");
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$stmt->bind_result($total_pages);
$stmt->fetch();
$stmt->close();

if ($total_pages == 0) {
    die("Este capítulo no tiene páginas aún.");
}

if ($page_number < 1) $page_number = 1;
if ($page_number > $total_pages) $page_number = $total_pages;

// Obtener capítulo anterior y siguiente (para los enlaces al final)
$prev_chapter_id = null;
$next_chapter_id = null;
foreach ($chapters_list as $i => $ch) {
    if ($ch['id'] == $chapter_id) {
        if ($i > 0) $prev_chapter_id = $chapters_list[$i - 1]['id'];
        if ($i < count($chapters_list) - 1) $next_chapter_id = $chapters_list[$i + 1]['id'];
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($chapter['manga_title']) ?> - Capítulo <?= htmlspecialchars($chapter['chapter_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #eee;
        }
        .reader-image {
            max-width: 100%;
            display: block;
            margin: 0 auto 1rem;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
        }
        .nav-buttons a {
            min-width: 120px;
        }
        .chapter-select, .mode-select {
            max-width: 400px;
            margin: 20px auto;
        }
    </style>
</head>
<body>

<?php include "includes/header.php"; ?>

<div class="container py-4 text-center">
    <h1 class="mb-3 text-warning"><?= htmlspecialchars($chapter['manga_title']) ?></h1>

    <!-- Select para elegir capítulo -->
    <div class="chapter-select mb-3">
        <select id="chapterSelect" class="form-select">
            <?php foreach ($chapters_list as $ch): ?>
                <option value="<?= $ch['id'] ?>" <?= $ch['id'] == $chapter_id ? 'selected' : '' ?>>
                    Capítulo <?= htmlspecialchars($ch['chapter_number']) ?> - <?= htmlspecialchars($ch['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Select para modo lectura -->
    <div class="mode-select mb-4">
        <select id="modeSelect" class="form-select">
            <option value="single" <?= $mode === 'single' ? 'selected' : '' ?>>Página por página</option>
            <option value="all" <?= $mode === 'all' ? 'selected' : '' ?>>Todo en uno (scroll)</option>
        </select>
    </div>

    <h3 class="mb-4">
        Capítulo <?= htmlspecialchars($chapter['chapter_number']) ?>: <?= htmlspecialchars($chapter['chapter_title']) ?>
    </h3>

    <?php if ($mode === 'single'): ?>
        <?php
        // Obtener la página actual
        $stmt = $conn->prepare("SELECT image_path FROM pages WHERE chapter_id = ? AND page_number = ?");
        $stmt->bind_param("ii", $chapter_id, $page_number);
        $stmt->execute();
        $stmt->bind_result($image_path);
        $stmt->fetch();
        $stmt->close();

        if (!$image_path) {
            echo "<p>Página no encontrada.</p>";
        } else {
            // URLs para navegación
            $prev_page = $page_number > 1 ? $page_number - 1 : 1;
            $next_page = $page_number < $total_pages ? $page_number + 1 : $total_pages;
        ?>
            <img src="uploads/pages/<?= htmlspecialchars($image_path) ?>" alt="Página <?= $page_number ?>" class="reader-image mb-4">

            <div class="d-flex justify-content-center align-items-center gap-3 nav-buttons mb-4">
                <a href="reader.php?id=<?= $chapter_id ?>&page=<?= $prev_page ?>&mode=single" class="btn btn-outline-warning <?= $page_number == 1 ? 'disabled' : '' ?>">« Página anterior</a>

                <span>Página <?= $page_number ?> de <?= $total_pages ?></span>

                <a href="reader.php?id=<?= $chapter_id ?>&page=<?= $next_page ?>&mode=single" class="btn btn-outline-warning <?= $page_number == $total_pages ? 'disabled' : '' ?>">Página siguiente »</a>
            </div>
        <?php } ?>

    <?php else: ?>
        <?php
        // Obtener todas las páginas
        $stmt = $conn->prepare("SELECT image_path, page_number FROM pages WHERE chapter_id = ? ORDER BY page_number ASC");
        $stmt->bind_param("i", $chapter_id);
        $stmt->execute();
        $result_pages = $stmt->get_result();
        ?>
        <div class="all-pages">
            <?php while ($page = $result_pages->fetch_assoc()): ?>
                <img src="uploads/pages/<?= htmlspecialchars($page['image_path']) ?>" alt="Página <?= $page['page_number'] ?>" class="reader-image mb-4">
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mt-4">
        <div>
            <?php if ($prev_chapter_id): ?>
                <a href="reader.php?id=<?= $prev_chapter_id ?>&page=1&mode=<?= $mode ?>" class="btn btn-outline-warning">Capítulo anterior</a>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($next_chapter_id): ?>
                <a href="reader.php?id=<?= $next_chapter_id ?>&page=1&mode=<?= $mode ?>" class="btn btn-outline-warning">Capítulo siguiente</a>
            <?php endif; ?>
        </div>
    </div>

    <a href="manga.php?id=<?= $chapter['manga_id'] ?>" class="btn btn-secondary mt-4">Volver al manga</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const chapterSelect = document.getElementById('chapterSelect');
    const modeSelect = document.getElementById('modeSelect');

    chapterSelect.addEventListener('change', () => {
        const selectedChapterId = chapterSelect.value;
        // Recarga al capítulo seleccionado, siempre en página 1 y modo actual
        window.location.href = `reader.php?id=${selectedChapterId}&page=1&mode=${modeSelect.value}`;
    });

    modeSelect.addEventListener('change', () => {
        // Recarga en modo seleccionado, siempre en página 1
        window.location.href = `reader.php?id=${chapterSelect.value}&page=1&mode=${modeSelect.value}`;
    });
</script>

</body>
</html>
