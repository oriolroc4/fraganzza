<?php
require "conexion.php";

$jsonPath = "../data/productos.json";

if (!file_exists($jsonPath)) {
  die("No existe el archivo JSON");
}

$data = json_decode(file_get_contents($jsonPath), true);
if (!is_array($data)) {
  die("JSON inválido");
}

$stmt = $conexion->prepare("
  INSERT INTO productos (marca, titulo, precio, imagen, url)
  VALUES (?, ?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE
    marca  = VALUES(marca),
    titulo = VALUES(titulo),
    precio = VALUES(precio),
    imagen = VALUES(imagen)
");

$insertados = 0;

foreach ($data as $brandItem) {
  $marca = trim($brandItem["brand"] ?? "");
  if ($marca === "") continue;

  if (!isset($brandItem["products"]) || !is_array($brandItem["products"])) continue;

  foreach ($brandItem["products"] as $p) {
    $titulo = trim($p["title"] ?? "");
    $precio = $p["price"] ?? null;
    $imagen = $p["image"] ?? null;
    $url    = trim($p["link"] ?? "");

    if ($titulo === "" || $url === "") continue;

    if ($precio === "Sin precio" || $precio === "") $precio = null;

    // OJO: tipos -> marca(s), titulo(s), precio(d), imagen(s), url(s)
    $stmt->bind_param("ssdss", $marca, $titulo, $precio, $imagen, $url);
    $stmt->execute();
  }
}

echo "Productos importados: $insertados";
