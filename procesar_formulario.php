<?php
// Reportar errores en pantalla (SÓLO PARA DEBUG, REMOVER DESPUÉS)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// procesar_formulario.php - Maneja la recepción, almacenamiento y envío de correos.
require_once 'db_config.php';

// 🚨 INCLUSIÓN DE PHPMailer 🚨
// Asegúrate de que los archivos PHPMailer.php, SMTP.php y Exception.php están en la raíz.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

// 🚨🚨🚨 CONFIGURACIÓN SMTP DE HOSTINGER 🚨🚨🚨
// Reemplaza estos valores con la configuración de tu cuenta de correo de Hostinger
define('SMTP_HOST', 'smtp.gmail.com'); // O el que te dé Hostinger
define('SMTP_USER', 'cortes270k@gmail.com'); // 🚨 TU CORREO CREADO
define('SMTP_PASS', 'ejleozriqeadawjw'); // 🚨 TU CONTRASEÑA DE CORREO
define('JEFA_EMAIL', 'cortes270k@gmail.com'); // Correo personal de la jefa

header('Content-Type: application/json');

function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Método de solicitud no permitido.");
}

// 3. RECUPERAR DATOS DEL FORMULARIO Y SANITIZARLOS
// Los campos son requeridos, así que no deberían ser null
$nombre = trim($_POST['nombre'] ?? '');
$empresa = trim($_POST['empresa'] ?? ''); 
$email = trim($_POST['email'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');
$origen = trim($_POST['pageUrl'] ?? 'Desconocido');
// El opt-in se comprueba para el correo de bienvenida
$opt_in = isset($_POST['recibir-info']) ? 1 : 0; 
$status = "Pendiente"; 
$email_enviado = 0; // Se actualiza a 1 si el correo de bienvenida se envía.

// 4. VALIDACIÓN BÁSICA DE CAMPOS REQUERIDOS
if (empty($nombre) || empty($email) || empty($empresa) || empty($comentario)) {
    sendResponse(false, "Todos los campos con * son obligatorios.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "El formato del email es inválido.");
}

// 5. PREPARAR Y EJECUTAR LA CONSULTA DE INSERCIÓN EN LA BASE DE DATOS
// ¡Se agregan las 9 columnas!
$sql = "INSERT INTO leads (nombre, empresa, email, comentario, origen, opt_in, status, email_enviado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql)) {
    // 8 parámetros: 7 s (strings) y 1 i (integer)
    mysqli_stmt_bind_param($stmt, "sssssisi", $param_nombre, $param_empresa, $param_email, $param_comentario, $param_origen, $param_opt_in, $param_status, $param_email_enviado);

    $param_nombre = $nombre;
    $param_empresa = $empresa;
    $param_email = $email;
    $param_comentario = $comentario;
    $param_origen = $origen;
    $param_opt_in = $opt_in;
    $param_status = $status;
    $param_email_enviado = $email_enviado; // Valor inicial 0

    if (mysqli_stmt_execute($stmt)) {
        // 6. ÉXITO EN LA INSERCIÓN: ENVIAR CORREOS
        
        // 🚨 Obtener el ID del lead recién insertado para actualizar el status
        $new_lead_id = mysqli_insert_id($link);

        // Intenta enviar el correo de bienvenida SÓLO si el usuario aceptó
        if ($opt_in == 1) {
            if(enviarCorreoUsuario($nombre, $email, $new_lead_id, $link)) {
                // Si se envía con éxito, email_enviado pasa a 1 en la DB
                $email_enviado = 1;
            }
        }
        
        // Envía la notificación de lead a la jefa (siempre se envía)
        enviarNotificacionJefa($nombre, $email, $comentario, $empresa, $origen);
        
        sendResponse(true, "Formulario enviado con éxito. ¡Gracias!");
    } else {
        sendResponse(false, "Error al guardar el lead en la base de datos.");
    }

    mysqli_stmt_close($stmt);
} else {
    sendResponse(false, "Error interno del servidor (MySQLi Prepare).");
}

mysqli_close($link);


// ----------------------------------------------------
// 8. FUNCIONES DE CORREO USANDO PHPMailer (CRÍTICAS)
// ----------------------------------------------------

function configurarMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SSL
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    
    // El remitente será el correo autenticado de Hostinger
    $mail->setFrom(SMTP_USER, 'Piedra en Punto Notificaciones'); 
    
    return $mail;
}

/**
 * Envía un correo de bienvenida al usuario y actualiza el estado en la DB.
 */
function enviarCorreoUsuario($nombre, $email, $lead_id, $link) {
    try {
        $mail = configurarMailer();
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true);
        $mail->Subject = "¡Bienvenido a Piedra en Punto, $nombre!";
        
        // El cliente responderá al correo real de la jefa
        $mail->addReplyTo(JEFA_EMAIL, 'Valeria - Piedra en Punto');

        $redes_sociales = [
            'facebook' => 'URL_REAL_FACEBOOK', 
            'instagram' => 'URL_REAL_INSTAGRAM'
        ];

        // Contenido HTML Profesional
        $html_content = "
            <html>
            <head><style>/* ... CSS ... */</style></head>
            <body>
                <div class='container'>
                    <h2>Hola $nombre, ¡Bienvenido!</h2>
                    <p>Tu solicitud ha sido recibida con éxito. Queremos darte las gracias por unirte a la familia Piedra en Punto.</p>
                    <p>En breve, un miembro de nuestro equipo se pondrá en contacto contigo para resolver tus dudas o iniciar tu proyecto.</p>
                    
                    <p style='font-weight: bold;'>Mientras esperas, te invitamos a:</p>
                    <ul>
                        <li><a href='https://www.piedraenpunto.com/'>Explorar nuestra página web</a> para ver nuestros últimos trabajos.</li>
                        <li>Seguirnos en redes sociales: 
                            <a href='{$redes_sociales['instagram']}'>Instagram</a> | 
                            <a href='{$redes_sociales['facebook']}'>Facebook</a>
                        </li>
                    </ul>
                    <p>Atentamente,<br>El equipo de Piedra en Punto.</p>
                </div>
            </body>
            </html>
        ";
        
        $mail->Body = $html_content;
        $mail->send();

        // 🚨 ACTUALIZAR DB: Si el envío fue exitoso
        $sql_update = "UPDATE leads SET email_enviado = 1 WHERE id = ?";
        if ($stmt_update = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "i", $lead_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        }
        return true;
    } catch (Exception $e) {
        error_log("Correo de usuario falló: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Envía una notificación por correo a la jefa.
 */
function enviarNotificacionJefa($nombre, $email_cliente, $comentario, $empresa, $origen) {
    try {
        $mail = configurarMailer();
        $mail->addAddress(JEFA_EMAIL);
        $mail->isHTML(false); 
        $mail->Subject = "🚨 NUEVO LEAD WEB: $nombre";

        $body = "Se ha recibido un nuevo lead a través del formulario de contacto:\n\n";
        $body .= "Nombre: $nombre\n";
        $body .= "Empresa: " . ($empresa ?: 'N/A') . "\n";
        $body .= "Email: $email_cliente\n";
        $body .= "Página de Origen: $origen\n";
        $body .= "Comentarios:\n$comentario\n\n";
        $body .= "Por favor, ingresa al Panel de Leads para su gestión.";

        $mail->Body = $body;
        $mail->send();
    } catch (Exception $e) {
        error_log("Notificacion a Jefa falló: {$mail->ErrorInfo}");
    }
}
?>