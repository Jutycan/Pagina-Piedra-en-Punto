//------------------------------------------------------------------------------
//---------------------------FORMULARIO Y MODAL--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // 1. Selecciona todos los formularios que tienen la clase 'contact-form'
    const forms = document.querySelectorAll('.contact-form');

    forms.forEach(form => {
        // Obtenemos el campo oculto para asignar la URL
        const formUrlInput = form.querySelector('#page-url');

        // Asignamos la ruta de la página actual al campo oculto.
        if (formUrlInput) {
            formUrlInput.value = window.location.pathname; 
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault(); // Detiene el envío normal (para que no recargue la página)

            // CREAMOS EL OBJETO FORMDATA (contiene todos los campos del formulario)
            const formData = new FormData(form);

            // Nota: Eliminamos la conversión a JSON (formObject) para evitar conflictos.

            try {
                // 🚨 CORRECCIÓN CRÍTICA: Envío de datos al servidor (URL de Apps Script)
                await fetch(form.action, {
                    method: 'POST',
                    mode: 'no-cors', // <-- SOLUCIÓN CORS: Ignora el bloqueo del navegador
                    body: formData // <-- Enviamos el formato nativo (funciona mejor con GAS)
                });
                
                // Debido a 'no-cors', el fetch siempre 'parece' exitoso si se conecta a Google. 
                // Por lo tanto, movemos la lógica de éxito aquí.

                // 1. Limpia los campos del formulario
                form.reset(); 

                // 2. Obtiene el modal y sus botones
                const modal = document.getElementById('success-modal');
                const closeBtn = modal.querySelector('.close-button-custom');

                // 3. Define la función para cerrar
                const closeModal = () => {
                    modal.style.display = 'none';
                };

                // 4. Muestra el modal (usando 'flex' para centrarlo)
                modal.style.display = 'flex'; 

                // 5. Configura los eventos para cerrar el modal
                closeBtn.onclick = closeModal;
                window.onclick = function(event) {
                    if (event.target == modal) {
                        closeModal();
                    }
                }

            } catch (error) {
                console.error('Error de red:', error);
                // Si llega a este bloque, es un problema de red real (ej: sin internet), no el bloqueo de Google.
                alert('Error de conexión. Por favor, revisa tu red.');
            }
        });
    });
});