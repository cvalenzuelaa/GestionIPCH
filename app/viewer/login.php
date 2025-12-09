<!DOCTYPE html>
<?php
    require_once './app/sesiones/session.php';
    $obj = new Session();
    $sesion = $obj->getSession();
    if (isset($sesion['idusuario'])) {
        header('Location: /dashboard');
        exit;
    }
?>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="/assets/img/logo.ico" type="image/x-icon" />
    <link rel="stylesheet" href="/assets/css/styles.css" />
    <script src="/assets/js/globales.js"></script>
    <script defer src="/assets/js/login.js"></script>
    <title>Gestión IPCH</title>
</head>
<body class="login-page">
    <div class="container" id="container-login-form">
        <img src="/assets/img/logo.ico" alt="Logo Ipch" style="display:block; margin:0 auto 10px; width:130px; height:auto;" />
        <h1>Gestión IPCH</h1>
        <form id="form-login">
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" autocomplete="off" />
                <small id="correo-error" style="color:red; display:none">Correo inválido</small>
            </div>
            <div class="form-group">
                <label for="pass">Contraseña:</label>
                <input type="password" id="pass" name="pass" autocomplete="off" />
                <small id="pass-error" style="color:red; display:none">Contraseña inválida</small>
            </div>
            <button type="submit" class="btn-login" id="btn-login">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>