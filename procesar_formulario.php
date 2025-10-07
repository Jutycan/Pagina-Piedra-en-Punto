<?php
// procesar_formulario.php - Maneja la recepción, almacenamiento y envío de correos.

// 1. INCLUIR LA CONFIGURACIÓN DE LA BASE DE DATOS
// CRÍTICO: db_config.php debe estar en el mismo directorio y con las credenciales correctas
require_once 'db_config.php';

// Establecer el tipo de contenido a JSON para la respuesta
header('Content-Type: application/json');

// Función para enviar una respuesta de error o éxito
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// 2. VERIFICAR QUE LA SOLICITUD SEA POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Método de solicitud no permitido.");
}

// 3. RECUPERAR DATOS DEL FORMULARIO Y SANITIZARLOS
$nombre = trim($_POST['nombre'] ?? '');
$empresa = trim($_POST['empresa'] ?? null); // Opcional, puede ser null
$email = trim($_POST['email'] ?? '');
$comentario = trim($_POST['comentario'] ?? null); // Opcional, puede ser null
$origen = trim($_POST['pageUrl'] ?? 'Desconocido');
// El campo opt-in solo se envía si está marcado.
$opt_in = $_POST['recibir-info'] ? 1 : 0; 
$status = "Pendiente"; // Valor predeterminado

// 4. VALIDACIÓN BÁSICA DE CAMPOS REQUERIDOS
if (empty($nombre) || empty($email)) {
    sendResponse(false, "El nombre y el email son campos obligatorios.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "El formato del email es inválido.");
}

// 5. PREPARAR Y EJECUTAR LA CONSULTA DE INSERCIÓN EN LA BASE DE DATOS
// La consulta usa marcadores de posición (?) para evitar inyecciones SQL (seguridad).
$sql = "INSERT INTO leads (nombre, empresa, email, comentario, origen, opt_in, status) VALUES (?, ?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql)) {
    // Vincular variables a la sentencia preparada (s=string, i=integer)
    mysqli_stmt_bind_param($stmt, "sssssis", $param_nombre, $param_empresa, $param_email, $param_comentario, $param_origen, $param_opt_in, $param_status);

    // Asignar parámetros
    $param_nombre = $nombre;
    $param_empresa = $empresa;
    $param_email = $email;
    $param_comentario = $comentario;
    $param_origen = $origen;
    $param_opt_in = $opt_in;
    $param_status = $status;

    // Ejecutar la sentencia
    if (mysqli_stmt_execute($stmt)) {
        // 6. ÉXITO EN LA INSERCIÓN: ENVIAR CORREOS
        enviarCorreos($nombre, $email, $comentario, $empresa, $origen);
        sendResponse(true, "Formulario enviado con éxito. ¡Gracias!");
    } else {
        // Error de ejecución de MySQL
        sendResponse(false, "Error al guardar el lead en la base de datos.");
    }

    // Cerrar sentencia
    mysqli_stmt_close($stmt);
} else {
    // Error de preparación de MySQL
    sendResponse(false, "Error interno del servidor (MySQLi Prepare).");
}

// 7. CERRAR CONEXIÓN
mysqli_close($link);


// ----------------------------------------------------
// 8. FUNCIONES DE CORREO (CRÍTICAS)
// ----------------------------------------------------

/**
 * Función principal que maneja el envío de correos.
 */
function enviarCorreos($nombre, $email, $comentario, $empresa, $origen) {
    // Intenta enviar el correo al usuario
    enviarCorreoUsuario($nombre, $email);
    
    // Intenta enviar la notificación a la jefa
    enviarNotificacionJefa($nombre, $email, $comentario, $empresa, $origen);
}


/**
 * Envía un correo de bienvenida al usuario.
 */
function enviarCorreoUsuario($nombre, $email) {
    // CORRECCIÓN 1: Usamos un remitente local del dominio para pasar el filtro SPF
    $subject = "¡Bienvenido a Piedra en Punto, $nombre!";
    $from = "no-reply@piedraenpunto.com"; // 🚨 REMITENTE LOCAL DEL DOMINIO
    $reply_to_email = "vdelapiedra11@gmail.com"; // El correo real de la jefa para respuestas

    $redes_sociales = [
        'facebook' => 'URL_REAL_FACEBOOK',  // 🚨 REEMPLAZAR
        'instagram' => 'URL_REAL_INSTAGRAM' // 🚨 REEMPLAZAR
    ];

    // Contenido HTML básico del correo al cliente
    $html_content = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
                .footer { margin-top: 20px; font-size: 0.8em; color: #777; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Hola $nombre,</h2>
                <p>¡Gracias por contactarnos! Hemos recibido tu mensaje y pronto nos pondremos en contacto contigo.</p>
                <p>Mientras tanto, puedes seguirnos en nuestras redes sociales:</p>
                <p>
                    <a href='{$redes_sociales['facebook']}' style='color: #4267B2;'>Facebook</a> | 
                    <a href='{$redes_sociales['instagram']}' style='color: #C13584;'>Instagram</a>
                </p>
                <p>Atentamente,<br>El equipo de Piedra en Punto.</p>
            </div>
            <div class='footer'>
                <p>Este es un correo automático, por favor no lo respondas.</p>
            </div>
        </body>
        </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    // El cliente responderá al Gmail de la jefa
    $headers .= "Reply-To: $reply_to_email" . "\r\n"; 
    $headers .= "From: $from" . "\r\n"; // Usamos el remitente local
    
    // Suprimimos errores con @mail en caso de fallo de envío.
    @mail($email, $subject, $html_content, $headers);
}

/**
 * Envía una notificación por correo a la jefa.
 */
function enviarNotificacionJefa($nombre, $email, $comentario, $empresa, $origen) {
    // DESTINATARIO: El Gmail de la jefa.
    $jefa_email = "vdelapiedra11@gmail.com"; 
    $subject = "🚨 NUEVO LEAD WEB: $nombre";
    // CORRECCIÓN 2: Usamos un remitente local del dominio
    $from = "notificaciones@piedraenpunto.com"; // 🚨 REMITENTE LOCAL DEL DOMINIO

    $body = "Se ha recibido un nuevo lead a través del formulario de contacto:\n\n";
    $body .= "Nombre: $nombre\n";
    $body .= "Empresa: " . ($empresa ?: 'N/A') . "\n";
    $body .= "Email: $email\n";
    $body .= "Página de Origen: $origen\n";
    $body .= "Comentarios:\n$comentario\n\n";
    $body .= "Revisa la base de datos en phpMyAdmin para ver el registro completo.";

    $headers = "From: $from" . "\r\n"; // Usamos el remitente local

    @mail($jefa_email, $subject, $body, $headers);
}
?>