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

            const formData = new FormData(form);
            const formObject = {};
            formData.forEach((value, key) => {
                formObject[key] = value;
            });
            
            // Si tenías un formMessage simple, ya no es necesario, lo ignoramos o eliminamos.
            // if (formMessage) formMessage.style.display = 'none';

            try {
                // 2. Envío de datos al servidor (URL de Apps Script)
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formObject)
                });

                // 🚨 SECCIÓN ACTUALIZADA: Manejo del Éxito usando el Modal
                if (response.ok) {
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
                    
                    // Cerrar al hacer clic en la 'X'
                    closeBtn.onclick = closeModal;
                    
                    // Cerrar al hacer clic en el fondo gris (fuera del contenido)
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            closeModal();
                        }
                    }
                    // 🚨 Fin de la Sección Actualizada

                } else {
                    // Si el servidor falla al guardar el dato
                    alert('Hubo un error al enviar el formulario. Intenta de nuevo.');
                }

            } catch (error) {
                console.error('Error de red:', error);
                alert('Error de conexión. Por favor, revisa tu red.');
            }
        });
    });
});