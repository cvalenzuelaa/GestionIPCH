const limpiarRut = inputRut => {
    // Limpiar todo lo que no sea número o letra K/k
    let limpio = inputRut.value.replace(/[^0-9kK]/g, '').toUpperCase();

    // Limitar a máximo 9 caracteres (8 dígitos + 1 DV)
    if (limpio.length > 9) {
        limpio = limpio.slice(0, 9);
    }

    // Formatear y validar
    const resultado = validarRut(limpio);

    // Asignar el rut formateado de vuelta al campo
    inputRut.value = resultado.rutFormateado;

    return resultado;
};

const validarRut = rutSinFormato => {
    // Limpiar todo lo que no sea número o letra K/k
    let rut = rutSinFormato.replace(/[^0-9kK]/g, '').toUpperCase();

    // Validar largo mínimo (2 caracteres: cuerpo mínimo 1 + DV) - luego se exige cuerpo >=7
    if (rut.length < 2) {
        return { valido: false, rutFormateado: rut };
    }

    const cuerpo = rut.slice(0, -1);
    const dv = rut.slice(-1);

    // Validar largo mínimo del cuerpo (7 dígitos mínimo)
    if (cuerpo.length < 7) {
        return { valido: false, rutFormateado: rut };
    }

    // Calcular DV esperado
    let suma = 0;
    let multiplo = 2;

    for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += parseInt(cuerpo[i], 10) * multiplo;
        multiplo = multiplo === 7 ? 2 : multiplo + 1;
    }

    let dvEsperado = 11 - (suma % 11);
    dvEsperado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();

    const valido = dv === dvEsperado;

    // Formatear cuerpo con puntos
    const cuerpoFormateado = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    const rutFormateado = `${cuerpoFormateado}-${dv}`;

    return { valido, rutFormateado };
};

const validaCorreo = (inputCorreo, inputError) => {
    const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!inputCorreo.value || !regexCorreo.test(inputCorreo.value.trim())) {
        inputError.textContent = 'Correo inválido.';
        formatoInputError(inputCorreo, inputError);
        return false;
    } else {
        inputError.textContent = '';
        formatoInputExito(inputCorreo, inputError);
        return true;
    }
};

const validaNombres = (inputNombre, inputError) => {
    const soloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,100}$/;
    if (!inputNombre.value || !soloLetras.test(inputNombre.value.trim())) {
        inputError.textContent = 'Nombre/apellido inválido (mín. 3 letras).';
        formatoInputError(inputNombre, inputError);
        return false;
    } else {
        inputError.textContent = '';
        formatoInputExito(inputNombre, inputError);
        return true;
    }
};

const validaPass = (inputPass, inputError) => {
    if (!inputPass.value || inputPass.value.length < 4) {
        inputError.textContent = 'La contraseña debe tener al menos 4 caracteres.';
        formatoInputError(inputPass, inputError);
        return false;
    } else {
        inputError.textContent = '';
        formatoInputExito(inputPass, inputError);
        return true;
    }
};

const validaPassConfirm = (inputPass, inputPassConfirm, inputError) => {
    if (inputPass.value !== inputPassConfirm.value) {
        inputError.textContent = 'Las contraseñas no coinciden.';
        formatoInputError(inputPassConfirm, inputError);
        return false;
    } else {
        inputError.textContent = '';
        formatoInputExito(inputPassConfirm, inputError);
        return true;
    }
};

// Valida teléfono con prefijo +56 9 y 8 dígitos adicionales.
// inputTelefono.value puede estar en formato visual "+56 9 1234 5678" o vacio.
// Se extraen dígitos y se valida que, tras quitar country code y prefijo 9,
// queden exactamente 8 dígitos.
const validaTelefono = (inputTelefono, inputError) => {
    if (!inputTelefono || !inputTelefono.value) {
        // campo vacío aceptable en muchos casos; si deseas hacerlo obligatorio, maneja fuera
        inputError.textContent = '';
        // si está vacío, no marcar error visual (pero puedes cambiar)
        inputTelefono.style.border = '';
        inputError.style.display = 'none';
        return true;
    }

    // Extraer solo dígitos
    let digits = inputTelefono.value.replace(/\D/g, '');

    // Si incluye country code 56 al inicio, quitarlo
    if (digits.startsWith('56')) digits = digits.slice(2);

    // Si viene con el 9 del prefijo, quitarlo (porque el 9 está fijo en prefijo)
    if (digits.startsWith('9')) digits = digits.slice(1);

    // Ahora deben quedar exactamente 8 dígitos
    if (!/^\d{8}$/.test(digits)) {
        inputError.textContent = 'Teléfono inválido. Deben ingresarse 8 dígitos después de "+56 9".';
        formatoInputError(inputTelefono, inputError);
        return false;
    }

    inputError.textContent = '';
    formatoInputExito(inputTelefono, inputError);
    return true;
};

const validaEstado = (selectEstado, inputError) => {
    if (!selectEstado || !selectEstado.value || selectEstado.value === "" || selectEstado.value === "-1") {
        inputError.textContent = "Por favor, selecciona un estado válido.";
        formatoInputError(selectEstado, inputError);
        return false;
    }
    inputError.textContent = '';
    formatoInputExito(selectEstado, inputError);
    return true;
};

const formatoInputError = (inputControl, inputError) => {
    if (inputError) inputError.style.display = 'block';
    if (inputControl) inputControl.style.border = '2px solid rgba(255,107,107,0.9)';
    return false;
};

const formatoInputExito = (inputExito, inputError) => {
    if (inputExito) inputExito.style.border = '2px solid rgba(24,197,163,0.9)';
    if (inputError) inputError.style.display = 'none';
    return true;
};


/**
 * attachTelefonoMask(input)
 * - Muestra el prefijo fijo "+56 9 "
 * - Permite máximo 8 dígitos adicionales
 * - Impide ingresar más hasta que el usuario borre
 * - Maneja pegado y borrado correctamente
 */
const attachTelefonoMask = (inputTelefono) => {
    if (!inputTelefono) return;

    const prefix = '+56 9 ';
    const formatRest = (rest) => {
        if (rest.length <= 4) return rest;
        return rest.slice(0,4) + (rest.length > 4 ? ' ' + rest.slice(4) : '');
    };

    const rebuild = (rawDigits) => {
        // rawDigits: sólo dígitos
        // quitar country code si existe
        if (rawDigits.startsWith('56')) rawDigits = rawDigits.slice(2);
        // quitar primer 9 si el usuario lo pegó (el 9 va en el prefijo fijo)
        if (rawDigits.startsWith('9')) rawDigits = rawDigits.slice(1);
        // limitar a 8 dígitos
        rawDigits = rawDigits.slice(0, 8);
        const restFormatted = formatRest(rawDigits);
        return prefix + restFormatted;
    };

    const setCursorToEnd = () => {
        const len = inputTelefono.value.length;
        try { inputTelefono.setSelectionRange(len, len); } catch (e) {}
    };

    // Inicializar valor si está vacío
    if (!inputTelefono.value || inputTelefono.value.trim() === '') {
        inputTelefono.value = '';
    } else {
        // Si ya contiene algo (editar), reconstruir formato
        const digits = inputTelefono.value.replace(/\D/g, '');
        if (digits.length > 0) inputTelefono.value = rebuild(digits);
    }

    inputTelefono.addEventListener('focus', () => {
        if (!inputTelefono.value || inputTelefono.value.trim() === '') inputTelefono.value = prefix;
        setTimeout(setCursorToEnd, 0);
    });

    inputTelefono.addEventListener('blur', () => {
        // si quedó sólo el prefijo, limpiar el campo
        const digits = inputTelefono.value.replace(/\D/g, '');
        if (!digits || digits === '56' || digits === '9') {
            inputTelefono.value = '';
        }
    });

    inputTelefono.addEventListener('keydown', (e) => {
        const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
        if (allowed.includes(e.key)) return;

        if (e.ctrlKey || e.metaKey) return;

        // permitir sólo dígitos imprimibles (evitar letras y símbolos)
        if (/^\d$/.test(e.key)) {
            const digits = inputTelefono.value.replace(/\D/g, '');
            let core = digits;
            if (core.startsWith('56')) core = core.slice(2);
            if (core.startsWith('9')) core = core.slice(1);
            if (core.length >= 8) {
                // impedir más dígitos
                e.preventDefault();
            }
            return;
        }

        // impedir cualquier otro carácter imprimible
        if (e.key.length === 1) e.preventDefault();
    });

    inputTelefono.addEventListener('input', (e) => {
        const digits = inputTelefono.value.replace(/\D/g, '');
        // reconstruir y setear
        inputTelefono.value = digits.length > 0 ? rebuild(digits) : '';
        setTimeout(setCursorToEnd, 0);
    });

    inputTelefono.addEventListener('paste', (e) => {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text');
        const digits = text.replace(/\D/g, '');
        const currentDigits = inputTelefono.value.replace(/\D/g, '');
        let combined = currentDigits + digits;
        inputTelefono.value = combined.length > 0 ? rebuild(combined) : '';
        setTimeout(setCursorToEnd, 0);
    });
};