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
            .then(data => successCallback(data))
            .catch(err => failureCallback(err));
        },

        dateClick: function(info) {
            if (typeof USER_ROLE !== 'undefined' && USER_ROLE === 'admin') {
                document.getElementById('activityDate').value = info.dateStr;
                openModal();
            }
        },

        eventClick: function(info) {
            const props = info.event.extendedProps;
            
            if (props.tipo === 'cumpleanos') {
                const nombreCompleto = info.event.title.replace('🎂 ', '');
                Swal.fire({
                    icon: 'success',
                    title: '🎂 ¡Cumpleaños!',
                    text: `Hoy es el cumpleaños de ${nombreCompleto}`,
                    confirmButtonColor: '#18c5a3'
                });
                return;
            }

            if (props.tipo === 'oracion') {
                const fechaStr = info.event.start ? new Date(info.event.start).toLocaleDateString('es-ES') : '';
                Swal.fire({
                    icon: 'info',
                    title: '🙏 Oración',
                    html: `<strong>Solicitante:</strong> ${props.solicitante}<br><strong>Fecha:</strong> ${fechaStr}<br><br>${props.desc}`,
                    confirmButtonColor: '#2b66b3'
                });
                return;
            }

            const idReal = info.event.id.replace('act_', '');

            if (props.estado === 'finalizada') {
                abrirDetallesFinalizada(info.event, props);
                return;
            }

            if (typeof USER_ROLE !== 'undefined' && USER_ROLE === 'admin') {
                Swal.fire({
                    title: '¿Qué deseas hacer?',
                    text: `Actividad: "${info.event.title}"`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#18c5a3',
                    cancelButtonColor: '#2b66b3',
                    confirmButtonText: 'Tomar Asistencia',
                    cancelButtonText: 'Ver Detalles'
                }).then((result) => {
                    if (result.isConfirmed) {
                        abrirAsistencia(idReal, info.event.title);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        abrirDetalles(info.event, props);
                    }
                });
            } else {
                abrirDetalles(info.event, props);
            }
        }
    });

    window.calendarObj.render();

    function cargarResponsables() {
        const fd = new FormData();
        fd.append('accion', 'getResponsables');
        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            responsableSelect.innerHTML = '<option value="">Seleccionar responsable</option>';
            data.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.idmiembro;
                opt.textContent = `${m.nombre} ${m.apellido}`;
                responsableSelect.appendChild(opt);
            });
        });
    }

    window.openModal = function() { modal.classList.add('active'); };
    window.closeModal = function() { modal.classList.remove('active'); form.reset(); };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(form);
        fd.append('accion', 'insert');

        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.conflict) {
                Swal.fire({
                    title: 'Conflicto de horario',
                    html: data.message + ':<br><br>' + data.details.join('<br>'),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar de todas formas',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fd.append('force_save', '1');
                        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire('¡Éxito!', 'Actividad creada', 'success');
                                closeModal();
                                window.calendarObj.refetchEvents();
                            } else {
                                Swal.fire('Error', res.error, 'error');
                            }
                        });
                    }
                });
            } else if (data.success) {
                Swal.fire('¡Éxito!', 'Actividad creada', 'success');
                closeModal();
                window.calendarObj.refetchEvents();
            } else {
                Swal.fire('Error', data.error, 'error');
            }
        });
    });

    window.abrirDetalles = function(event, props) {
        const fecha = event.start.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' });
        const inicio = event.start.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        const fin = event.end ? event.end.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : '??:??';
        
        document.getElementById('detailTitle').innerText = event.title;
        document.getElementById('detailDate').innerText = fecha.charAt(0).toUpperCase() + fecha.slice(1);
        document.getElementById('detailTime').innerText = `${inicio} - ${fin} hrs`;
        document.getElementById('detailType').innerText = props.tipo ? props.tipo.charAt(0).toUpperCase() + props.tipo.slice(1) : 'Actividad';
        document.getElementById('detailResp').innerText = props.responsable || 'No asignado';
        document.getElementById('detailDesc').innerText = props.desc || 'Sin descripción';
        detailModal.classList.add('active');
    };

    window.abrirDetallesFinalizada = function(event, props) {
        abrirDetalles(event, props);
        const detailTitle = document.getElementById('detailTitle');
        detailTitle.innerHTML = `${event.title} <span style="background: rgba(107, 114, 128, 0.3); color: #9ca3af; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; margin-left: 10px; border: 1px solid #6b7280;">✅ Finalizada</span>`;
    };

    window.closeDetailsModal = function() { detailModal.classList.remove('active'); };

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
            if(data.success) {
                renderAttendanceTable(data.miembros);
            } else {
                Swal.fire('Error', data.error, 'error');
                closeAttendanceModal();
            }
        });
    };

    function renderAttendanceTable(miembros) {
        let html = '';
        let count = 0;
        
        miembros.forEach(m => {
            const isChecked = m.asistencia_estado === 'Presente';
            if(isChecked) count++;
            
            html += `
            <tr class="${isChecked ? 'presente' : ''}">
                <td>
                    <div class="member-info">
                        <span class="member-name">${m.apellido}, ${m.nombre}</span>
                        <span class="member-type">${m.estado}</span>
                    </div>
                </td>
                <td style="text-align:center;">
                    <span class="status-badge ${isChecked ? 'presente' : 'ausente'}">${isChecked ? 'Presente' : 'Ausente'}</span>
                </td>
                <td style="text-align:center;">
                    <input type="checkbox" class="attendance-checkbox att-check" value="${m.idmiembro}" ${isChecked ? 'checked' : ''} onchange="updateRowStyle(this)">
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

    window.closeAttendanceModal = function() { attModal.classList.remove('active'); };

    window.submitAttendance = function() {
        const idActividad = document.getElementById('att_idactividad').value;
        const checks = document.querySelectorAll('.att-check:checked');
        const asistentes = [];
        
        checks.forEach(c => asistentes.push({ id: c.value, estado: 'Presente' }));

        const fd = new FormData();
        fd.append('accion', 'saveAttendance');
        fd.append('idactividad', idActividad);
        fd.append('asistentes', JSON.stringify(asistentes));

        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.success) { 
                Swal.fire('¡Éxito!', 'Asistencia guardada', 'success');
                closeAttendanceModal();
            } else { 
                Swal.fire('Error', data.error, 'error');
            }
        });
    };

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            attBody.querySelectorAll('tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    }
});