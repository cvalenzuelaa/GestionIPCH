<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario'])) { header('Location: /login'); exit; }
?>
<?php include './app/viewer/plantillasAdmin/headAdmin.php'; ?>
<?php include './app/viewer/plantillasAdmin/headerAdmin.php'; ?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<style>
    /* --- CORRECCIÓN DISEÑO MODAL (SCROLL) --- */
    .modal-content {
        max-height: 90vh; /* Altura máxima del 90% de la pantalla */
        overflow-y: auto; /* Scroll vertical si es necesario */
        display: block; /* Asegura comportamiento de bloque */
    }

    /* ESTILOS BASE PARA MODALES */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.85);
        z-index: 3000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: var(--navy-900);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px;
        padding: 0;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        width: 90%;
    }

    .modal-header {
        padding: 20px 30px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(0,0,0,0.3);
        border-radius: 20px 20px 0 0;
    }

    .modal-header h2 {
        color: var(--calypso);
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.8rem;
        cursor: pointer;
        transition: 0.3s;
        line-height: 1;
        padding: 0;
        width: 30px;
        height: 30px;
    }

    .modal-close:hover {
        color: #ef4444;
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 25px 30px;
    }

    .modal-footer,
    .form-actions {
        padding: 15px 30px;
        border-top: 1px solid rgba(255,255,255,0.1);
        background: rgba(0,0,0,0.2);
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        border-radius: 0 0 20px 20px;
    }

    /* Estilos para tablas de asistencia */
    .attendance-search {
        padding: 15px 20px;
        background: rgba(0,0,0,0.3);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .attendance-search input {
        flex: 1;
        padding: 10px 15px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: white;
        font-size: 0.95rem;
    }

    .attendance-search input::placeholder {
        color: rgba(255,255,255,0.4);
    }

    .stat-badge {
        padding: 8px 15px;
        background: rgba(24, 197, 163, 0.2);
        border: 1px solid var(--calypso);
        border-radius: 20px;
        color: var(--calypso);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .attendance-table-container {
        flex: 1;
        overflow-y: auto;
        padding: 0;
        background: rgba(0,0,0,0.2);
    }

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
    }

    .attendance-table thead {
        background: rgba(0,0,0,0.4);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .attendance-table th {
        padding: 15px;
        text-align: left;
        color: var(--calypso);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        border-bottom: 2px solid rgba(24, 197, 163, 0.3);
    }

    .attendance-table th:nth-child(2),
    .attendance-table th:nth-child(3) {
        text-align: center;
    }

    .attendance-table tbody {
        background: transparent;
    }

    .attendance-table tbody tr {
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: 0.3s;
        background: transparent;
    }

    .attendance-table tbody tr:hover {
        background: rgba(255,255,255,0.05);
    }

    .attendance-table tbody tr.presente {
        background: rgba(16, 185, 129, 0.15);
    }

    .attendance-table td {
        padding: 15px;
        color: white;
        background: transparent;
    }

    .member-info {
        display: flex;
        flex-direction: column;
    }

    .member-name {
        font-weight: 700;
        font-size: 1rem;
        color: white;
    }

    .member-type {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.6);
        margin-top: 3px;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 700;
        text-align: center;
    }

    .status-badge.presente {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
        border: 1px solid #10b981;
    }

    .status-badge.ausente {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid #ef4444;
    }

    .attendance-checkbox {
        transform: scale(1.5);
        cursor: pointer;
        accent-color: var(--calypso);
    }

    /* Estilos para modal de detalles */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }

    .detail-item {
        background: rgba(255, 255, 255, 0.03);
        padding: 15px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: 0.3s;
        backdrop-filter: blur(10px);
    }

    .detail-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(24, 197, 163, 0.3);
    }

    .detail-label {
        display: block;
        font-size: 0.75rem;
        color: var(--calypso);
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .detail-label i {
        margin-right: 6px;
    }

    .detail-value {
        display: block;
        font-size: 1.1rem;
        color: white;
        font-weight: 600;
    }

    .detail-description {
        background: rgba(255,255,255,0.03);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid var(--calypso);
        font-size: 0.95rem;
        color: rgba(255,255,255,0.8);
        min-height: 60px;
        line-height: 1.6;
    }

    /* Modales especiales (cumpleaños, oración, confirmación) */
    .birthday-content,
    .prayer-content,
    .confirm-content,
    .alert-content {
        padding: 30px;
    }

    .birthday-icon,
    .confirm-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        text-align: center;
    }

    .birthday-name,
    .confirm-question,
    .alert-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
        text-align: center;
    }

    .birthday-message,
    .confirm-message,
    .alert-message {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 20px;
        line-height: 1.6;
        text-align: center;
    }


    .confirm-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .btn-confirm-yes {
        padding: 12px 30px;
        background: linear-gradient(135deg, var(--calypso), #15a386);
        color: #000;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-confirm-yes:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(24, 197, 163, 0.4);
    }

    .btn-confirm-no {
        padding: 12px 30px;
        background: transparent;
        color: #f87171;
        border: 2px solid #ef4444;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-confirm-no:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    .alert-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        text-align: center;
    }

    .alert-icon.success {
        color: #10b981;
    }

    .alert-icon.error {
        color: #ef4444;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .confirm-actions {
            flex-direction: column;
        }

        .btn-confirm-yes,
        .btn-confirm-no {
            width: 100%;
        }
    }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>

        <div class="calendar-header-section">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                <div>
                    <h1>Calendario de Actividades</h1>
                    <p class="calendar-description">
                        Agenda de gestión de actividades, oraciones y cumpleaños de la congregación.
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

<!-- Modal de Asistencia -->
<div id="attendanceModal" class="modal">
    <div class="modal-content" style="max-width: 800px; max-height: 85vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <h2 id="attTitle">Tomar Asistencia</h2>
            <button class="modal-close" onclick="closeAttendanceModal()">&times;</button>
        </div>
        
        <div class="attendance-search">
            <input type="text" id="searchMemberAtt" placeholder="🔍 Buscar miembro...">
            <span class="stat-badge">
                Presentes: <span id="totalPresentes">0</span>
            </span>
        </div>

        <div class="attendance-table-container">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Miembro</th>
                        <th style="width: 120px;">Estado</th>
                        <th style="width: 80px;">Marcar</th>
                    </tr>
                </thead>
                <tbody id="attendanceListBody"></tbody>
            </table>
        </div>

        <div class="form-actions">
            <input type="hidden" id="att_idactividad">
            <button type="button" class="btn-cancel-glass" onclick="closeAttendanceModal()">Cerrar</button>
            <button type="button" class="btn-save" onclick="submitAttendance()">
                <i class="fas fa-save"></i> Guardar Asistencia
            </button>
        </div>
    </div>
</div>

<!-- Modal de Detalles de Actividad -->
<div id="detailsModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 id="detailTitle">Detalles de la Actividad</h2>
            <button class="modal-close" onclick="closeDetailsModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-calendar-day"></i> Fecha</span>
                    <span class="detail-value" id="detailDate">--/--/----</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-clock"></i> Horario</span>
                    <span class="detail-value" id="detailTime">--:--</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-tag"></i> Tipo</span>
                    <span class="detail-value" id="detailType">General</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-user-tie"></i> Responsable</span>
                    <span class="detail-value" id="detailResp">Sin asignar</span>
                </div>
            </div>

            <div>
                <span class="detail-label" style="display:block; margin-bottom: 10px;">
                    <i class="fas fa-align-left"></i> Descripción
                </span>
                <div class="detail-description" id="detailDesc">
                    Sin descripción disponible.
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel-glass" onclick="closeDetailsModal()">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal de Cumpleaños -->
<div id="birthdayModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.3), rgba(139, 92, 246, 0.3));">
            <h2 style="color: white;">🎉 ¡Feliz Cumpleaños!</h2>
            <button class="modal-close" onclick="closeBirthdayModal()">&times;</button>
        </div>
        
        <div class="birthday-content">
            <div class="birthday-icon">🎂</div>
            <div class="birthday-name" id="birthdayName">Nombre del Cumpleañero</div>
            <div class="birthday-message">
                ¡Que Dios te bendiga en este día tan especial! 🙏✨
            </div>
        </div>

        <div class="form-actions">
            <button class="btn-save" onclick="closeBirthdayModal()">
                <i></i> Cerrar
            </button>
        </div>
    </div>
</div>

<div id="prayerModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2><i class="fas fa-praying-hands"></i> Petición de Oración</h2>
            <button class="modal-close" onclick="closePrayerModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-user"></i> Solicitante</span>
                    <span class="detail-value" id="prayerSolicitante">Sin especificar</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="fas fa-calendar-day"></i> Fecha</span>
                    <span class="detail-value" id="prayerDate">--/--/----</span>
                </div>
            </div>

            <div>
                <span class="detail-label" style="display:block; margin-bottom: 10px;">
                    <i class="fas fa-praying-hands"></i> Motivo de Oración
                </span>
                <div class="detail-description" id="prayerDesc">
                    Sin descripción disponible.
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel-glass" onclick="closePrayerModal()">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div id="confirmModal" class="modal">
    <div class="modal-content" style="max-width: 550px;">
        <div class="confirm-content">
            <div class="confirm-icon">⚠️</div>
            <div class="confirm-question" id="confirmQuestion">¿Estás seguro?</div>
            <div class="confirm-message" id="confirmMessage">Esta acción requerirá confirmación.</div>
            
            <div class="confirm-actions">
                <button class="btn-confirm-no" onclick="closeConfirmModal(false)">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn-confirm-yes" onclick="closeConfirmModal(true)">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Alerta -->
<div id="alertModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="alert-content">
            <div class="alert-icon" id="alertIcon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-title" id="alertTitle">Título</div>
            <div class="alert-message" id="alertMessage">Mensaje</div>
        </div>
        
        <div class="form-actions">
            <button class="btn-save" onclick="closeAlertModal()">
                <i class="fas fa-check"></i> Entendido
            </button>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>

<script>
    const USER_ROLE = "<?php echo $sesion['rol'] ?? 'usuario'; ?>";
</script>
<script src="/assets/js/actividades.js?v=<?php echo time(); ?>"></script>

<script>
    function openModal() {
        document.getElementById('activityForm').reset();
        document.getElementById('activityModal').classList.add('active');
    }
</script>
</body>
</html>