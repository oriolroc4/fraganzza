<?php
session_start();
require 'conexion.php';

// Verificar permisos de administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.html');
    exit;
}

// Variables para mensajes
$mensaje = '';
$tipoMensaje = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // --- ACCIONES PRODUCTOS ---
        if ($action === 'delete' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $mensaje = "Producto eliminado correctamente.";
                $tipoMensaje = "success";
            } else {
                $mensaje = "Error al eliminar: " . $conexion->error;
                $tipoMensaje = "error";
            }
        } elseif ($action === 'save') {
           // Recoger datos
           $id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
           $marca = trim($_POST['marca'] ?? '');
           $titulo = trim($_POST['titulo'] ?? '');
           
           $descripcion = "";
           $imagen = "";
           
           $url = trim($_POST['url'] ?? '');
           if (empty($url)) {
               $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
               $url = $slug; 
           }

           if ($id > 0) {
               $stmt = $conexion->prepare("UPDATE productos SET marca=?, titulo=?, url=? WHERE id=?");
               $stmt->bind_param("sssi", $marca, $titulo, $url, $id);
           } else {
               $stmt = $conexion->prepare("INSERT INTO productos (marca, titulo, descripcion, imagen, url) VALUES (?, ?, ?, ?, ?)");
               $stmt->bind_param("sssss", $marca, $titulo, $descripcion, $imagen, $url);
           }

           if ($stmt->execute()) {
               $mensaje = "Producto guardado correctamente.";
               $tipoMensaje = "success";
           } else {
                if ($conexion->errno === 1062) {
                     $mensaje = "Error: Ya existe un producto con esa URL o título similar.";
                } else {
                     $mensaje = "Error al guardar: " . $conexion->error;
                }
               $tipoMensaje = "error";
           }
        } 
        // --- ACCIONES USUARIOS ---
        elseif ($action === 'delete_user' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            // Prevenir auto-borrado
            if ($id == $_SESSION['id']) {
                $mensaje = "No puedes borrar tu propia cuenta de administrador.";
                $tipoMensaje = "error";
            } else {
                $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $mensaje = "Usuario eliminado correctamente.";
                    $tipoMensaje = "success";
                } else {
                    $mensaje = "Error al eliminar usuario: " . $conexion->error;
                    $tipoMensaje = "error";
                }
            }
        }
       
        elseif ($action === 'delete_review' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $stmt = $conexion->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $mensaje = "Reseña eliminada correctamente.";
                $tipoMensaje = "success";
            } else {
                $mensaje = "Error al eliminar reseña: " . $conexion->error;
                $tipoMensaje = "error";
            }
        }
    }
}


$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$total_result = $conexion->query("SELECT COUNT(*) as total FROM productos");
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

$stmt_list = $conexion->prepare("SELECT * FROM productos ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt_list->bind_param("ii", $limit, $offset);
$stmt_list->execute();
$resultado = $stmt_list->get_result();

$res_users = $conexion->query("SELECT * FROM usuarios ORDER BY id DESC LIMIT 5");
$res_reviews = $conexion->query("SELECT r.*, u.usuario, p.titulo as producto_titulo FROM reviews r LEFT JOIN usuarios u ON r.user_id = u.id LEFT JOIN productos p ON r.product_id = p.id ORDER BY r.id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Fraganzza</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="admin-page">

    <div class="container">
        <header>
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:12px; height:12px; background:var(--accent); border-radius:50%;"></div>
                <h1>Panel Fraganzza</h1>
            </div>
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                <span class="btn-logout" style="cursor:default; color:white;">Hola, Admin</span>
                <a href="../index.php" class="btn-logout">Ir a Tienda ↗</a>
                <a href="logout.php" class="btn-logout" style="color:var(--danger)">Salir</a>
            </div>
        </header>

        <div class="notification <?php echo $tipoMensaje; ?>" style="display: <?php echo !empty($mensaje) ? 'block' : 'none'; ?>;">
            <?php echo $mensaje; ?>
        </div>

        <div class="grid">
            <!-- Columna Izquierda: Formulario -->
            <div class="card">
                <h2>Gestionar</h2>
                <form action="" method="POST" id="productForm">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="product_id" id="product_id" value="0">
                    <input type="hidden" name="url" id="url" value="">
                    
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" id="marca" required placeholder="Marca">
                    </div>
                    
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" name="titulo" id="titulo" required placeholder="Producto">
                    </div>

                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button type="submit" class="btn-primary" id="btn-save">Guardar</button>
                        <button type="button" class="btn-primary" id="btn-cancel" style="background: var(--bg-body); color: var(--text-primary); border: 1px solid var(--border); display: none;" onclick="resetForm()">✕</button>
                    </div>
                </form>
            </div>

            <!-- Columna Derecha: Tabla Productos -->
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                     <h2>Inventario (<?php echo $total_products; ?>)</h2>
                     <div class="pagination" style="margin-top:0;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="page-link">←</a>
                        <?php else: ?>
                            <span class="page-link disabled">←</span>
                        <?php endif; ?>
                        <span class="page-info" style="margin:0 10px;"><?php echo $page; ?>/<?php echo $total_pages > 0 ? $total_pages : 1; ?></span>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="page-link">→</a>
                        <?php else: ?>
                            <span class="page-link disabled">→</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table width="100%">
                        <thead>
                            <tr>
                                <th width="50">Imagen</th>
                                <th>Detalle</th>
                                <th width="80" style="text-align:right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if($row['imagen']): ?>
                                        <img src="<?php echo htmlspecialchars($row['imagen']); ?>" alt="img" class="product-img">
                                    <?php else: ?>
                                        <div class="product-img" style="display:flex;align-items:center;justify-content:center;color:#000;">•</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 500; color: #fff;"><?php echo htmlspecialchars($row['titulo']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><?php echo htmlspecialchars($row['marca']); ?></div>
                                </td>
                                <td style="text-align:right;">
                                    <button class="action-btn edit" onclick='editProduct(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>)' title="Editar">✎</button>
                                    <form action="" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="action-btn delete" title="Eliminar">✕</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Seccion Inferior: Usuarios y Reviews Lado a Lado -->
        <div class="grid-half">
            
            <!-- Lista de Usuarios -->
            <div class="card">
                <h2>Usuarios Recientes</h2>
                <div class="table-responsive">
                    <table width="100%">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th width="50" style="text-align:right;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($usr = $res_users->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 500; color: #fff; display:flex; align-items:center; gap:6px;">
                                        <div style="width:24px; height:24px; background: #334155; border-radius:50%; display:flex; justify-content:center; align-items:center; font-size:0.7rem; color:#fff;">
                                            <?php echo strtoupper(substr($usr['usuario'],0,1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($usr['usuario']); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-left:30px;"><?php echo htmlspecialchars($usr['email']); ?></div>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $usr['rol'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                        <?php echo htmlspecialchars($usr['rol']); ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <?php if($usr['id'] != $_SESSION['id']): ?>
                                    <form action="" method="POST" style="display:inline;" onsubmit="return confirm('¿Borrar?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?php echo $usr['id']; ?>">
                                        <button type="submit" class="action-btn delete" title="Borrar">✕</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lista de Reviews -->
            <div class="card">
                <h2>Últimas Reseñas</h2>
                <div class="table-responsive">
                    <table width="100%">
                        <thead>
                            <tr>
                                <th>Reseña</th>
                                <th width="50" style="text-align:right;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($rev = $res_reviews->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size: 0.8rem; color: var(--accent);"><?php echo htmlspecialchars($rev['producto_titulo'] ?? '-'); ?></span>
                                        <span style="color: gold; font-size: 0.75rem;">
                                            <?php echo str_repeat('★', (int)$rev['rating']); ?>
                                        </span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #e2e8f0; margin: 4px 0;">
                                        "<?php echo htmlspecialchars(substr($rev['comment'], 0, 60)); ?>..."
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        Por <?php echo htmlspecialchars($rev['usuario'] ?? 'Anonimo'); ?>
                                    </div>
                                </td>
                                <td style="text-align:right; vertical-align:top;">
                                    <form action="" method="POST" style="display:inline;" onsubmit="return confirm('¿Borrar?');">
                                        <input type="hidden" name="action" value="delete_review">
                                        <input type="hidden" name="id" value="<?php echo $rev['id']; ?>">
                                        <button type="submit" class="action-btn delete" title="Eliminar">✕</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> 
    </div> 

    <script>
        function editProduct(data) {
            document.getElementById('product_id').value = data.id;
            document.getElementById('marca').value = data.marca || '';
            document.getElementById('titulo').value = data.titulo || '';
            document.getElementById('url').value = data.url || ''; 

         
            document.getElementById('btn-save').innerText = 'Guardar';
            document.getElementById('btn-cancel').style.display = 'block';
            
        
            document.getElementById('titulo').focus();
        }

        function resetForm() {
            document.getElementById('productForm').reset();
            document.getElementById('product_id').value = 0;
            document.getElementById('btn-save').innerText = 'Guardar';
            document.getElementById('btn-cancel').style.display = 'none';
        }
    </script>
</body>
</html>