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
//-----------------------------FORMULARIO CONTACTO---------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // 1. Elementos del Formulario
    const form = document.getElementById('contact-form');

    // 2. Elementos del Modal
    const modal = document.getElementById('contact-modal');
    const modalTitle = document.getElementById('modal-title-contact');
    const modalMessage = document.getElementById('modal-message-contact');
    const modalIcon = document.getElementById('modal-icon-contact');
    const closeBtn = document.getElementById('modal-close-btn-contact');

    if (!form) {
        console.error("No se encontró el formulario de contacto con ID 'contact-form'.");
        return; 
    }

    // --- Funcionalidad del Modal ---
    const showModal = (success, title, message) => {
        modalTitle.textContent = title;
        modalMessage.textContent = message;
            
        // Estilos del icono
        modalIcon.style.color = success ? '#4CAF50' : '#F44336'; 
        modalIcon.innerHTML = success ? '✔️' : '❌';
        
        modal.style.display = 'block';
    };

    closeBtn.onclick = () => {
        modal.style.display = 'none';
    };

    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
    // -------------------------------

    // --- Manejo del Envío del Formulario ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault(); 

        const submitBtn = form.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';

        try {
            const formData = new FormData(form);
            
            // Envío de la petición AJAX al script PHP
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                showModal(
                    true, 
                    '¡Mensaje Recibido!', 
                    'Gracias por contactarnos, ' + result.nombre + '. Hemos recibido tu mensaje y te responderemos en breve.'
                );
                form.reset(); // Limpiar el formulario
            } else {
                showModal(
                    false, 
                    'Error al enviar el mensaje', 
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
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar →';
        }
    });
});