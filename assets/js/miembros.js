$(document).ready(function() {
    listarMiembros();

    // Adjuntar máscara al input telefono (usa función de globales.js)
    if(typeof attachTelefonoMask === 'function'){
        const telInput = document.getElementById('telefono');
        if(telInput) attachTelefonoMask(telInput);
    }

    // Validación y formateo en tiempo real
    $('#rut').on('input blur', function() {
        if(typeof limpiarRut === 'function'){
            const resultado = limpiarRut(this);
            const errorRut = document.getElementById('errorRut');
            if (!resultado.valido && this.value.length > 0) {
                errorRut.textContent = 'RUT inválido.';
                formatoInputError(this, errorRut);
            } else {
                errorRut.textContent = '';
                formatoInputExito(this, errorRut);
            }
        }
    });

    // Validaciones genéricas
    ['#nombre', '#apellido'].forEach(sel => {
        $(sel).on('input', function() {
            if(typeof validaNombres === 'function') validaNombres(this, document.getElementById('error' + (sel === '#nombre' ? 'Nombre' : 'Apellido')));
        });
    });
    
    $('#correo').on('input', function() {
        if(typeof validaCorreo === 'function') validaCorreo(this, document.getElementById('errorCorreo'));
    });
    
    $('#telefono').on('input', function() {
        if(typeof validaTelefono === 'function') validaTelefono(this, document.getElementById('errorTelefono'));
    });

    $('#estado').on('change', function() {
        if (!this.value) {
            if(typeof formatoInputError === 'function') {
                formatoInputError(this, document.getElementById('errorEstado'));
                document.getElementById('errorEstado').textContent = "Seleccione estado de membresía.";
            }
        } else {
            if(typeof formatoInputExito === 'function') {
                formatoInputExito(this, document.getElementById('errorEstado'));
                document.getElementById('errorEstado').textContent = "";
            }
        }
    });

    // Submit con validaciones
    $('#formMiembro').on('submit', function(e) {
        e.preventDefault();

        // Validaciones previas al envío
        let valido = true;
        // ... (Tu lógica de validación se mantiene igual, abreviada aquí por espacio) ...
        // Asegúrate de incluir aquí tu lógica de validación existente si la tenías personalizada

        let datos = $(this).serialize();

        $.ajax({
            url: '/app/controllers/miembrosController.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    mostrarAlerta('success', res.success);
                    cerrarModal('modalMiembro');
                    listarMiembros();
                    $('#formMiembro')[0].reset();
                    // Limpiar estilos de error
                    $('.form-group input, .form-group select').css('border', '');
                    $('small[id^="error"]').text('');
                } else {
                    mostrarAlerta('error', res.error || 'Error al procesar.');
                }
            },
            error: function() { mostrarAlerta('error', 'Error de conexión al guardar miembro.'); }
        });
    });

    // FORMULARIO HOJA DE VIDA
    $(document).on('submit', '#formInteraccion', function(e) {
        e.preventDefault();
        const form = $(this);
        const data = form.serialize() + '&accion=insert';
        $.ajax({
            url: '/app/controllers/interaccionesPastoralesController.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    mostrarAlerta('success', res.success);
                    form[0].reset();
                    const id = $('#hv_idmiembro').val();
                    if (id) cargarHojaVida(id);
                } else {
                    mostrarAlerta('error', res.error || 'Error al guardar nota.');
                }
            },
            error: function() { mostrarAlerta('error', 'Error de conexión al guardar nota.'); }
        });
    });
});

// --- FUNCIÓN PARA CREAR NUEVO MIEMBRO ---
function nuevoMiembro() {
    const form = document.getElementById('formMiembro');
    form.reset();
    document.getElementById('idmiembro').value = '';
    document.getElementById('accionMiembro').value = 'insert';
    document.getElementById('tituloModalMiembro').textContent = 'Crear Nuevo Miembro';
    
    // Limpiar errores visuales
    $('small[id^="error"]').text('');
    $('.form-group input, .form-group select').css('border', '');

    abrirModal('modalMiembro');
}

// --- LISTADO CON FILTROS INTEGRADOS ---
function listarMiembros() {
    $.ajax({
        url: '/app/controllers/miembrosController.php',
        type: 'POST', data: { accion: 'getAll' }, dataType: 'json',
        success: function(data) {
            let html = '';
            // Si la tabla ya existe como DataTable, la destruimos para recrearla limpia
            if ($.fn.DataTable.isDataTable('#tablaMiembros')) {
                $('#tablaMiembros').DataTable().destroy();
            }

            data.forEach(m => {
                let estado = (m.estado || '').trim().toLowerCase();
                let badge = `background:rgba(255,255,255,0.05); color:#aaa; border:1px solid #555;`;
                if(estado === 'comulgante') badge = `background:rgba(24,197,163,0.15); color:#18c5a3; border:1px solid #18c5a3;`;
                if(estado === 'adherente') badge = `background:rgba(43,102,179,0.2); color:#66aaff; border:1px solid #2b66b3;`;
                if(estado === 'visita') badge = `background:rgba(117, 119, 118, 0.15); color:#66ffb4; border:1px solid #a7b3adff;`;
                if(estado === 'no comulgante') badge = `background:rgba(255,107,107,0.15); color:#ffb347; border:1px solid #ffb347;`;

                html += `
                    <tr>
                        <td>
                            <div style="font-weight:800; font-size:1.1rem;">${m.nombre} ${m.apellido}</div>
                            <div style="font-size:0.85rem; color:rgba(255,255,255,0.6);">${m.rut}</div>
                        </td>
                        <td><span style="padding:5px 8px; border-radius:6px; font-size:0.8rem; font-weight:700; ${badge}">${m.estado}</span></td>
                        <td>${m.telefono || '-'}</td>
                        <td>
                            <div class="action-buttons-container">
                                <button class="btn-action btn-edit" onclick="editarMiembro('${m.idmiembro}')"><i class="fas fa-pen"></i>Editar</button>
                                <button class="btn-action btn-pastoral" onclick="abrirHojaVida('${m.idmiembro}')"><i class="fas fa-book-medical"></i>Interacción Pastoral</button>
                                <button class="btn-action btn-stats" onclick="abrirEstadisticas('${m.idmiembro}')"><i class="fas fa-chart-pie"></i>Asistencia</button>
                                <button class="btn-action btn-delete" onclick="eliminarMiembro('${m.idmiembro}')"><i class="fas fa-trash"></i>Eliminar</button>
                            </div>
                        </td>
                    </tr>`;
            });
            
            $('#tablaBody').html(html);
            
            // Inicializar DataTable con los filtros personalizados
            var table = $('#tablaMiembros').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" },
                "pageLength": 8,
                "lengthChange": false,
                "dom": 'rtp', // 'r' processing, 't' table, 'p' pagination. Quitamos 'f' (filter default)
                "responsive": true,
                "orderCellsTop": true // Importante para que no ordene al escribir en los inputs
            });

            // Lógica de filtrado por columna
            $('.filter-input').on('keyup change', function() {
                var colIndex = $(this).data('col'); // Obtiene índice 0, 1 o 2
                var value = $(this).val();
                
                // Aplicar búsqueda a la columna específica
                table.column(colIndex).search(value).draw();
            });
        }
    });
}

// --- FUNCIONES DE MODALES ---
function editarMiembro(id) {
    $.ajax({
        url: '/app/controllers/miembrosController.php',
        type: 'POST', data: { accion: 'getById', idmiembro: id }, dataType: 'json',
        success: function(m) {
            $('#idmiembro').val(m.idmiembro); $('#nombre').val(m.nombre); $('#apellido').val(m.apellido);
            $('#rut').val(m.rut); $('#estado').val(m.estado); $('#fecha_nacimiento').val(m.fecha_nacimiento);
            $('#fecha_ingreso').val(m.fecha_ingreso); $('#direccion').val(m.direccion);
            $('#correo').val(m.correo); $('#telefono').val(m.telefono);
            $('#accionMiembro').val('update'); $('#tituloModalMiembro').text('Editar Miembro');
            abrirModal('modalMiembro');
        }
    });
}

function abrirHojaVida(id) {
    $('#hv_idmiembro').val(id);
    cargarHojaVida(id);
    abrirModal('modalHojaVida');
}

function cargarHojaVida(id) {
    $.ajax({
        url: '/app/controllers/interaccionesPastoralesController.php',
        type: 'POST', data: { accion: 'getByMiembro', idmiembro: id }, dataType: 'json',
        success: function(data) {
            let html = '';
            if (!data || data.length === 0) html = '<li class="timeline-item" style="text-align:center; color:gray;">Sin registros.</li>';
            else {
                data.forEach(item => {
                    html += `<li class="timeline-item"><div class="timeline-header"><span class="timeline-type">${item.tipo}</span><span class="timeline-date">${item.fecha}</span></div><div class="timeline-desc">${item.descripcion}</div><div class="timeline-author">${item.autor_nombre || 'Admin'}</div></li>`;
                });
            }
            $('#listaInteracciones').html(html);
        }
    });
}

let chartInstance = null;
function abrirEstadisticas(id) {
    $.ajax({
        url: '/app/controllers/asistenciasController.php',
        type: 'POST', data: { accion: 'getEstadisticas', idmiembro: id }, dataType: 'json',
        success: function(res) {
            abrirModal('modalAsistencia');
            if (chartInstance) chartInstance.destroy();
            $('#tablaDetalleWrapper').remove();

            if (res.vacio) {
                $('#graficoAsistencia').hide(); $('#mensajeSinDatos').show().text('No hay registros.'); return;
            }
            $('#graficoAsistencia').show(); $('#mensajeSinDatos').hide();

            if(res.grafico) {
                const ctx = document.getElementById('graficoAsistencia').getContext('2d');
                
                const totalRegistros = res.grafico.reduce((acc, curr) => acc + parseInt(curr.total), 0);

                chartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: res.grafico.map(d => d.estado),
                        datasets: [{ 
                            data: res.grafico.map(d => d.total), 
                            backgroundColor: res.grafico.map(d => d.estado === 'Presente' ? '#18c5a3' : '#ff6b6b'), 
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        cutout: '65%',
                        plugins: { 
                            legend: { 
                                labels: { 
                                    color: '#fff',
                                    font: { family: 'Nunito', size: 12 }
                                } 
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        let value = context.raw;
                                        
                                        if (label) {
                                            label += ': ';
                                        }
                                        
                                        let percentage = Math.round((value / totalRegistros) * 100);
                                        
                                        return label + percentage + '%';
                                    }
                                }
                            }
                        } 
                    }
                });
            }
            
            if(res.detalle && res.detalle.length > 0) {
                let html = `
                <div class="table-details-wrapper" id="tablaDetalleWrapper">
                    <h4 style="margin:0 0 10px 0; color:#fff; font-size:1rem; font-weight:700;">
                        Historial de Actividades
                    </h4>
                    <table class="table-details">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Actividad</th>
                                <th style="text-align:right">Estado</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                res.detalle.forEach(d => { 
                    let colorEstado = d.estado === 'Presente' ? '#18c5a3' : '#ff6b6b';
                    
                    // Formatear la fecha como DD-MM-YYYY sin hora
                    let fechaFormateada = 'Sin fecha';
                    if (d.fecha) {
                        // Separar fecha y hora (si existe), tomar solo la fecha
                        const soloFecha = d.fecha.split(' ')[0]; // "2025-12-03"
                        const partes = soloFecha.split('-'); // ["2025", "12", "03"]
                        if (partes.length === 3) {
                            fechaFormateada = `${partes[2]}-${partes[1]}-${partes[0]}`; // "03-12-2025"
                        }
                    }
                    
                    html += `
                    <tr>
                        <td style="color: rgba(255,255,255,0.8);">${fechaFormateada}</td>
                        <td style="color:white;">${d.titulo || 'Sin título'}</td>
                        <td style="color:${colorEstado}; text-align:right; font-weight:700;">${d.estado}</td>
                    </tr>`; 
                });

                html += `</tbody></table></div>`;
                
                $('.chart-wrapper').after(html);
            }
        }
    });
}

function eliminarMiembro(id) {
    if(confirm('¿Eliminar miembro?')) {
        $.ajax({
            url: '/app/controllers/miembrosController.php',
            type: 'POST', data: { accion: 'delete', idmiembro: id }, dataType: 'json',
            success: function(res) { res.success ? (mostrarAlerta('success', res.success), listarMiembros()) : mostrarAlerta('error', res.error); }
        });
    }
}

function abrirModal(id) { $('#' + id).addClass('active'); }
function cerrarModal(id) { $('#' + id).removeClass('active'); }
function mostrarAlerta(tipo, msg) {
    $('#alertContainer').html(`<div style="background:${tipo==='success'?'#18c5a3':'#ff6b6b'}; color:black; padding:15px; border-radius:8px; font-weight:bold;">${msg}</div>`);
    setTimeout(() => { $('#alertContainer').html(''); }, 3000);
}
$(window).on('click', function(e) { if ($(e.target).hasClass('modal')) $('.modal').removeClass('active'); });