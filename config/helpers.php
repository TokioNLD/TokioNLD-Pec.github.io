<?php
function iniciarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function validarAdmin() {
    iniciarSesion();
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function limpiarEntrada($valor) {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}
