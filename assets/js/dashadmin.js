document.addEventListener('DOMContentLoaded', () => {
    
    // Seleccionar todas las tarjetas del dashboard
    const cards = document.querySelectorAll('.dash-card');

    cards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Evitamos conflicto si el usuario hace clic en algún botón interno (si lo hubiera)
            if (e.target.tagName === 'BUTTON' || e.target.closest('a')) return;

            // 1. Obtener la URL destino del atributo data-link
            const url = this.getAttribute('data-link');
            
            if (url) {
                // 2. Efecto visual instantáneo (Feedback de click)
                this.style.transform = 'scale(0.96)';
                this.style.transition = 'transform 0.1s ease';

                // 3. Redirigir tras una micro-pausa para ver el efecto
                setTimeout(() => {
                    window.location.href = url;
                }, 150);
            }
        });
    });

    // Opcional: Animación de entrada escalonada al cargar la página
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.animation = `fadeInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards ${index * 0.1}s`;
    });
});

// Inyectar estilos de animación dinámicamente si no existen en el CSS
const style = document.createElement('style');
style.innerHTML = `
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);