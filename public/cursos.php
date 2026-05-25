<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM cursos WHERE id_curso = ?');
    $stmt->execute([$id]);
    $curso = $stmt->fetch();
    if (!$curso) {
        header('Location: index.php');
        exit;
    }
}

$cursos = $pdo->query('SELECT * FROM cursos ORDER BY nombre')->fetchAll();

// Obtener datos del usuario si está logueado
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : '';
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '';
$usuario_telefono = '';

// Obtener el teléfono del usuario logueado
if ($usuario_id) {
    $stmt_usuario = $pdo->prepare('SELECT telefono FROM usuarios WHERE id_usuario = ?');
    $stmt_usuario->execute([$usuario_id]);
    $usuario_data = $stmt_usuario->fetch();
    if ($usuario_data) {
        $usuario_telefono = $usuario_data['telefono'] ?? '';
    }
}

// Procesar inscripción desde el modal
$mensaje_inscripcion = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscribirse'])) {
    $id_curso = (int)($_POST['id_curso'] ?? 0);

    if ($id_curso && $usuario_id) {
        // Obtener requisitos y costos del curso
        $stmt_curso = $pdo->prepare('SELECT requisitos FROM cursos WHERE id_curso = ?');
        $stmt_curso->execute([$id_curso]);
        $curso_data = $stmt_curso->fetch();
        
        $requisitos = $curso_data['requisitos'] ?? '';
        $costos = '';

        // Verificar si ya está inscrito
        $stmt_check = $pdo->prepare('SELECT * FROM inscripciones WHERE id_curso = ? AND id_usuario = ?');
        $stmt_check->execute([$id_curso, $usuario_id]);
        $ya_inscrito = $stmt_check->fetch();

        if ($ya_inscrito) {
            $mensaje_inscripcion = 'Ya estás inscrito en este curso.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO inscripciones (id_curso, id_usuario, requisitos, costos) VALUES (?, ?, ?, ?)');
            $stmt->execute([$id_curso, $usuario_id, $requisitos, $costos]);
            $mensaje_inscripcion = 'Inscripción guardada. Gracias.';
        }
    } else {
        $mensaje_inscripcion = 'Error en la inscripción.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cursos - Depo Xona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { 
    background: linear-gradient(135deg, #FFFFFF 0%, #FFA500 50%, #FFFFFF 100%); 
    min-height: 100vh; 
}

.navbar.navbar-dark.bg-primary { 
    background: linear-gradient(135deg, #FFA500 0%, #DDA0DD 100%) !important; 
}

.card { 
    border: none; 
    border-radius: 12px; 
    box-shadow: 0 2px 10px rgba(221, 160, 221, 0.2); 
}

.btn-primary { 
    background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%) !important; 
    border: none; 
}

.btn-primary:hover { 
    background: linear-gradient(135deg, #E69500 0%, #DDA0DD 100%) !important; 
}

.btn-info { 
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); 
    border: none; 
}

.btn-light { 
    background: linear-gradient(135deg, #fff0e6 0%, #f3d9ff 100%); 
    color: #333; 
    border: 1px solid #d1a3ff; 
}

.form-control:focus { 
    border-color: #8a2be2; 
    box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25); 
}

.alert-success { 
    background-color: #fff0e6; 
    color: #8a2be2; 
    border: 1px solid #ffb347; 
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
    /* LOGO */
        .logo-header {
            width: 70px;
            transition: 0.3s;
            border-radius: 50%;
        }

        .logo-header:hover {
            transform: scale(1.1);
        }
        .info-curso-box {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: flex;
    gap: 20px;
    align-items: center;
    margin-bottom: 20px;
}

.info-texto {
    flex: 1;
}

.info-img {
    width: 280px;
    border-radius: 12px;
    object-fit: cover;
}
</style>
</head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a href="https://www.xonacatlan.gob.mx/" target="_blank">
               <img src="logo.jpeg" class="logo-header" title="Xonacatlan">
        <a class="navbar-brand" href="index.php">Depo Xona</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link active" href="cursos.php">Cursos</a></li>
                <li class="nav-item"><a class="nav-link" href="eventos.php">Eventos</a></li>
                <li class="nav-item"><a class="nav-link" href="inscripcion.php">Inscripción</a></li>
            </ul>
            <div class="d-flex">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <span class="navbar-text me-2">Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <a href="inscripcion.php" class="btn btn-info me-2">Mis Inscripciones</a>
                    <a href="logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login_usuario.php" class="btn btn-outline-light me-2">Iniciar Sesión</a>
                <?php endif; ?>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#adminModal">Admin</button>
            </div>
        </div>
    </div>
</nav>

<!-- Modal Admin Login -->
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="adminModalLabel">Ingreso Administrador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="../admin/login.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <p class="text-muted small">Usuario: Evan | Contraseña: 1234</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Inscripción -->
<?php if ($id && isset($curso)): ?>
<div class="modal fade" id="inscripcionModal" tabindex="-1" aria-labelledby="inscripcionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="inscripcionModalLabel">Inscribirse al curso: <?= htmlspecialchars($curso['nombre']) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" class="needs-validation" novalidate>
                <div class="modal-body">
                    <?php if ($mensaje_inscripcion): ?>
                        <div class="alert alert-info"><?= htmlspecialchars($mensaje_inscripcion) ?></div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_modal" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre_modal" name="nombre" value="<?= htmlspecialchars($usuario_nombre) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_modal" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono_modal" name="telefono" value="<?= htmlspecialchars($usuario_telefono) ?>" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="curso_modal" class="form-label">Curso</label>
                            <input type="text" class="form-control" id="curso_modal" value="<?= htmlspecialchars($curso['nombre']) ?>" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="modalidad_modal" class="form-label">Modalidad</label>
                            <input type="text" class="form-control" id="modalidad_modal" value="<?= htmlspecialchars($curso['modalidad']) ?>" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="horario_modal" class="form-label">Horario</label>
                            <input type="text" class="form-control" id="horario_modal" value="<?= htmlspecialchars($curso['horario']) ?>" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="requisitos_modal" class="form-label">Requisitos</label>
                            <textarea class="form-control" id="requisitos_modal" name="requisitos" rows="3" readonly><?= htmlspecialchars($curso['requisitos'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="inscribirse" value="1">Aceptar</button>
                    <input type="hidden" name="id_curso" value="<?= $curso['id_curso'] ?>">
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="container">
    <h2>Cursos</h2>
    
    <!-- Grid de cursos con imágenes -->
    <div class="row mb-4">
        <?php foreach ($cursos as $c): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 cursor-pointer" onclick="location.href='cursos.php?id=<?= $c['id_curso'] ?>'" style="cursor: pointer; transition: transform 0.3s, box-shadow 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(138, 43, 226, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(138, 43, 226, 0.2)'">
                    <?php if (!empty($c['imagen'])): ?>
                        <img src="../<?= $c['imagen'] ?>" class="card-img-top" alt="<?= htmlspecialchars($c['nombre']) ?>" style="height: 250px; object-fit: cover;">
                    <?php else: ?>
                        <img src="logo.jpeg" class="card-img-top" alt="<?= htmlspecialchars($c['nombre']) ?>" style="height: 250px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($c['nombre']) ?></h5>
                        <p class="card-text"><strong>Modalidad:</strong> <?= htmlspecialchars($c['modalidad']) ?></p>
                        <p class="card-text"><strong>Horario:</strong> <?= htmlspecialchars($c['horario']) ?></p>
                        <p class="card-text text-truncate"><?= htmlspecialchars($c['descripcion']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Detalles del curso seleccionado -->
    <?php if ($id): ?>
        <div class="row mt-4">
            <div class="col-12">
                <h3>Detalles del curso</h3>
                <div class="info-curso-box">

                    <div class="info-texto">
                        <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
                        <p><strong>Modalidad:</strong> <?= htmlspecialchars($curso['modalidad']) ?></p>
                        <p><strong>Horario:</strong> <?= htmlspecialchars($curso['horario']) ?></p>
                        <p><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>

                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#inscripcionModal">
                                Inscribirse
                            </button>
                        <?php else: ?>
                            <p class="text-muted">Inicia sesión para inscribirte.</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($curso['imagen'])): ?>
                        <img src="../<?= $curso['imagen'] ?>" class="info-img">
                    <?php else: ?>
                        <img src="logo.jpeg" class="info-img">
                    <?php endif; ?>

                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
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
