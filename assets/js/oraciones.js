document.addEventListener('DOMContentLoaded', function() {
    listar();
    
    // Guardar (Auto-aprobada si es Admin)
    document.getElementById('oracionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('accion', 'insert');
        
        fetch('/app/controllers/oracionesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', res.success);
                closeModal();
                listar();
            } else {
                mostrarAlerta('error', res.error);
            }
        });
    });
});

function listar() {
    fetch('/app/controllers/oracionesController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'accion=getAll'
    })
    .then(r => r.json())
    .then(data => {
        if ($.fn.DataTable.isDataTable('#tablaOraciones')) $('#tablaOraciones').DataTable().destroy();
        
        let html = '';
        data.forEach(d => {
            // 1. Badge de Estado
            let badgeClass = 'b-pendiente';
            let badgeText = 'Pendiente';
            
            if(d.estado === 'aprobada') { badgeClass = 'b-aprobada'; badgeText = 'Publicada'; }
            if(d.estado === 'rechazada') { badgeClass = 'b-rechazada'; badgeText = 'Rechazada'; }

            // 2. Acciones Disponibles
            let botones = '';
            
            // Si está pendiente, mostrar opciones de moderación
            if(d.estado === 'pendiente') {
                botones = `
                    <div class="action-buttons-container">
                        <button class="btn-action btn-stats" onclick="cambiarEstado('${d.idoracion}', 'aprobada')" title="Aprobar y Publicar">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                        <button class="btn-action btn-delete" onclick="cambiarEstado('${d.idoracion}', 'rechazada')" title="Rechazar">
                            <i class="fas fa-times"></i> Rechazar
                        </button>
                    </div>`;
            } else {
                // Si ya está procesada, solo permitir eliminar
                botones = `
                    <div class="action-buttons-container">
                        <button class="btn-action btn-delete" onclick="eliminar('${d.idoracion}')" title="Eliminar del historial">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>`;
            }

            // 3. Formato Fecha
            let fecha = d.fecha.split(' ')[0]; // YYYY-MM-DD

            html += `
                <tr>
                    <td>${fecha}</td>
                    <td>
                        <div style="font-weight:bold;">${d.nombre} ${d.apellido}</div>
                        <div class="user-role">${d.rol.toUpperCase()}</div>
                    </td>
                    <td style="font-style:italic; color:#ddd;">"${d.descripcion}"</td>
                    <td><span class="badge-estado ${badgeClass}">${badgeText}</span></td>
                    <td>${botones}</td>
                </tr>`;
        });

        $('#tablaBody').html(html);
        $('#tablaOraciones').DataTable({ 
            language: {url:"//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"},
            pageLength: 10,
            order: [] // Respetar orden del backend (Pendientes primero)
        });
    });
}

function cambiarEstado(id, estado) {
    let msg = estado === 'aprobada' ? '¿Aprobar y publicar en el muro?' : '¿Rechazar esta petición?';
    if(confirm(msg)) {
        const fd = new FormData();
        fd.append('accion', 'cambiarEstado');
        fd.append('idoracion', id);
        fd.append('estado', estado);
        
        fetch('/app/controllers/oracionesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', res.success);
                listar();
            } else {
                mostrarAlerta('error', res.error);
            }
        });
    }
}

function eliminar(id) {
    if(confirm('¿Eliminar permanentemente este registro?')) {
        const fd = new FormData();
        fd.append('accion', 'delete');
        fd.append('idoracion', id);
        
        fetch('/app/controllers/oracionesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', 'Registro eliminado');
                listar();
            } else {
                mostrarAlerta('error', res.error);
            }
        });
    }
}

// Utilidades
const modal = document.getElementById('oracionModal');
window.openModal = function() { document.getElementById('oracionForm').reset(); modal.classList.add('active'); }
window.closeModal = function() { modal.classList.remove('active'); }
function mostrarAlerta(t, m) { 
    $('#alertContainer').html(`<div style="background:${t==='success'?'#18c5a3':'#ff6b6b'}; color:white; padding:15px; border-radius:8px; margin-bottom:10px;">${m}</div>`);
    setTimeout(() => $('#alertContainer').html(''), 3000);
}