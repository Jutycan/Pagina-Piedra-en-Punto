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

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contact-form");

    if (!form) {
        console.error("❌ No se encontró el formulario con id='contact-form'.");
        return;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        grecaptcha.ready(function () {
            grecaptcha.execute("6Ldk0OwrAAAAAPUdgSoQmF1GAkIKls0SME5qy4f2", { action: "submit" })
            .then(function (token) {
                const formData = new FormData(form);
                formData.append("recaptcha_response", token);

                fetch("/procesar_contacto.php", {
                    method: "POST",
                    body: formData
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        const modal = document.getElementById("contact-success-modal");
                        if (modal) modal.style.display = "flex";
                        form.reset();
                    } else {
                        alert("⚠️ Ocurrió un error: " + (data.error || "Intenta nuevamente."));
                    }
                })
                .catch((error) => {
                    console.error("❌ Error en la solicitud:", error);
                    alert("Error al enviar el formulario.");
                });
            });
        });
    });

    // Cerrar el modal
    const closeBtn = document.querySelector(".close-button-custom");
    if (closeBtn) {
        closeBtn.addEventListener("click", function () {
            document.getElementById("contact-success-modal").style.display = "none";
        });
    }
});

