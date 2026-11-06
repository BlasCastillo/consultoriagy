// Función global reutilizable para validar formularios en SGCJ
window.validarFormularioSGCJ = function (form) {
    if (!form) return true;

    const requiredInputs = form.querySelectorAll('[required]');
    let hasError = false;

    requiredInputs.forEach(input => {
        // Ignorar inputs deshabilitados
        if (input.disabled) return;

        // Verificar si está oculto (excepto selects mejorados por TomSelect)
        const isTomSelect = input.tagName === 'SELECT' && input.tomselect;
        const isVisible = isTomSelect || (input.offsetParent !== null);
        if (!isVisible) return;

        let isEmpty = false;
        if (input.type === 'checkbox' || input.type === 'radio') {
            const group = form.querySelectorAll(`[name="${CSS.escape(input.name)}"]`);
            isEmpty = !Array.from(group).some(el => el.checked);
        } else if (input.tagName === 'SELECT' && input.multiple) {
            isEmpty = input.selectedOptions.length === 0;
        } else {
            isEmpty = !input.value || !input.value.trim();
        }

        if (isEmpty || !input.checkValidity()) {
            hasError = true;
            const targetEl = isTomSelect && input.tomselect.control ? input.tomselect.control : input;
            targetEl.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

            const removeError = () => {
                targetEl.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                input.removeEventListener('input', removeError);
                input.removeEventListener('change', removeError);
            };
            input.addEventListener('input', removeError);
            input.addEventListener('change', removeError);
        }
    });

    // Verificar validez nativa de todos los demás campos (minlength, maxlength, min, max, email)
    if (!form.checkValidity()) {
        hasError = true;
        form.querySelectorAll(':invalid').forEach(invalidEl => {
            if (invalidEl.disabled || invalidEl.offsetParent === null) return;
            invalidEl.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
            const removeInvalid = () => {
                invalidEl.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                invalidEl.removeEventListener('input', removeInvalid);
                invalidEl.removeEventListener('change', removeInvalid);
            };
            invalidEl.addEventListener('input', removeInvalid);
            invalidEl.addEventListener('change', removeInvalid);
        });
    }

    if (hasError) {
        if (typeof form.reportValidity === 'function') {
            form.reportValidity();
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                icon: 'error',
                title: 'Por favor, complete correctamente los campos remarcados en rojo.'
            });
        }
        return false;
    }

    return true;
};

document.addEventListener('DOMContentLoaded', () => {
    const now = new Date();
    const today = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().split('T')[0];

    // Inicializar fechas futuras con zona horaria local
    document.querySelectorAll('.val-fecha-futura').forEach(input => {
        input.setAttribute('min', today);
    });

    // Función auxiliar para sincronizar .val-fecha-rango
    const sincronizarFechaRango = (sourceInput) => {
        const destSelector = sourceInput.getAttribute('data-target');
        if (destSelector && sourceInput.value) {
            const destInput = document.querySelector(destSelector);
            if (destInput) {
                destInput.setAttribute('min', sourceInput.value);
                if (destInput.value && destInput.value < sourceInput.value) {
                    destInput.value = sourceInput.value;
                }
            }
        }
    };

    // Inicializar .val-fecha-rango al cargar la página
    document.querySelectorAll('.val-fecha-rango').forEach(input => {
        sincronizarFechaRango(input);
    });

    // Delegación de eventos para validaciones dinámicas en tiempo real (inputs)
    document.body.addEventListener('input', (e) => {
        const target = e.target;

        // Regla .val-numerico
        if (target.classList.contains('val-numerico')) {
            target.value = target.value.replace(/[^0-9]/g, '');
        }

        // Regla .val-texto-limpio (Evita caracteres de inyección XSS)
        if (target.classList.contains('val-texto-limpio')) {
            target.value = target.value.replace(/[<>;={}]/g, '');
        }

        // Regla .val-solo-letras (Solo permite letras, acentos, ñ y espacios mientras se escribe)
        if (target.classList.contains('val-solo-letras')) {
            target.value = target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        }

        // Regla .val-fecha-rango (Sincronización de origen a destino)
        if (target.classList.contains('val-fecha-rango')) {
            sincronizarFechaRango(target);
        }
    });

    // Escuchar también 'change' para selectores de fecha .val-fecha-rango
    document.body.addEventListener('change', (e) => {
        const target = e.target;
        if (target && target.classList && target.classList.contains('val-fecha-rango')) {
            sincronizarFechaRango(target);
        }
    });

    // Regla de Validación Global al enviar formularios
    document.body.addEventListener('submit', (e) => {
        if (e.target && e.target.tagName === 'FORM') {
            if (!window.validarFormularioSGCJ(e.target)) {
                e.preventDefault();
            }
        }
    });
});
