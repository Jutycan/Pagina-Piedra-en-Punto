//------------------------------------------------------------------------------
//---------------------------FORMULARIO Y MODAL--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // 1. Selecciona todos los formularios con la clase 'contact-form'
    const forms = document.querySelectorAll('.contact-form');

    forms.forEach(form => {
        // Ya no es necesario el campo pageUrl en el JS si lo eliminaste del HTML

        // 2. Maneja el evento de envío del formulario
        form.addEventListener('submit', async function(e) {
            e.preventDefault(); 

            const formData = new FormData(form);

            try {
                // La acción del formulario debe ser la ruta absoluta: /procesar_formulario.php
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData 
                });
                
                // 3. Leer la respuesta JSON
                const result = await response.json(); 

                // 4. Lógica de éxito o error
                if (response.ok && result.success) { 
                    
                    form.reset(); 
                    
                    // --- Lógica del Modal de "¡Muchas gracias!" ---
                    const modal = document.getElementById('success-modal');
                    const closeBtn = modal.querySelector('.close-button-custom'); 
                    
                    const closeModal = () => { modal.style.display = 'none'; };

                    modal.style.display = 'flex'; 
                    
                    if (closeBtn) { closeBtn.onclick = closeModal; }
                    
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            closeModal();
                        }
                    }
                    // --------------------------------------------------

                } else {
                    // Muestra el mensaje de error del servidor.
                    console.error('Error al procesar formulario:', result.message || 'Error desconocido del servidor.');
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