<?php
require_once __DIR__ . '/../config/helpers.php';
validarAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #FFFFFF 0%, #FFA500 50%, #FFFFFF 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, #FFA500 0%, #DDA0DD 100%);
        }

        /* HEADER */
        .page-header {
            background: linear-gradient(135deg, #FFA500 0%, #DDA0DD 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
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

        /* CARDS */
        .dashboard-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(221, 160, 221, 0.2);
            transition: 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(221, 160, 221, 0.3);
        }

        .card-title {
            font-weight: bold;
            color: #6b4a7f;
        }

        /* BOTONES */
        .btn-primary {
            background: linear-gradient(135deg, #FFA500, #FFD700);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, #FFD700, #DDA0DD);
            border: none;
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, #DDA0DD, #FFA500);
            border: none;
            color: white;
        }

        .btn-outline-secondary {
            color: white;
            border-color: rgba(255,255,255,0.6);
        }

        .btn-outline-secondary:hover {
            background-color: rgba(255,255,255,0.2);
        }

        /* FOOTER */
        footer {
            background: linear-gradient(135deg, #ff8c00 0%, #8a2be2 100%);
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }

        .footer-img {
            width: 100%;
            max-height: 120px;
            object-fit: cover;
            margin-top: 10px;
        }

        /* RESPONSIVO */
        @media (max-width: 768px) {
            .page-header {
                text-align: center;
            }

            .header-left {
                flex-direction: column;
            }

            .logo-header {
                width: 55px;
            }

            .btn {
                width: 100%;
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
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
         <a href="https://www.xonacatlan.gob.mx/" target="_blank">
               <img src="logo.jpeg" class="logo-header" title="Xonacatlan">

        <a class="navbar-brand fw-bold">Panel Admin</a>

        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">📊 Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="cursos.php">📚 Cursos</a></li>
                <li class="nav-item"><a class="nav-link" href="eventos.php">🎉 Eventos</a></li>
                <li class="nav-item"><a class="nav-link" href="inscripciones.php">✍️ Inscripciones</a></li>
                <li class="nav-item"><a class="nav-link" href="usuarios.php">👥 Usuarios</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">🚪 Salir</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- CONTENIDO -->
<main class="container mt-4">

    <div class="page-header">

        <div class="header-left">
            <!-- LOGO JUNTO AL TITULO -->
          
            </a>

            <div>
                <h1 class="m-0">📊 Panel de Control</h1>
                <p class="mb-0">Bienvenido, <strong><?= htmlspecialchars($_SESSION['admin_usuario']) ?></strong></p>
            </div>
        </div>

        <!-- BOTÓN ORIGINAL -->
        <a href="../public/index.php" class="btn btn-outline-secondary">Ver Sitio Público</a>

    </div>

    <div class="row">

        <div class="col-12 col-sm-6 col-lg-3 mb-4">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <h5 class="card-title">📚 Cursos</h5>
                    <p class="card-text">Gestionar cursos deportivos</p>
                    <a href="cursos.php" class="btn btn-primary">Ir a Cursos</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3 mb-4">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <h5 class="card-title">🎉 Eventos</h5>
                    <p class="card-text">Administrar eventos y multimedia</p>
                    <a href="eventos.php" class="btn btn-success">Ir a Eventos</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3 mb-4">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <h5 class="card-title">✍️ Inscripciones</h5>
                    <p class="card-text">Ver y gestionar inscripciones</p>
                    <a href="inscripciones.php" class="btn btn-warning">Ir a Inscripciones</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3 mb-4">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <h5 class="card-title">👥 Usuarios</h5>
                    <p class="card-text">Administrar usuarios del sistema</p>
                    <a href="usuarios.php" class="btn btn-info">Ir a Usuarios</a>
                </div>
            </div>
        </div>

    </div>

</main>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
