document.addEventListener('DOMContentLoaded', function() {
    
    const userMenu = document.getElementById('userMenu');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifWrapper = document.getElementById('notifWrapper');

    if (userMenu && dropdownMenu) {
        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = dropdownMenu.classList.toggle('active');
            if (isOpen && notifDropdown) {
                notifDropdown.classList.remove('active');
            }
        });

        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    document.addEventListener('click', function(e) {
        if (dropdownMenu && dropdownMenu.classList.contains('active')) {
            dropdownMenu.classList.remove('active');
        }

        if (notifDropdown && notifDropdown.classList.contains('active')) {
            if (notifWrapper && !notifWrapper.contains(e.target)) {
                notifDropdown.classList.remove('active');
            }
        }
    });

    window.logout = function() {
        if (confirm('¿Estás seguro de que deseas cerrar tu sesión?')) {
            fetch('/app/sesiones/sessionClose.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = '/login';
                } else {
                    alert('Error al cerrar sesión: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error en logout:', error);
                window.location.href = '/login';
            });
        }
    };
});