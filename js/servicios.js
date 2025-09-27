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

    const desarrolloSection = document.querySelector(".desarrollo-content");
    if (desarrolloSection) {
        observer.observe(desarrolloSection);
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


//--------------------------------------------------------------------------------
//------------------------FORMULARIO DE CONTACTO---------------------------------
//-------------------------------------------------------------------------------
const form = document.getElementById('contact-form');
const formContent = document.querySelector('.contact-form-content');

form.addEventListener('submit', function(e) {
    e.preventDefault(); // Evita que la página se recargue

    // Muestra un mensaje de "Enviando..."
    const button = form.querySelector('.contact-form-button');
    button.innerText = 'Enviando...';
    button.disabled = true;

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        throw new Error('El envío falló. Por favor, inténtalo de nuevo.');
    })
    .then(data => {
        if (data.result === 'success') {
            // Mensaje de éxito
            const successMessage = document.createElement('div');
            successMessage.className = 'success-message';
            successMessage.innerHTML = '¡Mensaje enviado con éxito!';

            // Oculta el formulario y muestra el mensaje
            formContent.innerHTML = '';
            formContent.appendChild(successMessage)

            // Muestra un emoji profesional de éxito
            console.log("¡Envío exitoso! 🎉");
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        // Muestra un mensaje de error
        alert(error.message);
        button.innerText = 'Enviar →';
        button.disabled = false;
    });
});