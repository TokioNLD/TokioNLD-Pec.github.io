<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/helpers.php';

validarAdmin();
$error = '';
$mensaje = '';
$uploadDir = __DIR__ . '/../assets/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir'])) {
    $descripcion = trim($_POST['descripcion']);
    $id_evento = (int)($_POST['id_evento'] ?? 0);

    if (!empty($_FILES['multimedia']['name']) && $id_evento) {
        $file = $_FILES['multimedia'];
        $allowed = ['image/jpeg','image/png','video/mp4','video/webm','application/pdf'];
        if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowed)) {
            $nombreArchivo = time() . '_' . basename($file['name']);
            $ruta = 'assets/uploads/' . $nombreArchivo;
            move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $ruta);
            $stmt = $pdo->prepare('INSERT INTO archivos (descripcion, multimedia, id_evento) VALUES (?,?,?)');
            $stmt->execute([$descripcion, $ruta, $id_evento]);
            $mensaje = 'Archivo subido con éxito.';
        } else {
            $error = 'Formato no permitido o error al subir.';
        }
    } else {
        $error = 'Seleccione un archivo y evento.';
    }
}

$eventos = $pdo->query('SELECT id_evento, nombre FROM eventos ORDER BY nombre')->fetchAll();
$archivos = $pdo->query('SELECT a.*, e.nombre AS evento FROM archivos a LEFT JOIN eventos e ON a.id_evento=e.id_evento ORDER BY a.id_archivo DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Archivos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container my-5">
    <h2>Subir Multimedia</h2>
    <p><a href="dashboard.php">Volver a Dashboard</a></p>
    <?php if ($error): ?><div class="alert alert-danger"><?= limpiarEntrada($error) ?></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="alert alert-success"><?= limpiarEntrada($mensaje) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mb-4">
        <div class="row"> 
            <div class="col-md-3 mb-2">
                <select name="id_evento" class="form-select" required>
                    <option value="">Selecciona evento</option>
                    <?php foreach ($eventos as $evento): ?>
                        <option value="<?= $evento['id_evento'] ?>"><?= htmlspecialchars($evento['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2"><input type="text" name="descripcion" class="form-control" placeholder="Descripción"></div>
            <div class="col-md-3 mb-2"><input type="file" name="multimedia" class="form-control" accept="image/*,video/*,.pdf" required></div>
            <div class="col-md-3 mb-2"><button name="subir" class="btn btn-primary">Subir</button></div>
        </div>
    </form>

    <h4>Archivos</h4>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Evento</th><th>Descripción</th><th>Archivo</th></tr></thead>
        <tbody>
        <?php foreach ($archivos as $archivo): ?>
            <tr>
                <td><?= $archivo['id_archivo'] ?></td>
                <td><?= htmlspecialchars($archivo['evento']) ?></td>
                <td><?= htmlspecialchars($archivo['descripcion']) ?></td>
                <td><a href="../<?= $archivo['multimedia'] ?>" target="_blank">Ver</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>