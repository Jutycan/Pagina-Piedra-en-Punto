//------------------------------------------------------------------------------
//---------------------------HEADER--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.header nav');
    
    // Abrir/cerrar menú principal en móvil
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });

    // En móviles, todos los enlaces del menú principal navegan directamente
    document.querySelectorAll('.header nav ul li a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                // Cierra el menú móvil al hacer clic en cualquier enlace
                navMenu.classList.remove('active');
            }
        });
    });
});

//------------------------------------------------------------------------------
//-----------------------------FORMULARIO PQRS---------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('pqrs-form');
    const modal = document.getElementById('pqrs-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const modalIcon = document.getElementById('modal-icon');
    const closeBtn = document.getElementById('modal-close-btn');

    // Si por alguna razón el formulario no existe (puede ser el problema inicial),
    // terminamos la ejecución del script aquí para evitar errores.
    if (!form) {
        console.error("No se encontró el formulario con ID 'pqrs-form'.");
        return; 
    }

    // Función para mostrar el modal
    const showModal = (success, title, message) => {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
            
        if (success) {
            modalIcon.innerHTML = '✔️'; 
            modalIcon.className = 'modal-success-icon';
        } else {
            modalIcon.innerHTML = '❌'; 
            modalIcon.className = 'modal-error-icon';
        }
        // Asegúrate de que el modal siempre tenga display: block para mostrarse
        modal.style.display = 'block'; 
    };

    // Función para cerrar el modal
    closeBtn.onclick = () => {
        modal.style.display = 'none';
    };

    // Cerrar el modal haciendo clic fuera de él
    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    // Captura del envío del formulario
    form.addEventListener('submit', async (e) => {
        // ESTO ES LO QUE DEBE FUNCIONAR
        e.preventDefault(); 

        // Deshabilitar botón para evitar doble envío
        const submitBtn = form.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';

        try {
            const formData = new FormData(form);
                
            // Usamos form.action para enviar la petición a procesar_pqr.php
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            // La respuesta del servidor es exitosa
            if (result.success) {
                showModal(
                    true, 
                    '¡Solicitud PQRS Recibida!', 
                    'Hemos registrado tu solicitud (#'+result.id+'). Te enviaremos una respuesta formal y profesional en el menor tiempo posible.'
                );
                form.reset(); // Limpiar el formulario
            } else {
                // Error reportado por el script PHP
                showModal(
                    false, 
                    'Error al enviar la solicitud', 
                    result.message || 'Hubo un error en el servidor. Por favor, inténtalo de nuevo.'
                );
            }

        } catch (error) {
            // Error de red/conexión
            console.error('Error de red:', error);
            showModal(
                false, 
                'Error de Conexión', 
                'No pudimos conectar con el servidor. Verifica tu conexión e inténtalo de nuevo.'
            );
        } finally {
            // Habilitar botón al finalizar
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar →';
        }
    });
});