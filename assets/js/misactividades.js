document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('activityDetailModal');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        allDayText: 'Todo el día',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },
        // CORRECCIÓN: Usar events como función con fechas dinámicas
        events: function(info, successCallback, failureCallback) {
            const formData = new FormData();
            formData.append('accion', 'getCalendarEvents');
            formData.append('start', info.startStr.split('T')[0]);
            formData.append('end', info.endStr.split('T')[0]);

            fetch('/app/controllers/actividadesController.php', {
                method: 'POST',
                body: formData
            })
            .then(r => {
                if (!r.ok) throw new Error('Error en la respuesta del servidor');
                return r.json();
            })
            .then(eventos => {
                console.log('Eventos recibidos:', eventos);
                successCallback(eventos);
            })
            .catch(error => {
                console.error('Error al cargar eventos:', error);
                failureCallback(error);
            });
        },
        eventClick: function(info) {
            mostrarDetalle(info.event);
        },
        eventDisplay: 'block',
        displayEventTime: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        },
        height: 'auto',
        navLinks: true,
        editable: false,
        dayMaxEvents: true
    });

    calendar.render();
});

function mostrarDetalle(event) {
    const props = event.extendedProps;
    
    document.getElementById('modalTitulo').textContent = event.title;

    let detalle = '';

    // Según el tipo de evento
    if (props.tipo === 'actividad') {
        const tipos = {
            'culto': 'Culto',
            'reunion': 'Reunión',
            'ensayo': 'Ensayo',
            'evento_especial': 'Evento Especial',
            'otro': 'Otro'
        };

        detalle = `
            <div class="detail-row">
                <i class="fas fa-tag detail-icon"></i>
                <span class="detail-label">Tipo:</span>
                <span class="detail-value">${tipos[event.extendedProps.tipo] || 'N/A'}</span>
            </div>

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
                <i class="fas fa-pray detail-icon"></i>
                <span class="detail-label">Tipo:</span>
                <span class="detail-value">Petición de Oración</span>
            </div>

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
                <i class="fas fa-user detail-icon"></i>
                <span class="detail-label">Solicitante:</span>
                <span class="detail-value">${props.solicitante}</span>
            </div>

            ${props.desc ? `
            <div class="detail-row">
                <i class="fas fa-hands-praying detail-icon"></i>
                <span class="detail-label">Petición:</span>
                <span class="detail-value" style="font-style:italic;">"${props.desc}"</span>
            </div>` : ''}
        `;
    }
    else if (props.tipo === 'cumpleanos') {
        detalle = `
            <div class="detail-row">
                <i class="fas fa-birthday-cake detail-icon"></i>
                <span class="detail-label">Tipo:</span>
                <span class="detail-value">Cumpleaños</span>
            </div>

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

            <div class="detail-row" style="background: rgba(255,107,107,0.1); border-left: 3px solid #ff6b6b;">
                <i class="fas fa-gift detail-icon" style="color: #ff6b6b;"></i>
                <span class="detail-value" style="color: white; font-size: 1.1rem;">
                    ¡Que Dios bendiga a <strong>${event.title.replace('🎂 ', '')}</strong> en su día especial!
                </span>
            </div>
        `;
    }

    document.getElementById('modalDetalle').innerHTML = detalle;
    document.getElementById('activityDetailModal').classList.add('active');
}

function closeDetailModal() {
    document.getElementById('activityDetailModal').classList.remove('active');
}

window.addEventListener('click', (e) => {
    if (e.target.id === 'activityDetailModal') {
        closeDetailModal();
    }
});