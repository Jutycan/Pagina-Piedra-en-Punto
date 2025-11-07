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
document.addEventListener("DOMContentLoaded", () => {
    const section = document.querySelector(".service-description-animated-section-2");
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                section.classList.add("in-view");
            } else {
                section.classList.remove("in-view");
            }
        });
    }, { threshold: 0.6 });
    observer.observe(section);
});

// ----------------------------------------------------------------------------- 
//----------------------Estilos de beneficios-----------------------------------
// ----------------------------------------------------------------------------- 


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
