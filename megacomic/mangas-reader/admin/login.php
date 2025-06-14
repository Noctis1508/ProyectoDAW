<?php
session_start();
require_once "../includes/db.php";
require_once '../config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: ../index.php"); // Redirige al panel de subir manga
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - MegaComic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css" />
</head>
<body class="bg-dark text-light">
    <div class="container pt-5" style="max-width: 400px; margin: 2rem auto;">
        <h2 class="text-warning mb-4 text-center fs-4">Iniciar sesión - MegaComic</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-secondary p-4 rounded shadow">
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control bg-dark text-light border-warning" id="username" name="username" required />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control bg-dark text-light border-warning" id="password" name="password" required />
            </div>
            <button type="submit" class="btn btn-warning w-100">
                <span class="icon-enter" style="font-size: 0.85rem;"></span> Entrar
            </button>
        </form>
    </div>
</body>
</html>

