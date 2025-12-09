document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();

    // Guardar
    const form = document.getElementById('inventarioForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        formData.append('accion', 'insert');

        fetch('/app/controllers/inventarioController.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                mostrarAlerta('success', data.success);
                closeModal();
                cargarDatos();
            } else {
                mostrarAlerta('error', data.error);
            }
        });
    });
});

function cargarDatos() {
    fetch('/app/controllers/inventarioController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'accion=getAll'
    })
    .then(r => r.json())
    .then(data => {
        renderTabla(data);
        calcularTotal(data);
    });
}

function renderTabla(data) {
    if ($.fn.DataTable.isDataTable('#tablaInventario')) {
        $('#tablaInventario').DataTable().destroy();
    }

    let html = '';
    data.forEach(item => {
        const montoFmt = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(item.monto);
        
        let btnArchivo = '<span style="color:#666;">Sin archivo</span>';
        if(item.archivo) {
            btnArchivo = `<a href="/${item.archivo}" target="_blank" class="btn-file"><i class="fas fa-image"></i> Ver</a>`;
        }

        html += `
            <tr>
                <td>${item.fecha}</td>
                <td style="font-weight:bold; color:white;">${item.descripcion}</td>
                <td style="color:#18c5a3; font-weight:bold;">${montoFmt}</td>
                <td>${btnArchivo}</td>
                <td style="text-align:center;">
                    <button class="btn-action btn-delete" onclick="eliminarBien('${item.idbien}')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </td>
            </tr>`;
    });

    $('#tablaBody').html(html);

    $('#tablaInventario').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: 'Excel', className: 'btn-action btn-edit' },
            { extend: 'pdf', text: 'PDF', className: 'btn-action btn-pastoral' }
        ],
        language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" },
        order: [[0, 'desc']],
        pageLength: 10
    });
}

function calcularTotal(data) {
    let total = 0;
    data.forEach(item => total += parseFloat(item.monto));
    const fmt = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(total);
    document.getElementById('totalInventario').innerText = fmt;
}

function eliminarBien(id) {
    if(confirm('¿Eliminar este bien del inventario?')) {
        const fd = new FormData();
        fd.append('accion', 'delete');
        fd.append('idbien', id);

        fetch('/app/controllers/inventarioController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            res.success ? (mostrarAlerta('success', 'Eliminado'), cargarDatos()) : mostrarAlerta('error', res.error);
        });
    }
}

// Utilidades
const modal = document.getElementById('inventarioModal');
window.openModal = function() { document.getElementById('inventarioForm').reset(); modal.classList.add('active'); }
window.closeModal = function() { modal.classList.remove('active'); }
function mostrarAlerta(tipo, msg) {
    const cont = document.getElementById('alertContainer');
    cont.innerHTML = `<div style="background:${tipo==='success'?'#18c5a3':'#ff6b6b'}; color:white; padding:15px; border-radius:8px; margin-bottom:10px;">${msg}</div>`;
    setTimeout(() => cont.innerHTML='', 4000);
}