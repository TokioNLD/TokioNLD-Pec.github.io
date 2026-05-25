<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

// Redirigir si no hay usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_usuario.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// Obtener inscripciones del usuario en cursos
$inscripciones_cursos = $pdo->prepare('
    SELECT i.*, c.nombre AS curso_nombre, c.requisitos, i.creado_en
    FROM inscripciones i
    LEFT JOIN cursos c ON i.id_curso = c.id_curso
    WHERE i.id_usuario = ?
    ORDER BY i.creado_en DESC
');
$inscripciones_cursos->execute([$usuario_id]);
$inscripciones_cursos = $inscripciones_cursos->fetchAll();

// Obtener inscripciones del usuario en eventos (a través de cursos relacionados)
$inscripciones_eventos = $pdo->prepare('
    SELECT DISTINCT e.*, c.nombre AS curso_nombre, i.creado_en
    FROM inscripciones i
    LEFT JOIN cursos c ON i.id_curso = c.id_curso
    LEFT JOIN eventos e ON e.id_curso = i.id_curso
    WHERE i.id_usuario = ?
    ORDER BY e.id_evento DESC
');
$inscripciones_eventos->execute([$usuario_id]);
$inscripciones_eventos = $inscripciones_eventos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <a href="https://www.xonacatlan.gob.mx/" target="_blank">
               <img src="logo.jpeg" class="logo-header" title="Xonacatlan">
    <title>Inscripción - Depo Xona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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

.btn-light { 
    background: linear-gradient(135deg, #FFF8E1 0%, #F0E6FF 100%); 
    color: #333; 
    border: 1px solid #DDA0DD; 
}

.table thead { 
    background: linear-gradient(135deg, #FFA500 0%, #DDA0DD 100%); 
    color: white; 
}

.table tbody tr:hover { 
    background-color: #FFF8E1; 
}

.form-control:focus { 
    border-color: #DDA0DD; 
    box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25); 
}

.container h2 { 
    color: #6b4a7f; 
    font-weight: 600; 
    margin: 30px 0 20px 0; 
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
                <li class="nav-item"><a class="nav-link" href="eventos.php">Eventos</a></li>
                <li class="nav-item"><a class="nav-link active" href="inscripcion.php">Inscripción</a></li>
            </ul>
            <div class="d-flex">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <span class="navbar-text me-2">Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <a href="logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
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
<div class="container">
    <h2>Mis Inscripciones</h2>
    
    <div class="mb-3">
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>

    <!-- Cursos -->
    <?php if (!empty($inscripciones_cursos)): ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <h4>📚 Cursos en los que estoy inscrito</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Curso</th>
                                <th>Requisitos</th>
                                <th>Costos</th>
                                <th>Fecha de Inscripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones_cursos as $inscripcion): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($inscripcion['curso_nombre'] ?? 'N/A') ?></strong></td>
                                    <td><?= nl2br(htmlspecialchars($inscripcion['requisitos'] ?? 'Sin requisitos')) ?></td>
                                    <td><?= htmlspecialchars($inscripcion['costos'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($inscripcion['creado_en']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Eventos -->
    <?php if (!empty($inscripciones_eventos) && $inscripciones_eventos[0]['id_evento'] !== null): ?>
        <div class="row mt-5">
            <div class="col-md-12">
                <h4>🎉 Eventos en los que estoy inscrito</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-success">
                            <tr>
                                <th>Evento</th>
                                <th>Curso Relacionado</th>
                                <th>Horario</th>
                                <th>Ubicación</th>
                                <th>Costos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones_eventos as $evento): ?>
                                <?php if ($evento['id_evento'] !== null): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($evento['nombre'] ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($evento['curso_nombre'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($evento['horario'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($evento['ubicacion'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($evento['costos'] ?? 'N/A') ?></td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sin inscripciones -->
    <?php if (empty($inscripciones_cursos) && (empty($inscripciones_eventos) || $inscripciones_eventos[0]['id_evento'] === null)): ?>
        <div class="alert alert-info mt-4">
            <p>No tienes inscripciones aún. <a href="cursos.php">Explora los cursos disponibles</a> o <a href="eventos.php">mira los eventos</a>.</p>
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