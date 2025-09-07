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
    const benefitsSection = document.querySelector('.benefits-section');
    const benefitCards = document.querySelectorAll('.benefit-card');

    if (window.innerWidth <= 768) {
        // Lógica para móviles (clic)
        benefitCards.forEach(card => {
            card.addEventListener('click', () => {
                // Si la tarjeta ya está activa, la desactiva
                if (card.classList.contains('is-active')) {
                    card.classList.remove('is-active');
                } else {
                    // Desactiva todas las demás tarjetas
                    benefitCards.forEach(otherCard => {
                        otherCard.classList.remove('is-active');
                    });
                    // Activa la tarjeta actual
                    card.classList.add('is-active');
                }
            });
        });

        // Restablecer los bloques al bajar o salir de la sección
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    benefitCards.forEach(card => card.classList.remove('is-active'));
                }
            });
        });

        observer.observe(benefitsSection);
    }
});

//------------------------------------------------------------------------------
//----------------------------Como se Ofrece--------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const howOfferSection = document.querySelector('.how-offer-section');

    if (!howOfferSection) return;

    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.6 // La sección debe estar al menos 60% visible
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    howOfferSection.classList.add('is-visible');
                } else {
                    howOfferSection.classList.remove('is-visible');
                }
            });
        }, observerOptions);

        observer.observe(howOfferSection);
    }
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