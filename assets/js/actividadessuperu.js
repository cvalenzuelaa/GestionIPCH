document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('activityModal');
    const form = document.getElementById('activityForm');
    const responsableSelect = document.getElementById('responsableSelect');

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
                .then(d => d.error ? failureCallback(d.error) : successCallback(d))
                .catch(e => failureCallback(e));
        },

        dateClick: function(info) {
            form.reset();
            document.getElementById('fecha').value = info.dateStr;
            modal.classList.add('active');
        },

        eventClick: function(info) {
            mostrarDetalle(info.event);
        }
    });

    window.calendarObj.render();

    window.mostrarAlerta = function(tipo, titulo, mensaje) {
        const color = tipo === 'success' ? '#18c5a3' : '#ff6b6b';
        const icon = tipo === 'success' ? '✅' : '❌';
        
        const alertDiv = document.createElement('div');
        alertDiv.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: ${color}; color: white; padding: 20px;
            border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            font-weight: bold; animation: slideIn 0.3s ease;
        `;
        alertDiv.innerHTML = `${icon} ${titulo}<br><small style="font-weight:normal;">${mensaje}</small>`;
        
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 4000);
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        formData.append('accion', 'insert');
        
        fetch('/app/controllers/actividadesController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.conflict) {
                if(confirm(`⚠️ ${data.message}\n\n¿Deseas guardar de todos modos?`)) {
                    formData.append('force_save', 'true');
                    guardarForzado(formData);
                }
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

    const btnSummary = document.getElementById('btnSummary');
    if (btnSummary) {
        btnSummary.addEventListener('click', function() {
            const date = window.calendarObj.getDate();
            const year = date.getFullYear();
            const month = date.getMonth() + 1;
            
            console.log('Descargando resumen:', year, month);
            
            window.location.href = `/app/controllers/actividadesController.php?accion=exportExcel&year=${year}&month=${month}`;
        });
    }

    window.closeModal = function() { 
        modal.classList.remove('active'); 
        form.reset(); 
    };

    window.addEventListener('click', function(e) {
        if (e.target.id === 'activityModal' || e.target.id === 'activityDetailModal') {
            e.target.classList.remove('active');
        }
    });
});

// ==========================================
// FUNCIÓN IDÉNTICA A MISACTIVIDADES.JS
// ==========================================
// BUSCA function mostrarDetalle(event) y REEMPLÁZALA COMPLETA:
function mostrarDetalle(event) {
    const props = event.extendedProps;
    
    document.getElementById('modalTitulo').textContent = event.title;

    let detalle = '';

    if (props.tipo === 'actividad') {
        // NUEVO: Badge de finalizada
        let estadoBadge = '';
        if (props.estado === 'finalizada') {
            estadoBadge = `
            <div class="detail-row" style="background: rgba(107, 114, 128, 0.2); border-left: 3px solid #6b7280; padding: 15px; margin-bottom: 10px;">
                <i class="fas fa-check-circle detail-icon" style="color: #9ca3af;"></i>
                <span class="detail-value" style="color: #9ca3af; font-size: 1.1rem; font-weight: 700;">
                    ✅ ACTIVIDAD FINALIZADA
                </span>
            </div>`;
        }

        detalle = estadoBadge + `
            <div class="detail-row">
                <i class="fas fa-calendar detail-icon"></i>
                <span class="detail-label">Fecha:</span>
                <span class="detail-value">${event.start.toLocaleDateString('es-ES', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                })}</span>
            </div>

            <div class="detail-row">
                <i class="fas fa-clock detail-icon"></i>
                <span class="detail-label">Hora:</span>
                <span class="detail-value">${event.start.toLocaleTimeString('es-ES', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                })}${event.end ? ' - ' + event.end.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) : ''}</span>
            </div>

            ${props.desc ? `
            <div class="detail-row">
                <i class="fas fa-info-circle detail-icon"></i>
                <span class="detail-label">Descripción:</span>
                <span class="detail-value">${props.desc}</span>
            </div>` : ''}

            <div class="detail-row">
                <i class="fas fa-user-tie detail-icon"></i>
                <span class="detail-label">Responsable:</span>
                <span class="detail-value">${props.responsable || 'No asignado'}</span>
            </div>
        `;
    } 
    else if (props.tipo === 'oracion') {
        detalle = `
            <div class="detail-row">
                <i class="fas fa-praying-hands detail-icon"></i>
                <span class="detail-label">Solicitante:</span>
                <span class="detail-value">${props.solicitante}</span>
            </div>

            <div class="detail-row">
                <i class="fas fa-calendar detail-icon"></i>
                <span class="detail-label">Fecha:</span>
                <span class="detail-value">${event.start.toLocaleDateString('es-ES')}</span>
            </div>

            <div class="detail-row">
                <i class="fas fa-comment-dots detail-icon"></i>
                <span class="detail-label">Motivo:</span>
                <span class="detail-value">${props.desc}</span>
            </div>
        `;
    } 
    else if (props.tipo === 'cumpleanos') {
        detalle = `
            <div class="detail-row" style="text-align:center; padding:20px;">
                <i class="fas fa-birthday-cake" style="font-size:3rem; color:#ec4899; margin-bottom:10px;"></i>
                <h3 style="color:#ec4899; margin:10px 0;">¡Feliz Cumpleaños!</h3>
                <p style="font-size:1.1rem;">${props.nombre}</p>
                <p style="color:#9ca3af;">${event.start.toLocaleDateString('es-ES', { 
                    day: 'numeric', 
                    month: 'long' 
                })}</p>
            </div>
        `;
    }

    document.getElementById('modalDetalle').innerHTML = detalle;
    document.getElementById('activityDetailModal').classList.add('active');
}

function closeDetailModal() {
    document.getElementById('activityDetailModal').classList.remove('active');
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(style);