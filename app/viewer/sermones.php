<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario'])) { header('Location: /login'); exit; }
$esAdmin = ($sesion['rol'] === 'admin' || $sesion['rol'] === 'super');
?>

<?php include './app/viewer/plantillasAdmin/headAdmin.php'; ?>
<?php include './app/viewer/plantillasAdmin/headerAdmin.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<style>
    /* Reutilizamos estilos globales del tema */
    .main-wrapper {
        margin-top: 70px; padding: 24px;
        background: var(--blur-bg); border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.05);
        backdrop-filter: blur(10px);
        min-height: calc(100vh - 100px);
    }

    /* GRID DE SERIES */
    .series-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    /* TARJETA DE SERIE */
    .serie-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px; overflow: hidden;
        transition: 0.3s; cursor: pointer;
        display: flex; flex-direction: column;
    }
    .serie-card:hover { transform: translateY(-5px); border-color: var(--calypso); }

    .serie-cover {
        height: 160px; width: 100%;
        background-color: #1e293b;
        background-size: cover; background-position: center;
        position: relative;
    }
    .serie-badge {
        position: absolute; top: 10px; right: 10px;
        background: rgba(0,0,0,0.7); color: var(--calypso);
        padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;
    }

    .serie-body { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
    .serie-title { font-size: 1.1rem; font-weight: 800; color: white; margin-bottom: 5px; }
    .serie-date { font-size: 0.8rem; color: #aaa; margin-bottom: 10px; }
    .serie-desc { font-size: 0.9rem; color: #ccc; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    /* ESTILOS INTERNOS DE SERMONES */
    .sermon-item {
        background: rgba(255,255,255,0.03);
        border-radius: 12px; padding: 15px;
        margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.05);
        display: flex; gap: 15px; align-items: start;
    }
    .sermon-icon {
        width: 45px; height: 45px; background: rgba(24,197,163,0.1);
        border-radius: 10px; color: var(--calypso);
        display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }
    .sermon-content { flex-grow: 1; }
    .sermon-title { font-weight: bold; color: white; font-size: 1rem; }
    .sermon-meta { font-size: 0.85rem; color: #888; margin: 4px 0; }
    
    .btn-resource {
        padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; text-decoration: none;
        display: inline-flex; align-items: center; gap: 5px; margin-right: 5px; cursor: pointer;
    }
    .btn-vid { background: rgba(59,130,246,0.2); color: #60a5fa; border: 1px solid #3b82f6; }
    .btn-pdf { background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid #ef4444; }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>
        
        <div class="calendar-header-section">
            <h1>Sermones y Predicaciones</h1>
            <p class="calendar-description">Acceda y gestione las series de enseñanzas y recursos bíblicos de la iglesia.</p>
            
            <div class="calendar-actions">
                <div style="flex-grow: 1;"></div>
                <?php if($esAdmin): ?>
                <button class="btn-add-activity" onclick="abrirModalSerie()">
                    <i class="fas fa-folder-plus"></i> Nueva Serie
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div id="seriesGrid" class="series-grid">
            </div>
    </div>
</div>

<div id="modalDetalleSerie" class="modal">
    <div class="modal-content modal-fixed" style="width: 900px; height: 90vh;">
        <div class="modal-header">
            <h2 id="tituloSerieModal">Detalle de Serie</h2>
            <button class="modal-close" onclick="cerrarModal('modalDetalleSerie')">&times;</button>
        </div>
        
        <div class="modal-body" style="padding: 20px;">
            <div style="display:flex; gap:20px; margin-bottom:30px;">
                <img id="imgSerieModal" src="" style="width:120px; height:120px; object-fit:cover; border-radius:12px; border:2px solid var(--calypso);">
                <div>
                    <h3 id="nombreSerieBig" style="color:white; font-size:1.5rem; margin:0;"></h3>
                    <p id="descSerieBig" style="color:#aaa; margin-top:10px; line-height:1.4;"></p>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">
                <h4 style="color:var(--calypso); margin:0;">Lista de Sermones</h4>
                <?php if($esAdmin): ?>
                <button class="btn-action btn-stats" onclick="abrirModalSermon()">
                    <i class="fas fa-plus"></i> Agregar Sermón
                </button>
                <?php endif; ?>
            </div>

            <div id="listaSermones">
                </div>
        </div>
    </div>
</div>

<div id="modalSerie" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Nueva Serie de Sermones</h2>
            <button class="modal-close" onclick="cerrarModal('modalSerie')">&times;</button>
        </div>
        <form id="formSerie">
            <input type="hidden" name="accion" value="addSerie">
            <div class="form-group">
                <label>Título de la Serie</label>
                <input type="text" name="titulo" required class="form-control">
            </div>
            <div class="form-group">
                <label>Descripción General</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Fecha de Inicio</label>
                <input type="date" name="fecha_inicio" required class="form-control">
            </div>
            <div class="form-group">
                <label>Imagen de Portada (Opcional)</label>
                <input type="file" name="imagen" class="form-control" accept="image/*">
            </div>
            <div class="form-actions" style="justify-content:flex-end; display:flex;">
                <button type="submit" class="btn-save">Crear Serie</button>
            </div>
        </form>
    </div>
</div>

<div id="modalSermon" class="modal" style="z-index: 2100;"> <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2>Agregar Sermón</h2>
            <button class="modal-close" onclick="cerrarModal('modalSermon')">&times;</button>
        </div>
        <form id="formSermon">
            <input type="hidden" name="accion" value="addSermon">
            <input type="hidden" name="idserie" id="idserieInput">
            
            <div class="form-group">
                <label>Título del Mensaje</label>
                <input type="text" name="titulo" required class="form-control">
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Predicador</label>
                    <input type="text" name="predicador" required class="form-control" placeholder="Ej: Pastor Juan">
                </div>
                <div class="form-group">
                    <label>Cita Bíblica</label>
                    <input type="text" name="cita" class="form-control" placeholder="Ej: Romanos 8:1-5">
                </div>
            </div>
            <div class="form-group">
                <label>Extracto / Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>URL Video (YouTube)</label>
                <input type="url" name="video" class="form-control" placeholder="https://youtube.com/...">
            </div>
            <div class="form-group">
                <label>Archivo de Notas (PDF)</label>
                <input type="file" name="pdf" class="form-control" accept="application/pdf">
            </div>
            <div class="form-group">
                <label>Presentación en PPT</label>
                <input type="file" name="ppt" required class="form-control" accept=".ppt,.pptx">
            </div>
            <div class="form-group">
                <label>Fecha Predicación</label>
                <input type="date" name="fecha" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-actions" style="justify-content:flex-end; display:flex;">
                <button type="submit" class="btn-save">Publicar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets/js/sermones.js"></script>
</body>
</html>