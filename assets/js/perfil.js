document.addEventListener('DOMContentLoaded', function() {
    
    // -- CAMPOS PERFIL --
    const inputNombre = document.getElementById('nombre');
    const inputApellido = document.getElementById('apellido');
    const inputCorreo = document.getElementById('correo');
    const inputTelefono = document.getElementById('telefono');
    const inputFoto = document.getElementById('inputFoto');
    const imgPreview = document.getElementById('previewAvatar');

    // -- CAMPOS PASS --
    const inputP1 = document.getElementById('p1');
    const inputP2 = document.getElementById('p2');
    const errP1 = document.getElementById('errorP1');
    const errP2 = document.getElementById('errorP2');

    // 1. VALIDACIONES GLOBALES (Si existen en globales.js)
    if(typeof attachTelefonoMask === 'function') attachTelefonoMask(inputTelefono);
    
    inputNombre.addEventListener('input', () => validaNombres(inputNombre, document.getElementById('errorNombre')));
    inputApellido.addEventListener('input', () => validaNombres(inputApellido, document.getElementById('errorApellido')));
    inputCorreo.addEventListener('input', () => validaCorreo(inputCorreo, document.getElementById('errorCorreo')));
    inputTelefono.addEventListener('input', () => validaTelefono(inputTelefono, document.getElementById('errorTelefono')));

    // 2. VALIDACIÓN CONTRASEÑA (Personalizada para el modal)
    function validarPass() {
        let valido = true;
        // Check P1
        if(inputP1.value.length < 6) {
            errP1.style.display = 'block';
            errP1.textContent = 'La contraseña debe tener al menos 6 caracteres.';
            inputP1.style.borderColor = '#ff6b6b';
            valido = false;
        } else {
            errP1.style.display = 'none';
            inputP1.style.borderColor = '#18c5a3';
        }
        
        // Check P2 (Coincidencia)
        if(inputP2.value !== inputP1.value) {
            errP2.style.display = 'block';
            errP2.textContent = 'Las contraseñas no coinciden.';
            inputP2.style.borderColor = '#ff6b6b';
            valido = false;
        } else if (inputP2.value.length > 0) {
            errP2.style.display = 'none';
            inputP2.style.borderColor = '#18c5a3';
        }
        return valido;
    }

    inputP1.addEventListener('input', validarPass);
    inputP2.addEventListener('input', validarPass);

    // 3. PREVIEW FOTO EN TIEMPO REAL
    inputFoto.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { 
                imgPreview.src = e.target.result; 
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // 4. GUARDAR DATOS (Perfil con foto)
    document.getElementById('formDatos').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('accion', 'updateProfile');
        
        // Si hay foto seleccionada, la agregamos
        if(inputFoto.files.length > 0) {
            fd.append('foto', inputFoto.files[0]);
        }

        fetch('/app/controllers/usuarioController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                alert('✅ ' + res.success);
                
                // ACTUALIZAR NOMBRE EN HEADER
                const menuName = document.querySelector('.user-name');
                if(menuName) menuName.textContent = inputNombre.value;
                
                // ACTUALIZAR AVATAR EN HEADER (si cambió)
                if(res.avatar) {
                    const headerImg = document.getElementById('headerAvatarImg');
                    if(headerImg) {
                        headerImg.src = '/' + res.avatar + '?t=' + new Date().getTime();
                    }
                    
                    // También actualizar el preview en la página de perfil
                    imgPreview.src = '/' + res.avatar + '?t=' + new Date().getTime();
                }
                
                // Limpiar input de foto
                inputFoto.value = '';
                
            } else {
                alert('❌ Error: ' + res.error);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('❌ Error de conexión');
        });
    });

    // 5. CAMBIAR CONTRASEÑA (CON CIERRE DE SESIÓN)
    document.getElementById('formPass').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if(!validarPass()) {
            alert('⚠️ Por favor corrige los errores antes de continuar.');
            return;
        }

        if(!confirm('⚠️ Se cerrará tu sesión al cambiar la contraseña. ¿Continuar?')) return;

        const fd = new FormData(this);
        fd.append('accion', 'changePassProfile');

        fetch('/app/controllers/usuarioController.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                alert('✅ ' + res.success + '\n\nSerás redirigido al login.');
                cerrarModalPass();
                
                // Esperar 1 segundo y redirigir
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1000);
                
            } else {
                alert('❌ ' + res.error);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('❌ Error de conexión');
        });
    });
});

// UTILIDADES MODAL
function abrirModalPass() {
    document.getElementById('modalPass').classList.add('active');
    document.getElementById('formPass').reset();
    // Limpiar estilos visuales
    document.querySelectorAll('#formPass small').forEach(s => s.style.display = 'none');
    document.querySelectorAll('#formPass input').forEach(i => i.style.borderColor = 'rgba(255,255,255,0.1)');
}

function cerrarModalPass() {
    document.getElementById('modalPass').classList.remove('active');
}

window.onclick = function(e) {
    if (e.target == document.getElementById('modalPass')) cerrarModalPass();
}