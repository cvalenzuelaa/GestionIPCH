<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario']) || $sesion['rol'] !== 'super') { header('Location: /login'); exit; }
?>
<?php include './app/viewer\plantillasSusuario\headSusuario.php'; ?>
<?php include './app/viewer\plantillasSusuario\headerSusuario.php'; ?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>

        <div class="calendar-header-section">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                <div>
                    <h1>Calendario de Actividades</h1>
                    <p class="calendar-description">
                        Agenda de actividades, oraciones y cumpleaños de la congregación.
                    </p>
                </div>
                <div class="calendar-actions" style="gap:10px;">
                    <button class="btn-action btn-edit" id="btnSummary" style="background:#10b981;">
                        <i class="fas fa-file-excel"></i> Descargar Resumen Mensual
                    </button>
                    
                    <button class="btn-add-activity" onclick="openModal()">
                        <i class="fas fa-plus"></i> Nueva actividad
                    </button>
                </div>
            </div>
        </div>

        <div class="calendar-wrapper" style="margin-top: 20px;">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Modal Nueva Actividad -->
<div id="activityModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2>Programar Actividad</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="activityForm">
            <div style="padding: 25px;">
                <div class="form-group"><label>Título</label><input type="text" name="titulo" required placeholder="Ej: Culto Dominical"></div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group"><label>Fecha</label><input type="date" id="fecha" name="fecha" required></div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo" required>
                            <option value="culto">Culto</option>
                            <option value="reunion">Reunión</option>
                            <option value="actividad">Actividad Especial</option>
                            <option value="ensayo">Ensayo</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group"><label>Inicio</label><input type="time" name="hora_inicio" required></div>
                    <div class="form-group"><label>Fin</label><input type="time" name="hora_fin" required></div>
                </div>

                <div class="form-group"><label>Responsable</label><select id="responsableSelect" name="responsable" required></select></div>
                <div class="form-group"><label>Descripción</label><textarea name="descripcion" style="height:60px;"></textarea></div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel-glass" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Detalle (MISMO HTML QUE MISACTIVIDADES) -->
<div id="activityDetailModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 id="modalTitulo">Detalle de Actividad</h2>
            <button class="modal-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalDetalle" style="padding: 20px;">
            <!-- Contenido dinámico -->
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
<script src="/assets/js/actividadessuperu.js?v=<?php echo time(); ?>"></script>

<script>
    function openModal() {
        document.getElementById('activityForm').reset();
        document.getElementById('activityModal').classList.add('active');
    }

    function closeDetailModal() {
        document.getElementById('activityDetailModal').classList.remove('active');
    }
</script>
</body>
</html>