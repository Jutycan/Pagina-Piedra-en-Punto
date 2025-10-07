<?php
// 🚨🚨🚨 LÍNEAS DE DEPURACIÓN (CRÍTICAS PARA VER EL ERROR) 🚨🚨🚨
// Muestra errores en pantalla. ¡NO REMOVER HASTA QUE FUNCIONE!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// 🚨🚨🚨 FIN LÍNEAS DE DEPURACIÓN 🚨🚨🚨

// 1. CONEXIÓN A LA BASE DE DATOS Y DEFINICIÓN DE CONSTANTES
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u894610526_P_Formulario1'); // TU USUARIO REAL DE HOSTINGER
define('DB_PASSWORD', 'Ejercicios$2021$'); // TU CONTRASEÑA REAL
define('DB_NAME', 'u894610526_Formulario_1_P'); // TU NOMBRE DE BD REAL

// Intento de conexión
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión. Si falla, se detiene y muestra el error.
if($link === false){
    die("ERROR: No se pudo conectar a la base de datos. " . mysqli_connect_error());
}

// INCLUSIÓN DE PHPMailer (Archivos DEBEN estar en la misma carpeta)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';


// 🚨 CONFIGURACIÓN SMTP USANDO GMAIL CON LA NUEVA APP PASSWORD 🚨
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'cortes270k@gmail.com'); // GMAIL DE LA JEFA
define('SMTP_PASS', 'pkgwbezvtiyqiire'); // NUEVA APP PASSWORD SIN ESPACIOS
define('JEFA_EMAIL', 'cortes270k@gmail.com');

// Función de envío de respuesta JSON
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
$origen = trim($_POST['pageUrl'] ?? 'Desconocido');
$opt_in = isset($_POST['recibir-info']) ? 1 : 0; 
$status = "Pendiente"; 
$email_enviado = 0; 

// 4. VALIDACIÓN BÁSICA DE CAMPOS REQUERIDOS
if (empty($nombre) || empty($email) || empty($comentario)) {
    sendResponse(false, "Los campos Nombre, Email y Comentario son obligatorios.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "El formato del email es inválido.");
}

// 5. PREPARAR Y EJECUTAR LA CONSULTA DE INSERCIÓN
// 8 campos, ya que fecha_registro se llena automáticamente
$sql = "INSERT INTO leads (nombre, empresa, email, comentario, origen, opt_in, status, email_enviado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql)) {
    // 8 parámetros
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
        // 6. ÉXITO EN LA INSERCIÓN: ENVIAR CORREOS
        $new_lead_id = mysqli_insert_id($link);

        // Notificación de Nuevo Lead a la Jefa (siempre se envía)
        enviarNotificacionJefa($nombre, $email, $comentario, $empresa, $origen);

        // Envío de Correo al Usuario (solo si aceptó recibir información)
        if ($opt_in == 1) {
            // Si el envío funciona, la función actualiza el campo email_enviado a 1
            enviarCorreoUsuario($nombre, $email, $new_lead_id, $link); 
        }
        
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
// FUNCIONES DE CORREO (Incluidas abajo)
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
        
        $redes_sociales = [/* ... URLs ... */];
        $html_content = "
            <html><body>
                <h2>Hola $nombre, ¡Bienvenido!</h2>
                <p>Tu solicitud ha sido recibida con éxito...</p>
                <p>Atentamente,<br>El equipo de Piedra en Punto.</p>
            </body></html>
        ";
        
        $mail->Body = $html_content;
        $mail->send();

        // ACTUALIZAR DB: Si el envío fue exitoso, marcamos email_enviado = 1
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

        $body = "Se ha recibido un nuevo lead web...";
        // ... Contenido del cuerpo ...

        $mail->Body = $body;
        $mail->send();
    } catch (Exception $e) {
        error_log("Notificacion a Jefa falló: {$mail->ErrorInfo}");
    }
}
?>