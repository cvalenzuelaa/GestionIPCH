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
    
    /* BADGE DE ESTADO - VISIBLE Y DESTACADO */
    .serie-badge {
        position: absolute; 
        top: 10px; 
        right: 10px;
        padding: 6px 14px; 
        border-radius: 20px; 
        font-size: 0.75rem; 
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    
    /* Estado EN CURSO - Verde brillante */
    .serie-badge.activa {
        background: rgba(24, 197, 163, 0.95);
        color: #000;
        border: 1px solid rgba(24, 197, 163, 1);
    }
    
    /* Estado FINALIZADA - Rojo/Gris */
    .serie-badge.finalizada {
        background: rgba(239, 68, 68, 0.95);
        color: #fff;
        border: 1px solid rgba(239, 68, 68, 1);
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
    
    .calendar-header-section {
        margin-bottom: 30px;
    }
    .calendar-header-section h1 {
        font-size: 2rem;
        font-weight: 800;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 8px;
    }
    .calendar-description {
        color: #94a3b8;
        font-size: 1rem;
        margin-bottom: 20px;
    }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>
        
        <div class="calendar-header-section">
            <h1>Sermones y Predicaciones</h1>
            <p class="calendar-description">Accede a las series de enseñanzas y recursos bíblicos de la iglesia.</p>
        </div>

        <div id="seriesGrid" class="series-grid"></div>
    </div>
</div>

<!-- Modal Detalle de Serie -->
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

            <div style="border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px; margin-bottom:15px;">
                <h4 style="color:var(--calypso); margin:0;">Lista de Sermones</h4>
            </div>

            <div id="listaSermones"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets/js/missermones.js"></script>
</body>
</html>