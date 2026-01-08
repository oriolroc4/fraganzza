<?php
session_start();
require "php/conexion.php";

$logged_in = isset($_SESSION["user_id"]) && (int)$_SESSION["user_id"] > 0;


$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  die("Producto inválido.");
}

$stmt = $conexion->prepare("SELECT id, marca, titulo, precio, imagen, url, descripcion FROM productos WHERE id = ?");

$stmt->bind_param("i", $id);
$stmt->execute();

$producto = $stmt->get_result()->fetch_assoc();

// Notas (salida / corazon / fondo)
$stmtN = $conexion->prepare("
  SELECT n.nombre, pn.tipo, pn.orden
  FROM producto_notas pn
  JOIN notas n ON n.id = pn.nota_id
  WHERE pn.product_id = ?
  ORDER BY FIELD(pn.tipo,'salida','corazon','fondo'), pn.orden ASC
");
$stmtN->bind_param("i", $id);
$stmtN->execute();
$resN = $stmtN->get_result();

$notas = ['salida'=>[], 'corazon'=>[], 'fondo'=>[]];
while ($row = $resN->fetch_assoc()) {
  $tipo = $row['tipo'];
  if (isset($notas[$tipo])) $notas[$tipo][] = $row['nombre'];
}


if (!$producto) {
  http_response_code(404);
  die("Producto no encontrado.");
  
}



$errors = [];

 if ($_SERVER["REQUEST_METHOD"] === "POST" && $logged_in) {
  $rating  = (int)($_POST["rating"] ?? 0);
  $comment = trim($_POST["comment"] ?? "");

  if ($rating < 1 || $rating > 5) $errors[] = "Rating inválido.";
  if ($comment === "") $errors[] = "El comentario no puede estar vacío.";

  if (!$errors) {
    $stmtIns = $conexion->prepare("
      INSERT INTO reviews (product_id, user_id, rating, comment)
      VALUES (?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE
        rating = VALUES(rating),
        comment = VALUES(comment)
    ");
    $stmtIns->bind_param("iiis", $id, $_SESSION["user_id"], $rating, $comment);
    $stmtIns->execute();

    header("Location: producto.php?id=" . $id);
    exit;
  }
}


// Carrusel mezclado (aleatorio), excluyendo el producto actual
$stmtC = $conexion->prepare("
  SELECT id, marca, titulo, imagen
  FROM productos
  WHERE id <> ? AND imagen IS NOT NULL AND imagen <> ''
  ORDER BY RAND()
  LIMIT 20
");
$stmtC->bind_param("i", $id);
$stmtC->execute();
$masProductos = $stmtC->get_result()->fetch_all(MYSQLI_ASSOC);

$stmtN = $conexion->prepare("
  SELECT pn.tipo, n.nombre
  FROM producto_notas pn
  JOIN notas n ON n.id = pn.nota_id
  WHERE pn.product_id = ?
  ORDER BY FIELD(pn.tipo,'salida','corazon','fondo'), pn.orden ASC, n.nombre ASC
");
$stmtN->bind_param("i", $id);
$stmtN->execute();
$resN = $stmtN->get_result();

$notas = [
  "salida"  => [],
  "corazon" => [],
  "fondo"   => []
];

while ($row = $resN->fetch_assoc()) {
  $notas[$row["tipo"]][] = $row["nombre"];
}


$stmtR = $conexion->prepare("
  SELECT r.rating, r.comment, r.creada_fecha, u.usuario
  FROM reviews r
  JOIN usuarios u ON u.id = r.user_id
  WHERE r.product_id = ?
  ORDER BY r.creada_fecha DESC
");
$stmtR->bind_param("i", $id);
$stmtR->execute();
$reviews = $stmtR->get_result();

?>




<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/estilos.css">
  <link rel="icon" type="image/png" href="img/icon.png">
  <title><?= htmlspecialchars($producto["titulo"]) ?> - Fraganzza</title>
</head>

<body>
  <?php include "php/navbar.php"; ?>
 

   <main class="product">
  <div class="product-media">
    <img src="<?= htmlspecialchars($producto["imagen"]) ?>" alt="<?= htmlspecialchars($producto["titulo"]) ?>">
  </div>

  <div class="product-info">
    <div class="pill"><?= htmlspecialchars($producto["marca"] ?? "") ?></div>

    <h1 class="h1"><?= htmlspecialchars($producto["titulo"]) ?></h1>

    <?php if (!empty($producto["descripcion"])): ?>
      <div class="descripcion">
        <?= nl2br(htmlspecialchars($producto["descripcion"])) ?>
      </div>
    <?php endif; ?>

    

  <div class="notes-block">
  <div class="notes-title">Notas</div>

  <?php
    $hayNotas = !empty($notas["salida"]) || !empty($notas["corazon"]) || !empty($notas["fondo"]);
  ?> 

  <?php if ($hayNotas): ?>
    <div class="notes-row">
      <span class="notes-label">Salida</span>
      <div class="notes-chips">
        <?php foreach ($notas["salida"] as $n): ?>
          <span class="note-chip"><?= htmlspecialchars($n) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="notes-row">
      <span class="notes-label">Corazón</span>
      <div class="notes-chips">
        <?php foreach ($notas["corazon"] as $n): ?>
          <span class="note-chip"><?= htmlspecialchars($n) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="notes-row">
      <span class="notes-label">Fondo</span>
      <div class="notes-chips">
        <?php foreach ($notas["fondo"] as $n): ?>
          <span class="note-chip"><?= htmlspecialchars($n) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php else: ?>
    <div class="muted" style="margin-top:8px;">Aún no tenemos notas para este perfume.</div>
  <?php endif; ?>
</div>
</main>

    <?php if (!empty($masProductos)): ?>
  <section style="margin-top:22px;">
    <h2 style="margin:0 0 10px;">Descubre otros perfumes</h2>

    <div class="carousel" id="carousel-mix">
      <?php foreach($masProductos as $p): ?>
        <a class="card" href="producto.php?id=<?= (int)$p['id'] ?>">
          <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="">
          <div class="marca"><?= htmlspecialchars($p['marca']) ?></div>
          <div class="titulo"><?= htmlspecialchars($p['titulo']) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>




   <!-- reviews -->
<div id="reviews" class="reviews">

  <h2>Opiniones</h2>

  <?php if ($reviews && $reviews->num_rows > 0): ?>
    <?php while ($r = $reviews->fetch_assoc()): ?>
      <div class="review">
        <div class="review-header">
          <strong><?= htmlspecialchars($r["usuario"]) ?></strong>
          <span class="review-date">
            <?= date("d/m/Y", strtotime($r["creada_fecha"])) ?>
          </span>
        </div>

        <div class="review-rating">
          <?= str_repeat("★", (int)$r["rating"]) . str_repeat("☆", 5 - (int)$r["rating"]) ?>
        </div>

        <p class="review-text">
          <?= nl2br(htmlspecialchars($r["comment"])) ?>
        </p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="muted" style="margin:0;">Todavía no hay opiniones. Sé el primero en comentar.</p>
  <?php endif; ?>


  <?php if ($logged_in): ?>
    <hr>

    <h3>Dejar una opinión</h3>

    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>



    <form method="POST" class="premium-form">
      
      <label class="input-label">Tu Valoración</label>
      <div class="star-rating-group">
        <!-- 5 stars (reversed in CSS) -->
        <input type="radio" id="s5" name="rating" value="5" required><label for="s5" title="5 estrellas">★</label>
        <input type="radio" id="s4" name="rating" value="4"><label for="s4" title="4 estrellas">★</label>
        <input type="radio" id="s3" name="rating" value="3"><label for="s3" title="3 estrellas">★</label>
        <input type="radio" id="s2" name="rating" value="2"><label for="s2" title="2 estrellas">★</label>
        <input type="radio" id="s1" name="rating" value="1"><label for="s1" title="1 estrella">★</label>
      </div>

      <label class="input-label">Tu Opinión</label>
      <textarea name="comment" rows="4" placeholder="¿Qué te ha parecido este perfume? Cuéntanos tu experiencia..." required></textarea>

      <button type="submit" class="btn-submit">Publicar Reseña</button>
    </form>

  <?php else: ?>
    <p class="muted" style="margin-top:14px;">
      Para dejar una opinión,<a href="login.html" class="animated-link"> Inicia sesión.</a> 
      
    </p>
  <?php endif; ?>

</div>
<!-- reviews -->



    <footer>
     
      
    </footer>
  </div>
</body>
</html>
