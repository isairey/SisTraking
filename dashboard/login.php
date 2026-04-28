<?php
require_once __DIR__ . '/../config.php';
session_start();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = $_POST['u'] ?? '';
  $p = $_POST['p'] ?? '';
  if ($u === ADMIN_USER && $p === ADMIN_PASS) {
    $_SESSION['admin'] = true;
    header("Location: ./");
    exit;
  } else {
    $error = "Credenciales incorrectas";
  }
}
?><!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mini Analytics – Login</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <div class="login">
    <h2 style="margin-top:0">Mini Analytics</h2>
    <p class="label">Acceso al dashboard</p>
    <?php if ($error): ?>
      <p style="color:#fca5a5"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post">
      <div style="margin:8px 0">
        <input name="u" placeholder="Usuario" required>
      </div>
      <div style="margin:8px 0">
        <input type="password" name="p" placeholder="Contraseña" required>
      </div>
      <button>Entrar</button>
    </form>
    <p class="footer">Configura usuario y contraseña en <code>config.php</code>.</p>
  </div>
</body>

</html>