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


// ----------------------------------------------------------------------------- 
//----------------------Estilos de Beneficios------------------------------------- 
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const benefitCards = document.querySelectorAll('.benefit-card');
    const isMobile = window.innerWidth <= 768;

    // Desactivamos el 'is-featured' para móvil
    if (isMobile) {
        benefitCards.forEach(card => {
            card.classList.remove('is-featured');
        });
    }

    if (isMobile) {
        const observerCallback = (entries) => {
            entries.forEach(entry => {
                const card = entry.target;
                
                // Si la tarjeta está entrando o ya está visible en el umbral del 50%
                if (entry.isIntersecting) {
                    // Desactivar todas las tarjetas primero
                    benefitCards.forEach(c => c.classList.remove('is-active-mobile'));
                    
                    // Activar la tarjeta que está en el centro
                    card.classList.add('is-active-mobile');
                }
            });
        };

        const observerOptions = {
            // Root Margin ajustado para que el "centro" de la tarjeta sea el centro del viewport
            rootMargin: '-40% 0px -40% 0px', // Ajustado a 40% para una mejor detección de centro
            threshold: 0.0 
        };

        const observer = new IntersectionObserver(observerCallback, observerOptions);

        benefitCards.forEach(card => {
            observer.observe(card);
        });

    } else {
        // Lógica para Escritorio: Manejo de la tarjeta "destacada" y el hover
        // Esto solo es para asegurar el comportamiento si se requiere JS para el featured
        
        benefitCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                // Si haces hover, desactiva temporalmente el featured si existe
                const featured = document.querySelector('.benefit-card.is-featured:not(:hover)');
                if (featured) {
                    featured.style.transform = 'translateY(0)';
                    featured.style.backgroundColor = 'var(--color-gris-card)';
                }
            });
            card.addEventListener('mouseleave', () => {
                // Al salir, asegura que el featured vuelva a su estado activo
                const featured = document.querySelector('.benefit-card.is-featured');
                if (featured) {
                    featured.style.transform = 'translateY(-10px)';
                    featured.style.backgroundColor = 'var(--color-verde-destacado)';
                }
            });
        });
    }

    // Nota: El evento de resize para recargar la página no es ideal en producción.
    // Lo he eliminado. Los estilos y JS ahora deberían adaptarse de forma dinámica.
});

