<?php
require_once './app/sesiones/session.php';
$obj = new Session();
$sesion = $obj->getSession();
if (!isset($sesion['idusuario'])) {
    header('Location: /login');
    exit;
}
?>

<?php include './app/viewer/plantillasAdmin/headAdmin.php'; ?>
<?php include './app/viewer/plantillasAdmin/headerAdmin.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<style>
    /* Estilos locales para la vista */
    .main-wrapper {
        margin-top: 70px;
        padding: 24px;
        background: var(--blur-bg);
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        backdrop-filter: blur(10px);
        width: 98%;
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
        min-height: calc(100vh - 100px);
    }
    small { display:block; color:#ff6b6b; font-size:0.9rem; margin-top:2px; }
    .chart-wrapper { width:100%; height:280px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.02); border-radius:8px; padding:10px; }
    .timeline-list { list-style:none; padding:0; margin:0; }

    /* ESTILOS PARA LA TABLA DE DETALLES DE ASISTENCIA */
    .table-details-wrapper {
        margin-top: 20px;
        padding: 15px;
        background: rgba(255,255,255,0.02);
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .table-details {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .table-details thead {
        background: rgba(24, 197, 163, 0.1);
        border-bottom: 2px solid rgba(24, 197, 163, 0.3);
    }

    .table-details th {
        padding: 12px;
        text-align: left;
        color: var(--calypso);
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-details tbody tr {
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: 0.3s;
    }

    .table-details tbody tr:hover {
        background: rgba(255,255,255,0.03);
    }

    .table-details td {
        padding: 12px;
        font-size: 0.9rem;
    }

    /* ESTILOS ESPECÍFICOS PARA LOS FILTROS DE BÚSQUEDA */
    .filter-input {
        width: 100%;
        padding: 8px 10px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: white;
        font-size: 0.9rem;
        outline: none;
        transition: all 0.3s ease;
    }
    .filter-input:focus {
        border-color: var(--calypso);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 8px rgba(24, 197, 163, 0.2);
    }
    .filter-input::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }
    .dataTables_filter { display: none; }
    
    #tablaMiembros thead tr.filters th {
        padding: 5px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .btn-filter {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-filter:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--calypso);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(24, 197, 163, 0.2);
}

.btn-filter:active {
    transform: translateY(0);
}

.btn-filter i {
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.btn-filter:hover i {
    transform: rotate(15deg);
}

/* Estado activo del botón (cuando se muestran inactivos) */
.btn-filter.active {
    background: linear-gradient(135deg, rgba(255, 107, 107, 0.2), rgba(255, 165, 0, 0.2));
    border-color: #ff6b6b;
    color: #ff6b6b;
}

.btn-filter.active:hover {
    background: linear-gradient(135deg, rgba(255, 107, 107, 0.3), rgba(255, 165, 0, 0.3));
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}
.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    background: rgba(24, 197, 163, 0.1);
    border: 1px solid rgba(24, 197, 163, 0.3);
    border-radius: 10px;
    color: var(--calypso);
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-icon:hover {
    background: rgba(24, 197, 163, 0.25);
    border-color: var(--calypso);
    transform: translateY(-2px) rotate(-15deg);
    box-shadow: 0 4px 15px rgba(24, 197, 163, 0.4);
}

.btn-icon:active {
    transform: translateY(0) rotate(0deg);
}

.btn-icon i {
    transition: transform 0.3s ease;
}

.btn-icon:hover i {
    transform: scale(1.1);
}

</style>

<div class="container-fluid" style="padding: 24px;">
    <div class="main-wrapper">
        <div id="alertContainer"></div>
        <div class="calendar-header-section">
            <h1>Directorio de Miembros</h1>
            <p class="calendar-description">
                Administración centralizada de la congregación. Gestiona datos personales,
                estado de membresía, historial pastoral y asistencia.
            </p>
            <div class="calendar-actions">
                <button class="btn-filter" onclick="toggleInactivos()" id="btnInactivos">
                    <i class="fas fa-eye-slash"></i> Ver usuarios inactivos
                </button>
                <div style="flex-grow: 1;"></div>
                <button class="btn-add-activity" onclick="nuevoMiembro()" title="Registrar nuevo miembro">
                    <i class="fas fa-user-plus"></i>
                    Crear nuevo miembro
                </button>
            </div>
        </div>
        
        <div class="calendar-wrapper" style="overflow-x: auto; margin-top: 20px;">
            <table id="tablaMiembros" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Miembro (Nombre/RUT)</th>
                        <th>Estado / Categoría</th>
                        <th>Contacto</th>
                        <th style="text-align: right;">Gestión</th>
                    </tr>
                    <tr class="filters">
                        <th><input type="text" class="filter-input" placeholder="Buscar por Nombre o RUT..." data-col="0"></th>
                        <th>
                            <select class="filter-input" data-col="1">
                                <option value="">Todos los estados</option>
                                <option value="Comulgante">Comulgante</option>
                                <option value="No comulgante">No comulgante</option>
                                <option value="Adherente">Adherente</option>
                                <option value="Visita">Visita</option>
                            </select>
                        </th>
                        <th><input type="text" class="filter-input" placeholder="Buscar teléfono/email..." data-col="2"></th>
                        <th></th> </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalMiembro" class="modal">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <h2 id="tituloModalMiembro">Datos del Miembro</h2>
            <button class="modal-close" onclick="cerrarModal('modalMiembro')">&times;</button>
        </div>
        <form id="formMiembro" autocomplete="off">
            <input type="hidden" id="idmiembro" name="idmiembro">
            <input type="hidden" name="accion" id="accionMiembro" value="insert">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Nombres</label>
                    <input type="text" id="nombre" name="nombre" required autocomplete="off">
                    <small id="errorNombre"></small>
                </div>
                <div class="form-group">
                    <label>Apellidos</label>
                    <input type="text" id="apellido" name="apellido" required autocomplete="off">
                    <small id="errorApellido"></small>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>RUT</label>
                    <input type="text" id="rut" name="rut" placeholder="Ej: 12.345.678-9" required autocomplete="off">
                    <small id="errorRut"></small>
                </div>
                <div class="form-group">
                    <label>Estado Membresía</label>
                    <select id="estado" name="estado" required>
                        <option value="">Seleccione...</option>
                        <option value="Visita">Visita</option>
                        <option value="Adherente">Adherente</option>
                        <option value="Comulgante">Miembro Comulgante</option>
                        <option value="No comulgante">Miembro No Comulgante</option>
                    </select>
                    <small id="errorEstado"></small>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Fecha Nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    <small id="errorNacimiento"></small>
                </div>
                <div class="form-group">
                    <label>Fecha Ingreso</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
                    <small id="errorIngreso"></small>
                </div>
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <input type="text" id="direccion" name="direccion">
                <small id="errorDireccion"></small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="correo" name="correo" autocomplete="off">
                    <small id="errorCorreo"></small>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="telefono" name="telefono" autocomplete="off" placeholder="+56 9 ____ ____">
                    <small id="errorTelefono"></small>
                </div>
            </div>

            <div class="form-actions modal-footer" style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end; flex:0 0 auto; z-index:2;">
                <button type="button" class="btn-cancel-glass" onclick="cerrarModal('modalMiembro')" style="padding:10px 20px; background:transparent; border:1px solid var(--muted); color:white; border-radius:8px; cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-save" style="padding:10px 20px; background:var(--gradient-primary); border:none; color:black; font-weight:bold; border-radius:8px; cursor:pointer;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalHojaVida" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Hoja de Vida Pastoral</h2>
            <button class="modal-close" onclick="cerrarModal('modalHojaVida')">&times;</button>
        </div>
        
        <form id="formInteraccion" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 15px;">
            <input type="hidden" id="hv_idmiembro" name="idmiembro">
            <input type="hidden" name="idusuario_registro" value="<?php echo $sesion['idusuario']; ?>">
            
            <div class="form-group">
                <select name="tipo" required style="width:100%; padding:10px; margin-bottom:10px; background:rgba(255,255,255,0.05); color:white; border:1px solid rgba(255,255,255,0.1); border-radius:8px;">
                    <option value="">Tipo de interacción...</option>
                    <option value="Visita">Visita Pastoral</option>
                    <option value="Discipulado">Discipulado</option>
                    <option value="Consejería">Consejería</option>
                    <option value="Disciplina">Disciplina</option>
                    <option value="Mensajería">Mensajería</option>
                    <option value="Videollamada">Videollamada</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
            <div class="form-group">
                <textarea name="descripcion" placeholder="Detalle de la interacción..." style="width:100%; height:80px; padding:10px; background:rgba(255,255,255,0.05); color:white; border:1px solid rgba(255,255,255,0.1); border-radius:8px;" required></textarea>
            </div>
            <button type="submit" style="width:100%; padding:10px; background:var(--calypso); border:none; color:black; font-weight:bold; border-radius:8px; cursor:pointer;">Registrar Nota</button>
        </form>

        <h3 style="font-size:0.9rem; color:var(--calypso); margin-bottom:10px;">Historial</h3>
        <ul id="listaInteracciones" class="timeline">
            </ul>
    </div>
</div>

<div id="modalAsistencia" class="modal">
    <div class="modal-content"> 
        <div class="modal-header">
            <h2>Asistencia</h2>
            <button class="modal-close" onclick="cerrarModal('modalAsistencia')">&times;</button>
        </div>
        <div class="chart-wrapper">
            <canvas id="graficoAsistencia"></canvas>
        </div>
        <div id="mensajeSinDatos" style="text-align:center; padding:20px; color:var(--muted); display:none;">
            Sin registros de asistencia.
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets/js/miembros.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>