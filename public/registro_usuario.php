<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($nombre && $telefono) {
        // Validar que el teléfono tenga exactamente 10 dígitos
        if (!preg_match('/^\d{10}$/', $telefono)) {
            $error = 'El teléfono debe tener exactamente 10 dígitos.';
        } else {
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nombre = ? AND telefono = ?');
            $stmt->execute([$nombre, $telefono]);
            $usuario_existe = $stmt->fetch();

            if ($usuario_existe) {
                $error = 'Este usuario y teléfono ya están registrados. Intenta iniciar sesión.';
            } else {
                // Crear nuevo usuario
                $insert = $pdo->prepare('INSERT INTO usuarios (nombre, telefono) VALUES (?, ?)');
                $insert->execute([$nombre, $telefono]);
                
                // Obtener el usuario recién creado
                $stmt_nuevo = $pdo->prepare('SELECT * FROM usuarios WHERE nombre = ? AND telefono = ?');
                $stmt_nuevo->execute([$nombre, $telefono]);
                $usuario_nuevo = $stmt_nuevo->fetch();
                
                // Iniciar sesión automáticamente
                $_SESSION['usuario_id'] = $usuario_nuevo['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario_nuevo['nombre'];
                $_SESSION['usuario_telefono'] = $usuario_nuevo['telefono'];
                
                // Redirigir a inicio
                header('Location: index.php');
                exit;
            }
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
    <title>Registro - Depo Xona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
body { 
    background: linear-gradient(135deg, #FFF8E1 0%, #F0E6FF 100%); 
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

.card-header { 
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); 
    color: white; 
    border-radius: 12px 12px 0 0; 
}

.btn-primary { 
    background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%) !important; 
    border: none; 
}

.btn-primary:hover { 
    background: linear-gradient(135deg, #E69500 0%, #DDA0DD 100%) !important; 
}

.btn-success { 
    background: linear-gradient(135deg, #FFD700 0%, #DDA0DD 100%); 
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
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Depo Xona</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Registrarse como Usuario</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono (10 dígitos)</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" pattern="\d{10}" placeholder="1234567890" required>
                            <small class="text-muted d-block mt-1">Ingresa exactamente 10 dígitos numéricos</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                    </form>
                    
                    <hr>
                    <p class="text-muted text-center">¿Ya tienes cuenta? <br><a href="login_usuario.php">Inicia sesión aquí</a></p>
                </div>
            </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
