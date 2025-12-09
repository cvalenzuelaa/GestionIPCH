document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Obtener elementos
    const userMenu = document.getElementById('userMenu');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    // Referencias a notificaciones para coordinar cierre
    const notifDropdown = document.getElementById('notifDropdown');
    const notifWrapper = document.getElementById('notifWrapper');

    // 2. Evento Click en Usuario
    if (userMenu && dropdownMenu) {
        userMenu.addEventListener('click', function(e) {
            // Detenemos propagación para que el click no llegue al document (que lo cerraría)
            e.stopPropagation();
            
            // Alternar clase ACTIVE
            const isOpen = dropdownMenu.classList.toggle('active');
            
            // Si acabamos de abrir el usuario, cerramos notificaciones
            if (isOpen && notifDropdown) {
                notifDropdown.classList.remove('active');
            }
        });

        // Evitar que click DENTRO del menú lo cierre
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // 3. Cerrar todo al hacer click fuera (Global)
    document.addEventListener('click', function(e) {
        // Cerrar Usuario
        if (dropdownMenu && dropdownMenu.classList.contains('active')) {
            dropdownMenu.classList.remove('active');
        }

        // Cerrar Notificaciones (Respaldo para clicks fuera del wrapper)
        if (notifDropdown && notifDropdown.classList.contains('active')) {
            // Verificamos si el click fue fuera del wrapper de notificaciones
            if (notifWrapper && !notifWrapper.contains(e.target)) {
                notifDropdown.classList.remove('active');
            }
        }
    });

    // --- LOGOUT ---
    window.logout = function() {
        if (confirm('¿Estás seguro de que deseas cerrar tu sesión?')) {
            fetch('/app/sesiones/sessionClose.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin' // Importante para mantener cookies de sesión
            })
            .then(response => {
                // Verificar si la respuesta es exitosa
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta logout:', data); // Debug
                
                if (data.success) {
                    // Redirigir inmediatamente
                    window.location.href = '/login';
                } else {
                    alert('Error al cerrar sesión: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error en logout:', error);
                // Incluso si hay error, intentar redirigir (la sesión probablemente se cerró)
                window.location.href = '/login';
            });
        }
    };
});