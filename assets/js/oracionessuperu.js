document.addEventListener('DOMContentLoaded', function() {
    listar();
    
    // Guardar petición (Siempre pendiente para superusuario)
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

            // 2. Acciones: SUPERUSUARIO NO PUEDE MODERAR
            // Solo puede ver el estado
            let botones = `<span style="color:#888; font-size:0.85rem;">Solo lectura</span>`;

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
            order: [] // Respetar orden del backend
        });
    });
}

// Utilidades
const modal = document.getElementById('oracionModal');
window.openModal = function() { document.getElementById('oracionForm').reset(); modal.classList.add('active'); }
window.closeModal = function() { modal.classList.remove('active'); }

function mostrarAlerta(t, m) { 
    $('#alertContainer').html(`<div style="background:${t==='success'?'#18c5a3':'#ff6b6b'}; color:white; padding:15px; border-radius:8px; margin-bottom:10px;">${m}</div>`);
    setTimeout(() => $('#alertContainer').html(''), 4000);
}