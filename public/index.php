<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$cursos = $pdo->query('SELECT * FROM cursos ORDER BY nombre')->fetchAll();
$eventos = $pdo->query('SELECT e.*, c.nombre AS curso FROM eventos e LEFT JOIN cursos c ON e.id_curso=c.id_curso ORDER BY e.id_evento DESC')->fetchAll();
$multimedia = $pdo->query('SELECT a.*, e.nombre AS evento FROM archivos a LEFT JOIN eventos e ON a.id_evento=e.id_evento ORDER BY a.id_archivo DESC')->fetchAll();

// Obtener eventos con imágenes para el carrusel
$eventos_con_imagenes = $pdo->query('
    SELECT e.*, a.multimedia, c.nombre AS curso
    FROM eventos e
    LEFT JOIN archivos a ON e.id_evento = a.id_evento
    LEFT JOIN cursos c ON e.id_curso = c.id_curso
    WHERE a.multimedia REGEXP "\\.(jpg|png|jpeg)$"
    ORDER BY e.id_evento DESC
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Depo Xona - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { 
    background: linear-gradient(135deg, #FFFFFF 0%, #FFA500 50%, #FFFFFF 100%); /* White and orange background */
    min-height: 100vh; 
    position: relative;
    z-index: 1;
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

.btn-info:hover { 
    background: linear-gradient(135deg, #E6C300 0%, #DDA0DD 100%); 
}

.btn-light { 
    background: linear-gradient(135deg, #FFF8E1 0%, #F0E6FF 100%); 
    color: #333; 
    border: 1px solid #DDA0DD; 
}

.form-control:focus { 
    border-color: #DDA0DD; 
    box-shadow: 0 0 0 0.2rem rgba(221, 160, 221, 0.25); 
}

/* Estilos del Carrusel de Fondo */
.carrusel-fondo {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
    opacity: 1.0; /* Completamente visible para verificar funcionamiento */
    overflow: hidden;
    pointer-events: none; /* No interfiere con clics */
}

/* Indicador temporal del carrusel */

.carrusel-fondo .carousel-item img {
    transition: opacity 2s ease;
    object-fit: cover;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
}

.carrusel-fondo .carousel-caption {
    display: none; /* Ocultar captions en el fondo */
}

.carrusel-fondo .carousel-control-prev,
.carrusel-fondo .carousel-control-next,
.carrusel-fondo .carousel-indicators {
    display: none; /* Ocultar controles en el fondo */
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
    position: relative;
    z-index: 2;
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
                <li class="nav-item"><a class="nav-link active" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="cursos.php">Cursos</a></li>
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

<!-- Carrusel de Fondo -->
<div class="carrusel-fondo">
    <div id="imagenesCarrusel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="logo.jpeg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'">
            </div>
            <div class="carousel-item">
                <img src="pie.jpeg" alt="Instalaciones" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'">
            </div>
            <div class="carousel-item">
                <img src="../assets/uploads/1774385212_IMG-20251029-WA0012.jpg" alt="Deportes Xonacatlán" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</div>



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
<div class="container">
    <h1>Bienvenido a Dirección de Deportes Xonacatlán</h1>
    <p>Explora cursos, eventos y multimedia en un solo lugar.</p>

    <h2>Cursos disponibles</h2>
    <?php if ($cursos): ?>
        <div class="row">
            <?php foreach ($cursos as $curso): ?>
                <div class="col-md-12 mb-3">

                    <div class="info-curso-box">

                        <div class="info-texto">
                            <h5><?= htmlspecialchars($curso['nombre']) ?></h5>
                            <p>Modalidad: <?= htmlspecialchars($curso['modalidad']) ?></p>
                            <p>Horario: <?= htmlspecialchars($curso['horario']) ?></p>
                            <p><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>

                            <a class="btn btn-sm btn-primary" href="cursos.php?id=<?= $curso['id_curso'] ?>">
                                Ver detalles
                            </a>
                        </div>

                        <?php if (!empty($curso['imagen'])): ?>
                            <img src="../<?= $curso['imagen'] ?>" class="info-img">
                        <?php else: ?>
                            <img src="logo.jpeg" class="info-img">
                        <?php endif; ?>

                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No hay cursos aún.</p>
    <?php endif; ?>

    <h2>Próximos eventos</h2>
    <?php if ($eventos_con_imagenes): ?>
        <div class="row">
            <?php foreach ($eventos_con_imagenes as $evento): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 cursor-pointer" onclick="location.href='eventos.php?id=<?= $evento['id_evento'] ?>'" style="cursor: pointer; transition: transform 0.3s, box-shadow 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(138, 43, 226, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(138, 43, 226, 0.2)'">
                        <img src="../<?= $evento['multimedia'] ?>" class="card-img-top" alt="<?= htmlspecialchars($evento['nombre']) ?>" style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($evento['nombre']) ?></h5>
                            <p class="card-text"><strong>Curso:</strong> <?= htmlspecialchars($evento['curso'] ?? 'Sin curso') ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No hay eventos con imágenes aún.</p>
    <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script>
// Inicializar carrusel de fondo
document.addEventListener('DOMContentLoaded', function() {
    // El carrusel se inicializa automáticamente con data-bs-ride
    const carousel = document.getElementById('imagenesCarrusel');
    if (carousel) {
        console.log('Carrusel de fondo inicializado correctamente');
    }
});
</script>
<!-- Footer -->
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
