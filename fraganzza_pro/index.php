<?php
session_start();

require "php/conexion.php";


$buscarQuery  = trim($_GET['query'] ?? '');


$logged_in = isset($_SESSION["user_id"]) && is_numeric($_SESSION["user_id"]) && (int)$_SESSION["user_id"] > 0;

$sql = "SELECT id, marca, titulo, imagen, url
        FROM productos
        WHERE imagen IS NOT NULL AND imagen <> ''";

$params = [];
$types  = "";

if ($buscarQuery !== "") {
  $sql .= " AND (marca LIKE ? OR titulo LIKE ?)";
  $params[] = "%$buscarQuery%";
  $params[] = "%$buscarQuery%";
  $types .= "ss";
}


$sql .= " ORDER BY id DESC LIMIT 60";

$stmt = $conexion->prepare($sql);

if ($types !== "") {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);



?>



<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/estilos.css">
  <title>Fraganzza</title>
  <link rel="icon" type="image/png" href="img/icon.png">
</head>
<body>
    <?php include "php/navbar.php"; ?>
    <section class="hero">
  <div class="hero-inner">
    <h1>Descubre el universo de los perfumes nicho</h1>
    <p>
      Fraganzza te acerca a fragancias exclusivas, marcas premium 
      y opiniones reales para ayudarte a encontrar tu aroma ideal.
    </p>
    
  </div>
</section>
  <!-- <h1>Bienvenido a Fraganzza</h1> -->
  <form method="GET" class="search-container">
    <input 
        type="text" 
        name="query" 
        placeholder="Buscar por marca o perfume"
        value="<?= htmlspecialchars($buscarQuery) ?>" 
    >
    
    <button type="submit" class="btn primary">Buscar</button>
    
    <a href="productos.php" class="btn-clear">Limpiar</a>
</form>



<?php if (empty($productos)): ?>
  <p class="muted" style="text-align:center;margin:20px;">
    No se encontraron perfumes con ese criterio.
  </p>
<?php endif; ?>

  <div class="carousel" id="carousel">
    <?php foreach($productos as $p): ?>
      <a class="card" href="producto.php?id=<?= (int)$p['id'] ?>">
        <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="">
        <div class="marca"><?= htmlspecialchars($p['marca']) ?></div>
        <div class="titulo"><?= htmlspecialchars($p['titulo']) ?></div>
      </a>
    <?php endforeach; ?>
  </div>

  <footer><p>Todos los derechos reservados ©2025</p></footer>
</body>
</html>