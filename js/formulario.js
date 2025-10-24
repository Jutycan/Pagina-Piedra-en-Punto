// ===============================
// FORMULARIO GENERAL - Piedra en Punto
// ===============================
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contact-form");
    const modal = document.getElementById("success-modal");
    const closeBtn = document.querySelector(".close-button-custom");

    if (!form) {
        console.error("❌ No se encontró el formulario con id='contact-form'.");
        return;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("📤 Enviando formulario...");

        grecaptcha.ready(function () {
            grecaptcha.execute("6Ldk0OwrAAAAAPUdgSoQmF1GAkIKls0SME5qy4f2", { action: "submit" })
            .then(function (token) {
                document.getElementById("recaptchaResponse").value = token;

                const formData = new FormData(form);

                fetch("/procesar_formulario.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.json())
                .then((data) => {
                    console.log("📦 Respuesta del servidor:", data);

                    if (data.success) {
                        // ✅ Mostrar el modal correctamente
                        modal.style.display = "flex";
                        form.reset();
                    } else {
                        alert("⚠️ Hubo un problema al enviar el formulario. Intenta nuevamente.");
                    }
                })
                .catch((error) => {
                    console.error("❌ Error en la petición fetch:", error);
                    alert("Error de conexión. Verifica tu red o el archivo PHP.");
                });
            })
            .catch(function (error) {
                console.error("❌ Error al ejecutar reCAPTCHA:", error);
                alert("Error con reCAPTCHA. Recarga la página e inténtalo otra vez.");
            });
        });
    });

    // ✅ Cerrar el modal al hacer clic en la X
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    // ✅ Cerrar el modal si el usuario hace clic fuera del contenido
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});


