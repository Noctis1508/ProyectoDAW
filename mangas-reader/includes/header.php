<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand text-warning" href="<?= BASE_URL ?>index.php">MegaComic</a>

  <form class="d-flex ms-auto" action="search.php" method="get">
    <input class="form-control me-2 bg-dark text-light border-warning" type="search" placeholder="Buscar manga" aria-label="Buscar" name="q">
    <button class="btn btn-warning" type="submit">Buscar</button>
  </form>

  <div class="ms-3">
    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
      <div class="dropdown">
        <a class="btn btn-outline-warning dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="white-space: nowrap;">
          <!-- Icono persona -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="orange" class="bi bi-person-circle" viewBox="0 0 16 16">
            <path d="M13.468 12.37C12.758 11.226 11.45 10.5 10 10.5s-2.758.726-3.468 1.87A6.987 6.987 0 0 1 8 15a6.987 6.987 0 0 1 5.468-2.63z"/>
            <path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
            <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1z"/>
          </svg>
          <?= htmlspecialchars($_SESSION['admin_username']) ?>
        </a>

        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
          <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/upload_manga.php">Tus mangas</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php">Cerrar sesión</a></li>
        </ul>
      </div>
    <?php else: ?>
      <a href="<?= BASE_URL ?>admin/login.php" class="btn btn-outline-light">Iniciar sesión</a>
    <?php endif; ?>
  </div>
</nav>
