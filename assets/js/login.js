document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const correoInput = document.getElementById('correo');
    const passInput = document.getElementById('pass');
    const correoError = document.getElementById('correo-error');
    const passError = document.getElementById('pass-error');
    const btnLogin = document.getElementById('btn-login');

    // Validación Visual
    const validarForm = () => {
        // Habilita el botón si hay algo escrito (validación básica)
        if (correoInput.value.length > 0 && passInput.value.length > 0) {
            btnLogin.disabled = false;
            btnLogin.classList.remove('disabled');
        } else {
            btnLogin.disabled = true;
            btnLogin.classList.add('disabled');
        }
    };

    correoInput.addEventListener('input', validarForm);
    passInput.addEventListener('input', validarForm);

    // ENVÍO DEL FORMULARIO
    formLogin.addEventListener('submit', async e => {
        e.preventDefault();

        passError.style.display = 'none';
        correoError.style.display = 'none';

        const formData = new FormData();
        formData.append('accion', 'login');
        formData.append('correo', correoInput.value.trim());
        formData.append('pass', passInput.value.trim());

        try {
            const response = await fetch('/app/controllers/usuarioController.php', {
                method: 'POST',
                body: formData
            });

            // 1. Obtenemos el texto crudo primero para debug
            const textResponse = await response.text(); 

            try {
                // 2. Intentamos convertir a JSON
                const data = JSON.parse(textResponse);

                if (data.success) {
                    if (data.rol === 'admin') window.location.href = '/dashadmin';
                    else if (data.rol === 'super') window.location.href = '/dashsuperu';
                    else window.location.href = '/dashboard'; 
                } else {
                    passError.textContent = data.error || "Credenciales incorrectas.";
                    passError.style.display = "block";
                }
            } catch (jsonError) {
                // 3. SI FALLA EL JSON, MOSTRAMOS EL ERROR PHP EN CONSOLA
                console.error("Error crítico del servidor (HTML recibido):", textResponse);
                passError.textContent = "Error interno del servidor. Revisa la consola (F12).";
                passError.style.display = "block";
            }

        } catch (error) {
            console.error(error);
            passError.textContent = "Error de conexión con el servidor.";
            passError.style.display = "block";
        }
    });
});