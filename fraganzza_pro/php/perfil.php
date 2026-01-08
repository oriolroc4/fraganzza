
<?php
session_start();
require "conexion.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: ../login.html");
  exit;
}

$user_id = (int)$_SESSION["user_id"];

$stmt = $conexion->prepare("
  SELECT 
    p.id,
    p.marca,
    p.titulo,
    p.imagen,
    r.rating,
    r.comment,
    r.creada_fecha
  FROM reviews r
  JOIN productos p ON p.id = r.product_id
  WHERE r.user_id = ?
  ORDER BY r.creada_fecha DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$comentarios = $stmt->get_result();


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="icon" type="image/png" href="../img/icon.png">
    <title>Mi perfil</title>
</head>
<body>
  <?php include "navbar.php"; ?>

  <div class="product-wrap">
  <div class="profile-header">
  <h1>Bienvenido, <?= htmlspecialchars($_SESSION["usuario"]) ?></h1>

  <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
      <div style="margin-bottom: 20px;">
          <a href="paneladmin.php" style="
              display: inline-block;
              padding: 10px 20px;
              background-color: #38bdf8;
              color: #000;
              text-decoration: none;
              font-weight: 600;
              border-radius: 8px;
              transition: background 0.2s;
          " onmouseover="this.style.backgroundColor='#0ea5e9'" onmouseout="this.style.backgroundColor='#38bdf8'">
              ⚙️ Acceder al Panel de Administrador
          </a>
      </div>
  <?php endif; ?>
  <p>Estos son los perfumes que has comentado:</p>

    <?php if ($comentarios->num_rows === 0): ?>
  <p class="muted">Todavía no has dejado ninguna opinión.</p>
<?php else: ?>
  <div class="reviews">
    <?php while ($c = $comentarios->fetch_assoc()): ?>
      <div class="review">
        <div class="review-header">
          <strong>
            <a href="../producto.php?id=<?= (int)$c["id"] ?>">
              <?= htmlspecialchars($c["marca"]) ?> – <?= htmlspecialchars($c["titulo"]) ?>
            </a>
          </strong>
          <span class="review-date">
            <?= date("d/m/Y", strtotime($c["creada_fecha"])) ?>
          </span>
        </div>

        <div class="review-rating">
          <?= str_repeat("★", (int)$c["rating"]) . str_repeat("☆", 5 - (int)$c["rating"]) ?>
        </div>

        <p class="review-text">
          <?= nl2br(htmlspecialchars($c["comment"])) ?>
        </p>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

  </div>
</body>
</html>