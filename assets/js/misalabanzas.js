document.addEventListener('DOMContentLoaded', function() {
    listar();
    
    // Guardar (Insertar o Editar)
    document.getElementById('alabanzaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        
        fetch('/app/controllers/alabanzasController.php', { 
            method: 'POST', 
            body: fd 
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', res.success);
                closeModal();
                listar();
            } else {
                mostrarAlerta('error', res.error || 'Error al guardar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error de conexión');
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
            // Lógica de estado (activo/inactivo) para iconos
            const hasPDF = d.archivo_pdf ? '' : 'disabled';
            const linkPDF = d.archivo_pdf ? `/${d.archivo_pdf}` : '#';
            
            const hasPPT = d.archivo_ppt ? '' : 'disabled';
            const linkPPT = d.archivo_ppt ? `/${d.archivo_ppt}` : '#';
            
            const hasVid = d.enlace_video ? '' : 'disabled';
            const linkVid = d.enlace_video ? d.enlace_video : '#';

            // Formato de fecha
            let fechaParts = d.fecha_subida.split(' ')[0].split('-');
            let fechaFmt = `${fechaParts[2]}/${fechaParts[1]}/${fechaParts[0]}`;

            html += `
                <tr>
                    <td style="font-weight:700; font-size:1rem; color:white;">
                        <i class="fas fa-music" style="color:var(--calypso); margin-right:8px;"></i>
                        ${d.titulo}
                    </td>
                    
                    <td>
                        <div class="resource-icons">
                            <a href="${linkPDF}" target="_blank" class="icon-link bg-pdf ${hasPDF}" title="Descargar PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            
                            <a href="${linkPPT}" target="_blank" class="icon-link bg-ppt ${hasPPT}" title="Descargar PPT">
                                <i class="fas fa-file-powerpoint"></i> PPT
                            </a>
                            
                            <a href="${linkVid}" target="_blank" class="icon-link bg-vid ${hasVid}" title="Ver Video">
                                <i class="fab fa-youtube"></i> Video
                            </a>
                        </div>
                    </td>
                    
                    <td style="color:rgba(255,255,255,0.7); font-size:0.9rem;">${fechaFmt}</td>
                    <td style="font-size:0.9rem; color:rgba(255,255,255,0.8);">
                        ${d.nombre} ${d.apellido}
                    </td>
                    
                    <td>
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
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
                emptyTable: "No hay alabanzas registradas todavía",
                zeroRecords: "No se encontraron resultados"
            },
            pageLength: 10,
            lengthChange: false,
            dom: 'rtp',
            autoWidth: false,
            order: [[2, 'desc']] // Ordenar por fecha descendente
        });
    })
    .catch(error => {
        console.error('Error al listar:', error);
        mostrarAlerta('error', 'Error al cargar las alabanzas');
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

    fetch('/app/controllers/alabanzasController.php', { 
        method: 'POST', 
        body: fd 
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('accionInput').value = 'update';
        document.getElementById('idAlabanzaInput').value = data.idalabanza;
        document.getElementById('titulo').value = data.titulo;
        document.getElementById('enlace_video').value = data.enlace_video || '';
        
        // Mostrar archivos actuales
        if (data.archivo_pdf) {
            document.getElementById('currentPDF').innerHTML = `
                <i class="fas fa-check-circle" style="color:#18c5a3;"></i>
                Archivo actual cargado. <a href="/${data.archivo_pdf}" target="_blank" style="color:#60a5fa;">Ver PDF</a>
                <br><small style="color:#888;">Sube un nuevo archivo para reemplazarlo</small>
            `;
        } else {
            document.getElementById('currentPDF').innerText = '';
        }
        
        if (data.archivo_ppt) {
            document.getElementById('currentPPT').innerHTML = `
                <i class="fas fa-check-circle" style="color:#18c5a3;"></i>
                Archivo actual cargado. <a href="/${data.archivo_ppt}" target="_blank" style="color:#f97316;">Descargar PPT</a>
                <br><small style="color:#888;">Sube un nuevo archivo para reemplazarlo</small>
            `;
        } else {
            document.getElementById('currentPPT').innerText = '';
        }
        
        document.getElementById('modalTitle').innerText = 'Editar Alabanza';
        document.getElementById('alabanzaModal').classList.add('active');
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error al cargar los datos');
    });
}

function eliminar(id) {
    if(confirm('¿Estás seguro de eliminar esta alabanza y todos sus archivos?\n\nEsta acción no se puede deshacer.')) {
        const fd = new FormData();
        fd.append('accion', 'delete');
        fd.append('idalabanza', id);
        
        fetch('/app/controllers/alabanzasController.php', { 
            method: 'POST', 
            body: fd 
        })
        .then(r => r.json())
        .then(res => { 
            if (res.success) {
                mostrarAlerta('success', 'Alabanza eliminada correctamente');
                listar();
            } else {
                mostrarAlerta('error', res.error || 'Error al eliminar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error de conexión');
        });
    }
}

window.closeModal = function() { 
    document.getElementById('alabanzaModal').classList.remove('active'); 
}

function mostrarAlerta(tipo, mensaje) { 
    const color = tipo === 'success' ? '#18c5a3' : '#ff6b6b';
    const icon = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    $('#alertContainer').html(`
        <div style="background:${color}; color:white; padding:15px 20px; border-radius:10px; margin-bottom:20px; 
                    display:flex; align-items:center; gap:12px; animation: slideDown 0.3s ease; font-weight:600;">
            <i class="fas ${icon}" style="font-size:1.2rem;"></i>
            <span>${mensaje}</span>
        </div>
    `);
    
    setTimeout(() => { 
        $('#alertContainer').html(''); 
    }, 4000);
}

// Cerrar modal al hacer clic fuera
window.addEventListener('click', function(e) {
    const modal = document.getElementById('alabanzaModal');
    if (e.target === modal) {
        closeModal();
    }
});