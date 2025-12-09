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
    /* --- CORRECCIÓN DISEÑO MODAL (SCROLL) --- */
    .modal-content {
        max-height: 90vh; /* Altura máxima del 90% de la pantalla */
        overflow-y: auto; /* Scroll vertical si es necesario */
        display: block; /* Asegura comportamiento de bloque */
    }
    
    /* Estilos Dashboard */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card {
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);
        border-radius: 15px; padding: 20px; display: flex; flex-direction: column; backdrop-filter: blur(10px);
    }
    .stat-card h3 { font-size: 0.9rem; color: var(--muted); margin-bottom: 10px; text-transform: uppercase; }
    .stat-card .value { font-size: 1.8rem; font-weight: 800; color: white; }
    .stat-card.ingreso .value { color: #18c5a3; }
    .stat-card.gasto .value { color: #ff6b6b; }
    .stat-card.balance .value { color: #60a5fa; }

    .chart-section {
        background: rgba(30,30,40,0.5); border-radius: 15px; padding: 20px; margin-bottom: 30px; height: 350px;
    }
    .btn-file {
        background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 5px; 
        color: white; text-decoration: none; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-file:hover { background: var(--calypso); color: black; }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>

        <div class="calendar-header-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h1>Tesorería</h1>
                    <p class="calendar-description">Control administrativo y financiero de la iglesia.</p>
                </div>
                <button class="btn-add-activity" onclick="openModal()">
                    <i class="fas fa-plus-circle"></i> Nuevo Movimiento
                </button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card ingreso"><h3>Ingresos</h3><span class="value" id="totalIngresos">$0</span></div>
            <div class="stat-card gasto"><h3>Gastos</h3><span class="value" id="totalGastos">$0</span></div>
            <div class="stat-card balance"><h3>Balance</h3><span class="value" id="totalBalance">$0</span></div>
        </div>

        <div class="chart-section">
            <canvas id="financeChart"></canvas>
        </div>

        <div class="calendar-wrapper" style="padding: 20px;">
            <table id="tablaTesoreria" class="display" style="width:100%">
                <thead>
                    <tr><th>Fecha</th><th>Tipo</th><th>Categoría</th><th>Descripción</th><th>Monto</th><th>Archivo</th><th>Acción</th></tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="tesoreriaModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2>Registrar Movimiento</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="tesoreriaForm" enctype="multipart/form-data">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label>Tipo de Operación</label>
                <div style="display:flex; gap:20px; background:rgba(255,255,255,0.05); padding:10px; border-radius:8px;">
                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <input type="radio" name="tipo" value="ingreso" checked onchange="toggleCategories()"> 
                        <span style="color:#18c5a3; font-weight:bold;">Ingreso (+)</span>
                    </label>
                    <label style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <input type="radio" name="tipo" value="gasto" onchange="toggleCategories()"> 
                        <span style="color:#ff6b6b; font-weight:bold;">Gasto (-)</span>
                    </label>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_tipo" id="categoria_tipo" required></select>
                </div>
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Título / Referencia</label>
                <input type="text" name="categoria" placeholder="Ej: Ofrenda Culto Domingo" required>
            </div>

            <div class="form-group">
                <label>Monto ($)</label>
                <input type="number" name="monto" required min="0" step="0.01" style="font-size:1.2rem; font-weight:bold; color:var(--calypso);">
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" style="height:60px;" placeholder="Detalles opcionales..."></textarea>
            </div>

            <div class="form-group">
                <label>Comprobante (Opcional)</label>
                <input type="file" name="comprobante" accept="image/*,.pdf" style="padding:10px; background:rgba(255,255,255,0.05); border-radius:8px; width:100%;">
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel-glass" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="/assets/js/tesoreria.js"></script> 

</body>
</html>