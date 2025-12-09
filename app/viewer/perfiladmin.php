<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario'])) { header('Location: /login'); exit; }

// LÓGICA DE IMAGEN ROBUSTA
$avatarDb = $sesion['avatar'] ?? '';
if (!empty($avatarDb)) {
    // Aseguramos que tenga la barra inicial si no la tiene
    $avatarUrl = (strpos($avatarDb, '/') === 0 ? '' : '/') . $avatarDb . '?t=' . time();
} else {
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($sesion['nombre'].'+'.$sesion['apellido']) . '&background=18c5a3&color=fff';
}
?>
<?php include './app/viewer/plantillasAdmin/headAdmin.php'; ?>

<style>
    /* --- FIX DE CENTRADO --- */
    body {
        background: var(--navy-900);
        overflow-x: hidden;
        /* Importante: Quitamos padding-top global si existía y lo controlamos aquí */
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    /* El wrapper ocupa el espacio disponible entre el header y el final */
    .profile-wrapper {
        flex-grow: 1;
        display: flex;
        justify-content: center; /* Centrado Horizontal */
        align-items: center;     /* Centrado Vertical */
        padding: 40px 20px;
        margin-top: 60px; /* Espacio para que el navbar no lo tape */
    }

    .profile-container {
        width: 100%;
        max-width: 950px;
        position: relative;
    }

    /* Botón Volver */
    .back-btn {
        position: absolute;
        top: -50px;
        left: 0;
        display: inline-flex; align-items: center; gap: 8px;
        color: rgba(255,255,255,0.6); text-decoration: none; font-weight: 700;
        padding: 8px 15px; border-radius: 8px; background: rgba(255,255,255,0.05);
        transition: all 0.3s;
    }
    .back-btn:hover { color: white; background: var(--calypso); transform: translateX(-5px); }
    
    .profile-grid { 
        display: grid; 
        grid-template-columns: 320px 1fr; 
        gap: 30px; 
        align-items: stretch;
    }
    @media(max-width: 850px) { .profile-grid { grid-template-columns: 1fr; } }
    
    .glass-card { 
        background: rgba(30, 30, 40, 0.6); 
        border: 1px solid rgba(255,255,255,0.08); 
        border-radius: 20px; 
        padding: 40px 30px; 
        backdrop-filter: blur(12px); 
        box-shadow: 0 10px 40px rgba(0,0,0,0.4); 
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
    }
    
    /* Avatar */
    .avatar-img { 
        width: 140px; height: 140px; 
        object-fit: cover; 
        border-radius: 50%; 
        border: 4px solid var(--calypso); 
        box-shadow: 0 0 25px rgba(24, 197, 163, 0.2); 
        margin: 0 auto 15px; 
        display: block; 
    }
    
    /* Botón de Foto VISIBLE */
    .btn-photo { 
        background: rgba(255,255,255,0.1); 
        color: white; 
        border: 1px solid rgba(255,255,255,0.2); 
        padding: 8px 20px; 
        border-radius: 20px; 
        cursor: pointer; 
        font-size: 0.85rem; 
        margin-bottom: 20px; 
        transition: 0.3s; 
        display: inline-block; 
    }
    .btn-photo:hover { background: var(--calypso); color: black; border-color: var(--calypso); }

    .form-group { margin-bottom: 20px; }
    .form-group label { color: #aaa; font-size: 0.9rem; margin-bottom: 8px; display: block; }
    .form-control { width: 100%; padding: 12px 15px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: white; font-size: 1rem; transition: 0.3s; }
    .form-control:focus { border-color: var(--calypso); outline: none; }
    
    small { display: none; color: #ff6b6b; font-size: 0.85rem; margin-top: 5px; font-weight: 600; }

    .btn-save { background: var(--gradient-primary); color: #000; font-weight: 800; border: none; padding: 12px; border-radius: 10px; cursor: pointer; width: 100%; margin-top: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .btn-password { 
        background: transparent; color: #ff8888; border: 1px solid rgba(239, 68, 68, 0.5); 
        padding: 10px; border-radius: 10px; cursor: pointer; width: 100%; margin-top: auto; 
        font-weight: bold; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; 
    }
    .btn-password:hover { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #fff; }

    /* Modal */
    .modal-center { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 3000; align-items: center; justify-content: center; backdrop-filter: blur(5px); opacity: 0; transition: opacity 0.3s; }
    .modal-center.active { display: flex; opacity: 1; }
    .modal-box { width: 400px; max-width: 90%; background: var(--navy-900); border: 1px solid var(--calypso); border-radius: 20px; padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
</style>

<div class="profile-wrapper">
    <div class="profile-container">
        <a href="/dashadmin" class="back-btn"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>

        <div class="profile-grid">
            <div class="glass-card" style="text-align:center;">
                <form id="avatarForm">
                    <div class="avatar-wrapper">
                        <img src="<?php echo $avatarUrl; ?>" id="previewAvatar" class="avatar-img">
                        
                        <button type="button" class="btn-photo" onclick="document.getElementById('inputFoto').click()">
                            <i class="fas fa-camera"></i> Cambiar Foto
                        </button>
                        
                        <input type="file" id="inputFoto" name="foto" hidden accept="image/*">
                    </div>
                </form>
                
                <h2 style="margin: 5px 0; color:white;"><?php echo $sesion['nombre'] . ' ' . $sesion['apellido']; ?></h2>
                <p style="color:var(--calypso); font-weight:bold; text-transform: uppercase; font-size:0.9rem; letter-spacing: 1px;">
                    <?php echo $sesion['rol']; ?>
                </p>

                <div style="margin-top:auto; width:100%;">
                    <button class="btn-password" onclick="abrirModalPass()">
                        <i class="fas fa-lock"></i> Cambiar Contraseña
                    </button>
                </div>
            </div>

            <div class="glass-card">
                <h3 style="color:var(--calypso); margin-top:0; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">
                    <i class="fas fa-user-edit"></i> Editar Información
                </h3>
                <form id="formDatos" autocomplete="off">
                    <input type="hidden" name="idusuario" value="<?php echo $sesion['idusuario']; ?>">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo $sesion['nombre']; ?>" required>
                            <small id="errorNombre"></small>
                        </div>
                        <div class="form-group">
                            <label>Apellido</label>
                            <input type="text" name="apellido" id="apellido" class="form-control" value="<?php echo $sesion['apellido']; ?>" required>
                            <small id="errorApellido"></small>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label>Correo Electrónico</label>
                            <input type="email" name="correo" id="correo" class="form-control" value="<?php echo $sesion['correo']; ?>" required>
                            <small id="errorCorreo"></small>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo $sesion['telefono']; ?>">
                            <small id="errorTelefono"></small>
                        </div>
                    </div>
                    <button type="submit" class="btn-save">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modalPass" class="modal-center">
    <div class="modal-box">
        <h3 style="color:white; margin-top:0; text-align:center;">Seguridad</h3>
        <p style="color:#aaa; font-size:0.9rem; text-align:center; margin-bottom:20px;">
            Al cambiar tu contraseña se cerrará la sesión.
        </p>
        <form id="formPass">
            <input type="hidden" name="idusuario" value="<?php echo $sesion['idusuario']; ?>">
            <div class="form-group">
                <label>Nueva Contraseña</label>
                <input type="password" name="p1" id="p1" class="form-control" required placeholder="Mínimo 6 caracteres">
                <small id="errorP1"></small>
            </div>
            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <input type="password" name="p2" id="p2" class="form-control" required placeholder="Confirma tu contraseña">
                <small id="errorP2"></small>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button" onclick="cerrarModalPass()" class="btn-password" style="margin:0; border-color:#666; color:#ccc;">Cancelar</button>
                <button type="submit" class="btn-save" style="margin:0;">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets/js/perfil.js"></script>
</body>
</html>