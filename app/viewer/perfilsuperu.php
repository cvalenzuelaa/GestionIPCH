<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario']) || $sesion['rol'] !== 'super') { header('Location: /login'); exit; }

$avatarDb = $sesion['avatar'] ?? '';
if (!empty($avatarDb)) {
    $avatarUrl = (strpos($avatarDb, '/') === 0 ? '' : '/') . $avatarDb . '?t=' . time();
} else {
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($sesion['nombre'].'+'.$sesion['apellido']) . '&background=18c5a3&color=fff';
}
?>
<?php include './app/viewer\plantillasSusuario\headSusuario.php'; ?>
<?php include './app/viewer\plantillasSusuario\headerSusuario.php'; ?>

<style>
    body {
        background: var(--navy-900);
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    .profile-wrapper {
        flex-grow: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 20px;
        margin-top: 60px;
    }

    .profile-container {
        width: 100%;
        max-width: 950px;
        position: relative;
    }

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
    }
    
    .avatar-img {
        width: 150px; height: 150px; border-radius: 50%; object-fit: cover;
        border: 4px solid var(--calypso); margin: 0 auto 20px; display: block;
        box-shadow: 0 8px 20px rgba(24,197,163,0.3);
    }
    
    .btn-photo {
        background: rgba(255,255,255,0.05); color: var(--calypso);
        border: 1.5px solid var(--calypso); padding: 10px 20px;
        border-radius: 10px; cursor: pointer; width: 100%; margin-top: 15px;
        font-weight: 700; text-align: center; transition: 0.3s;
    }
    .btn-photo:hover { background: var(--calypso); color: black; border-color: var(--calypso); }

    .form-group { margin-bottom: 20px; }
    .form-group label { color: #aaa; font-size: 0.9rem; margin-bottom: 8px; display: block; }
    .form-control { width: 100%; padding: 12px 15px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: white; font-size: 1rem; transition: 0.3s; }
    .form-control:focus { border-color: var(--calypso); outline: none; }
    
    small { display: none; color: #ff6b6b; font-size: 0.85rem; margin-top: 5px; font-weight: 600; }

    .btn-save { background: var(--gradient-primary); color: #000; font-weight: 800; border: none; padding: 12px; border-radius: 10px; cursor: pointer; width: 100%; margin-top: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .btn-password {
        background: transparent; color: #ef4444; border: 1.5px solid #ef4444;
        padding: 12px; border-radius: 10px; cursor: pointer; width: 100%;
        margin-top: 20px; font-weight: 700; text-transform: uppercase;
        transition: 0.3s;
    }
    .btn-password:hover { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #fff; }

    .modal-center { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 3000; align-items: center; justify-content: center; backdrop-filter: blur(5px); opacity: 0; transition: opacity 0.3s; }
    .modal-center.active { display: flex; opacity: 1; }
    .modal-box { width: 400px; max-width: 90%; background: var(--navy-900); border: 1px solid var(--calypso); border-radius: 20px; padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
</style>

<div class="profile-wrapper">
    <div class="profile-container">
        <a href="/dashsuperu" class="back-btn">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>

        <div class="profile-grid">
            <div class="glass-card" style="text-align:center;">
                <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="avatar-img" id="previewAvatar">
                <h2 style="color:white; margin:0;"><?php echo htmlspecialchars($sesion['nombre'].' '.$sesion['apellido']); ?></h2>
                <p style="color:var(--calypso); font-weight:700; margin:5px 0 0 0;">Ministerio</p>
                
                <label for="inputFoto" class="btn-photo">
                    <i class="fas fa-camera"></i> Cambiar Foto
                </label>
                <input type="file" id="inputFoto" name="foto" accept="image/*" style="display:none;">
            </div>

            <div class="glass-card">
                <h2 style="color:white; margin-bottom:25px;">Datos Personales</h2>
                <form id="formDatos">
                    <input type="hidden" name="idusuario" value="<?php echo $sesion['idusuario']; ?>">
                    
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($sesion['nombre']); ?>" required>
                        <small id="errorNombre"></small>
                    </div>

                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($sesion['apellido']); ?>" required>
                        <small id="errorApellido"></small>
                    </div>

                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($sesion['correo']); ?>" required>
                        <small id="errorCorreo"></small>
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($sesion['telefono']); ?>">
                        <small id="errorTelefono"></small>
                    </div>

                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar Cambios</button>
                </form>

                <button type="button" class="btn-password" onclick="abrirModalPass()">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </button>
            </div>
        </div>
    </div>
</div>

<div id="modalPass" class="modal-center">
    <div class="modal-box">
        <h2 style="color:white; margin-bottom:20px;">Cambiar Contraseña</h2>
        <form id="formPass">
            <input type="hidden" name="idusuario" value="<?php echo $sesion['idusuario']; ?>">
            
            <div class="form-group">
                <label>Nueva Contraseña</label>
                <input type="password" class="form-control" id="p1" name="p1" required>
                <small id="errorP1"></small>
            </div>

            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <input type="password" class="form-control" id="p2" required>
                <small id="errorP2"></small>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button" class="btn-cancel-glass" onclick="cerrarModalPass()" style="flex:1;">Cancelar</button>
                <button type="submit" class="btn-save" style="flex:1;">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets/js/perfil.js"></script>
</body>
</html>