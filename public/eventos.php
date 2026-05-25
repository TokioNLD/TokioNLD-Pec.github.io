<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$archivos_evento = [];
if ($id) {
    $stmt = $pdo->prepare('SELECT e.*, c.nombre AS curso FROM eventos e LEFT JOIN cursos c ON e.id_curso=c.id_curso WHERE id_evento = ?');
    $stmt->execute([$id]);
    $evento = $stmt->fetch();
    if (!$evento) {
        header('Location: index.php');
        exit;
    }
    // Obtener archivos multimedia del evento
    $archivos_evento = $pdo->prepare('SELECT * FROM archivos WHERE id_evento = ? ORDER BY id_archivo DESC');
    $archivos_evento->execute([$id]);
    $archivos_evento = $archivos_evento->fetchAll();
}

$eventos = $pdo->query('SELECT * FROM eventos ORDER BY id_evento DESC')->fetchAll();
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
    $id_evento = (int)($_POST['id_evento'] ?? 0);

    if ($id_curso && $usuario_id) {
        // Obtener requisitos y costos del evento
        $stmt_evento = $pdo->prepare('SELECT requisitos, costos FROM eventos WHERE id_evento = ?');
        $stmt_evento->execute([$id_evento]);
        $evento_data = $stmt_evento->fetch();
        
        $requisitos = $evento_data['requisitos'] ?? '';
        $costos = $evento_data['costos'] ?? '';

        $stmt = $pdo->prepare('INSERT INTO inscripciones (id_curso, id_usuario, requisitos, costos) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id_curso, $usuario_id, $requisitos, $costos]);
        $mensaje_inscripcion = 'Inscripción guardada. Gracias.';
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
    <title>Eventos - Depo Xona</title>
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
                <li class="nav-item"><a class="nav-link" href="cursos.php">Cursos</a></li>
                <li class="nav-item"><a class="nav-link active" href="eventos.php">Eventos</a></li>
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

<!-- Modal Usuario Login -->
<div class="modal fade" id="usuarioModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="usuarioModalLabel">Ingreso Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="login_usuario.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Entrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
<?php if ($id && isset($evento)): ?>
<div class="modal fade" id="inscripcionModal" tabindex="-1" aria-labelledby="inscripcionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="inscripcionModalLabel">Inscribirse al evento: <?= htmlspecialchars($evento['nombre']) ?></h5>
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
                            <label for="evento_modal" class="form-label">Evento</label>
                            <input type="text" class="form-control" id="evento_modal" value="<?= htmlspecialchars($evento['nombre']) ?>" disabled>
                            <input type="hidden" name="id_evento" value="<?= $evento['id_evento'] ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="curso_modal" class="form-label">Curso</label>
                            <select class="form-select" id="curso_modal" name="id_curso" required>
                                <option value="">Selecciona curso</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id_curso'] ?>" <?= $curso['id_curso'] == $evento['id_curso'] ? 'selected' : '' ?>><?= htmlspecialchars($curso['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="requisitos_modal" class="form-label">Requisitos</label>
                            <textarea class="form-control" id="requisitos_modal" name="requisitos" rows="3" readonly><?= htmlspecialchars($evento['requisitos'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="costos_modal" class="form-label">Costos</label>
                            <input type="text" class="form-control" id="costos_modal" name="costos" value="<?= htmlspecialchars($evento['costos']) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="inscribirse" value="1">Aceptar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <h2>Eventos</h2>
    <div class="row">
        <div class="col-md-4">
            <ul class="list-group">
                <?php foreach ($eventos as $e): ?>
                    <li class="list-group-item <?= $id === (int)$e['id_evento'] ? 'active' : '' ?>">
                        <a class="<?= $id === (int)$e['id_evento'] ? 'text-white' : '' ?>" href="eventos.php?id=<?= $e['id_evento'] ?>"><?= htmlspecialchars($e['nombre']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-8">
            <?php if ($id): ?>
                <h3><?= htmlspecialchars($evento['nombre']) ?></h3>
                <p><strong>Curso:</strong> <?= htmlspecialchars($evento['curso'] ?? 'N/A') ?></p>
                <p><strong>Horario:</strong> <?= htmlspecialchars($evento['horario']) ?></p>
                <p><strong>Costos:</strong> <?= htmlspecialchars($evento['costos']) ?></p>
                <p><strong>Ubicación:</strong> <?= htmlspecialchars($evento['ubicacion']) ?></p>
                <p><?= nl2br(htmlspecialchars($evento['descripcion'])) ?></p>
                <?php if ($archivos_evento): ?>
                    <div class="row">
                        <?php foreach ($archivos_evento as $archivo): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><?= htmlspecialchars($archivo['descripcion'] ?: 'Archivo') ?></h6>
                                        <?php if (preg_match('/\.(jpeg|jpg|png|gif)$/i', $archivo['multimedia'])): ?>
                                            <img src="../<?= $archivo['multimedia'] ?>" class="img-fluid" alt="Imagen del evento">
                                        <?php elseif (preg_match('/\.(mp4|webm)$/i', $archivo['multimedia'])): ?>
                                            <video controls class="w-100" src="../<?= $archivo['multimedia'] ?>"></video>
                                        <?php else: ?>
                                            <a href="../<?= $archivo['multimedia'] ?>" target="_blank" class="btn btn-primary">Descargar archivo</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#inscripcionModal">Inscribirse</button>
                <?php else: ?>
                    <p class="text-muted">Inicia sesión para inscribirte.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Selecciona un evento para ver detalle.</p>
            <?php endif; ?>
        </div>
    </div>
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