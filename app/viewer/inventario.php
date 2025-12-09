<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario'])) { header('Location: /login'); exit; }
?>
<?php include './app/viewer/plantillasAdmin/headAdmin.php'; ?>
<?php include './app/viewer/plantillasAdmin/headerAdmin.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<style>
    /* Reutilizamos estilos de dashboard */
    .stat-card-full {
        background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        backdrop-filter: blur(10px);
    }
    .stat-title { font-size: 1.1rem; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-size: 2.5rem; font-weight: 800; color: var(--calypso); }
    .stat-icon { font-size: 3rem; color: rgba(255,255,255,0.1); }

    .btn-file {
        background: rgba(255,255,255,0.1); border: none; color: white;
        padding: 5px 10px; border-radius: 5px; cursor: pointer; text-decoration: none;
        font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-file:hover { background: var(--calypso); color: black; }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>

        <div class="calendar-header-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h1>Inventario de Bienes</h1>
                    <p class="calendar-description">Registro y control de activos de la iglesia.</p>
                </div>
                <button class="btn-add-activity" onclick="openModal()">
                    <i class="fas fa-box-open"></i> Nuevo Bien
                </button>
            </div>
        </div>

        <div class="stat-card-full">
            <div>
                <span class="stat-title">Valor Total del Inventario</span>
                <div class="stat-value" id="totalInventario">$0</div>
            </div>
            <i class="fas fa-boxes stat-icon"></i>
        </div>

        <div class="calendar-wrapper" style="padding: 20px;">
            <table id="tablaInventario" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Fecha Adquisición</th>
                        <th>Descripción del Bien</th>
                        <th>Valor Estimado</th>
                        <th>Evidencia / Foto</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="inventarioModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Registrar Bien</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="inventarioForm" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Descripción del Bien</label>
                <input type="text" name="descripcion" placeholder="Ej: Proyector Epson, Sillas, Piano..." required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Fecha Adquisición</label>
                    <input type="date" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Valor ($)</label>
                    <input type="number" name="monto" required min="0" step="0.01" style="font-weight:bold; color:var(--calypso);">
                </div>
            </div>

            <div class="form-group">
                <label>Foto o Factura (Opcional)</label>
                <input type="file" name="archivo" accept="image/*,.pdf" style="padding:10px; background:rgba(255,255,255,0.05); border-radius:8px; width:100%; color:#aaa;">
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel-glass" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Bien</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

<script src="/assets/js/inventario.js"></script>
</body>
</html>