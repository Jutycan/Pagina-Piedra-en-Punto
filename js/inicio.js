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
//---------------------------¿Quiénes somos?--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const aboutUsSection = document.querySelector('.about-us-section');
    const aboutUsImage = document.querySelector('.about-us-image');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Si la sección es visible, muestra la imagen
                aboutUsImage.classList.add('is-visible');
            } else {
                // Si la sección NO es visible, oculta la imagen
                aboutUsImage.classList.remove('is-visible');
            }
        });
    }, {
        threshold: 0.2 // Se activa cuando el 20% de la sección es visible
    });

    if (aboutUsSection) {
        observer.observe(aboutUsSection);
    }
});

//---------------------------------------------------------------------------------------
//-----------Formulario Contacto---------------------------------------------------------
//---------------------------------------------------------------------------------------
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
