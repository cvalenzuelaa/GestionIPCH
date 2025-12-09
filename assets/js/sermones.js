let currentSerieId = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarSeries();

    const formSerie = document.getElementById('formSerie');
    if (formSerie) {
        formSerie.addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fetch('/app/controllers/sermonesController.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    cargarSeries();
                    cerrarModal('modalSerie');
                    this.reset();
                    mostrarAlerta('success', res.success);
                } else { mostrarAlerta('error', res.error); }
            });
        });
    }

    const formSermon = document.getElementById('formSermon');
    if (formSermon) {
        formSermon.addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fetch('/app/controllers/sermonesController.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    cargarSermones(currentSerieId);
                    cerrarModal('modalSermon');
                    this.reset();
                    mostrarAlerta('success', res.success);
                } else { mostrarAlerta('error', res.error); }
            });
        });
    }
});

function cargarSeries() {
    const fd = new FormData();
    fd.append('accion', 'getSeries');

    fetch('/app/controllers/sermonesController.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        let html = '';
        if(!data || data.length === 0) {
            html = '<p style="color:#aaa; text-align:center; width:100%;">No hay series creadas.</p>';
        } else {
            // Detectamos si es admin buscando si existe el botón de crear serie en el DOM
            const isAdmin = document.querySelector('.btn-add-activity') !== null;

            data.forEach(s => {
                let img = s.imagen_cover ? `/${s.imagen_cover}` : '/assets/img/logo.ico';
                let tituloSafe = s.titulo.replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                let descSafe = s.descripcion ? s.descripcion.replace(/'/g, "&apos;").replace(/"/g, "&quot;") : '';
                
                let bgStyle = s.imagen_cover 
                    ? `background-image: url('${img}');` 
                    : `background: linear-gradient(135deg, var(--navy-700), var(--blue-500));`;

                // Lógica del Badge/Botón de Estado
                let estadoTexto = s.estado == 1 ? 'En Curso' : 'Finalizada';
                let estadoColor = s.estado == 1 ? 'rgba(24,197,163,0.9)' : 'rgba(239,68,68,0.9)'; // Verde o Rojo
                
                let badgeHtml = '';
                if(isAdmin) {
                    // Si es admin, es un botón onclick que previene propagación
                    badgeHtml = `<button class="serie-badge" style="background:${estadoColor}; cursor:pointer; border:none; color:white;" 
                                onclick="event.stopPropagation(); cambiarEstadoSerie('${s.idserie}', ${s.estado})">
                                ${estadoTexto} <i class="fas fa-sync-alt" style="font-size:0.7rem; margin-left:4px;"></i>
                                </button>`;
                } else {
                    // Si no, es solo etiqueta visual
                    badgeHtml = `<span class="serie-badge" style="background:${estadoColor};">${estadoTexto}</span>`;
                }

                html += `
                <div class="serie-card" onclick="verSerie('${s.idserie}', '${tituloSafe}', '${descSafe}', '${img}')">
                    <div class="serie-cover" style="${bgStyle}">
                        ${badgeHtml}
                    </div>
                    <div class="serie-body">
                        <div class="serie-title">${s.titulo}</div>
                        <div class="serie-date">Inicio: ${s.fecha_inicio}</div>
                        <div class="serie-desc">${s.descripcion}</div>
                    </div>
                </div>`;
            });
        }
        document.getElementById('seriesGrid').innerHTML = html;
    });
}

// --- NUEVA FUNCIÓN: CAMBIAR ESTADO ---
function cambiarEstadoSerie(idSerie, estadoActual) {
    // Invertir estado (Si es 1 pasa a 0, si es 0 pasa a 1)
    let nuevoEstado = estadoActual == 1 ? 0 : 1;
    let confirmMsg = nuevoEstado == 0 ? "¿Marcar esta serie como Finalizada?" : "¿Reactivar esta serie como En Curso?";

    if(confirm(confirmMsg)) {
        const fd = new FormData();
        fd.append('accion', 'updateStatus');
        fd.append('idserie', idSerie);
        fd.append('estado', nuevoEstado);

        fetch('/app/controllers/sermonesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', 'Estado actualizado');
                cargarSeries(); // Recargar para ver cambios y reordenar
            } else {
                mostrarAlerta('error', res.error || 'Error al cambiar estado');
            }
        });
    }
}

// ... Resto de funciones (verSerie, cargarSermones, etc.) se mantienen igual ...

function verSerie(id, titulo, desc, img) {
    currentSerieId = id;
    document.getElementById('nombreSerieBig').textContent = titulo;
    document.getElementById('descSerieBig').textContent = desc;
    document.getElementById('imgSerieModal').src = img;
    const inputIdSerie = document.getElementById('idserieInput');
    if(inputIdSerie) inputIdSerie.value = id;
    cargarSermones(id);
    abrirModal('modalDetalleSerie');
}

function cargarSermones(idSerie) {
    const fd = new FormData();
    fd.append('accion', 'getSermones');
    fd.append('idserie', idSerie);

    fetch('/app/controllers/sermonesController.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        let html = '';
        if(!data || data.length === 0) {
            html = '<p style="color:#888; text-align:center; padding:20px;">Aún no hay sermones en esta serie.</p>';
        } else {
            data.forEach(s => {
                let btnVideo = s.url_video ? `<a href="${s.url_video}" target="_blank" class="btn-resource btn-vid"><i class="fas fa-play"></i> Video</a>` : '';
                let btnPdf = s.archivo_notas ? `<a href="/${s.archivo_notas}" target="_blank" class="btn-resource btn-pdf"><i class="fas fa-file-pdf"></i> Notas</a>` : '';
                
                let btnDel = document.querySelector('.btn-add-activity') 
                    ? `<button onclick="eliminarSermon('${s.idsermon}')" style="background:none; border:none; color:#ef4444; cursor:pointer; margin-left:auto;"><i class="fas fa-trash"></i></button>` 
                    : '';

                html += `
                <div class="sermon-item">
                    <div class="sermon-icon"><i class="fas fa-bible"></i></div>
                    <div class="sermon-content">
                        <div style="display:flex; align-items:center;">
                            <div class="sermon-title">${s.titulo}</div>
                            ${btnDel}
                        </div>
                        <div class="sermon-meta">
                            <i class="fas fa-user-tie"></i> ${s.predicador} | 
                            <i class="fas fa-calendar"></i> ${s.fecha_predicacion}
                        </div>
                        <p style="color:#ccc; font-size:0.9rem; margin:5px 0;">${s.descripcion}</p>
                        <div style="margin-top:8px; display:flex;">
                            ${btnVideo}
                            ${btnPdf}
                        </div>
                    </div>
                </div>`;
            });
        }
        document.getElementById('listaSermones').innerHTML = html;
    });
}

function eliminarSermon(id) {
    if(confirm('¿Seguro de borrar este sermón?')) {
        const fd = new FormData();
        fd.append('accion', 'deleteSermon');
        fd.append('idsermon', id);

        fetch('/app/controllers/sermonesController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', 'Eliminado correctamente');
                cargarSermones(currentSerieId);
            }
        });
    }
}

function abrirModalSerie() { 
    const f = document.getElementById('formSerie');
    if(f) f.reset(); 
    abrirModal('modalSerie'); 
}

function abrirModalSermon() { 
    const f = document.getElementById('formSermon');
    if(f) {
        f.reset();
        document.getElementById('idserieInput').value = currentSerieId;
    }
    abrirModal('modalSermon'); 
}

// Funciones globales de UI (incluidas aquí por si globales.js falla o no está)
function abrirModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        const content = modal.querySelector('.modal-content');
        if(content) {
            content.style.opacity = '0';
            content.style.transform = 'translateY(20px)';
            setTimeout(() => {
                content.style.opacity = '1';
                content.style.transform = 'translateY(0)';
            }, 10);
        }
    }
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

function mostrarAlerta(tipo, msg) {
    const container = document.getElementById('alertContainer');
    if (!container) return;

    let color = tipo === 'success' ? '#18c5a3' : '#ff6b6b';
    let icon = tipo === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
    
    container.innerHTML = `
        <div style="background:${color}; color:black; padding:15px; margin-bottom:20px; border-radius:8px; font-weight:bold; box-shadow:0 4px 10px rgba(0,0,0,0.2); display:flex; align-items:center; gap:10px; animation: fadeIn 0.3s;">
            ${icon} <span>${msg}</span>
        </div>
    `;
    setTimeout(() => { container.innerHTML = ''; }, 3000);
}

window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});