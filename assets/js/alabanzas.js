document.addEventListener('DOMContentLoaded', function() {
    listar();
    
    // Guardar (Insertar o Editar)
    document.getElementById('alabanzaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        // La acción se toma del input hidden (insert o update)
        
        fetch('/app/controllers/alabanzasController.php', { method: 'POST', body: fd })
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
    fetch('/app/controllers/alabanzasController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'accion=getAll'
    })
    .then(r => r.json())
    .then(data => {
        if ($.fn.DataTable.isDataTable('#tablaAlabanzas')) {
            $('#tablaAlabanzas').DataTable().destroy();
        }
        
        let html = '';
        data.forEach(d => {
            // Lógica de estado (activo/inactivo)
            const hasPDF = d.archivo_pdf ? '' : 'disabled';
            const linkPDF = d.archivo_pdf ? `/${d.archivo_pdf}` : '#';
            
            const hasPPT = d.archivo_ppt ? '' : 'disabled';
            const linkPPT = d.archivo_ppt ? `/${d.archivo_ppt}` : '#';
            
            const hasVid = d.enlace_video ? '' : 'disabled';
            const linkVid = d.enlace_video ? d.enlace_video : '#';

            // Formato de fecha limpio
            let fecha = d.fecha_subida.split('-');
            let fechaFmt = `${fecha[2]}/${fecha[1]}/${fecha[0]}`;

            html += `
                <tr>
                    <td style="font-weight:700; font-size:1rem; color:white;">${d.titulo}</td>
                    
                    <td>
                        <div class="resource-icons">
                            <a href="${linkPDF}" target="_blank" class="icon-link bg-pdf ${hasPDF}">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            
                            <a href="${linkPPT}" target="_blank" class="icon-link bg-ppt ${hasPPT}">
                                <i class="fas fa-file-powerpoint"></i> PPT
                            </a>
                            
                            <a href="${linkVid}" target="_blank" class="icon-link bg-vid ${hasVid}">
                                <i class="fab fa-youtube"></i> Video
                            </a>
                        </div>
                    </td>
                    
                    <td style="color:rgba(255,255,255,0.7); font-size:0.9rem;">${fechaFmt}</td>
                    <td style="font-size:0.9rem;">${d.nombre} ${d.apellido}</td>
                    
                    <td style="text-align:right;">
                        <div class="action-buttons-container">
                            <button class="btn-action btn-edit" onclick="editar('${d.idalabanza}')">
                                <i class="fas fa-pen"></i> Editar
                            </button>
                            <button class="btn-action btn-delete" onclick="eliminar('${d.idalabanza}')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </td>
                </tr>`;
        });

        $('#tablaBody').html(html);
        
        $('#tablaAlabanzas').DataTable({ 
            language: {url:"//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"},
            pageLength: 8,
            lengthChange: false,
            dom: 'rtp',
            autoWidth: false
        });
    });
}

function nuevoRecurso() {
    document.getElementById('alabanzaForm').reset();
    document.getElementById('accionInput').value = 'insert';
    document.getElementById('idAlabanzaInput').value = '';
    document.getElementById('modalTitle').innerText = 'Nueva Alabanza';
    document.getElementById('currentPDF').innerText = '';
    document.getElementById('currentPPT').innerText = '';
    
    document.getElementById('alabanzaModal').classList.add('active');
}

function editar(id) {
    const fd = new FormData();
    fd.append('accion', 'getById');
    fd.append('idalabanza', id);

    fetch('/app/controllers/alabanzasController.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        document.getElementById('accionInput').value = 'update';
        document.getElementById('idAlabanzaInput').value = data.idalabanza;
        document.getElementById('titulo').value = data.titulo;
        document.getElementById('enlace_video').value = data.enlace_video;
        
        // Mostrar archivos actuales
        document.getElementById('currentPDF').innerText = data.archivo_pdf ? 'Archivo actual: Cargado (Subir otro para reemplazar)' : '';
        document.getElementById('currentPPT').innerText = data.archivo_ppt ? 'Archivo actual: Cargado (Subir otro para reemplazar)' : '';
        
        document.getElementById('modalTitle').innerText = 'Editar Alabanza';
        document.getElementById('alabanzaModal').classList.add('active');
    });
}

function eliminar(id) {
    if(confirm('¿Eliminar esta alabanza y sus archivos?')) {
        const fd = new FormData();
        fd.append('accion', 'delete');
        fd.append('idalabanza', id);
        fetch('/app/controllers/alabanzasController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => { res.success ? listar() : mostrarAlerta('error', res.error); });
    }
}

window.closeModal = function() { document.getElementById('alabanzaModal').classList.remove('active'); }
function mostrarAlerta(t, m) { 
    $('#alertContainer').html(`<div style="background:${t==='success'?'#18c5a3':'#ff6b6b'}; color:white; padding:15px; border-radius:8px; margin-bottom:10px;">${m}</div>`);
    setTimeout(() => $('#alertContainer').html(''), 3000);
}