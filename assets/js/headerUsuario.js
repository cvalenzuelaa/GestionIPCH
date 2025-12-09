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

    // Logout
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
                    alert('Error al cerrar sesión');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.href = '/login';
            });
        }
    };

    // Resaltar link activo
    const currentPath = window.location.pathname;
    const links = {
        '/dashboard': 'nav-dash',
        '/misactividades': 'nav-actividades',
        '/misoraciones': 'nav-oraciones',
        '/missermones': 'nav-sermones',
        '/misalabanzas': 'nav-alabanzas'
    };

    if (links[currentPath]) {
        const activeLink = document.getElementById(links[currentPath]);
        if (activeLink) activeLink.classList.add('active');
    }
});