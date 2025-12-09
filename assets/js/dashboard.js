document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard de usuario cargado correctamente');
    
    // Animación de entrada de las tarjetas
    const cards = document.querySelectorAll('.glass-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Efecto hover mejorado
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Verificar si hay notificaciones pendientes
    checkDashboardNotifications();
});

function checkDashboardNotifications() {
    fetch('/app/controllers/notificacionesController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'accion=getMy'
    })
    .then(r => r.json())
    .then(notificaciones => {
        const pendientes = notificaciones.filter(n => n.estado === 'pendiente');
        
        if (pendientes.length > 0) {
            mostrarResumenNotificaciones(pendientes);
        }
    })
    .catch(error => console.error('Error al cargar notificaciones:', error));
}

function mostrarResumenNotificaciones(notificaciones) {
    // Crear banner de notificaciones si hay pendientes
    const banner = document.createElement('div');
    banner.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(24, 197, 163, 0.95);
        color: #000;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        z-index: 999;
        animation: slideInRight 0.5s ease;
        max-width: 350px;
    `;

    const icono = document.createElement('i');
    icono.className = 'fas fa-bell';
    icono.style.fontSize = '1.5rem';

    const texto = document.createElement('div');
    texto.innerHTML = `
        <strong>Tienes ${notificaciones.length} notificación(es) nueva(s)</strong><br>
        <small style="opacity: 0.8;">Haz clic para ver todas</small>
    `;

    banner.appendChild(icono);
    banner.appendChild(texto);
    document.body.appendChild(banner);

    // Al hacer clic, abrir dropdown de notificaciones
    banner.addEventListener('click', function() {
        document.getElementById('notifWrapper')?.querySelector('.notif-btn')?.click();
        banner.remove();
    });

    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        banner.style.animation = 'slideOutRight 0.5s ease';
        setTimeout(() => banner.remove(), 500);
    }, 5000);
}

// Animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .glass-card {
        position: relative;
        overflow: hidden;
    }

    .glass-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: left 0.5s ease;
    }

    .glass-card:hover::before {
        left: 100%;
    }
`;
document.head.appendChild(style);