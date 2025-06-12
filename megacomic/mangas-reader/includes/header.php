<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3 py-2" style="border-bottom: 2px solid;">
  <div class="container-fluid">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>index.php">
      <img src="<?= BASE_URL ?>megacomic.png" alt="Logo MegaComic"
           style="height: 50px; width: auto; object-fit: contain;">
    </a>

    <!-- Admin login / usuario, SIEMPRE visible a la derecha en todas las vistas -->
    <div class="d-flex d-lg-none ms-auto">
      <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
        <div class="dropdown">
          <a class="btn btn-outline-warning dropdown-toggle d-flex align-items-center gap-2" href="#" role="button"
             id="userDropdownMobile" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="icon-user" style="font-size: 1.2rem;"></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/upload_manga.php"><span class="icon-books" style="font-size: 0.85rem;"></span> Tus mangas</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php"><span class="icon-exit" style="font-size: 0.9rem;"></span> Cerrar sesión</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="<?= BASE_URL ?>admin/login.php" class="btn btn-outline-light">Admin</a>
      <?php endif; ?>
    </div>

    <!-- Botón hamburguesa para colapsar buscador en móvil -->
    <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="icon-search"></span>
    </button>

    <!-- Contenido colapsable (solo buscador en móvil) -->
    <div class="collapse navbar-collapse" id="navbarContent">
    <form class="d-flex ms-auto" action="<?= BASE_URL ?>search.php" method="get">
      <input class="form-control bg-dark text-light border-warning search-input" 
            type="search" 
            aria-label="Buscar" 
            name="q">
            
      <button class="btn btn-warning d-flex align-items-center justify-content-center px-3 search-button" type="submit">
        <span class="icon-search" style="font-size: 1.2rem;"></span>
      </button>
    </form>

      <!-- Admin visible en escritorio (fuera del collapse) -->
      <div class="d-none d-lg-flex ms-3">
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
          <div class="dropdown">
            <a class="btn btn-outline-warning dropdown-toggle d-flex align-items-center gap-2" href="#" role="button"
               id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="icon-user" style="font-size: 1.2rem;"></span>
              <?= htmlspecialchars($_SESSION['admin_username']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/upload_manga.php"><span class="icon-books" style="font-size: 0.85rem;"></span> Tus mangas</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php"><span class="icon-exit" style="font-size: 0.9rem;"></span> Cerrar sesión</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?= BASE_URL ?>admin/login.php" class="btn btn-outline-light">Acceder admin</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
