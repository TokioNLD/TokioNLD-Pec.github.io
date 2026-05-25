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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear'])) {
        $nombre = trim($_POST['nombre']);
        $horario = trim($_POST['horario']);
        $costos = trim($_POST['costos']);
        $ubicacion = trim($_POST['ubicacion']);
        $descripcion = trim($_POST['descripcion']);
        $requisitos = trim($_POST['requisitos']);
        $id_curso = (int)$_POST['id_curso'];

        if ($nombre) {
            $stmt = $pdo->prepare('INSERT INTO eventos (nombre, horario, costos, ubicacion, descripcion, requisitos, id_curso) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$nombre, $horario, $costos, $ubicacion, $descripcion, $requisitos, $id_curso ?: null]);
            $id_evento = $pdo->lastInsertId();

            // Subir imagen si existe
            if (!empty($_FILES['imagen']['name'])) {
                $file = $_FILES['imagen'];
                $allowed = ['image/jpeg','image/png','image/gif'];
                if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowed)) {
                    $nombreArchivo = time() . '_' . basename($file['name']);
                    $ruta = 'assets/uploads/' . $nombreArchivo;
                    move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $ruta);
                    $pdo->prepare('INSERT INTO archivos (descripcion, multimedia, id_evento) VALUES (?,?,?)')->execute(['Imagen del evento', $ruta, $id_evento]);
                }
            }

            $mensaje = 'Evento creado con éxito.';
        } else {
            $error = 'El nombre del evento es obligatorio.';
        }
    }

    if (isset($_POST['editar'])) {
        $id_evento = (int)$_POST['id_evento'];
        $nombre = trim($_POST['nombre']);
        $horario = trim($_POST['horario']);
        $costos = trim($_POST['costos']);
        $ubicacion = trim($_POST['ubicacion']);
        $descripcion = trim($_POST['descripcion']);
        $requisitos = trim($_POST['requisitos']);
        $id_curso = (int)$_POST['id_curso'];

        if ($nombre) {
            $stmt = $pdo->prepare('UPDATE eventos SET nombre=?, horario=?, costos=?, ubicacion=?, descripcion=?, requisitos=?, id_curso=? WHERE id_evento=?');
            $stmt->execute([$nombre, $horario, $costos, $ubicacion, $descripcion, $requisitos, $id_curso ?: null, $id_evento]);
            
            // Subir nueva imagen solo si se selecciona una
            if (!empty($_FILES['imagen']['name'])) {
                $file = $_FILES['imagen'];
                $allowed = ['image/jpeg','image/png','image/gif'];
                if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowed)) {
                    $nombreArchivo = time() . '_' . basename($file['name']);
                    $ruta = 'assets/uploads/' . $nombreArchivo;
                    move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $ruta);
                    
                    // Eliminar imagen anterior si existe
                    $stmt_old = $pdo->prepare('SELECT multimedia FROM archivos WHERE id_evento = ? AND multimedia REGEXP "\\.(jpg|png|jpeg)$"');
                    $stmt_old->execute([$id_evento]);
                    $old_file = $stmt_old->fetch();
                    if ($old_file && $old_file['multimedia'] && file_exists(__DIR__ . '/../' . $old_file['multimedia'])) {
                        unlink(__DIR__ . '/../' . $old_file['multimedia']);
                        // Eliminar el registro de la imagen anterior
                        $pdo->prepare('DELETE FROM archivos WHERE id_evento = ? AND multimedia = ?')->execute([$id_evento, $old_file['multimedia']]);
                    }
                    
                    // Insertar la nueva imagen
                    $pdo->prepare('INSERT INTO archivos (descripcion, multimedia, id_evento) VALUES (?,?,?)')->execute(['Imagen del evento', $ruta, $id_evento]);
                }
            }
            
            $mensaje = 'Evento actualizado.';
        } else {
            $error = 'El nombre del evento es obligatorio.';
        }
    }
}

if (isset($_GET['borrar'])) {
    $id = (int)$_GET['borrar'];
    $pdo->prepare('DELETE FROM eventos WHERE id_evento = ?')->execute([$id]);
    $mensaje = 'Evento eliminado.';
}

$cursos = $pdo->query('SELECT id_curso, nombre FROM cursos ORDER BY nombre')->fetchAll();
$eventos = $pdo->query('SELECT e.*, c.nombre as curso_nombre FROM eventos e LEFT JOIN cursos c ON e.id_curso=c.id_curso ORDER BY e.id_evento DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
  :root {
    --naranja: #FFA500;
    --morado: #DDA0DD;
    --morado-claro: #F0E6FF;
    --naranja-claro: #FFF8E1;
}

body {
    background: linear-gradient(135deg, #FFFFFF 0%, #FFA500 50%, #FFFFFF 100%);
    min-height: 100vh;
}

.sidebar {
    background: linear-gradient(180deg, #FFA500 0%, #DDA0DD 100%);
    position: fixed;
    height: 100vh;
    top: 0;
    left: 0;
    padding: 0;
    box-shadow: 4px 0 15px rgba(221, 160, 221, 0.3);
}

.sidebar-sticky {
    padding: 20px 0;
    position: sticky;
    top: 0;
}

.sidebar h5 {
    font-weight: 700;
    padding: 15px 20px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

.sidebar .nav-link {
    padding: 12px 20px;
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
    color: rgba(255, 255, 255, 0.85);
}

.sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: #ffd699;
    color: white;
}

.sidebar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    border-left-color: #ffd699;
    color: white;
}

main {
    margin-left: 200px;
    padding: 30px;
}

.page-header {
    background: linear-gradient(135deg, #ff8c00 0%, #8a2be2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(138, 43, 226, 0.2);
}

.page-header h1 {
    font-weight: 700;
    margin: 0;
}

.alert {
    border-radius: 10px;
    border: none;
    margin-bottom: 25px;
}

.alert-danger {
    background-color: #ffe6e6;
    color: #cc0000;
    border: 1px solid #ff8c00;
}

.alert-success {
    background-color: #fff0e6;
    color: #8a2be2;
    border: 1px solid #ffb347;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(138, 43, 226, 0.2);
    margin-bottom: 25px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(138, 43, 226, 0.3);
}

.card-header {
    background: linear-gradient(135deg, #ffb347 0%, #b266ff 100%);
    border: none;
    border-radius: 12px 12px 0 0;
    padding: 20px;
    font-weight: 600;
    color: white;
}

.card-body {
    padding: 25px;
}

.card h5 {
    color: #6b4a7f;
    font-weight: 600;
}

.form-control, .form-control:focus {
    border-radius: 8px;
    border: 2px solid #d1a3ff;
    padding: 10px 15px;
}

.form-control:focus {
    border-color: #8a2be2;
    box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25);
    background-color: #fafafa;
}

.btn-primary {
    background: linear-gradient(135deg, #ff8c00 0%, #a64dff 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e67300 0%, #8a2be2 100%);
}

.btn-success {
    background: linear-gradient(135deg, #ffb347 0%, #b266ff 100%);
    border: none;
}

.btn-warning {
    background: linear-gradient(135deg, #ffb347 0%, #ff9933 100%);
    border: none;
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #ff6666 0%, #cc0000 100%);
    border: none;
}

.table {
    border-radius: 10px;
    overflow: hidden;
}

.table thead {
    background: linear-gradient(135deg, #ff8c00 0%, #8a2be2 100%);
    color: white;
}

.table tbody tr:hover {
    background-color: #fdf0e6;
}

#editarForm {
    border-top: 3px solid #ffb347;
    margin-top: 30px;
}

@media (max-width: 768px) {
    main {
        margin-left: 0;
        padding: 15px;
    }

    .sidebar {
        position: relative;
        height: auto;
        margin-bottom: 20px;
    }
}
footer {
    background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
    color: white;
    padding: 20px 0;
    margin-top: 40px;
}

footer a {
    color: white;
    margin: 0 10px;
    font-size: 22px;
    transition: 0.3s;
}

footer a:hover {
    color: #FFD700;
}
html, body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
}

.container, .container-fluid {
    flex: 1;
}

/* RESPONSIVO */
@media (max-width: 768px) {
    .page-header {
        text-align: center;
        padding: 20px;
    }

    .card-body {
        padding: 15px;
    }

    .table {
        font-size: 14px;
    }

    .btn {
        width: 100%;
        margin-bottom: 5px;
    }

    .form-control {
        font-size: 14px;
    }
}
   /* LOGO */
        .logo-header {
            width: 70px;
            transition: 0.3s;
            border-radius: 50%;
        }

        .logo-header:hover {
            transform: scale(1.1);
        }

    </style>
</head>
<body>
<!-- NAVBAR RESPONSIVA -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #ff8c00 0%, #8a2be2 100%);">
    <div class="container-fluid">
         <a href="https://www.xonacatlan.gob.mx/" target="_blank">
               <img src="logo.jpeg" class="logo-header" title="Xonacatlan">
        <a class="navbar-brand fw-bold" href="#">Panel Admin</a>

        <!-- Botón hamburguesa -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuAdmin">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">📊 Inicio</a></li>
                <li class="nav-item"><a class="nav-link active" href="cursos.php">📚 Cursos</a></li>
                <li class="nav-item"><a class="nav-link" href="eventos.php">🎉 Eventos</a></li>
                <li class="nav-item"><a class="nav-link" href="inscripciones.php">✍️ Inscripciones</a></li>
                <li class="nav-item"><a class="nav-link" href="usuarios.php">👥 Usuarios</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">🚪 Salir</a></li>
            </ul>
        </div>
    </div>
</nav>

        <!-- Main content -->
        <main class="col-md-10 ms-sm-auto col-lg-11 px-md-4">
            <div class="page-header">
                <h1>🎉 Gestión de Eventos</h1>
            </div>

            <?php if ($error): ?><div class="alert alert-danger"><?= limpiarEntrada($error) ?></div><?php endif; ?>
            <?php if ($mensaje): ?><div class="alert alert-success"><?= limpiarEntrada($mensaje) ?></div><?php endif; ?>

            <div class="card">
                <div class="card-header">
                    ➕ Crear Nuevo Evento
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Nombre</label>
                            <input class="form-control" name="nombre" placeholder="Nombre del evento" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Horario</label>
                            <input class="form-control" name="horario" placeholder="Ej: 10:00 AM">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Costos</label>
                            <input class="form-control" name="costos" placeholder="$0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Ubicación</label>
                            <input class="form-control" name="ubicacion" placeholder="Lugar del evento">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Curso Relacionado</label>
                            <select class="form-select" name="id_curso">
                                <option value="">Seleccionar curso (opcional)</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Imagen del Evento</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Descripción</label>
                            <textarea class="form-control" name="descripcion" placeholder="Descripción del evento" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Requisitos</label>
                            <textarea class="form-control" name="requisitos" placeholder="Requisitos (opcional)" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" name="crear">➕ Crear Evento</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    📋 Lista de Eventos
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Curso</th>
                                    <th>Horario</th>
                                    <th>Ubicación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($eventos as $evento): ?>
                                <tr>
                                    <td><strong>#<?= $evento['id_evento'] ?></strong></td>
                                    <td><?= htmlspecialchars($evento['nombre']) ?></td>
                                    <td><?= htmlspecialchars($evento['curso_nombre'] ?: 'Sin curso') ?></td>
                                    <td><?= htmlspecialchars($evento['horario']) ?></td>
                                    <td><?= htmlspecialchars($evento['ubicacion']) ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-warning" href="?editar_form=<?= $evento['id_evento'] ?>">✏️ Editar</a>
                                        <a class="btn btn-sm btn-danger" href="?borrar=<?= $evento['id_evento'] ?>" onclick="return confirm('¿Eliminar evento?')">🗑️ Borrar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['editar_form'])):
                $ev = $pdo->prepare('SELECT * FROM eventos WHERE id_evento = ?');
                $ev->execute([$_GET['editar_form']]);
                $eventoEdit = $ev->fetch();
                if ($eventoEdit):
            ?>
            <div class="card" style="margin-top: 30px; border-top: 3px solid #d8a8d8;">
                <div class="card-header">
                    ✏️ Editar Evento
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="id_evento" value="<?= $eventoEdit['id_evento'] ?>">
                        <div class="col-md-3">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Nombre</label>
                            <input class="form-control" name="nombre" value="<?= htmlspecialchars($eventoEdit['nombre']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Horario</label>
                            <input class="form-control" name="horario" value="<?= htmlspecialchars($eventoEdit['horario']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Costos</label>
                            <input class="form-control" name="costos" value="<?= htmlspecialchars($eventoEdit['costos']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Ubicación</label>
                            <input class="form-control" name="ubicacion" value="<?= htmlspecialchars($eventoEdit['ubicacion']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Curso Relacionado</label>
                            <select class="form-select" name="id_curso">
                                <option value="">Seleccionar curso (opcional)</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id_curso'] ?>" <?= $curso['id_curso'] == $eventoEdit['id_curso'] ? 'selected' : '' ?>><?= htmlspecialchars($curso['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"><?= htmlspecialchars($eventoEdit['descripcion']) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Requisitos</label>
                            <textarea class="form-control" name="requisitos" rows="2"><?= htmlspecialchars($eventoEdit['requisitos']) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Imagen del Evento</label>
                            <?php 
                            // Obtener la imagen actual del evento
                            $imgStmt = $pdo->prepare('SELECT multimedia FROM archivos WHERE id_evento = ? AND multimedia REGEXP "\\.(jpg|png|jpeg)$"');
                            $imgStmt->execute([$eventoEdit['id_evento']]);
                            $imgActual = $imgStmt->fetch();
                            if ($imgActual): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($imgActual['multimedia']) ?>" alt="Imagen actual" style="max-width: 200px; height: auto; border-radius: 8px;">
                                <p class="text-muted small mt-1">Imagen actual. Deja este campo vacío para conservarla.</p>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success" name="editar">✅ Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; endif; ?>
        </main>
    </div>
</div>
<td class="d-flex flex-column flex-md-row gap-2">
    <a class="btn btn-sm btn-warning" href="?editar_form=<?= $curso['id_curso'] ?>">✏️</a>
    <a class="btn btn-sm btn-danger" href="?borrar=<?= $curso['id_curso'] ?>" onclick="return confirm('¿Eliminar curso?')">🗑️</a>
</td>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<footer class="text-center">
    <div class="container">
        <p class="mb-2">Estado de México | Municipio de Xonacatlán</p>
        
        <!-- Iconos redes -->
        <div>
            <a href="https://www.facebook.com/share/18TW9ZU4Ys/" target="_blank">
                <i class="bi bi-facebook"></i>
            </a>
            <a href="https://www.instagram.com/sporthousexonacatlan?igsh=eW93a2ZuNjRybW9y/" target="_blank">
                <i class="bi bi-instagram"></i>
            </a>
            <a href="https://wa.me/5210000000000" target="_blank">
                <i class="bi bi-whatsapp"></i>
            </a>
        </div>

        <p class="mt-2 mb-0">© 2026 Dirección de Deportes Xonacatlán</p>
        <p class="mt-1 mb-0" style="font-size: 12px;">Auntamiento 2025-2027</p>
    </div>
</footer>
</body>
</html>