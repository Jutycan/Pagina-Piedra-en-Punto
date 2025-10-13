<?php
// --------------------------------------------------------------------------
// Archivo: procesar_pqr.php
// Función: Gestiona la recepción de un formulario PQRS (Petición, Queja, Reclamo, Sugerencia).
// Tareas: 1. Guarda los datos en la tabla 'pqrs'.
//         2. Envía notificación por correo a la Jefa (vía PHPMailer/SMTP) CON ENLACE AL DASHBOARD.
//         3. Envía correo de confirmación al usuario (vía PHPMailer/SMTP).
// --------------------------------------------------------------------------

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Incluir los archivos de PHPMailer (DEBEN ESTAR EN EL MISMO DIRECTORIO)
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

// 🚨🚨🚨 CONFIGURACIÓN - ¡MODIFICA ESTO CON TUS DATOS REALES! 🚨🚨🚨
// --- Configuración de la Base de Datos ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u894610526_P_Formulario1'); 
define('DB_PASSWORD', 'Ejercicios$2021$'); // Contraseña de la base de datos
define('DB_NAME', 'u894610526_Formulario_1_P'); 
// --- Configuración de Correo (SMTP) ---
define('JEFA_EMAIL', 'cortes270k@gmail.com'); // Correo de la Jefa (DESTINO)
define('SENDER_EMAIL', 'no-responder@piedraenpunto.com'); // Correo profesional para ENVIAR (DEBE ser un correo real de tu dominio)
define('SMTP_USERNAME', 'no-responder@piedraenpunto.com'); // Mismo que SENDER_EMAIL
define('SMTP_PASSWORD', 'pkgwbezvtiyqiire'); // ⚠️ ¡TU CONTRASEÑA DE APLICACIÓN REAL!
define('SMTP_HOST', 'smtp.gmail.com'); // O el servidor SMTP que uses (ej: smtp.gmail.com si usas Google Workspace)
define('SMTP_PORT', 587); // Puerto estándar TSL
// --- Otros Parámetros ---
// ⚠️ ESTA ES LA URL COMPLETA DEL DASHBOARD EN LA CARPETA 'dashboard2'
define('DASHBOARD_URL', 'https://piedraenpunto.com/dashboard2/gestion_pqr.php'); 

header('Content-Type: application/json');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($link === false){
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// 1. Recolección, Validación y Sanitización de Datos
$fields = ['clasificacion', 'nombres', 'identificacion', 'fecha', 'motivo', 'email', 'telefono', 'mensaje'];
$data = [];
$is_valid = true;

foreach ($fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Falta el campo obligatorio: ' . $field]);
        $is_valid = false;
        mysqli_close($link);
        exit;
    }
    $data[$field] = mysqli_real_escape_string($link, $_POST[$field]);
}

if (empty($_POST['politicas'])) {
    echo json_encode(['success' => false, 'message' => 'Debe aceptar las políticas de tratamiento de datos.']);
    mysqli_close($link);
    exit;
}

// 2. Inserción en la Base de Datos (Tabla 'pqrs')
$sql = "INSERT INTO pqrs (clasificacion, nombres, identificacion, fecha, motivo, email, telefono, mensaje, status, email_enviado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 0)";
$stmt = mysqli_prepare($link, $sql);

mysqli_stmt_bind_param($stmt, "ssssssss", 
    $data['clasificacion'], 
    $data['nombres'], 
    $data['identificacion'], 
    $data['fecha'], 
    $data['motivo'], 
    $data['email'], 
    $data['telefono'], 
    $data['mensaje']
);

$db_success = mysqli_stmt_execute($stmt);
$last_id = mysqli_insert_id($link); 
mysqli_stmt_close($stmt);

if (!$db_success) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la solicitud en la base de datos.']);
    mysqli_close($link);
    exit;
}

$email_sent = false;
try {
    // Inicializar PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    
    // Configuración del Remitente
    $mail->setFrom(SENDER_EMAIL, 'Piedra en Punto - PQRS');
    
    // --- 3. Envío de CORREO a la JEFA (Notificación Interna) ---
    $mail->clearAllRecipients(); // Limpiar destinatarios anteriores
    $mail->addAddress(JEFA_EMAIL);
    $mail->addReplyTo($data['email'], $data['nombres']); // Para responder directamente al usuario

    $mail->isHTML(true);
    $mail->Subject = "🚨 NUEVA SOLICITUD PQRS #{$last_id} ({$data['clasificacion']})";
    
    // Usamos la constante definida arriba
    $dashboard_url = DASHBOARD_URL; 
    
    $jefa_message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 20px auto; padding: 25px; border-radius: 10px; background-color: #f0f7f5; border-left: 5px solid #EC0868; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                h2 { color: #33614a; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
                .data-item { padding: 8px 0; border-bottom: 1px dashed #eee; }
                .data-item strong { color: #EC0868; display: inline-block; width: 150px; }
                /* Estilo del Botón Actualizado */
                .btn { 
                    display: inline-block; 
                    padding: 12px 25px; 
                    margin-top: 20px; 
                    background-color: #33614a; 
                    color: white !important; 
                    text-decoration: none; 
                    border-radius: 50px; 
                    font-weight: bold; 
                    transition: background-color 0.3s; 
                    text-align: center;
                }
                .btn:hover {
                    background-color: #2e5743;
                }
                .footer { margin-top: 30px; font-size: 0.8em; color: #777; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Notificación de Nueva Solicitud PQRS</h2>
                <p>Estimada Jefa,</p>
                <p>Se ha registrado una nueva solicitud en el sistema y ha sido marcada por defecto como <strong>PENDIENTE</strong>. Por favor, revísala y actualiza su estado.</p>
                
                <div class='data-item'><strong>ID de Solicitud:</strong> #{$last_id}</div>
                <div class='data-item'><strong>Clasificación:</strong> {$data['clasificacion']}</div>
                <div class='data-item'><strong>Nombre:</strong> {$data['nombres']}</div>
                <div class='data-item'><strong>Email:</strong> {$data['email']}</div>
                <div class='data-item'><strong>Motivo:</strong> {$data['motivo']}</div>
                <div class='data-item'><strong>Mensaje Detallado:</strong> {$data['mensaje']}</div>
                
                <!-- 💥 ENLACE AL DASHBOARD 💥 -->
                <a href='{$dashboard_url}' class='btn' target='_blank'>GESTIONAR SOLICITUD AHORA</a>
                
                <div class='footer'>
                    Sistema de Gestión Automático.
                </div>
            </div>
        </body>
        </html>
    ";

    $mail->Body = $jefa_message;
    $mail->send();
    
    // --- 4. Envío de CORREO al USUARIO (Confirmación) ---
    $mail->clearAllRecipients();
    $mail->addAddress($data['email'], $data['nombres']);

    $mail->Subject = "Confirmación: Tu Solicitud PQRS #{$last_id} ha sido Recibida";

    $user_message = "
        <html>
        <head>
            <style>
                body { font-family: 'Lato', sans-serif; line-height: 1.6; color: #444; }
                .container { max-width: 600px; margin: 20px auto; padding: 30px; border-radius: 10px; background-color: #ffffff; border: 1px solid #ddd; }
                .header { background-color: #EC0868; color: white; padding: 15px; border-radius: 8px 8px 0 0; text-align: center; font-size: 1.2rem; font-weight: bold; }
                .content { padding: 20px 0; }
                .highlight { color: #EC0868; font-weight: bold; }
                .footer { margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px; font-size: 0.9em; color: #777; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    Confirmación de Recepción - Piedra en Punto
                </div>
                <div class='content'>
                    <p>Estimado/a {$data['nombres']},</p>
                    <p>Agradecemos sinceramente que te hayas comunicado con nosotros. Confirmamos la recepción de tu solicitud, clasificada como <strong>{$data['clasificacion']}</strong>, bajo el número de referencia <span class='highlight'>#{$last_id}</span>.</p>
                    
                    <p>Tu petición ha sido asignada a un gestor especializado y te garantizamos un manejo <span class='highlight'>profesional y confidencial</span>. Te daremos una respuesta formal lo antes posible, cumpliendo con nuestros estándares de calidad.</p>
                    
                    <p>Gracias por tu confianza y paciencia.</p>
                    <p>Atentamente,<br>El equipo de Gestión de PQRS.</p>
                </div>
                
                <div class='footer'>
                    Este es un correo automático. Por favor, evita responder a este mensaje.
                </div>
            </div>
        </body>
        </html>
    ";
    
    $mail->Body = $user_message;
    $mail->send();
    $email_sent = true;

    // 5. Actualizar estado de envío en la DB si todo fue exitoso
    if ($email_sent) {
        $update_stmt = mysqli_prepare($link, "UPDATE pqrs SET email_enviado = 1 WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "i", $last_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Solicitud PQRS guardada y correos enviados.',
        'id' => $last_id
    ]);

} catch (Exception $e) {
    // Error al enviar correo, pero los datos se guardaron en la DB
    error_log("Error al enviar correos PHPMailer para PQRS #{$last_id}: " . $e->getMessage());
    echo json_encode([
        'success' => true, 
        'message' => 'Solicitud PQRS guardada, pero hubo un error al enviar el email de notificación. (ID: ' . $last_id . ')',
        'id' => $last_id
    ]);
}

mysqli_close($link);
?>