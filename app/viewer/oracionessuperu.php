<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario']) || $sesion['rol'] !== 'super') { header('Location: /login'); exit; }
?>
<?php include './app/viewer\plantillasSusuario\headSusuario.php'; ?>
<?php include './app/viewer\plantillasSusuario\headerSusuario.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<style>
    .badge-estado { padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
    .b-pendiente { background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid #f59e0b; }
    .b-aprobada { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
    .b-rechazada { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }
    .user-role { font-size: 0.75rem; color: #aaa; font-style: italic; }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>

        <div class="calendar-header-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h1>Gestión de Oraciones</h1>
                    <p class="calendar-description">
                        Visualiza las peticiones y envía motivos ministeriales para aprobación pastoral.
                    </p>
                </div>
                <button class="btn-add-activity" onclick="openModal()">
                    <i class="fas fa-plus-circle"></i> Enviar Petición
                </button>
            </div>
        </div>

        <div class="calendar-wrapper" style="padding: 20px;">
            <table id="tablaOraciones" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Solicitante</th>
                        <th>Petición</th>
                        <th>Estado</th>
                        <th>Gestión</th>
                    </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="oracionModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Nueva Petición de Oración</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="oracionForm">
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" style="height:120px;" required placeholder="Escribe el motivo de oración..."></textarea>
                <small style="color:#f59e0b;">
                    <i class="fas fa-info-circle"></i> Tu petición será revisada por el Pastor antes de publicarse.
                </small>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel-glass" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-save">Enviar para Aprobación</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="/assets/js/oracionessuperu.js"></script>
</body>
</html>