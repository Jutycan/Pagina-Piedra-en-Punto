<?php
// 🚨🚨🚨 LÍNEAS DE DEPURACIÓN (CRÍTICAS PARA VER EL ERROR) 🚨🚨🚨
// Muestra errores en pantalla. ¡NO REMOVER HASTA QUE FUNCIONE!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 🚨🚨🚨 FIN LÍNEAS DE DEPURACIÓN 🚨🚨🚨

// procesar_formulario.php - Maneja la recepción, almacenamiento y envío de correos.

// Aseguramos la carga correcta de la base de datos
require_once __DIR__ . '/db_config.php';

// INCLUSIÓN DE PHPMailer (Archivos DEBEN estar en la misma carpeta)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/PHPMailer.php';
require __DIR__ . '/SMTP.php';
require __DIR__ . '/Exception.php';


// 🚨🚨🚨 CONFIGURACIÓN SMTP USANDO GMAIL CON LA NUEVA APP PASSWORD 🚨🚨🚨
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'cortes270k@gmail.com'); // 🚨 GMAIL DE LA JEFA
define('SMTP_PASS', 'pkgwbezvtiyqiire'); // 🚨 TU NUEVA APP PASSWORD REAL SIN ESPACIOS
define('JEFA_EMAIL', 'cortes270k@gmail.com');

// Función de envío de respuesta
header('Content-Type: application/json');
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Método de solicitud no permitido.");
}

// 3. RECUPERAR DATOS DEL FORMULARIO Y SANITIZARLOS
$nombre = trim($_POST['nombre'] ?? '');
$empresa = trim($_POST['empresa'] ?? ''); 
$email = trim($_POST['email'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');
$origen = trim($_POST['pageUrl'] ?? 'Desconocido');
$opt_in = isset($_POST['recibir-info']) ? 1 : 0; 
$status = "Pendiente"; 
$email_enviado = 0; // Valor inicial

// 4. VALIDACIÓN BÁSICA DE CAMPOS REQUERIDOS
if (empty($nombre) || empty($email) || empty($comentario)) {
    sendResponse(false, "Los campos Nombre, Email y Comentario son obligatorios.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "El formato del email es inválido.");
}

// 5. PREPARAR Y EJECUTAR LA CONSULTA DE INSERCIÓN EN LA BASE DE DATOS
// CONSULTA SQL CORRECTA (8 campos, ya que fecha_registro se llena automáticamente)
$sql = "INSERT INTO leads (nombre, empresa, email, comentario, origen, opt_in, status, email_enviado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql)) {
    // BIND_PARAM CORREGIDO (8 parámetros)
    mysqli_stmt_bind_param($stmt, "sssssisi", $param_nombre, $param_empresa, $param_email, $param_comentario, $param_origen, $param_opt_in, $param_status, $param_email_enviado);

    $param_nombre = $nombre;
    $param_empresa = $empresa;
    $param_email = $email;
    $param_comentario = $comentario;
    $param_origen = $origen;
    $param_opt_in = $opt_in;
    $param_status = $status;
    $param_email_enviado = $email_enviado; // 0

    if (mysqli_stmt_execute($stmt)) {
        // ÉXITO EN LA INSERCIÓN: ENVIAR CORREOS
        $new_lead_id = mysqli_insert_id($link);

        // Envío de Correo al Usuario
        if ($opt_in == 1) {
            if(enviarCorreoUsuario($nombre, $email, $new_lead_id, $link)) {
                // Se actualiza el campo email_enviado dentro de la función
            }
        }
        
        // Notificación de Nuevo Lead a la Jefa (siempre se envía)
        enviarNotificacionJefa($nombre, $email, $comentario, $empresa, $origen);
        
        sendResponse(true, "Formulario enviado con éxito. ¡Gracias!");
    } else {
        error_log("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
        sendResponse(false, "Error interno del servidor (MySQLi Execute).");
    }

    mysqli_stmt_close($stmt);
} else {
    error_log("Error al preparar la consulta: " . mysqli_error($link));
    sendResponse(false, "Error interno del servidor (MySQLi Prepare).");
}

mysqli_close($link);


// ----------------------------------------------------
// FUNCIONES DE CORREO USANDO PHPMailer Y GMAIL 
// ----------------------------------------------------

function configurarMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER; 
    $mail->Password = SMTP_PASS; // ¡El App Password!
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SSL/TLS
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    
    $mail->setFrom(SMTP_USER, 'Piedra en Punto'); 
    
    return $mail;
}

function enviarCorreoUsuario($nombre, $email, $lead_id, $link) {
    try {
        $mail = configurarMailer();
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true);
        $mail->Subject = "¡Bienvenido a Piedra en Punto, $nombre!";
        
        $mail->addReplyTo(JEFA_EMAIL, 'Valeria - Piedra en Punto');

        $redes_sociales = [
            'facebook' => 'URL_REAL_FACEBOOK', 
            'instagram' => 'URL_REAL_INSTAGRAM'
        ];

        // Contenido HTML del Correo de Bienvenida
        $html_content = "
            <html>
            <head>
                <style>/* ... estilos ... */</style>
            </head>
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