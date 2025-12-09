$(document).ready(function() {
    listarUsuarios();

    // 1. MÁSCARA TELEFÓNICA (globales.js)
    if(typeof attachTelefonoMask === 'function'){
        const telInput = document.getElementById('telefono');
        if(telInput) attachTelefonoMask(telInput);
    }

    // 2. VALIDACIONES EN TIEMPO REAL
    ['#nombre', '#apellido'].forEach(sel => {
        $(sel).on('input', function() {
            if(typeof validaNombres === 'function') {
                validaNombres(this, document.getElementById('error' + (sel === '#nombre' ? 'Nombre' : 'Apellido')));
            }
        });
    });

    $('#correo').on('input', function() {
        if(typeof validaCorreo === 'function') {
            validaCorreo(this, document.getElementById('errorCorreo'));
        }
    });

    $('#telefono').on('input', function() {
        if(typeof validaTelefono === 'function') {
            validaTelefono(this, document.getElementById('errorTelefono'));
        }
    });

    // Validación Contraseña en tiempo real (solo si escribe algo)
    $('#pass').on('input', function() {
        const errorPass = document.getElementById('errorPass');
        if (this.value.length > 0 && this.value.length < 4) {
            errorPass.textContent = 'Mínimo 4 caracteres.';
            errorPass.style.display = 'block';
            this.style.border = '2px solid rgba(255,107,107,0.9)';
        } else if (this.value.length >= 4) {
            errorPass.textContent = '';
            errorPass.style.display = 'none';
            this.style.border = '2px solid rgba(24,197,163,0.9)';
        } else {
            errorPass.textContent = '';
            errorPass.style.display = 'none';
            this.style.border = '';
        }
    });

    // 3. ENVÍO DEL FORMULARIO CON VALIDACIÓN FINAL
    $('#formUsuario').on('submit', function(e) {
        e.preventDefault();

        let valido = true;

        if(typeof validaNombres === 'function') {
            if(!validaNombres(document.getElementById('nombre'), document.getElementById('errorNombre'))) valido = false;
            if(!validaNombres(document.getElementById('apellido'), document.getElementById('errorApellido'))) valido = false;
        }
        
        if(typeof validaCorreo === 'function') {
            if(!validaCorreo(document.getElementById('correo'), document.getElementById('errorCorreo'))) valido = false;
        }

        if(typeof validaTelefono === 'function') {
            if(!validaTelefono(document.getElementById('telefono'), document.getElementById('errorTelefono'))) valido = false;
        }

        const passInput = document.getElementById('pass');
        const accion = $('#accionUsuario').val();
        const errorPass = document.getElementById('errorPass');

        if (accion === 'insert' && !$('#idusuario').val()) {
            if (!passInput.value || passInput.value.length < 4) {
                errorPass.textContent = 'La contraseña es obligatoria (min 4 caracteres).';
                errorPass.style.display = 'block';
                passInput.style.border = '2px solid rgba(255,107,107,0.9)';
                valido = false;
            }
        } else {
            if (passInput.value.length > 0 && passInput.value.length < 4) {
                errorPass.textContent = 'Mínimo 4 caracteres.';
                errorPass.style.display = 'block';
                passInput.style.border = '2px solid rgba(255,107,107,0.9)';
                valido = false;
            }
        }

        if (!valido) {
            mostrarAlerta('error', 'Por favor corrige los errores en el formulario.');
            return;
        }

        // CORREGIDO: Construir FormData manualmente para controlar el checkbox
        const formData = new FormData();
        formData.append('accion', $('#accionUsuario').val());
        formData.append('nombre', $('#nombre').val());
        formData.append('apellido', $('#apellido').val());
        formData.append('correo', $('#correo').val());
        formData.append('telefono', $('#telefono').val());
        formData.append('rol', $('#rol').val());
        
        // ENVIAR es_alabanza SOLO si está marcado
        if ($('#es_alabanza').is(':checked')) {
            formData.append('es_alabanza', '1');
        }
        
        // Solo agregar idusuario si existe (edición)
        if ($('#idusuario').val()) {
            formData.append('idusuario', $('#idusuario').val());
        }
        
        // Solo agregar contraseña si tiene valor
        if ($('#pass').val()) {
            formData.append('pass', $('#pass').val());
        }

        $.ajax({
            url: '/app/controllers/usuarioController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    mostrarAlerta('success', res.success);
                    cerrarModal('modalUsuario');
                    listarUsuarios();
                } else {
                    mostrarAlerta('error', res.error || 'Error desconocido');
                }
            },
            error: function(xhr, status, error) { 
                console.error('Error AJAX:', xhr.responseText);
                mostrarAlerta('error', 'Error de conexión'); 
            }
        });
    });
});

// --- LISTAR USUARIOS ---
function listarUsuarios() {
    $.ajax({
        url: '/app/controllers/usuarioController.php',
        type: 'POST', 
        data: { accion: 'getAll' }, 
        dataType: 'json',
        success: function(data) {
            let html = '';
            
            if ($.fn.DataTable.isDataTable('#tablaUsuarios')) {
                $('#tablaUsuarios').DataTable().destroy();
            }

            data.forEach(u => {
                let avatar;
                if (u.avatar) {
                    avatar = `/${u.avatar}`; 
                } else {
                    let nombreCompleto = encodeURIComponent(`${u.nombre}+${u.apellido}`);
                    avatar = `https://ui-avatars.com/api/?name=${nombreCompleto}&background=random&color=fff&size=128&bold=true`;
                }
                
                let rolHtml = '';
                let rolTexto = '';
                
                if(u.rol === 'admin') { 
                    rolHtml = '<span class="badge-rol rol-admin">Pastor / Presbitero</span>'; 
                    rolTexto = 'Admin'; 
                } else if(u.rol === 'super') { 
                    rolHtml = '<span class="badge-rol rol-super">Líder / Especial</span>'; 
                    rolTexto = 'Líder'; 
                } else { 
                    rolHtml = '<span class="badge-rol rol-user">Miembro / Adherente</span>'; 
                    rolTexto = 'Miembro'; 
                }

                // AGREGAR BADGE DE ALABANZA si corresponde
                if(u.es_alabanza == 1) {
                    rolHtml += '<br><span class="badge-alabanza"><i class="fas fa-music"></i> Grupo Alabanza</span>';
                    rolTexto += ' Alabanza';
                }

                let estadoHtml = u.estado == 1 
                    ? '<span style="color:#18c5a3; font-weight:bold;">Activo</span>' 
                    : '<span style="color:#ef4444; font-weight:bold;">Inactivo</span>';
                let estadoTexto = u.estado == 1 ? 'Activo' : 'Inactivo';

                let btnEstado = u.estado == 1
                    ? `<button class="btn-action btn-delete" onclick="cambiarEstado('${u.idusuario}', 0)" title="Dar de baja"><i class="fas fa-ban"></i> Baja</button>`
                    : `<button class="btn-action btn-stats" onclick="cambiarEstado('${u.idusuario}', 1)" title="Reactivar"><i class="fas fa-check"></i> Activar</button>`;

                html += `
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <img src="${avatar}" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid var(--calypso);">
                                <div>
                                    <div style="font-weight:800; font-size:1rem; color:white;">${u.nombre} ${u.apellido}</div>
                                    <div style="font-size:0.8rem; color:rgba(255,255,255,0.6);">${u.correo}</div>
                                </div>
                            </div>
                        </td>
                        <td>${rolHtml}<span style="display:none;">${rolTexto}</span></td>
                        <td>${estadoHtml}<span style="display:none;">${estadoTexto}</span></td>
                        <td>
                            <div class="action-buttons-container">
                                <button class="btn-action btn-edit" onclick="editarUsuario(${u.idusuario}, '${u.nombre}', '${u.apellido}', '${u.correo}', '${u.telefono}', '${u.rol}', ${u.es_alabanza || 0})">
                                    <i class="fas fa-pen"></i> Editar
                                </button>
                                ${btnEstado}
                            </div>
                        </td>
                    </tr>
                `;
            });

            $('#tablaBody').html(html);

            var table = $('#tablaUsuarios').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" },
                "pageLength": 8,
                "lengthChange": false,
                "dom": 'rtp', 
                "responsive": true,
                "orderCellsTop": true 
            });

            $('.filter-input').on('keyup change', function() {
                var colIndex = $(this).data('col');
                var value = $(this).val();
                table.column(colIndex).search(value).draw();
            });
        },
        error: function(xhr, status, error) {
            console.error('Error al listar usuarios:', xhr.responseText);
            mostrarAlerta('error', 'Error al cargar usuarios');
        }
    });
}

function nuevoUsuario() {
    $('#formUsuario')[0].reset();
    limpiarErrores();
    
    $('#idusuario').val('');
    $('#accionUsuario').val('insert');
    $('#tituloModalUsuario').text('Crear Nuevo Usuario');
    $('#helpPass').text('Obligatoria para usuarios nuevos.');
    $('#es_alabanza').prop('checked', false);
    abrirModal('modalUsuario');
}

function editarUsuario(id, nombre, apellido, correo, telefono, rol, esAlabanza) {
    limpiarErrores();
    
    $('#idusuario').val(id);
    $('#nombre').val(nombre);
    $('#apellido').val(apellido);
    $('#correo').val(correo);
    $('#telefono').val(telefono);
    $('#rol').val(rol);
    
    // MARCAR O DESMARCAR CHECKBOX DE ALABANZA
    $('#es_alabanza').prop('checked', esAlabanza == 1);
    
    $('#accionUsuario').val('insert');
    $('#pass').val('');
    $('#helpPass').text('Dejar en blanco para mantener la contraseña actual.');
    
    $('#tituloModalUsuario').text('Editar Usuario');
    abrirModal('modalUsuario');
}

function cambiarEstado(id, nuevoEstado) {
    let msg = nuevoEstado == 0 ? "¿Desactivar usuario?" : "¿Reactivar usuario?";
    if(!confirm(msg)) return;

    let accion = nuevoEstado == 0 ? 'softDelete' : 'activate';
    
    $.ajax({
        url: '/app/controllers/usuarioController.php',
        type: 'POST',
        data: { accion: accion, idusuario: id },
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                mostrarAlerta('success', res.success);
                listarUsuarios();
            } else {
                mostrarAlerta('error', res.error);
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
            mostrarAlerta('error', 'Error al cambiar estado');
        }
    });
}

function limpiarErrores() {
    $('.form-group input').css('border', '');
    $('small[id^="error"]').text('').hide();
}

function abrirModal(id) { $('#' + id).addClass('active'); }
function cerrarModal(id) { $('#' + id).removeClass('active'); }

function mostrarAlerta(tipo, msg) {
    let color = tipo === 'success' ? '#18c5a3' : '#ff6b6b';
    $('#alertContainer').html(`
        <div style="background:${color}; color:black; padding:15px; margin-bottom:20px; border-radius:8px; font-weight:bold; box-shadow:0 4px 10px rgba(0,0,0,0.2);">
            ${msg}
        </div>
    `);
    setTimeout(() => { $('#alertContainer').html(''); }, 3000);
}

$(window).on('click', function(e) { 
    if ($(e.target).hasClass('modal')) $('.modal').removeClass('active'); 
});