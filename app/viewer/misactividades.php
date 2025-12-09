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

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />

<style>
    .main-wrapper {
        margin-top: 90px;
        padding: 24px;
        min-height: calc(100vh - 120px);
    }

    .calendar-header-section {
        margin-bottom: 30px;
    }

    .calendar-header-section h1 {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--calypso), var(--blue-500));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 8px;
    }

    .calendar-description {
        color: #94a3b8;
        font-size: 1rem;
        margin-bottom: 20px;
    }

    .calendar-wrapper {
        background: rgba(30, 41, 59, 0.4);
        border-radius: 16px;
        padding: 20px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    #calendar {
        min-height: 600px;
    }
</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>

        <div class="calendar-header-section">
            <h1>Calendario de Actividades</h1>
            <p class="calendar-description">
                Consulta cultos, reuniones y eventos de la iglesia.
            </p>
        </div>

        <div class="calendar-wrapper">
            <div id='calendar'></div>
        </div>
    </div>
</div>

<!-- Modal de Detalle -->
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

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/es.global.min.js'></script>
<script src="/assets/js/misactividades.js"></script>
</body>
</html>