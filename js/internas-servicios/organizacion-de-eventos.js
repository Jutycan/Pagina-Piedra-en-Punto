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
//----------------------------Servicio descripcion--------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const section2 = document.querySelector('.service-description-animated-section-2');

    if (!section2) return;

    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.6
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    section2.classList.add('is-visible');
                } else {
                    section2.classList.remove('is-visible');
                }
            });
        }, observerOptions);

        observer.observe(section2);
    }
});

// ----------------------------------------------------------------------------- 
//----------------------Estilos de beneficios-----------------------------------
// ----------------------------------------------------------------------------- 
document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // Lógica de Tarjetas de Beneficios (Activación por Scroll)
    // ----------------------------------------------------

    const benefitCards = document.querySelectorAll('.benefit-card');
    // Definimos si es móvil al cargar
    const isMobile = window.innerWidth <= 768; 
    let observer; 

    // Función para activar una tarjeta y desactivar las demás
    const setActiveCard = (targetCard) => {
        if (window.innerWidth <= 768) { 
            benefitCards.forEach(card => {
                if (card !== targetCard) {
                    card.classList.remove('is-active');
                }
            });
            targetCard.classList.add('is-active');
        }
    };
    
    // Función para desactivar
    const removeActiveCard = (targetCard) => {
        if (window.innerWidth <= 768) { 
            targetCard.classList.remove('is-active');
        }
    };


    if (isMobile) {
        
        // Configuración del Intersection Observer (manteniendo la lógica centrada)
        const activationMargin = '30%'; 
        const observerOptions = {
            root: null, 
            rootMargin: `-${activationMargin} 0px -${activationMargin} 0px`, 
            threshold: 0.0 
        };

        observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                
                if (entry.isIntersecting) {
                    setActiveCard(entry.target);
                } else {
                    removeActiveCard(entry.target);
                }
            });
        }, observerOptions);

        // APLICAR OBSERVER
        benefitCards.forEach(card => {
            observer.observe(card);
            card.classList.remove('is-active');
        });
        
        
        // CORRECCIÓN CLAVE: ANULACIÓN FORZADA DEL CLIC CON RETRASO
        benefitCards.forEach(card => {
            card.addEventListener('click', (event) => {
                event.preventDefault(); 
                
                // Usamos setTimeout para asegurar que la orden de "desactivar"
                // se ejecuta después de que el navegador ha terminado de procesar el evento 'click/tap'
                setTimeout(() => {
                    // Eliminamos la clase 'is-active' y cualquier estado residual
                    card.classList.remove('is-active');
                }, 50); // 50 milisegundos de retraso
            });
        });

    } else {
        // En escritorio: Limpieza
        benefitCards.forEach(card => {
            card.classList.remove('is-active');
            card.style.cursor = 'pointer';
            // Aseguramos que no haya listeners residuales
            const cardClone = card.cloneNode(true);
            card.parentNode.replaceChild(cardClone, card);
        });
    }

    // Lógica de Resize (Mantenemos la recarga por seguridad)
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if ((window.innerWidth > 768 && isMobile) || (window.innerWidth <= 768 && !isMobile)) {
                location.reload(); 
            }
        }, 250); 
    });
});

// ----------------------------------------------------------------------------- 
//----------------------Estilos de como se ofrece------------------------------------ 
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const howOfferSection = document.querySelector('.how-offer-section');

    if (!howOfferSection) return;

    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        const observerOptions = {
            root: null, // El viewport
            rootMargin: '0px',
            threshold: 0.4 // La sección debe estar al menos 40% visible
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Si la sección está visible, añadir la clase para animar la imagen
                    howOfferSection.classList.add('is-visible');
                } else {
                    // Si la sección NO está visible, remover la clase para ocultar la imagen
                    howOfferSection.classList.remove('is-visible');
                }
            });
        }, observerOptions);

        observer.observe(howOfferSection);
    }
});

