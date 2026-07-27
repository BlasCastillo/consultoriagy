document.addEventListener('DOMContentLoaded', () => {

    // Inicializar fechas futuras
    document.querySelectorAll('.val-fecha-futura').forEach(input => {
        const today = new Date().toISOString().split('T')[0];
        input.setAttribute('min', today);
    });

    // Delegación de eventos para validaciones dinámicas en tiempo real (inputs)
    document.body.addEventListener('input', (e) => {
        const target = e.target;
        
        // Regla .val-numerico
        if (target.classList.contains('val-numerico')) {
            target.value = target.value.replace(/[^0-9]/g, '');
        }

        // Regla .val-texto-limpio (Evita XSS)
        if (target.classList.contains('val-texto-limpio')) {
            target.value = target.value.replace(/[<>;={}]/g, '');
        }

        // Regla .val-solo-letras (Solo permite letras, acentos, ñ y espacios mientras se escribe)
        if (target.classList.contains('val-solo-letras')) {
            target.value = target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        }

        // Regla .val-fecha-rango (Sincronización de origen a destino)
        if (target.classList.contains('val-fecha-rango')) {
            const destSelector = target.getAttribute('data-target');
            if (destSelector) {
                const destInput = document.querySelector(destSelector);
                if (destInput) {
                    destInput.setAttribute('min', target.value);
                    if (destInput.value && destInput.value < target.value) {
                        destInput.value = target.value;
                    }
                }
            }
        }
    });

    // Regla de Formularios Vacíos
    document.body.addEventListener('submit', (e) => {
        if (e.target.tagName === 'FORM') {
            const requiredInputs = e.target.querySelectorAll('[required]');
            let hasError = false;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    hasError = true;
                    input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                    
                    // Remover clase al corregir
                    const removeError = () => {
                        input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                        input.removeEventListener('input', removeError);
                    };
                    input.addEventListener('input', removeError);
                }
            });

            if (hasError) {
                e.preventDefault();
                // Mostrar alerta visual genérica
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        icon: 'error',
                        title: 'Por favor, complete los campos remarcados en rojo.'
                    });
                }
            }
        }
    });

});
