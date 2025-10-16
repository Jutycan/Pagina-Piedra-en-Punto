// ===============================
// FORMULARIO GENERAL - Piedra en Punto
// ===============================

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contact-form");

    // Aseguramos que el formulario exista
    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault(); // Evita recargar la página

        // Desactivar botón mientras se envía
        const submitBtn = form.querySelector("button[type='submit']");
        submitBtn.disabled = true;
        submitBtn.textContent = "Enviando...";

        // Ejecutar reCAPTCHA v3
        grecaptcha.ready(function () {
            grecaptcha.execute("TU_CLAVE_PUBLICA_RECAPTCHA", { action: "submit" }).then(function (token) {
                // Insertar token en el campo oculto
                document.getElementById("recaptchaResponse").value = token;

                // Crear objeto con los datos del formulario
                const formData = new FormData(form);

                // Enviar datos al servidor
                fetch("procesar_formulario.php", {
                    method: "POST",
                    body: formData
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            mostrarMensaje("✅ ¡Formulario enviado correctamente! Gracias por contactarte con Piedra en Punto.", "success");
                            form.reset();
                        } else {
                            mostrarMensaje("⚠️ " + data.message, "error");
                        }
                    })
                    .catch(() => {
                        mostrarMensaje("❌ Ocurrió un error al enviar el formulario. Inténtalo nuevamente.", "error");
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = "Enviar →";
                    });
            });
        });
    });

    // Función para mostrar mensajes bonitos al usuario
    function mostrarMensaje(mensaje, tipo) {
        let box = document.createElement("div");
        box.textContent = mensaje;
        box.className = `alerta-formulario ${tipo}`;
        document.body.appendChild(box);

        // Desaparece automáticamente después de 4 segundos
        setTimeout(() => box.remove(), 4000);
    }
});

