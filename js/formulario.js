//------------------------------------------------------------------------------
//---------------------------FORMULARIO Y MODAL--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // 1. Selecciona todos los formularios con la clase 'contact-form' (para uso multi-página)
    const forms = document.querySelectorAll('.contact-form');

    forms.forEach(form => {
        // Establecer el campo oculto 'page-url' con la URL actual de la página
        const formUrlInput = form.querySelector('#page-url');
        
        if (formUrlInput) {
            formUrlInput.value = window.location.pathname; // Captura la URL actual (ej: /servicios/marketing)
        }

        // 2. Maneja el evento de envío del formulario
        form.addEventListener('submit', async function(e) {
            e.preventDefault(); // Detiene el envío normal (para usar AJAX)

            const formData = new FormData(form);

            try {
                // 3. Envío asíncrono al script PHP
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData // Envía todos los datos del formulario (incluyendo pageUrl y los checkboxes)
                });
                
                // 4. Leer la respuesta JSON del PHP
                const result = await response.json(); 

                // 5. Lógica de éxito o error
                if (response.ok && result.success) { 
                    
                    form.reset(); // Limpia los campos del formulario
                    
                    // --- Lógica del Modal de "¡Muchas gracias!" ---
                    const modal = document.getElementById('success-modal');
                    const closeBtn = modal.querySelector('.close-button-custom'); 
                    
                    const closeModal = () => { modal.style.display = 'none'; };

                    modal.style.display = 'flex'; // Muestra el modal (usando flex para el centrado)
                    
                    // Cierra al hacer clic en el botón (X)
                    if (closeBtn) {
                        closeBtn.onclick = closeModal;
                    }
                    
                    // Cierra al hacer clic fuera del modal
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            closeModal();
                        }
                    }
                    // --------------------------------------------------

                } else {
                    // Error reportado por el servidor PHP
                    console.error('Error al procesar formulario:', result.message || 'Error desconocido del servidor.');
                    // Muestra un mensaje de error al usuario, usando el mensaje enviado por el PHP
                    alert('Hubo un error al enviar el formulario. Intenta de nuevo. (Detalle: ' + (result.message || 'Error del servidor') + ')');
                }

            } catch (error) {
                // Error de red, timeout o fallo total de conexión
                console.error('Error de red/conexión:', error);
                alert('Error de conexión. Por favor, revisa tu red.');
            }
        });
    });
});