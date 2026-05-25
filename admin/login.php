<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/helpers.php';

iniciarSesion();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario && $password) {
        $stmt = $pdo->prepare('SELECT * FROM administradores WHERE usuario = ?');
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Credenciales inválidas.';
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-unknown" crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Ingreso Administrador</h3>
                    <?php if ($error): ?><div class="alert alert-danger"><?= limpiarEntrada($error) ?></div><?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input class="form-control" type="text" name="usuario" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Entrar</button>
                    </form>
                    <p class="mt-2 text-muted small">Usuario: Evan | Contraseña: 1234</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
