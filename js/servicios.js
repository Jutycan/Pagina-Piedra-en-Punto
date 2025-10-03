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
//---------------------------Título de la sección de Empresas-------------------
//-----------------------------------------------------------------------------
const textElement = document.querySelector('.animated-text');
const text = textElement.textContent;
textElement.textContent = '';

[...text].forEach((letter, i) => {
    const span = document.createElement('span');
    span.textContent = letter === ' ' ? '\u00A0' : letter; // 👈 mantiene espacio
    span.style.animationDelay = `${i * 0.1}s`;
    textElement.appendChild(span);
});

function restartAnimation() {
    const spans = document.querySelectorAll('.animated-text span');
    spans.forEach(span => {
        span.style.animation = 'none';
        void span.offsetWidth; // reinicia animación
        span.style.animation = '';
    });
}

setInterval(restartAnimation, 4000); // reinicia cada 4s

//------------------------------------------------------------------------------
//-----------------------------Servcicio 1------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    }, { threshold: 0.2 }); // Se activa cuando el 20% del section es visible

    const provisionSection = document.querySelector(".provision-content");
    if (provisionSection) {
        observer.observe(provisionSection);
    }
});

//------------------------------------------------------------------------------
//-----------------------------Servcicio 2------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    }, { threshold: 0.2 }); // Se activa cuando el 20% del section es visible

    const organizacionSection = document.querySelector(".organizacion-content");
    if (organizacionSection) {
        observer.observe(organizacionSection);
    }
});

//------------------------------------------------------------------------------
//-----------------------------Servcicio 3------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    }, { threshold: 0.2 }); // Se activa cuando el 20% del section es visible

    const degustacionesSection = document.querySelector(".degustaciones-content");
    if (degustacionesSection) {
        observer.observe(degustacionesSection);
    }
});

//------------------------------------------------------------------------------
//-----------------------------Servcicio 4------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    }, { threshold: 0.2 }); // Se activa cuando el 20% del section es visible

    const estudiosSection = document.querySelector(".estudios-content");
    if (estudiosSection) {
        observer.observe(estudiosSection);
    }
});

//------------------------------------------------------------------------------
//-----------------------------Servcicio 5------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    }, { threshold: 0.2 }); // Se activa cuando el 20% del section es visible

    const capacitacionSection = document.querySelector(".capacitacion-content");
    if (capacitacionSection) {
        observer.observe(capacitacionSection);
    }
});

//------------------------------------------------------------------------------
//-----------------------------Servcicio 6------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    }, { threshold: 0.2 }); // Se activa cuando el 20% del section es visible

    const activacionesSection = document.querySelector(".activaciones-content");
    if (activacionesSection) {
        observer.observe(activacionesSection);
    }
});
