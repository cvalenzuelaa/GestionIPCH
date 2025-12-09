<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario']) || $sesion['rol'] !== 'usuario') { 
    header('Location: /login'); 
    exit; 
}
?>

<?php include './app/viewer/plantillasUsuario/headUsuario.php'; ?>
<?php include './app/viewer/plantillasUsuario/headerUsuario.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<style>
    .badge-estado { padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
    .b-pendiente { background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid #f59e0b; }
    .b-aprobada { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
    .b-rechazada { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }
    .user-role { font-size: 0.75rem; color: #aaa; font-style: italic; }

    .tabs-container {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid rgba(255,255,255,0.1);
    }

    .tab-btn {
        padding: 12px 25px;
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
        border-bottom: 3px solid transparent;
        transition: 0.3s;
    }

    .tab-btn:hover {
        color: var(--calypso);
    }

    .tab-btn.active {
        color: var(--calypso);
        border-bottom-color: var(--calypso);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .badge-estado { 
        padding: 4px 8px; 
        border-radius: 6px; 
        font-size: 0.8rem; 
        font-weight: bold; 
        text-transform: uppercase; 
    }
    .b-pendiente { background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid #f59e0b; }
    .b-aprobada { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
    .b-rechazada { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }

    /* Muro de oraciones */
    .oraciones-muro {
        display: grid;
        gap: 20px;
    }


    .oracion-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 20px;
        transition: 0.3s;
    }

    .oracion-card:hover {
        border-color: var(--calypso);
        transform: translateY(-3px);
    }

    .oracion-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .oracion-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: var(--calypso);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #000;
    }

    .oracion-autor {
        flex-grow: 1;
    }

    .oracion-nombre {
        font-weight: bold;
        color: white;
        font-size: 1rem;
    }

    .oracion-fecha {
        font-size: 0.8rem;
        color: #888;
    }

    .oracion-texto {
        color: #ccc;
        font-style: italic;
        line-height: 1.6;
        padding: 12px;
        background: rgba(0,0,0,0.2);
        border-radius: 8px;
        border-left: 3px solid var(--calypso);
    }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>

        <div class="calendar-header-section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                <div>
                    <h1>Peticiones de Oración</h1>
                    <p class="calendar-description">
                        Comparte tus motivos de oración con la comunidad.
                    </p>
                </div>
                <button class="btn-add-activity" onclick="openModal()">
                    <i class="fas fa-plus-circle"></i> Nueva Petición
                </button>
            </div>

            <!-- TABS -->
            <div class="tabs-container">
                <button class="tab-btn active" onclick="switchTab('mispeticiones')">
                    <i class="fas fa-user"></i> Mis Peticiones
                </button>
                <button class="tab-btn" onclick="switchTab('muro')">
                    <i class="fas fa-users"></i> Muro Comunitario
                </button>
            </div>
        </div>

        <!-- TAB: Mis Peticiones -->
        <div id="tab-mispeticiones" class="tab-content active">
            <div class="calendar-wrapper" style="padding: 20px;">
                <table id="tablaMisPeticiones" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Mi Petición</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMisPeticionesBody"></tbody>
                </table>
            </div>
        </div>

        <!-- TAB: Muro Público -->
        <div id="tab-muro" class="tab-content">
            <div class="calendar-wrapper" style="padding: 20px;">
                <div id="muroOraciones" class="oraciones-muro"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Petición -->
<div id="oracionModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Nueva Petición de Oración</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="oracionForm">
            <div class="form-group">
                <label>Tu Petición</label>
                <textarea name="descripcion" style="height:120px;" required placeholder="Comparte tu motivo de oración..."></textarea>
                <small style="color:#f59e0b;">
                    <i class="fas fa-info-circle"></i> Tu petición será revisada por el Pastor antes de publicarse en el muro comunitario.
                </small>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel-glass" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-save">Enviar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="/assets/js/misoraciones.js"></script>
</body>
</html>