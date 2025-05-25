<?php require_once "includes/db.php"; ?>

<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MegaComic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark text-light">

<?php include "includes/header.php"; ?>

<div class="container py-5">

    <!-- Últimos 3 mangas -->
    <section class="mb-5">
        <h2 class="text-warning mb-4">Últimos mangas</h2>
        <div class="row">
        <?php
        $mangas = $conn->query("SELECT * FROM mangas ORDER BY id DESC LIMIT 3");
        while ($manga = $mangas->fetch_assoc()) {
           echo '<div class="col-md-4 mb-4">
                    <div class="card bg-secondary text-light h-100">
                        <a href="manga.php?id=' . $manga['id'] . '">
                            <img src="uploads/' . $manga['cover_image'] . '" class="card-img-top" alt="' . htmlspecialchars($manga['title']) . '">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title text-center text-warning">' . htmlspecialchars($manga['title']) . '</h5>
                        </div>
                    </div>
                </div>';

        }
        ?>
        </div>
    </section>

    <!-- Últimos 5 capítulos -->
    <section>
        <h2 class="text-warning mb-4">Capítulos recientes</h2>
        <ul class="list-group list-group-flush">
        <?php
        $capitulos = $conn->query("
            SELECT chapters.*, mangas.title AS manga_title
            FROM chapters 
            INNER JOIN mangas ON chapters.manga_id = mangas.id 
            ORDER BY chapters.id DESC LIMIT 5
        ");
        while ($cap = $capitulos->fetch_assoc()) {
            echo "<li class='list-group-item bg-dark text-light'>
                    <a class='text-decoration-none text-info' href='reader.php?id={$cap['id']}'>
                        <strong>{$cap['manga_title']}</strong> - Capítulo {$cap['chapter_number']}: {$cap['title']}
                    </a>
                  </li>";
        }
        ?>
        </ul>
    </section>

</div>

<!-- Al final, justo antes de cerrar body -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
