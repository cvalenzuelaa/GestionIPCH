document.addEventListener('DOMContentLoaded', function() {
    checkNotificaciones();
    // Polling cada 30 seg
    setInterval(checkNotificaciones, 30000);
});

function checkNotificaciones() {
    const fd = new FormData();
    fd.append('accion', 'check');

    fetch('/app/controllers/notificacionesController.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notifCount');
        const lista = document.getElementById('notifList');

        // 1. Calcular conteo PENDIENTES
        const pendientes = data.filter(n => n.estado === 'pendiente');
        
        if (badge) {
            if (pendientes.length > 0) {
                badge.style.display = 'flex';
                badge.innerText = pendientes.length;
            } else {
                badge.style.display = 'none';
            }
        }

        // 2. Construir lista HTML
        if (lista) {
            if (data.length === 0) {
                lista.innerHTML = '<div style="padding:20px; text-align:center; color:#888; font-size:0.9rem;">No tienes notificaciones.</div>';
            } else {
                let html = '';
                data.forEach(n => {
                    let icono = 'fa-info-circle';
                    let color = '#3b82f6';

                    // ICONOS Y COLORES SEGÚN TIPO
                    if(n.tipo === 'actividad') { 
                        icono = 'fa-calendar-check'; 
                        color = '#10b981'; // Verde
                    }
                    if(n.tipo === 'cumpleanos') { 
                        icono = 'fa-birthday-cake'; 
                        color = '#ef4444'; // Rojo
                    }
                    if(n.tipo === 'oracion') { 
                        icono = 'fa-hands-praying'; 
                        color = '#f59e0b'; // Naranja
                    }
                    if(n.tipo === 'alabanza') { 
                        icono = 'fa-music'; 
                        color = '#8b5cf6'; // Púrpura
                    }

                    let itemStyle = '';
                    let iconStyle = `background:${color}20; color:${color};`;
                    
                    // Si está leída, aplicar opacidad
                    if (n.estado === 'leida') {
                        itemStyle = 'opacity: 0.6; background: rgba(255,255,255,0.02);';
                        iconStyle = 'background: #334155; color: #94a3b8;';
                    }

                    html += `
                    <div class="notif-item" style="${itemStyle}" onclick="gestionarClick('${n.idnotificacion}', '${n.tipo}')">
                        <div class="notif-icon-box" style="${iconStyle}">
                            <i class="fas ${icono}"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-title" style="${n.estado === 'leida' ? 'color:#aaa' : ''}">${n.titulo}</div>
                            <div class="notif-msg">${n.mensaje}</div>
                            <div style="font-size:0.7rem; color:#666; margin-top:2px;">
                                ${formatDate(n.fecha_creacion)}
                            </div>
                        </div>
                        ${n.estado === 'pendiente' ? '<div class="notif-dot"></div>' : ''}
                    </div>`;
                });
                lista.innerHTML = html;
            }
        }
    })
    .catch(err => console.error('Error:', err));
}

function formatDate(dateString) {
    const d = new Date(dateString);
    const hoy = new Date();
    const ayer = new Date(hoy);
    ayer.setDate(ayer.getDate() - 1);

    // Si es de hoy
    if (d.toDateString() === hoy.toDateString()) {
        return 'Hoy ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
    // Si es de ayer
    if (d.toDateString() === ayer.toDateString()) {
        return 'Ayer ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
    // Fecha normal
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

function toggleNotificaciones() {
    const menu = document.getElementById('notifDropdown');
    const userMenu = document.getElementById('dropdownMenu');
    
    if (menu) {
        menu.classList.toggle('active');
        
        // Si abrimos notificaciones, CERRAR menú de usuario si está abierto
        if (userMenu && userMenu.classList.contains('active')) {
            userMenu.classList.remove('active');
        }
    }
}

// ...existing code...

function gestionarClick(id, tipo) {
    // Marcar como leída
    const fd = new FormData();
    fd.append('accion', 'markRead');
    fd.append('idnotificacion', id);
    fetch('/app/controllers/notificacionesController.php', { method: 'POST', body: fd });

    // Redirigir según tipo y rol del usuario
    let url = '/dashadmin';
    
    // Detectar si es superusuario
    const isSuper = window.location.pathname.includes('superu');
    
    switch(tipo) {
        case 'alabanza': 
            url = isSuper ? '/alabanzassuperu' : '/alabanzas'; 
            break;
        case 'oracion': 
            url = isSuper ? '/oracionessuperu' : '/oraciones'; 
            break;
        case 'actividad': 
            url = isSuper ? '/actividadessuperu' : '/actividades'; 
            break;
        case 'sermon':
            url = isSuper ? '/sermonessuperu' : '/sermones';
            break;
        case 'cumpleanos': 
            url = '/gestionmiembros'; 
            break;
    }
    
    window.location.href = url;
}