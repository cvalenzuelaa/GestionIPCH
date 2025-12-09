let chartInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();
    toggleCategories(); 

    const form = document.getElementById('tesoreriaForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('accion', 'insert');

        // RUTA ABSOLUTA PARA EVITAR ERRORES 404
        fetch('/app/controllers/tesoreriaController.php', {
            method: 'POST',
            body: formData 
        })
        .then(res => res.json()) // Si falla aquí es porque el PHP devuelve error
        .then(data => {
            if(data.success) {
                mostrarAlerta('success', data.success);
                closeModal();
                cargarDatos();
            } else {
                mostrarAlerta('error', data.error || 'Error desconocido');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error de conexión. Verifica la consola.');
        });
    });
});

function cargarDatos() {
    // 1. Obtener Tabla
    fetch('/app/controllers/tesoreriaController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'accion=getAll'
    })
    .then(r => r.json())
    .then(data => renderTabla(data));

    // 2. Obtener Balance
    fetch('/app/controllers/tesoreriaController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'accion=getBalance'
    })
    .then(r => r.json())
    .then(data => renderDashboard(data));
}

function renderTabla(data) {
    if ($.fn.DataTable.isDataTable('#tablaTesoreria')) {
        $('#tablaTesoreria').DataTable().destroy();
    }

    let html = '';
    data.forEach(item => {
        const montoFmt = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(item.monto);
        const color = item.tipo === 'ingreso' ? '#18c5a3' : '#ff6b6b';
        const signo = item.tipo === 'ingreso' ? '+' : '-';
        
        let btnArchivo = '<span style="color:#666;">-</span>';
        if(item.comprobante) {
            btnArchivo = `<a href="/${item.comprobante}" target="_blank" class="btn-file"><i class="fas fa-paperclip"></i> Ver</a>`;
        }

        html += `
            <tr>
                <td>${item.fecha}</td>
                <td><span style="text-transform:uppercase; font-weight:bold; color:${color}">${item.tipo}</span></td>
                <td>${item.categoria_tipo.replace('_', ' ')}</td>
                <td><div style="font-weight:bold;">${item.categoria}</div><small>${item.descripcion}</small></td>
                <td style="font-weight:bold; color:${color}">${signo} ${montoFmt}</td>
                <td style="text-align:center;">${btnArchivo}</td>
                <td style="text-align:center;">
                    <button class="btn-action btn-delete" onclick="eliminarMovimiento('${item.idmovimiento}')"><i class="fas fa-trash"></i> Eliminar</button>
                </td>
            </tr>`;
    });

    $('#tablaBody').html(html);

    $('#tablaTesoreria').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: 'Excel', className: 'btn-action btn-edit' },
            { extend: 'pdf', text: 'PDF', className: 'btn-action btn-pastoral' }
        ],
        language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" },
        order: [[0, 'desc']],
        pageLength: 5
    });
}

function renderDashboard(data) {
    let ingresos = 0, gastos = 0;
    const labels = [], values = [], colors = [];

    data.forEach(d => {
        const monto = parseFloat(d.total);
        if(d.tipo === 'ingreso') ingresos += monto; else gastos += monto;
        labels.push(d.categoria_tipo.replace('_', ' ').toUpperCase());
        values.push(monto);
        colors.push(d.tipo === 'ingreso' ? '#18c5a3' : '#ff6b6b');
    });

    const fmt = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' });
    document.getElementById('totalIngresos').innerText = fmt.format(ingresos);
    document.getElementById('totalGastos').innerText = fmt.format(gastos);
    
    const balance = ingresos - gastos;
    const elBal = document.getElementById('totalBalance');
    elBal.innerText = fmt.format(balance);
    elBal.style.color = balance >= 0 ? '#60a5fa' : '#ff6b6b';

    const ctx = document.getElementById('financeChart').getContext('2d');
    if(chartInstance) chartInstance.destroy();

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ label: 'Monto', data: values, backgroundColor: colors, borderRadius: 5 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { ticks: { color: 'white' }, grid: { color: 'rgba(255,255,255,0.1)' } }, x: { ticks: { color: 'white' } } }
        }
    });
}

function toggleCategories() {
    const tipo = document.querySelector('input[name="tipo"]:checked').value;
    const select = document.getElementById('categoria_tipo');
    
    if(tipo === 'ingreso') {
        select.innerHTML = '<option value="diezmo">Diezmo</option><option value="ofrenda">Ofrenda</option><option value="donacion">Donación</option>';
    } else {
        select.innerHTML = '<option value="gasto_operativo">Gasto Operativo</option><option value="gasto_extraordinario">Gasto Extraordinario</option>';
    }
}

function eliminarMovimiento(id) {
    if(confirm('¿Eliminar registro?')) {
        const fd = new FormData();
        fd.append('accion', 'delete');
        fd.append('idmovimiento', id);
        fetch('/app/controllers/tesoreriaController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            res.success ? (mostrarAlerta('success', 'Eliminado'), cargarDatos()) : mostrarAlerta('error', res.error);
        });
    }
}

const modal = document.getElementById('tesoreriaModal');
window.openModal = function() { document.getElementById('tesoreriaForm').reset(); toggleCategories(); modal.classList.add('active'); }
window.closeModal = function() { modal.classList.remove('active'); }
function mostrarAlerta(tipo, msg) {
    const cont = document.getElementById('alertContainer');
    cont.innerHTML = `<div style="background:${tipo==='success'?'#18c5a3':'#ff6b6b'}; color:white; padding:15px; border-radius:8px; margin-bottom:10px;">${msg}</div>`;
    setTimeout(() => cont.innerHTML='', 4000);
}