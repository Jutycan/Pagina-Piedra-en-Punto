//------------------------------------------------------------------------------
//---------------------------HEADER--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.header nav');
    const submenuItems = document.querySelectorAll('.header nav ul li.submenu > a');

    // Abrir/cerrar menú principal
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });

    // Manejar clics en los enlaces de submenú (solo en móvil)
    submenuItems.forEach(link => {
        link.addEventListener('click', function (e) {
            if (window.innerWidth <= 768) {
                // Evita la navegación para abrir el submenú
                e.preventDefault();

                // Cierra otros submenús abiertos
                document.querySelectorAll('.header nav ul li.submenu').forEach(item => {
                    if (item.querySelector('a') !== this) {
                        item.classList.remove('submenu-open');
                    }
                });

                // Abre o cierra el submenú actual
                this.parentElement.classList.toggle('submenu-open');
            }
        });
    });

    // Asegura que los enlaces dentro del submenú SÍ funcionen
    document.querySelectorAll('.header nav ul li ul a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                navMenu.classList.remove('active');
                document.querySelectorAll('.header nav ul li.submenu').forEach(item => {
                    item.classList.remove('submenu-open');
                });
            }
        });
    });
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
