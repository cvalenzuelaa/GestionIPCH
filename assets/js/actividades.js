document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('activityModal');
    const form = document.getElementById('activityForm');
    const responsableSelect = document.getElementById('responsableSelect');

    const attModal = document.getElementById('attendanceModal');
    const attBody = document.getElementById('attendanceListBody');
    const searchInput = document.getElementById('searchMemberAtt');

    const detailModal = document.getElementById('detailsModal');

    cargarResponsables();

    // ==========================================
    // CONFIGURACIÓN DEL CALENDARIO
    // ==========================================
    window.calendarObj = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana' },
        navLinks: true, 
        dayMaxEvents: true, 
        height: 'auto',

        events: function(info, successCallback, failureCallback) {
            const fd = new FormData();
            fd.append('accion', 'getCalendarEvents');
            fd.append('start', info.startStr);
            fd.append('end', info.endStr);
            fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => d.error ? failureCallback(d.error) : successCallback(d))
                .catch(e => failureCallback(e));
        },

        dateClick: function(info) {
            if(typeof USER_ROLE !== 'undefined' && USER_ROLE !== 'admin') return;
            form.reset();
            document.getElementById('fecha').value = info.dateStr;
            modal.classList.add('active');
        },

        eventClick: function(info) {
            const props = info.event.extendedProps;
            
            // CUMPLEAÑOS
            if (props.tipo === 'cumpleanos') {
                const nombreCompleto = info.event.title.replace('🎂 ', '');
                mostrarModalCumpleanos(nombreCompleto);
                return;
            }

            // ORACIONES
            if (props.tipo === 'oracion') {
                mostrarModalOracion(
                    props.solicitante, 
                    props.desc,
                    info.event.start
                );
                return;
            }

            // ACTIVIDADES
            const idReal = info.event.id.replace('act_', '');

            if (typeof USER_ROLE !== 'undefined' && USER_ROLE === 'admin') {
                mostrarConfirmacion(
                    '¿Qué deseas hacer?',
                    `Actividad: "${info.event.title}"\n\n¿Deseas registrar la asistencia o ver los detalles?`,
                    'Tomar Asistencia',
                    'Ver Detalles',
                    function(result) {
                        if (result) {
                            abrirAsistencia(idReal, info.event.title);
                        } else {
                            abrirDetalles(info.event, props);
                        }
                    }
                );
            } else {
                abrirDetalles(info.event, props);
            }
        }
    });

    window.calendarObj.render();

    // ==========================================
    // FUNCIONES DE MODALES PERSONALIZADOS
    // ==========================================

    // Modal de Cumpleaños
    window.mostrarModalCumpleanos = function(nombre) {
        document.getElementById('birthdayName').textContent = nombre;
        document.getElementById('birthdayModal').classList.add('active');
    };

    window.closeBirthdayModal = function() {
        document.getElementById('birthdayModal').classList.remove('active');
    };

    // Modal de Oración
    window.mostrarModalOracion = function(solicitante, descripcion, fecha) {
        document.getElementById('prayerSolicitante').textContent = solicitante || 'Anónimo';
        document.getElementById('prayerDesc').textContent = descripcion || 'Sin descripción';
        
        if (fecha) {
            const fechaFormato = fecha.toLocaleDateString('es-ES', { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            });
            const horaFormato = fecha.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
            document.getElementById('prayerDate').textContent = fechaFormato;
            document.getElementById('prayerTime').textContent = horaFormato;
        }
        
        document.getElementById('prayerModal').classList.add('active');
    };

    window.closePrayerModal = function() {
        document.getElementById('prayerModal').classList.remove('active');
    };

    // Modal de Detalles
    window.abrirDetalles = function(event, props) {
        document.getElementById('detailTitle').innerText = event.title;
        
        const fecha = event.start.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            day: 'numeric', 
            month: 'long' 
        });
        const inicio = event.start.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        const fin = event.end ? event.end.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : '??:??';
        
        document.getElementById('detailDate').innerText = fecha.charAt(0).toUpperCase() + fecha.slice(1);
        document.getElementById('detailTime').innerText = `${inicio} - ${fin} hrs`;
        document.getElementById('detailType').innerText = props.tipo ? 
            props.tipo.charAt(0).toUpperCase() + props.tipo.slice(1) : 'Actividad';
        document.getElementById('detailResp').innerText = props.responsable || 'No asignado';
        document.getElementById('detailDesc').innerText = props.desc || 'No hay descripción disponible.';

        detailModal.classList.add('active');
    };

    window.closeDetailsModal = function() {
        detailModal.classList.remove('active');
    };

    // Modal de Confirmación
    let confirmCallback = null;

    window.mostrarConfirmacion = function(pregunta, mensaje, btnSi = 'Aceptar', btnNo = 'Cancelar', callback) {
        document.getElementById('confirmQuestion').textContent = pregunta;
        document.getElementById('confirmMessage').textContent = mensaje;
        
        // Actualizar textos de botones
        const btnYes = document.querySelector('.btn-confirm-yes');
        const btnNoBtn = document.querySelector('.btn-confirm-no');
        
        btnYes.innerHTML = `<i class="fas fa-check"></i> ${btnSi}`;
        btnNoBtn.innerHTML = `<i class="fas fa-times"></i> ${btnNo}`;
        
        confirmCallback = callback;
        document.getElementById('confirmModal').classList.add('active');
    };

    window.closeConfirmModal = function(result) {
        document.getElementById('confirmModal').classList.remove('active');
        if (confirmCallback) {
            confirmCallback(result);
            confirmCallback = null;
        }
    };

    // Modal de Alerta (Success/Error)
    window.mostrarAlerta = function(tipo, titulo, mensaje) {
        const iconEl = document.getElementById('alertIcon');
        
        if (tipo === 'success') {
            iconEl.className = 'alert-icon success';
            iconEl.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else {
            iconEl.className = 'alert-icon error';
            iconEl.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
        }
        
        document.getElementById('alertTitle').textContent = titulo;
        document.getElementById('alertMessage').textContent = mensaje;
        document.getElementById('alertModal').classList.add('active');
    };

    window.closeAlertModal = function() {
        document.getElementById('alertModal').classList.remove('active');
    };

    // ==========================================
    // LÓGICA DE ASISTENCIA
    // ==========================================
    window.abrirAsistencia = function(idActividad, titulo) {
        document.getElementById('attTitle').innerText = 'Asistencia: ' + titulo;
        document.getElementById('att_idactividad').value = idActividad;
        attBody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:20px;">Cargando...</td></tr>';
        attModal.classList.add('active');

        const fd = new FormData();
        fd.append('accion', 'getAttendanceData');
        fd.append('idactividad', idActividad);

        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.error) { 
                mostrarAlerta('error', 'Error', data.error);
                closeAttendanceModal(); 
                return; 
            }
            renderAttendanceTable(data.miembros, data.asistencia);
        });
    };

    function renderAttendanceTable(miembros, asistenciaMap) {
        let html = '';
        let count = 0;
        miembros.forEach(m => {
            const estadoActual = asistenciaMap[m.idmiembro] || 'Ausente'; 
            const isChecked = estadoActual === 'Presente';
            if(isChecked) count++;
            
            const rowClass = isChecked ? 'presente' : '';
            
            html += `
            <tr class="${rowClass}">
                <td>
                    <div class="member-info">
                        <span class="member-name">${m.apellido}, ${m.nombre}</span>
                        <span class="member-type">${m.estado}</span>
                    </div>
                </td>
                <td style="text-align:center;">
                    <span class="status-badge ${isChecked ? 'presente' : 'ausente'}">
                        ${isChecked ? 'Presente' : 'Ausente'}
                    </span>
                </td>
                <td style="text-align:center;">
                    <input type="checkbox" class="attendance-checkbox att-check" value="${m.idmiembro}" ${isChecked ? 'checked' : ''} 
                        onchange="updateRowStyle(this)">
                </td>
            </tr>`;
        });
        attBody.innerHTML = html;
        document.getElementById('totalPresentes').innerText = count;
    }

    window.updateRowStyle = function(chk) {
        const row = chk.closest('tr');
        const badge = row.querySelector('.status-badge');
        
        if(chk.checked) {
            row.classList.add('presente');
            badge.classList.remove('ausente');
            badge.classList.add('presente');
            badge.innerText = 'Presente';
        } else {
            row.classList.remove('presente');
            badge.classList.remove('presente');
            badge.classList.add('ausente');
            badge.innerText = 'Ausente';
        }
        
        document.getElementById('totalPresentes').innerText = document.querySelectorAll('.att-check:checked').length;
    };

    window.updateRowStyle = function(chk) {
        const row = chk.closest('tr');
        const badge = row.querySelector('.badge');
        if(chk.checked) {
            row.style.background = '#e6fffa'; 
            badge.style.background = '#10b981'; 
            badge.innerText = 'Presente';
        } else {
            row.style.background = '#fff'; 
            badge.style.background = '#ef4444'; 
            badge.innerText = 'Ausente';
        }
        document.getElementById('totalPresentes').innerText = document.querySelectorAll('.att-check:checked').length;
    };

    window.submitAttendance = function() {
        const idActividad = document.getElementById('att_idactividad').value;
        const checks = document.querySelectorAll('.att-check:checked');
        const lista = [];
        checks.forEach(c => lista.push({ id: c.value, estado: 'Presente' }));

        const fd = new FormData();
        fd.append('accion', 'saveAttendance');
        fd.append('idactividad', idActividad);
        fd.append('lista', JSON.stringify(lista));

        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.success) { 
                mostrarAlerta('success', '✅ Éxito', 'Asistencia guardada correctamente');
                closeAttendanceModal(); 
            } else { 
                mostrarAlerta('error', '❌ Error', data.error || 'No se pudo guardar');
            }
        });
    };

    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            attBody.querySelectorAll('tr').forEach(r => {
                r.style.display = r.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }

    window.closeAttendanceModal = function() { 
        attModal.classList.remove('active'); 
    };

    // ==========================================
    // GUARDAR ACTIVIDAD
    // ==========================================
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        formData.append('accion', 'insert');
        
        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.conflict) {
                mostrarConfirmacion(
                    '⚠️ Conflicto de Horario',
                    data.message + '\n\n¿Deseas guardar de todos modos?',
                    'Guardar igual',
                    'Cancelar',
                    function(result) {
                        if (result) {
                            formData.append('force_save', 'true');
                            guardarForzado(formData);
                        }
                    }
                );
            } else if (data.success) {
                mostrarAlerta('success', '✅ Éxito', 'Actividad guardada correctamente');
                form.reset(); 
                closeModal(); 
                window.calendarObj.refetchEvents();
            } else { 
                mostrarAlerta('error', '❌ Error', data.error || 'Error desconocido');
            }
        });
    });

    function guardarForzado(fd) {
        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if(data.success) { 
                mostrarAlerta('success', '✅ Guardado', 'Actividad creada correctamente');
                form.reset(); 
                closeModal(); 
                window.calendarObj.refetchEvents(); 
            }
        });
    }

    function cargarResponsables() {
        const fd = new FormData();
        fd.append('accion', 'getResponsables');
        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            let html = '<option value="">Seleccione...</option>';
            if(Array.isArray(data)) {
                data.forEach(m => html += `<option value="${m.idmiembro}">${m.nombre} ${m.apellido}</option>`);
            }
            if(responsableSelect) responsableSelect.innerHTML = html;
        });
    }

    const btnSum = document.getElementById('btnSummary');
    if(btnSum) {
        btnSum.addEventListener('click', function() {
            const date = window.calendarObj.getDate();
            window.location.href = `/app/controllers/actividadesController.php?accion=exportExcel&year=${date.getFullYear()}&month=${date.getMonth() + 1}`;
        });
    }

    window.closeModal = function() { 
        modal.classList.remove('active'); 
        form.reset(); 
    };

    // Cerrar modales al hacer clic fuera
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });
});