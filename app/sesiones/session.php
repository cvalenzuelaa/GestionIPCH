<?php
class Session {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($idusuario, $nombre, $apellido, $correo, $telefono, $rol, $avatar = null, $es_alabanza = 0) {
        $_SESSION['idusuario'] = $idusuario;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['correo'] = $correo;
        $_SESSION['telefono'] = $telefono;
        $_SESSION['rol'] = $rol;
        $_SESSION['avatar'] = $avatar;
        $_SESSION['es_alabanza'] = $es_alabanza;
    }

    public function getSession() {
        return isset($_SESSION['idusuario']) ? [
            'idusuario' => $_SESSION['idusuario'],
            'nombre' => $_SESSION['nombre'],
            'apellido' => $_SESSION['apellido'],
            'correo' => $_SESSION['correo'],
            'telefono' => $_SESSION['telefono'],
            'rol' => $_SESSION['rol'],
            'avatar' => $_SESSION['avatar'] ?? null,
            'es_alabanza' => $_SESSION['es_alabanza'] ?? 0
        ] : null;
    }

    public function updateSession($datos) {
        if (isset($datos['nombre'])) $_SESSION['nombre'] = $datos['nombre'];
        if (isset($datos['apellido'])) $_SESSION['apellido'] = $datos['apellido'];
        if (isset($datos['correo'])) $_SESSION['correo'] = $datos['correo'];
        if (isset($datos['telefono'])) $_SESSION['telefono'] = $datos['telefono'];
        if (isset($datos['avatar'])) $_SESSION['avatar'] = $datos['avatar'];
    }

    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada correctamente'];
    }

    public function isActive() {
        return isset($_SESSION['idusuario']);
    }
}
?>