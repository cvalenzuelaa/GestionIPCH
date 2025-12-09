<?php
    require_once './app/sesiones/session.php';
    $obj = new Session();
    $sesion = $obj->getSession();
    $rutaAvatarHeader = !empty($sesion['avatar']) ? '/'.$sesion['avatar'] . '?t='.time() : '/assets/img/user-avatar.png';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* --- CONFIGURACIÓN GENERAL --- */
    .notif-wrapper { position: relative; display: flex; align-items: center; }
    
    /* --- ESTILOS DE LA CAMPANA (NOTIFICACIONES) --- */
    .notif-icon-btn { 
        font-size: 1.35rem; color: rgba(255,255,255,0.85); cursor: pointer; 
        padding: 8px; border-radius: 50%; transition: 0.3s; position: relative;
        display: flex; align-items: center; justify-content: center;
    }
    .notif-icon-btn:hover { background: rgba(255,255,255,0.1); color: white; }
    
    .notif-badge { 
        position: absolute; top: 0px; right: 0px; 
        background: #ef4444; color: white; font-size: 0.65rem; font-weight: 800; 
        min-width: 18px; height: 18px; padding: 0 4px; border-radius: 10px; 
        display: flex; align-items: center; justify-content: center;
        border: 2px solid var(--navy-900, #1e293b); z-index: 10;
    }

    .notif-dropdown, #dropdownMenu {
        position: absolute; top: 55px;
        background: #151f2e;
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 12px; 
        box-shadow: 0 15px 40px rgba(0,0,0,0.6);
        opacity: 0; 
        visibility: hidden; 
        transform: translateY(10px);
        pointer-events: none;
        transition: all 0.2s cubic-bezier(0.165, 0.84, 0.44, 1);
        z-index: 9999; overflow: hidden;
    }

    .notif-dropdown.active, #dropdownMenu.active { 
        opacity: 1; 
        visibility: visible; 
        transform: translateY(0);
        pointer-events: auto;
    }

    .notif-dropdown { right: -60px; width: 340px; }
    .notif-header { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); font-weight: bold; color: white; background: rgba(255,255,255,0.03); }
    .notif-body { max-height: 350px; overflow-y: auto; }
    
    .notif-item { display: flex; gap: 15px; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); cursor: pointer; transition: 0.2s; align-items: start; text-decoration: none; }
    .notif-item:hover { background: rgba(255,255,255,0.08); }
    .notif-icon-box { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .notif-content { flex-grow: 1; }
    .notif-title { margin: 0 0 4px 0; font-size: 0.85rem; font-weight: bold; color: white; }
    .notif-msg { margin: 0; font-size: 0.8rem; color: #bbb; line-height: 1.3; }
    .notif-dot { width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; margin-top: 6px; flex-shrink: 0;}
    
    .notif-body::-webkit-scrollbar { width: 6px; }
    .notif-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

    #dropdownMenu {
        right: 0; 
        width: 200px;
        padding: 5px 0;
        display: block !important; 
    }

    #dropdownMenu a, #dropdownMenu button {
        display: flex; align-items: center; width: 100%; padding: 12px 20px;
        color: #cbd5e1; font-size: 0.9rem; text-decoration: none;
        background: transparent; border: none; cursor: pointer;
        transition: all 0.2s; text-align: left; font-family: inherit;
    }

    #dropdownMenu a:hover, #dropdownMenu button:hover {
        background: rgba(255,255,255,0.1); color: #fff; padding-left: 24px;
    }
</style>

<nav class="navbar">
    <div class="logo-bar">
        <img src="/assets/img/YDRAY-LOGO-LOS-ANGELES-BLANCO.png" alt="Logo IPCH" class="logo-img" />
    </div>
    <div class="nav-links">
        <a href="/dashsuperu">Inicio</a>
        <a href="/actividadessuperu">Actividades</a>
        <a href="/alabanzassuperu">Alabanzas</a>
        <a href="/oracionessuperu">Oraciones</a>
        <a href="/sermonessuperu">Sermones</a>
    </div>

    <div style="display:flex; align-items:center; gap:15px;">
        
        <div class="notif-wrapper" id="notifWrapper">
            <div class="notif-icon-btn" onclick="toggleNotificaciones()">
                <i class="fas fa-bell"></i>
                <span class="notif-badge" id="notifCount" style="display:none;">0</span>
            </div>
            
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notificaciones</span>
                </div>
                <div class="notif-body" id="notifList">
                    <div style="padding:20px; text-align:center; color:#888;">Cargando...</div>
                </div>
            </div>
        </div>

        <div class="user-menu" id="userMenu" style="cursor:pointer; display:flex; align-items:center;">
            <img src="<?php echo $rutaAvatarHeader; ?>" alt="Avatar" class="user-avatar" id="headerAvatarImg" style="object-fit:cover;" />
            <span class="user-name"><?php echo $sesion['nombre']; ?></span>
            <i class="fas fa-chevron-down" style="font-size:0.8rem; margin-left:5px; color:white;"></i>
            
            <div class="dropdown-menu" id="dropdownMenu">
                <div style="padding: 10px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom:5px;">
                    <span style="display:block; color:#fff; font-weight:bold;"><?php echo $sesion['nombre']; ?></span>
                    <span style="display:block; color:#94a3b8; font-size:0.75rem;">Ministerio</span>
                </div>
                <a href="/perfilsuperu"><i class="fas fa-edit" style="margin-right:10px; width:20px; text-align:center;"></i> Mi Perfil</a>
                <button onclick="logout()"><i class="fas fa-sign-out-alt" style="margin-right:10px; width:20px; text-align:center;"></i> Cerrar sesión</button>
            </div>
        </div>
    </div>
</nav>

<script src="/assets/js/headerSusuario.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/notificaciones.js?v=<?php echo time(); ?>"></script>