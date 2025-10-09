<?php
// 🚨🚨🚨 CONFIGURACIÓN INICIAL Y DEPURACIÓN 🚨🚨🚨
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. CONEXIÓN A LA BASE DE DATOS Y DEFINICIÓN DE CONSTANTES
// **REVISA QUE ESTAS CREDENCIALES SEAN TUS REALES DE HOSTINGER**
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u894610526_P_Formulario1'); 
define('DB_PASSWORD', 'Ejercicios$2021$'); 
define('DB_NAME', 'u894610526_Formulario_1_P'); 

// 🚨🚨🚨 URL CRÍTICA PARA EL DASHBOARD DE LA JEFA 🚨🚨🚨
define('DASHBOARD_URL', 'https://www.piedraenpunto.com/dashboard/gestion_leads.php');


// Intento de conexión
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($link === false){
    die("ERROR: No se pudo conectar a la base de datos. " . mysqli_connect_error());
}

// INCLUSIÓN DE PHPMailer (Archivos DEBEN estar en la misma carpeta)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

// CONFIGURACIÓN SMTP GMAIL
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'cortes270k@gmail.com'); 
define('SMTP_PASS', 'pkgwbezvtiyqiire'); 
define('JEFA_EMAIL', 'cortes270k@gmail.com');

// Funciones de Respuesta y Validación (sin cambios)
header('Content-Type: application/json');
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Método de solicitud no permitido.");
}

// 3. RECUPERAR Y SANITIZAR DATOS
$nombre = trim($_POST['nombre'] ?? '');
$empresa = trim($_POST['empresa'] ?? ''); 
$email = trim($_POST['email'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');
// Se valida que acepte las políticas de datos
$politica_datos = isset($_POST['politica-datos']) ? true : false; 
$opt_in = isset($_POST['recibir-info']) ? 1 : 0; 

// Configuramos los valores por defecto para la DB
$status = "Pendiente"; // NUEVO: Estado por defecto
$email_enviado = 0; 

// VALIDACIÓN
if (empty($nombre) || empty($email) || empty($comentario) || !$politica_datos) {
    sendResponse(false, "Los campos marcados con * son obligatorios.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "El formato del email es inválido.");
}

// 5. PREPARAR Y EJECUTAR LA CONSULTA DE INSERCIÓN (7 campos)
$sql = "INSERT INTO leads (nombre, empresa, email, comentario, opt_in, status, email_enviado) VALUES (?, ?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql)) {
    // 7 parámetros (nombre, empresa, email, comentario, opt_in, status, email_enviado)
    mysqli_stmt_bind_param($stmt, "ssssisi", $param_nombre, $param_empresa, $param_email, $param_comentario, $param_opt_in, $param_status, $param_email_enviado);

    $param_nombre = $nombre;
    $param_empresa = $empresa;
    $param_email = $email;
    $param_comentario = $comentario;
    $param_opt_in = $opt_in;
    $param_status = $status;
    $param_email_enviado = $email_enviado; 

    if (mysqli_stmt_execute($stmt)) {
        // ÉXITO: ENVIAR CORREOS
        $new_lead_id = mysqli_insert_id($link);

        // Notificación de Nuevo Lead a la Jefa (siempre se envía)
        enviarNotificacionJefa($nombre, $email, $comentario, $empresa, $new_lead_id);

        // Envío de Correo al Usuario (si aceptó recibir información)
        if ($opt_in == 1) {
            enviarCorreoUsuario($nombre, $email, $comentario, $new_lead_id, $link); 
        }
        
        sendResponse(true, "Formulario enviado con éxito. ¡Gracias!");
    } else {
        error_log("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
        sendResponse(false, "Error interno del servidor al guardar los datos.");
    }

    mysqli_stmt_close($stmt);
} else {
    error_log("Error al preparar la consulta: " . mysqli_error($link));
    sendResponse(false, "Error interno del servidor (MySQLi Prepare).");
}

mysqli_close($link);


// ----------------------------------------------------
// FUNCIONES DE CORREO PROFESIONALES Y CON ENLACE
// ----------------------------------------------------

function configurarMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER; 
    $mail->Password = SMTP_PASS; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom(SMTP_USER, 'Piedra en Punto - Gestión'); 
    return $mail;
}

function enviarCorreoUsuario($nombre, $email, $comentario, $lead_id, $link) {
    try {
        $mail = configurarMailer();
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true); 
        $mail->Subject = "✅ Solicitud Recibida | Bienvenido a Piedra en Punto, $nombre";
        $mail->addReplyTo(JEFA_EMAIL, 'Valeria - Piedra en Punto');

        // Contenido HTML PROFESIONAL para el CLIENTE
        $html_content = "
            <html>
            <head><style>body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; } .container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; } .header { background-color: #33614a; color: white; padding: 20px; text-align: center; } .content { padding: 30px; } .footer { background-color: #f4f4f4; padding: 15px; font-size: 12px; color: #777; text-align: center; }</style></head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2 style='margin: 0;'>¡Tu proyecto es nuestra prioridad!</h2>
                    </div>
                    <div class='content'>
                        <h3>Hola $nombre,</h3>
                        <p>Gracias por contactar a <strong>Piedra en Punto</strong>. Hemos recibido tu solicitud con la siguiente información:</p>
                        <ul style='list-style: none; padding: 0; background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>
                            <li><strong>Tu Comentario:</strong> <em>\"$comentario\"</em></li>
                            <li><strong>Estado de tu Solicitud:</strong> PENDIENTE de gestión.</li>
                        </ul>
                        <p>Nuestro equipo revisará tu mensaje y se comunicará contigo personalmente en las próximas horas para ofrecerte la mejor solución.</p>
                        <p>Mientras tanto, te invitamos a explorar nuestra web o seguirnos en redes sociales.</p>
                        <p>Atentamente,<br>El equipo de Piedra en Punto.</p>
                    </div>
                    <div class='footer'>
                        Este es un mensaje automático. Por favor, no respondas a este correo.
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->Body = $html_content;
        $mail->send();

        // ACTUALIZAR DB: Marcamos email_enviado = 1
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

function enviarNotificacionJefa($nombre, $email_cliente, $comentario, $empresa, $lead_id) {
    try {
        $mail = configurarMailer();
        $mail->addAddress(JEFA_EMAIL);
        $mail->isHTML(true); // Usamos HTML para el botón del dashboard
        $mail->Subject = "🔔 NUEVO LEAD WEB - #$lead_id: $nombre ($empresa)";

        $body = "
            <html>
            <head><style>body { font-family: Arial, sans-serif; line-height: 1.5; }</style></head>
            <body>
                <h2 style='color: #F06292;'>¡Nuevo Lead Registrado!</h2>
                <p>Se ha recibido una nueva solicitud de contacto. El estado inicial es **PENDIENTE**.</p>
                <ul style='list-style: none; padding: 0;'>
                    <li><strong>ID:</strong> #$lead_id</li>
                    <li><strong>Nombre:</strong> $nombre</li>
                    <li><strong>Empresa:</strong> " . ($empresa ?: 'N/A') . "</li>
                    <li><strong>Email:</strong> $email_cliente</li>
                    <li><strong>Comentario:</strong> <em>$comentario</em></li>
                </ul>
                <hr>
                <p style='font-weight: bold;'>Por favor, ingresa al panel de gestión para actualizar el estado del cliente.</p>
                <a href='" . DASHBOARD_URL . "' style='display: inline-block; padding: 12px 25px; background-color: #33614a; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px;'>
                    Ir al Dashboard de Leads
                </a>
            </body>
            </html>
        ";

        $mail->Body = $body;
        $mail->send();
    } catch (Exception $e) {
        error_log("Notificacion a Jefa falló: {$mail->ErrorInfo}");
    }
}
?>