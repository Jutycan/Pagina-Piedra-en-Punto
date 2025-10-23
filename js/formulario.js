// ===============================
// FORMULARIO GENERAL - Piedra en Punto
// ===============================
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contact-form");

    if (!form) {
        console.error("❌ No se encontró el formulario con id='contact-form'.");
        return;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        console.log("📤 Enviando formulario...");
        grecaptcha.ready(function () {
            // ✅ Tu clave pública real de reCAPTCHA v3
            grecaptcha
            .execute("6Ldk0OwrAAAAAPUdgSoQmF1GAkIKls0SME5qy4f2", { action: "submit" })
            .then(function (token) {
                console.log("✅ reCAPTCHA ejecutado correctamente. Token recibido.");

                // Asignar el token al campo oculto
                document.getElementById("recaptchaResponse").value = token;

                // Enviar datos del formulario
                const formData = new FormData(form);

                fetch("/procesar_formulario.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.json())
                .then((data) => {
                    console.log("📦 Respuesta del servidor:", data);

                    if (data.success) {
                        // Mostrar modal o mensaje de éxito
                        const modal = document.getElementById("modal-exito");
                        if (modal) {
                            modal.style.display = "block";
                        } else {
                            alert("✅ Formulario enviado correctamente.");
                        }
                        form.reset();
                    } else {
                        console.error("⚠️ Error del servidor:", data.error || "Desconocido");
                        alert("Hubo un problema al enviar el formulario. Intenta nuevamente.");
                    }
                })
                .catch((error) => {
                    console.error("❌ Error en la petición fetch:", error);
                    alert("Error de conexión. Verifica tu red o el archivo PHP.");
                });
            })
            .catch(function (error) {
                console.error("❌ Error al ejecutar reCAPTCHA:", error);
                alert("Error con reCAPTCHA. Por favor, recarga la página e inténtalo otra vez.");
            });
        });
    });
});


