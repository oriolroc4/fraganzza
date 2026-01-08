<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$logged_in = isset($_SESSION["user_id"]) && is_numeric($_SESSION["user_id"]) && (int)$_SESSION["user_id"] > 0;
$usuario = $_SESSION["usuario"] ?? "usuario";
?>

<nav class="navbar">
  <div class="nav-left">
    <a href="/fraganzza_pro/index.php" class="logo">Fraganzza</a>
  </div>

  <div class="nav-right">
    <?php if ($logged_in): ?>
      <span class="nav-user">Bienvenido, <?= htmlspecialchars($usuario) ?></span>
      <a href="/fraganzza_pro/php/perfil.php" class="nav-link">Mi perfil</a>
      <a href="/fraganzza_pro/php/logout.php" class="nav-link">Cerrar Sesión</a>
    <?php else: ?>
      <a href="login.html" class="nav-link">Iniciar Sesión</a>
      <a href="registro.html" class="nav-link highlight">Registrate</a>
    <?php endif; ?>
  </div>
</nav>
