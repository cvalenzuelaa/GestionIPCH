let currentTab = 'mispeticiones';

document.addEventListener('DOMContentLoaded', function() {
    cargarMisPeticiones();
    
    document.getElementById('oracionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('accion', 'insert');
        
        fetch('/app/controllers/oracionesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', '✅ Petición enviada. Será revisada por el Pastor.');
                closeModal();
                cargarMisPeticiones();
            } else {
                mostrarAlerta('error', res.error);
            }
        });
    });
});

function switchTab(tab) {
    currentTab = tab;
    
    // Actualizar botones
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.tab-btn').classList.add('active');
    
    // Actualizar contenido
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    document.getElementById(`tab-${tab}`).classList.add('active');
    
    if (tab === 'muro') {
        cargarMuroPublico();
    }
}

function cargarMisPeticiones() {
    fetch('/app/controllers/oracionesController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'accion=getAll' // Para usuario, getAll devuelve solo las suyas
    })
    .then(r => r.json())
    .then(data => {
        if ($.fn.DataTable.isDataTable('#tablaMisPeticiones')) {
            $('#tablaMisPeticiones').DataTable().destroy();
        }
        
        let html = '';
        data.forEach(d => {
            let badgeClass = 'b-pendiente';
            let badgeText = 'Pendiente';
            
            if(d.estado === 'aprobada') { badgeClass = 'b-aprobada'; badgeText = 'Aprobada'; }
            if(d.estado === 'rechazada') { badgeClass = 'b-rechazada'; badgeText = 'Rechazada'; }

            let fecha = d.fecha.split(' ')[0];

            html += `
                <tr>
                    <td>${fecha}</td>
                    <td style="font-style:italic; color:#ddd;">"${d.descripcion}"</td>
                    <td><span class="badge-estado ${badgeClass}">${badgeText}</span></td>
                </tr>`;
        });

        $('#tablaMisPeticionesBody').html(html);
        $('#tablaMisPeticiones').DataTable({ 
            language: {url:"//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"},
            pageLength: 10,
            order: [[0, 'desc']]
        });
    });
}

function cargarMuroPublico() {
    fetch('/app/controllers/oracionesController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'accion=getApproved'
    })
    .then(r => r.json())
    .then(data => {
        let html = '';
        
        if(!data || data.length === 0) {
            html = '<p style="color:#888; text-align:center; padding:40px;">No hay peticiones publicadas aún.</p>';
        } else {
            data.forEach(d => {
                let iniciales = (d.nombre[0] + d.apellido[0]).toUpperCase();
                let fecha = new Date(d.fecha).toLocaleDateString('es-ES', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });

                html += `
                <div class="oracion-card">
                    <div class="oracion-header">
                        <div class="oracion-avatar">${iniciales}</div>
                        <div class="oracion-autor">
                            <div class="oracion-nombre">${d.nombre} ${d.apellido}</div>
                            <div class="oracion-fecha">${fecha}</div>
                        </div>
                    </div>
                    <div class="oracion-texto">${d.descripcion}</div>
                </div>`;
            });
        }
        
        document.getElementById('muroOraciones').innerHTML = html;
    });
}

const modal = document.getElementById('oracionModal');
window.openModal = function() { 
    document.getElementById('oracionForm').reset(); 
    modal.classList.add('active'); 
}
window.closeModal = function() { modal.classList.remove('active'); }

function mostrarAlerta(t, m) { 
    $('#alertContainer').html(`<div style="background:${t==='success'?'#18c5a3':'#ff6b6b'}; color:white; padding:15px; border-radius:8px; margin-bottom:10px;">${m}</div>`);
    setTimeout(() => $('#alertContainer').html(''), 4000);
}