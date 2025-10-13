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

    // Función para mostrar el modal
    const showModal = (success, title, message) => {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
            
        // Iconografía (simple con emojis)
        if (success) {
            modalIcon.innerHTML = '✔️'; 
            modalIcon.className = 'modal-success-icon';
        } else {
            modalIcon.innerHTML = '❌'; 
            modalIcon.className = 'modal-error-icon';
        }
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

    // Captura del envío del formulario (para evitar recargar la página)
    form.addEventListener('submit', async (e) => {
        // MUY IMPORTANTE: Evita el comportamiento predeterminado del formulario
        e.preventDefault(); 

        // Deshabilitar botón para evitar doble envío
        const submitBtn = form.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';

        try {
            const formData = new FormData(form);
                
            // ✅ VOLVEMOS A USAR form.action, que ahora SÍ tiene un valor en el HTML.
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            // La lógica de éxito (si la DB guardó y los correos se intentaron enviar)
            if (result.success) {
                showModal(
                    true, 
                    '¡Solicitud PQRS Recibida!', 
                    'Hemos registrado tu solicitud (#'+result.id+'). Te enviaremos una respuesta formal y profesional en el menor tiempo posible.'
                );
                form.reset(); // Limpiar el formulario
            } else {
                // Si 'success' es false (ej: faltan campos o falló la DB)
                showModal(
                    false, 
                    'Error al enviar la solicitud', 
                    result.message || 'Hubo un error en el servidor. Por favor, inténtalo de nuevo.'
                );
            }

        } catch (error) {
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