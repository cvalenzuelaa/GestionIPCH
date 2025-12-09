<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();

if (!isset($sesion['idusuario']) || ($sesion['rol'] !== 'admin' && $sesion['rol'] !== 'super')) {
    header('Location: /dashadmin');
    exit;
}
?>

<?php include './app/viewer/plantillasAdmin/headAdmin.php'; ?>
<?php include './app/viewer/plantillasAdmin/headerAdmin.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<style>
    /* Estilos del contenedor principal y tabla */
    .main-wrapper {
        margin-top: 70px;
        padding: 24px;
        background: var(--blur-bg);
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        backdrop-filter: blur(10px);
        width: 98%;
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
        min-height: calc(100vh - 100px);
    }
    
    /* Estilos de validación */
    small { display: none; color: #ff6b6b; font-size: 0.85rem; margin-top: 4px; font-weight: 600; }

    /* Filtros */
    .filter-input {
        width: 100%;
        padding: 8px 10px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: white;
        font-size: 0.9rem;
        outline: none;
        transition: all 0.3s ease;
    }
    .filter-input:focus {
        border-color: var(--calypso);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 8px rgba(24, 197, 163, 0.2);
    }
    
    .dataTables_filter { display: none; }
    
    #tablaUsuarios thead tr.filters th {
        padding: 5px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    /* Badges */
    .badge-rol { padding: 5px 10px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; }
    .rol-admin { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
    .rol-super { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
    .rol-user { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6; }
    
    .badge-alabanza {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: rgba(168, 85, 247, 0.2);
        border: 1px solid #a855f7;
        border-radius: 6px;
        color: #c084fc;
        font-size: 0.75rem;
        font-weight: 700;
        margin-top: 5px;
    }

    /* Checkbox personalizado para alabanza */
    .checkbox-alabanza {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        background: rgba(168, 85, 247, 0.1);
        border: 1px solid rgba(168, 85, 247, 0.3);
        border-radius: 10px;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
    }

    .checkbox-alabanza:hover {
        background: rgba(168, 85, 247, 0.2);
        border-color: #a855f7;
    }

    .checkbox-alabanza input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #a855f7;
    }

    .checkbox-alabanza label {
        color: #c084fc;
        font-weight: 600;
        cursor: pointer;
        margin: 0;
        flex-grow: 1;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-alabanza-info {
        font-size: 0.75rem;
        color: rgba(192, 132, 252, 0.7);
        margin-top: 5px;
        padding-left: 30px;
    }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>
        
        <div class="calendar-header-section">
            <h1>Gestión de Usuarios</h1>
            <p class="calendar-description">
                Control de acceso al sistema. Administra roles, permisos y estados de las cuentas de usuario.
            </p>
            <div class="calendar-actions">
                <div style="flex-grow: 1;"></div>
                <button class="btn-add-activity" onclick="nuevoUsuario()" title="Crear usuario">
                    <i class="fas fa-user-shield"></i>
                    Crear nuevo usuario
                </button>
            </div>
        </div>
        
        <div class="calendar-wrapper" style="overflow-x: auto; margin-top: 20px;">
            <table id="tablaUsuarios" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Usuario (Nombre/Email)</th>
                        <th>Rol / Permisos</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Gestión</th>
                    </tr>
                    <tr class="filters">
                        <th><input type="text" class="filter-input" placeholder="Buscar..." data-col="0"></th>
                        <th>
                            <select class="filter-input" data-col="1">
                                <option value="">Todos</option>
                                <option value="Admin">Pastor / Admin</option>
                                <option value="Líder">Líder / Especial</option>
                                <option value="Miembro">Miembro</option>
                                <option value="Alabanza">Grupo Alabanza</option>
                            </select>
                        </th>
                        <th>
                            <select class="filter-input" data-col="2">
                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalUsuario" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 id="tituloModalUsuario">Datos de Usuario</h2>
            <button class="modal-close" onclick="cerrarModal('modalUsuario')">&times;</button>
        </div>
        
        <form id="formUsuario" autocomplete="off">
            <input type="hidden" name="accion" id="accionUsuario" value="insert">
            <input type="hidden" name="idusuario" id="idusuario">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan">
                    <small id="errorNombre"></small>
                </div>
                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" id="apellido" name="apellido" placeholder="Ej: Pérez">
                    <small id="errorApellido"></small>
                </div>
            </div>

            <div class="form-group">
                <label>Correo Electrónico (Login)</label>
                <input type="email" id="correo" name="correo" placeholder="usuario@iglesia.cl">
                <small id="errorCorreo"></small>
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" id="telefono" name="telefono" placeholder="+56 9 ...">
                <small id="errorTelefono"></small>
            </div>
            
            <div class="form-group">
                <label>Rol de Acceso</label>
                <select id="rol" name="rol" required style="width:100%; padding:10px; background:rgba(255,255,255,0.05); color:white; border:1px solid rgba(255,255,255,0.1); border-radius:8px;">
                    <option value="usuario">Miembro o adherente (Acceso básico)</option>
                    <option value="super">Líder (Acceso a parte de la gestión)</option>
                    <option value="admin">Pastor / Presbitero (Acceso Total)</option>
                </select>
            </div>

            <!-- NUEVO: Checkbox para Grupo de Alabanza -->
            <div class="form-group">
                <div class="checkbox-alabanza" onclick="toggleAlabanza()">
                    <input type="checkbox" id="es_alabanza" name="es_alabanza" value="1">
                    <label for="es_alabanza">
                        <i class="fas fa-music"></i>
                        Miembro del Grupo de Alabanza
                    </label>
                </div>
                <p class="checkbox-alabanza-info">
                    <i class="fas fa-info-circle"></i>
                    Si está activado, tendrá acceso al módulo de Alabanzas para gestionar canciones, ensayos y presentaciones.
                </p>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="pass" id="pass" placeholder="****** (Dejar vacío si no cambia)">
                <small id="errorPass"></small>
                <p style="color:#aaa; font-size:0.75rem; margin-top:5px; margin-bottom:0;" id="helpPass">Obligatoria para usuarios nuevos.</p>
            </div>

            <div class="form-actions modal-footer" style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn-cancel-glass" onclick="cerrarModal('modalUsuario')" style="padding:10px 20px; background:transparent; border:1px solid var(--muted); color:white; border-radius:8px; cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-save" style="padding:10px 20px; background:var(--gradient-primary); border:none; color:black; font-weight:bold; border-radius:8px; cursor:pointer;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAlabanza() {
    const checkbox = document.getElementById('es_alabanza');
    checkbox.checked = !checkbox.checked;
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="/assets/js/usuarios.js?v=<?php echo time(); ?>"></script>