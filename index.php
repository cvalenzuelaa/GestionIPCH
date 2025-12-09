<?php
require_once('./app/sesiones/session.php');

$session = new Session();
$user = $session->getSession();

if (!isset($user['correo'])) {
    include_once('./app/viewer/login.php');
    exit();
}

// Vistas exclusivas para administrador
$paginasAdmin = ['dashadmin', 'usuarios', 'perfiladmin', 'actividades','gestionmiembros', 'tesoreria', 'inventario', 'alabanzas', 'oraciones', 'gestionusuarios', 'sermones', 'configuracion'];

// Vistas exclusivas para superusuario
$paginasSuperu = ['dashsuperu', 'actividadessuperu', 'alabanzassuperu', 'oracionessuperu', 'sermonessuperu', 'perfilsuperu'];

// Vistas exclusivas para usuario
$paginasUsuario = ['dashboard', 'miperfil', 'misactividades','misalabanzas', 'misoraciones', 'missermones'];

if (isset($user['rol']) && $user['rol'] === 'admin') {
    $nav = $_GET['nav'] ?? 'dashadmin';
    if (in_array($nav, $paginasAdmin)) {
        include_once('./app/viewer/' . $nav . '.php');
        exit();
    } else {
        include_once('./app/viewer/error/error.404.php');
        exit();
    }
} elseif (isset($user['rol']) && $user['rol'] === 'super') {
    $nav = $_GET['nav'] ?? 'dashsuperu';
    if (in_array($nav, $paginasSuperu)) {
        include_once('./app/viewer/' . $nav . '.php');
        exit();
    } else {
        include_once('./app/viewer/error/error.404.php');
        exit();
    }
} elseif (isset($user['rol'])) {
    $nav = $_GET['nav'] ?? 'dashboard';
    if (in_array($nav, $paginasUsuario)) {
        include_once('./app/viewer/' . $nav . '.php');
        exit();
    } else {
        include_once('./app/viewer/error/error.404.php');
        exit();
    }
} else {
    include_once('./app/viewer/error/error.404.php');
    exit();
}
?>