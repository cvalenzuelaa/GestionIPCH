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

function gestionarClick(id, tipo) {
    const fd = new FormData();
    fd.append('accion', 'markRead');
    fd.append('idnotificacion', id);
    fetch('/app/controllers/notificacionesController.php', { method: 'POST', body: fd });

    // DETECTAR ROL DEL USUARIO ACTUAL
    let url = '/dashboard'; // DEFAULT: usuario normal
    
    const isAdmin = window.location.pathname.includes('admin') || window.location.pathname.includes('dashadmin');
    const isSuper = window.location.pathname.includes('superu') || window.location.pathname.includes('dashsuperu');
    
    // MAPEO DE RUTAS SEGÚN TIPO Y ROL
    switch(tipo) {
        case 'alabanza': 
            if (isSuper) url = '/alabanzassuperu';
            else if (isAdmin) url = '/alabanzas';
            else url = '/misalabanzas'; // USUARIO NORMAL
            break;
            
        case 'oracion': 
            if (isSuper) url = '/oracionessuperu';
            else if (isAdmin) url = '/oraciones';
            else url = '/misoraciones'; // USUARIO NORMAL
            break;
            
        case 'actividad': 
            if (isSuper) url = '/actividadessuperu';
            else if (isAdmin) url = '/actividades';
            else url = '/misactividades'; // USUARIO NORMAL
            break;
            
        case 'sermon':
            if (isSuper) url = '/sermonessuperu';
            else if (isAdmin) url = '/sermones';
            else url = '/missermones'; // USUARIO NORMAL
            break;
            
        case 'cumpleanos': 
            if (isSuper) url = '/actividadessuperu';
            else if (isAdmin) url = '/actividades';
            else url = '/misactividades'; // USUARIO NORMAL
            break;
            
        default:
            if (isSuper) url = '/dashsuperu';
            else if (isAdmin) url = '/dashadmin';
            else url = '/dashboard'; // USUARIO NORMAL
            break;
    }
    
    window.location.href = url;
}