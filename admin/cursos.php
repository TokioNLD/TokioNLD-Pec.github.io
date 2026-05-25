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
        $modalidad = trim($_POST['modalidad']);
        $horario = trim($_POST['horario']);
        $descripcion = trim($_POST['descripcion']);
        $requisitos = trim($_POST['requisitos']);

        if ($nombre && $modalidad && $horario) {
            // Subir imagen si existe
            $imagen = '';
            if (!empty($_FILES['imagen']['name'])) {
                $file = $_FILES['imagen'];
                $allowed = ['image/jpeg','image/png','image/gif'];
                if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowed)) {
                    $nombreArchivo = time() . '_' . basename($file['name']);
                    $imagen = 'assets/uploads/' . $nombreArchivo;
                    move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $imagen);
                }
            }

            $stmt = $pdo->prepare('INSERT INTO cursos (nombre, modalidad, horario, descripcion, requisitos, imagen) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$nombre, $modalidad, $horario, $descripcion, $requisitos, $imagen]);
            $mensaje = 'Curso creado con éxito.';
        } else {
            $error = 'Todos los campos obligatorios deben estar completos.';
        }
    }

    if (isset($_POST['editar'])) {
        $id = (int)$_POST['id_curso'];
        $nombre = trim($_POST['nombre']);
        $modalidad = trim($_POST['modalidad']);
        $horario = trim($_POST['horario']);
        $descripcion = trim($_POST['descripcion']);
        $requisitos = trim($_POST['requisitos']);
        if ($id && $nombre && $modalidad && $horario) {
            // Obtener la imagen actual del curso
            $stmt_current = $pdo->prepare('SELECT imagen FROM cursos WHERE id_curso = ?');
            $stmt_current->execute([$id]);
            $current_curso = $stmt_current->fetch();
            $imagen = $current_curso['imagen'] ?? '';
            
            // Subir nueva imagen solo si se selecciona una
            if (!empty($_FILES['imagen']['name'])) {
                $file = $_FILES['imagen'];
                $allowed = ['image/jpeg','image/png','image/gif'];
                if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowed)) {
                    $nombreArchivo = time() . '_' . basename($file['name']);
                    $imagen = 'assets/uploads/' . $nombreArchivo;
                    move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $imagen);
                    
                    // Eliminar imagen anterior si existe
                    if ($current_curso['imagen'] && file_exists(__DIR__ . '/../' . $current_curso['imagen'])) {
                        unlink(__DIR__ . '/../' . $current_curso['imagen']);
                    }
                }
            }
            
            $stmt = $pdo->prepare('UPDATE cursos SET nombre = ?, modalidad = ?, horario = ?, descripcion = ?, requisitos = ?, imagen = ? WHERE id_curso = ?');
            $stmt->execute([$nombre, $modalidad, $horario, $descripcion, $requisitos, $imagen, $id]);
            $mensaje = 'Curso actualizado con éxito.';
        } else {
            $error = 'Todos los campos obligatorios deben estar completos.';
        }
    }
}

if (isset($_GET['borrar'])) {
    $id = (int)$_GET['borrar'];
    $stmt = $pdo->prepare('DELETE FROM cursos WHERE id_curso = ?');
    $stmt->execute([$id]);
    $mensaje = 'Curso eliminado.';
}

$cursos = $pdo->query('SELECT * FROM cursos ORDER BY id_curso DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Cursos</title>
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
                <h1>📚 Gestión de Cursos</h1>
            </div>

            <?php if ($error): ?><div class="alert alert-danger"><?= limpiarEntrada($error) ?></div><?php endif; ?>
            <?php if ($mensaje): ?><div class="alert alert-success"><?= limpiarEntrada($mensaje) ?></div><?php endif; ?>

            <div class="card">
                <div class="card-header">
                    ➕ Crear Nuevo Curso
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Nombre</label>
                            <input class="form-control" type="text" name="nombre" placeholder="Nombre del curso" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Modalidad</label>
                            <input class="form-control" type="text" name="modalidad" placeholder="Presencial / Virtual" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Horario</label>
                            <input class="form-control" type="text" name="horario" placeholder="Ej: Lunes a Viernes" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Descripción</label>
                            <textarea class="form-control" name="descripcion" placeholder="Descripción del curso" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Requisitos</label>
                            <textarea class="form-control" name="requisitos" placeholder="Requisitos (opcional)" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Imagen del Curso</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" name="crear">➕ Crear Curso</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    📋 Lista de Cursos
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Modalidad</th>
                                    <th>Horario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cursos as $curso): ?>
                                <tr>
                                    <td><strong>#<?= $curso['id_curso'] ?></strong></td>
                                    <td><?= htmlspecialchars($curso['nombre']) ?></td>
                                    <td><?= htmlspecialchars($curso['modalidad']) ?></td>
                                    <td><?= htmlspecialchars($curso['horario']) ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-warning" href="?editar_form=<?= $curso['id_curso'] ?>">✏️ Editar</a>
                                        <a class="btn btn-sm btn-danger" href="?borrar=<?= $curso['id_curso'] ?>" onclick="return confirm('¿Eliminar curso?')">🗑️ Borrar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['editar_form'])):
                $cursoEdit = $pdo->prepare('SELECT * FROM cursos WHERE id_curso = ?');
                $cursoEdit->execute([$_GET['editar_form']]);
                $cursoEdit = $cursoEdit->fetch();
                if ($cursoEdit):
            ?>
            <div class="card" style="margin-top: 30px; border-top: 3px solid #d8a8d8;">
                <div class="card-header">
                    ✏️ Editar Curso
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="id_curso" value="<?= $cursoEdit['id_curso'] ?>">
                        <div class="col-md-4">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Nombre</label>
                            <input class="form-control" type="text" name="nombre" value="<?= htmlspecialchars($cursoEdit['nombre']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Modalidad</label>
                            <input class="form-control" type="text" name="modalidad" value="<?= htmlspecialchars($cursoEdit['modalidad']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Horario</label>
                            <input class="form-control" type="text" name="horario" value="<?= htmlspecialchars($cursoEdit['horario']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"><?= htmlspecialchars($cursoEdit['descripcion']) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Requisitos</label>
                            <textarea class="form-control" name="requisitos" rows="2"><?= htmlspecialchars($cursoEdit['requisitos']) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #6b4a7f; font-weight: 600;">Imagen del Curso</label>
                            <?php if (!empty($cursoEdit['imagen'])): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($cursoEdit['imagen']) ?>" alt="Imagen actual" style="max-width: 200px; height: auto; border-radius: 8px;">
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