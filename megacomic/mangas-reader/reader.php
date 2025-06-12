<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    die("Capítulo no especificado.");
}

$chapter_id = (int)$_GET['id'];
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'single';
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

// Obtener capítulo anterior y siguiente
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
    <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($chapter['manga_title']) ?> - Capítulo <?= htmlspecialchars($chapter['chapter_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #eee;
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex gap-2">
            <a href="reader.php?id=<?= $chapter_id ?>&page=1&mode=<?= $mode === 'single' ? 'all' : 'single' ?>" class="btn btn-outline-warning d-flex align-items-center gap-2">
                <?php if ($mode === 'single'): ?>
                    <span class="icon-move-vertical"></span> <span class="btn-text">Cascada</span>
                <?php else: ?>
                    <span class="icon-move-horizontal"></span> <span class="btn-text">Páginada</span>
                <?php endif; ?>
            </a>
            <a href="manga.php?id=<?= $chapter['manga_id'] ?>" class="btn btn-secondary">
                <span class="icon-arrow-left-alt1" style="font-size: 1rem;"></span> <span class="btn-text">Volver</span>
            </a>
        </div>
    </div>

    <h1 class="text-warning m-0"><?= htmlspecialchars($chapter['manga_title']) ?></h1>

    <h3 class="mb-4">
        Capítulo <?= htmlspecialchars($chapter['chapter_number']) ?>: <?= htmlspecialchars($chapter['chapter_title']) ?>
    </h3>

    <?php if ($mode === 'single'): ?>
        <?php
        $stmt = $conn->prepare("SELECT image_path FROM pages WHERE chapter_id = ? AND page_number = ?");
        $stmt->bind_param("ii", $chapter_id, $page_number);
        $stmt->execute();
        $stmt->bind_result($image_path);
        $stmt->fetch();
        $stmt->close();

        if (!$image_path) {
            echo "<p>Página no encontrada.</p>";
        } else {
            $prev_page = max(1, $page_number - 1);
            $next_page = min($total_pages, $page_number + 1);
        ?>
        <!-- Select de página (arriba) -->
        <div class="mb-3">
            <select class="form-select select-w mx-auto" id="pageSelectTop">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $page_number ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div id="clickable-image" style="position: relative; width: 100%;">
            <img src="uploads/pages/<?= htmlspecialchars($image_path) ?>" alt="Página <?= $page_number ?>" class="reader-image mb-4">
            <div style="position: absolute; top: 0; bottom: 0; left: 0; width: 50%; cursor: pointer;" onclick="goToPage(<?= $page_number - 1 ?>)"></div>
            <div style="position: absolute; top: 0; bottom: 0; right: 0; width: 50%; cursor: pointer;" onclick="goToPage(<?= $page_number + 1 ?>)"></div>
        </div>


        <!-- Select de página (abajo) -->
        <div class="mt-3">
            <select class="form-select select-w mx-auto" id="pageSelectBottom">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $page_number ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <?php } ?>
    <?php else: ?>
        <?php
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

    <!-- Botones de capítulo anterior y siguiente -->
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-start">
        <?php if ($prev_chapter_id): ?>
            <a href="reader.php?id=<?= $prev_chapter_id ?>&page=1&mode=<?= $mode ?>" class="btn btn-outline-warning">
                <span class="icon-backward2" style="font-size: 2rem; margin-right: 5px;"></span>
            </a>
        <?php endif; ?>
    </div>
    <div class="text-end">
        <?php if ($next_chapter_id): ?>
            <a href="reader.php?id=<?= $next_chapter_id ?>&page=1&mode=<?= $mode ?>" class="btn btn-outline-warning">
                <span class="icon-forward3" style="font-size: 2rem; margin-left: 4px;"></span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Botón Volver abajo centrado respecto al contenido -->
<div class="d-flex justify-content-center mt-4">
    <a href="manga.php?id=<?= $chapter['manga_id'] ?>" class="btn btn-secondary">
        <span class="icon-arrow-left-alt1" style="font-size: 1rem;"></span> <span class="btn-text">Volver</span>
    </a>
</div>

</div>

</div>

<script>
const currentPage = <?= $page_number ?>;
const totalPages = <?= $total_pages ?>;
const currentChapterId = <?= $chapter_id ?>;
const mode = '<?= $mode ?>';
const prevChapterId = <?= $prev_chapter_id ?? 'null' ?>;
const nextChapterId = <?= $next_chapter_id ?? 'null' ?>;

function goToPage(page) {
    if (mode !== 'single') {
        // En modo scroll, solo cambiar la página
        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        window.location.href = `reader.php?${params.toString()}`;
        return;
    }

    if (page < 1) {
        if (prevChapterId !== null) {
            window.location.href = `reader.php?id=${prevChapterId}&mode=${mode}&page=9999`;
        }
    } else if (page > totalPages) {
        if (nextChapterId !== null) {
            window.location.href = `reader.php?id=${nextChapterId}&mode=${mode}&page=1`;
        }
    } else {
        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        window.location.href = `reader.php?${params.toString()}`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const selectTop = document.getElementById('pageSelectTop');
    const selectBottom = document.getElementById('pageSelectBottom');

    if (selectTop) {
        selectTop.addEventListener('change', () => {
            goToPage(Number(selectTop.value));
        });
    }

    if (selectBottom) {
        selectBottom.addEventListener('change', () => {
            goToPage(Number(selectBottom.value));
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
