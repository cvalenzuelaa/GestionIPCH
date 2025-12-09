<?php
// filepath: c:\wamp64\www\GestionIPCH\app\viewer\misalabanzas.php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();

// VERIFICAR: Solo usuarios normales Y que sean del grupo de alabanza
if (!isset($sesion['idusuario']) || $sesion['rol'] !== 'usuario' || $sesion['es_alabanza'] != 1) { 
    header('Location: /dashboard'); 
    exit; 
}
?>

<?php include './app/viewer/plantillasUsuario/headUsuario.php'; ?>
<?php include './app/viewer/plantillasUsuario/headerUsuario.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<style>
    /* Estilos para los iconos de recursos */
    .resource-icons { display: flex; gap: 8px; justify-content: center; }
    .icon-link {
        width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center;
        color: white; transition: transform 0.2s; text-decoration: none;
    }
    .icon-link:hover { transform: translateY(-2px); filter: brightness(1.1); }
    .bg-pdf { background: #ef4444; } /* Rojo */
    .bg-ppt { background: #f97316; } /* Naranja */
    .bg-vid { background: #3b82f6; } /* Azul */
    .disabled { opacity: 0.3; pointer-events: none; background: #444; }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>

        <div class="calendar-header-section">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h1><i style="color:var(--calypso);"></i> Gestión de Alabanzas</h1>
                    <p class="calendar-description">
                        Gestiona las partituras, letras y presentaciones del grupo de alabanza.
                    </p>
                </div>
                <button class="btn-add-activity" onclick="nuevoRecurso()">
                    <i class="fas fa-plus"></i> Nueva Alabanza
                </button>
            </div>
        </div>

        <div class="calendar-wrapper" style="padding: 20px;">
            <table id="tablaAlabanzas" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th style="text-align:center;">Recursos Disponibles</th>
                        <th>Fecha Subida</th>
                        <th>Subido por</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar -->
<div id="alabanzaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Nueva Alabanza</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="alabanzaForm" enctype="multipart/form-data">
            <input type="hidden" name="accion" id="accionInput" value="insert">
            <input type="hidden" name="idalabanza" id="idAlabanzaInput">

            <div class="form-group">
                <label><i class="fas fa-music"></i> Título de la Canción</label>
                <input type="text" name="titulo" id="titulo" required placeholder="Ej: Nuestro Dios">
            </div>

            <div style="background:rgba(255,255,255,0.03); padding:15px; border-radius:10px; margin-bottom:15px; border:1px solid rgba(255,255,255,0.05);">
                <label style="color:#ef4444; font-weight:bold;">
                    <i class="fas fa-file-pdf"></i> Partitura / Letra (PDF)
                </label>
                <input type="file" name="file_pdf" accept=".pdf" style="margin-top:8px;">
                <div id="currentPDF" style="font-size:0.8rem; color:#aaa; margin-top:5px;"></div>
            </div>

            <div style="background:rgba(255,255,255,0.03); padding:15px; border-radius:10px; margin-bottom:15px; border:1px solid rgba(255,255,255,0.05);">
                <label style="color:#f97316; font-weight:bold;">
                    <i class="fas fa-file-powerpoint"></i> Presentación (PPT)
                </label>
                <input type="file" name="file_ppt" accept=".ppt,.pptx" style="margin-top:8px;">
                <div id="currentPPT" style="font-size:0.8rem; color:#aaa; margin-top:5px;"></div>
            </div>

            <div style="background:rgba(255,255,255,0.03); padding:15px; border-radius:10px; margin-bottom:15px; border:1px solid rgba(255,255,255,0.05);">
                <label style="color:#3b82f6; font-weight:bold;">
                    <i class="fas fa-link"></i> Enlace de Video (YouTube/Spotify)
                </label>
                <input type="url" name="enlace_video" id="enlace_video" placeholder="https://youtube.com/..." style="margin-top:8px;">
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel-glass" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="/assets/js/misalabanzas.js?v=<?php echo time(); ?>"></script>
</body>
</html>