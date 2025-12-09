document.addEventListener('DOMContentLoaded', function() {
    
    // Cambiar Foto de Perfil
    document.getElementById('inputFoto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            mostrarAlerta('error', 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP)');
            this.value = '';
            return;
        }

        // Validar tamaño (máximo 5MB)
        if (file.size > 5 * 1024 * 1024) {
            mostrarAlerta('error', 'La imagen no debe superar 5MB');
            this.value = '';
            return;
        }

        // Preview inmediato
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewAvatar').src = event.target.result;
        };
        reader.readAsDataURL(file);

        // Subir automáticamente
        const formData = new FormData();
        formData.append('accion', 'updateProfile');
        formData.append('idusuario', document.querySelector('input[name="idusuario"]').value);
        formData.append('avatar', file);
        formData.append('nombre', document.querySelector('input[name="nombre"]').value);
        formData.append('apellido', document.querySelector('input[name="apellido"]').value);
        formData.append('correo', document.querySelector('input[name="correo"]').value);
        formData.append('telefono', document.querySelector('input[name="telefono"]').value);

        fetch('/app/controllers/usuarioController.php', {
            method: 'POST',
            body: formData
        })
        .then(r => {
            if (!r.ok) throw new Error('Error en la respuesta del servidor');
            return r.json();
        })
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', 'Foto de perfil actualizada correctamente');
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarAlerta('error', res.error || 'Error al actualizar foto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error de conexión al actualizar foto');
        });
    });

    // Actualizar Datos Personales
    document.getElementById('formDatos').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'updateProfile');

        fetch('/app/controllers/usuarioController.php', {
            method: 'POST',
            body: formData
        })
        .then(r => {
            if (!r.ok) throw new Error('Error en la respuesta del servidor');
            return r.json();
        })
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', 'Datos actualizados correctamente');
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarAlerta('error', res.error || 'Error al actualizar datos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error de conexión al actualizar datos');
        });
    });

    // Cambiar Contraseña
    document.getElementById('formPass').addEventListener('submit', function(e) {
        e.preventDefault();

        const passNueva = document.querySelector('input[name="pass_nueva"]').value;
        const passConfirmar = document.getElementById('pass_confirmar').value;

        if(passNueva.length < 6) {
            mostrarAlerta('error', 'La contraseña debe tener al menos 6 caracteres');
            return;
        }

        if(passNueva !== passConfirmar) {
            mostrarAlerta('error', 'Las contraseñas no coinciden');
            return;
        }

        const formData = new FormData(this);
        formData.append('accion', 'changePassProfile');

        fetch('/app/controllers/usuarioController.php', {
            method: 'POST',
            body: formData
        })
        .then(r => {
            if (!r.ok) throw new Error('Error en la respuesta del servidor');
            return r.json();
        })
        .then(res => {
            if(res.success) {
                mostrarAlerta('success', 'Contraseña actualizada correctamente');
                cerrarModalPass();
                this.reset();
            } else {
                mostrarAlerta('error', res.error || 'Error al cambiar contraseña');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error de conexión al cambiar contraseña');
        });
    });
});

// FUNCIONES DEL MODAL
function abrirModalPass() {
    document.getElementById('modalPass').classList.add('active');
}

function cerrarModalPass() {
    document.getElementById('modalPass').classList.remove('active');
    document.getElementById('formPass').reset();
}

window.onclick = function(e) {
    const modal = document.getElementById('modalPass');
    if (e.target === modal) {
        cerrarModalPass();
    }
}

// FUNCIÓN DE ALERTAS
function mostrarAlerta(tipo, mensaje) {
    const color = tipo === 'success' ? '#18c5a3' : '#ff6b6b';
    const icon = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const alertContainer = document.getElementById('alertContainer');
    
    alertContainer.innerHTML = `
        <div style="background:${color}; color:white; padding:15px; border-radius:10px; margin-bottom:20px; 
                    display:flex; align-items:center; gap:10px; animation: slideDown 0.3s ease;">
            <i class="fas ${icon}"></i>
            <span>${mensaje}</span>
        </div>
    `;

    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 4000);
}