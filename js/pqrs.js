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
//-----------------------------FORMULARIO PQRS---------------------------------------
//-----------------------------------------------------------------------------
// pqrs.js
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("pqrs-form");
    const modal = document.getElementById("success-modal-pqr");
    const closeBtn = modal ? modal.querySelector(".close-button-custom") : null;
    const siteKey = "6Ldk0OwrAAAAAPUdgSoQmF1GAkIKls0SME5qy4f2"; // <-- reemplaza por tu clave pública reCAPTCHA v3

    // pageUrl auto
    const pageUrlInput = document.getElementById("pageUrl");
    if (pageUrlInput) pageUrlInput.value = window.location.href;

    if (!form) {
        console.error("Formulario PQRS no encontrado (id='pqrs-form').");
        return;
    }

    function mostrarAlerta(mensaje) {
        alert(mensaje); // puedes mejorar con toasts personalizados
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Validación cliente mínima
        const politicas = document.getElementById("politicas");
        if (!politicas || !politicas.checked) {
            mostrarAlerta("Debes aceptar las políticas de tratamiento de datos.");
            return;
        }

        // Desactivar botón
        const btn = form.querySelector("button[type='submit']");
        const originalText = btn ? btn.innerHTML : null;
        if (btn) { btn.disabled = true; btn.innerText = "Enviando..."; }

        // Ejecutar reCAPTCHA v3
        if (typeof grecaptcha === "undefined") {
            console.error("reCAPTCHA no cargado.");
            mostrarAlerta("Error de seguridad (reCAPTCHA). Recarga la página e inténtalo de nuevo.");
            if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
            return;
        }

        grecaptcha.ready(function () {
            grecaptcha.execute(siteKey, { action: "submit_pqr" }).then(function (token) {
                // set token
                const recaptchaField = document.getElementById("recaptchaResponse");
                if (recaptchaField) recaptchaField.value = token;

                // build FormData
                const formData = new FormData(form);

                fetch("/procesar_pqr.php", {
                    method: "POST",
                    body: formData
                })
                .then(async (res) => {
                    // intentar parsear JSON; si no es JSON, mostrar error
                    let data;
                    try { data = await res.json(); } catch (err) {
                        throw new Error("Respuesta inválida del servidor.");
                    }
                    if (res.ok && data.success) {
                        // mostrar modal
                        if (modal) {
                            modal.style.display = "flex";
                            // autocerrar en 5 seg (opcional)
                            setTimeout(()=> { modal.style.display = "none"; }, 5000);
                        } else {
                            mostrarAlerta("Formulario enviado correctamente.");
                        }
                        form.reset();
                    } else {
                        console.error("Error servidor PQRS:", data.message || data);
                        mostrarAlerta("Ocurrió un error al enviar. Intenta nuevamente.");
                    }
                })
                .catch((err) => {
                    console.error("Fetch error procesar_pqr:", err);
                    mostrarAlerta("Error de conexión. Verifica tu red o el servidor.");
                })
                .finally(() => {
                    if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
                });

            }).catch(function (err) {
                console.error("reCAPTCHA error:", err);
                mostrarAlerta("Error con reCAPTCHA. Recarga la página e inténtalo otra vez.");
                if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
            });
        });

    });

    // cerrar modal en X o clic fuera
    if (closeBtn) {
        closeBtn.addEventListener("click", ()=> modal.style.display = "none");
    }
    window.addEventListener("click", (ev)=> {
        if (ev.target === modal) modal.style.display = "none";
    });
});
