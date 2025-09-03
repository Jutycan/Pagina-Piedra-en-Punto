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

// -------------------------------------- ----------------------------------------
//------------------------- Estilos del Banner ----------------------------------
// -------------------------------------- ------------------------------------ -----
document.addEventListener('DOMContentLoaded', () => {
    const chefContainer = document.getElementById('chef-container');
    const textContent = document.querySelector('.service-banner-text');

    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        // Al cargar en móvil, añade el pulso
        chefContainer.classList.add('pulse-on');

        chefContainer.addEventListener('click', () => {
            // Remueve el pulso y oculta la imagen colapsándola
            chefContainer.classList.remove('pulse-on');
            chefContainer.classList.add('is-hidden');
            
            // Muestra el texto expandiéndolo
            textContent.classList.add('is-visible');

            // Deshabilita el clic para evitar interacciones duplicadas
            chefContainer.style.pointerEvents = 'none';
        });

        // NOTA: No agregues aquí ninguna lógica que escuche el evento de scroll
        // para revertir el estado. El estado se mantendrá hasta que se recargue
        // la página.
    } else {
        // En escritorio, elimina las clases de móvil para asegurar el comportamiento correcto
        chefContainer.classList.remove('pulse-on');
        chefContainer.classList.remove('is-hidden');
        textContent.classList.remove('is-visible');
        chefContainer.style.pointerEvents = 'auto';
    }
});

// ----------------------------------------------------------------------------- 
//----------------------Estilos de Beneficios------------------------------------- 
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const benefitCards = document.querySelectorAll('.benefit-card');
    const benefitsSection = document.querySelector('.benefits-section');
    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        benefitCards.forEach(card => {
            card.addEventListener('click', () => {
                const isActive = card.classList.contains('is-active');

                // Si la tarjeta clicada ya está activa, ciérrala.
                if (isActive) {
                    card.classList.remove('is-active');
                    return; // Sal del evento
                }

                // Cierra todas las demás tarjetas
                benefitCards.forEach(otherCard => {
                    otherCard.classList.remove('is-active');
                });
                
                // Activa la tarjeta clicada
                card.classList.add('is-active');
            });
        });

        // Restablece los bloques al bajar o al salir de la sección
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    benefitCards.forEach(card => {
                        card.classList.remove('is-active');
                    });
                }
            });
        }, {
            threshold: 0.0
        });

        observer.observe(benefitsSection);

    } else {
        // Lógica de hover para escritorio (no se necesita si el CSS lo maneja)
        // Puedes dejar esto vacío si solo usas el CSS para el hover
    }

    // Asegura el comportamiento correcto al cambiar el tamaño de la ventana
    window.addEventListener('resize', () => {
        if ((window.innerWidth > 768 && isMobile) || (window.innerWidth <= 768 && !isMobile)) {
            location.reload();
        }
    });
});

// ----------------------------------------------------------------------------- 
//----------------------Estilos de faqs-uno------------------------------------- 
// ----------------------------------------------------------------------------- 
document.querySelectorAll(".faq-uno-question").forEach(button => {
    button.addEventListener("click", () => {
        const faqItem = button.parentElement;

        // Cierra todos los bloques antes de abrir el nuevo
        document.querySelectorAll(".faq-uno-item").forEach(item => {
            if (item !== faqItem) {
                item.classList.remove("active");
            }
        });

        // Alternar el estado del clic
        faqItem.classList.toggle("active");
    });
});

// ----------------------------------------------------------------------------- 
//----------------------Estilos de faqs-dos------------------------------------- 
// -----------------------------------------------------------------------------
document.querySelectorAll(".faq-dos-question").forEach(button => {
    button.addEventListener("click", () => {
        const faqItem = button.parentElement;

        // Cierra todos los bloques antes de abrir el nuevo
        document.querySelectorAll(".faq-dos-item").forEach(item => {
            if (item !== faqItem) {
                item.classList.remove("active");
            }
        });

        // Alternar el estado del clic
        faqItem.classList.toggle("active");
    });
});

// ----------------------------------------------------------------------------- 
//----------------------Estilos de faqs-tres------------------------------------- 
// -----------------------------------------------------------------------------
document.querySelectorAll(".faq-tres-question").forEach(button => {
    button.addEventListener("click", () => {
        const faqItem = button.parentElement;

        // Cierra todos los bloques antes de abrir el nuevo
        document.querySelectorAll(".faq-tres-item").forEach(item => {
            if (item !== faqItem) {
                item.classList.remove("active");
            }
        });

        // Alternar el estado del clic
        faqItem.classList.toggle("active");
    });
});
