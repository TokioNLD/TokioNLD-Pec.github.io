<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($nombre && $telefono) {
        // Verificar si el usuario existe
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nombre = ? AND telefono = ?');
        $stmt->execute([$nombre, $telefono]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Usuario encontrado, iniciar sesión
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_telefono'] = $usuario['telefono'];
            header('Location: index.php');
            exit;
        } else {
            // Usuario no encontrado
            $error = 'Usuario o teléfono no encontrado. Verifica los datos.';
        }
    } else {
        $error = 'Completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <title>Iniciar Sesión - Depo Xona</title>
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

.btn-light { 
    background: linear-gradient(135deg, #FFF8E1 0%, #F0E6FF 100%); 
    color: #333; 
    border: 1px solid #DDA0DD; 
}

.form-control:focus { 
    border-color: #DDA0DD; 
    box-shadow: 0 0 0 0.2rem rgba(221, 160, 221, 0.25); 
}

.alert-danger { 
    background-color: #ffe6e6; 
    color: #cc0000; 
    border: 1px solid #FFA500; 
}

/* HEADER PERSONALIZADO (ANTES ERA VERDE) */
.card-header-custom {
    background: linear-gradient(135deg, #ff8c00 0%, #8a2be2 100%);
    color: white;
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
   </style>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a href="https://www.xonacatlan.gob.mx/" target="_blank">
               <img src="logo.jpeg" class="logo-header" title="Xonacatlan">
        <a class="navbar-brand" href="index.php">Depo Xona</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="cursos.php">Cursos</a></li>
                <li class="nav-item"><a class="nav-link" href="eventos.php">Eventos</a></li>
            </ul>
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#adminModal">Admin</button>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">

                <!-- 🔥 HEADER CAMBIADO -->
                <div class="card-header card-header-custom">
                    <h5 class="mb-0">Iniciar Sesión Usuario</h5>
                </div>

                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" required>
                        </div>

                        <!-- 🔥 BOTÓN CAMBIADO -->
                        <button type="submit" class="btn btn-primary w-100">
                            Iniciar Sesión
                        </button>
                    </form>

                    <hr>

                    <p class="text-muted text-center">
                        ¿Aún no estás registrado? <br>
                        <a href="registro_usuario.php">Regístrate aquí</a>
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Admin Login -->
<div class="modal fade" id="adminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Ingreso Administrador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="post" action="../admin/login.php">
                <div class="modal-body">
                    <input type="text" class="form-control mb-2" name="usuario" placeholder="Usuario" required>
                    <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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