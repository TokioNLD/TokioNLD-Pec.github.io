<?php
// Conexión PDO para MySQL
$host = 'localhost';
$usuario = 'root';
$pass = '';
$db = 'depo_xona';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $usuario, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '1049') { // Unknown database
        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $usuario, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $pdo->exec("USE `$db`;");

            // Crea tablas básicas si no existen
            $pdo->exec("CREATE TABLE IF NOT EXISTS cursos (
                id_curso INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(150) NOT NULL,
                modalidad VARCHAR(80) NOT NULL,
                horario VARCHAR(120) NOT NULL,
                descripcion TEXT
            ) ENGINE=InnoDB;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
                id_usuario INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(150) NOT NULL,
                telefono VARCHAR(20)
            ) ENGINE=InnoDB;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS inscripciones (
                id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
                requisitos TEXT,
                costos VARCHAR(80),
                id_curso INT NOT NULL,
                id_usuario INT NOT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE,
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
            ) ENGINE=InnoDB;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS eventos (
                id_evento INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(150) NOT NULL,
                horario VARCHAR(120),
                costos VARCHAR(80),
                ubicacion VARCHAR(150),
                descripcion TEXT,
                id_curso INT,
                FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE SET NULL
            ) ENGINE=InnoDB;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS archivos (
                id_archivo INT AUTO_INCREMENT PRIMARY KEY,
                descripcion VARCHAR(255),
                multimedia VARCHAR(255),
                id_evento INT,
                FOREIGN KEY (id_evento) REFERENCES eventos(id_evento) ON DELETE CASCADE
            ) ENGINE=InnoDB;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS administradores (
                id_admin INT AUTO_INCREMENT PRIMARY KEY,
                usuario VARCHAR(100) UNIQUE,
                password VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB;");

            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM administradores WHERE usuario = ?');
            $stmt->execute(['Evan']);
            $row = $stmt->fetch();
            if ($row['total'] == 0) {
                $passwordHash = password_hash('1234', PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO administradores (usuario,password) VALUES (?,?)')->execute(['Evan', $passwordHash]);
            }
        } catch (PDOException $e2) {
            die('Error en creación de BD: ' . $e2->getMessage());
        }
    } else {
        die('Error de BD: ' . $e->getMessage());
    }
}

