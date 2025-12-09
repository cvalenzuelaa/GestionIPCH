let currentSerieId = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarSeries();
});

function cargarSeries() {
    const fd = new FormData();
    fd.append('accion', 'getSeries');

    fetch('/app/controllers/sermonesController.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        let html = '';
        if(!data || data.length === 0) {
            html = '<p style="color:#aaa; text-align:center; width:100%;">No hay series disponibles.</p>';
        } else {
            data.forEach(s => {
                let img = s.imagen_cover ? `/${s.imagen_cover}` : '/assets/img/logo.ico';
                let tituloSafe = s.titulo.replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                let descSafe = s.descripcion ? s.descripcion.replace(/'/g, "&apos;").replace(/"/g, "&quot;") : '';
                
                let bgStyle = s.imagen_cover 
                    ? `background-image: url('${img}');` 
                    : `background: linear-gradient(135deg, var(--navy-700), var(--blue-500));`;

                let estadoTexto = s.estado == 1 ? 'En Curso' : 'Finalizada';
                let estadoClass = s.estado == 1 ? 'activa' : 'finalizada';
                
                html += `
                <div class="serie-card" onclick="verSerie('${s.idserie}', '${tituloSafe}', '${descSafe}', '${img}')">
                    <div class="serie-cover" style="${bgStyle}">
                        <span class="serie-badge ${estadoClass}">${estadoTexto}</span>
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

function verSerie(id, titulo, desc, img) {
    currentSerieId = id;
    document.getElementById('nombreSerieBig').textContent = titulo;
    document.getElementById('descSerieBig').textContent = desc;
    document.getElementById('imgSerieModal').src = img;
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
                let btnVideo = s.url_video ? `<a href="${s.url_video}" target="_blank" class="btn-resource btn-vid"><i class="fas fa-play"></i> Ver Video</a>` : '';
                let btnPdf = s.archivo_notas ? `<a href="/${s.archivo_notas}" target="_blank" class="btn-resource btn-pdf"><i class="fas fa-file-pdf"></i> Descargar Notas</a>` : '';

                html += `
                <div class="sermon-item">
                    <div class="sermon-icon"><i class="fas fa-bible"></i></div>
                    <div class="sermon-content">
                        <div class="sermon-title">${s.titulo}</div>
                        <div class="sermon-meta">
                            <i class="fas fa-user-tie"></i> ${s.predicador} | 
                            <i class="fas fa-calendar"></i> ${s.fecha_predicacion}
                            ${s.cita_biblica ? ` | <i class="fas fa-book"></i> ${s.cita_biblica}` : ''}
                        </div>
                        ${s.descripcion ? `<p style="color:#ccc; font-size:0.9rem; margin:5px 0;">${s.descripcion}</p>` : ''}
                        <div style="margin-top:8px; display:flex; gap:5px;">
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

window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});