<?php
// Establecer la zona horaria a México City para las marcas de tiempo
date_default_timezone_set('America/Mexico_City');
// Configurar el encabezado para que la respuesta sea JSON (necesario para AJAX)
header('Content-Type: application/json');

// --- 1. CONFIGURACIÓN CRÍTICA (¡REEMPLAZA ESTOS VALORES!) ---

// Configuración de la Base de Datos MySQL
$db_host = 'localhost';         // Ejemplo: 'localhost'
$db_user = 'u894610526_P_Formulario1';  // Ejemplo: 'root'
$db_pass = 'Ejercicios$2021$';// Ejemplo: 'miclave123'
$db_name = 'u894610526_Formulario_1_P';  // Ejemplo: 'piedra11_db'

// Configuración del Correo Saliente (Usando un correo de la empresa o Gmail)
$jefa_email = 'cortes270k@gmail.com'; // El correo desde donde se enviarán las notificaciones
$jefa_pass = 'pkgwbezvtiyqiire';        // Si usas Gmail: Contraseña de Aplicación. Si es hosting: Contraseña normal.
$jefa_address = 'cortes270k@gmail.com'; // El correo que recibirá la notificación del nuevo contacto

// URL de la Interfaz de Administración (Para el botón en el correo de notificación)
$admin_url = 'https://piedraenpunto.com/admin_contacto.html'; 
$nombre_empresa = 'Piedra en Punto';


// --- 2. CONFIGURACIÓN PHPMailer (Asegúrate que las rutas sean correctas) ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Incluir los archivos de PHPMailer (AJUSTA ESTAS RUTAS SEGÚN TU ESTRUCTURA)
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';


// Función de respuesta JSON para terminar la ejecución
function jsonResponse($success, $message, $extra = []) {
    echo json_encode(array_merge(["success" => $success, "message" => $message], $extra));
    exit;
}

// --- 3. VALIDACIÓN INICIAL Y PREPARACIÓN DE DATOS ---
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    jsonResponse(false, "Método no permitido.");
}

// Limpiar y obtener datos del formulario
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
$mensaje = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_STRING);

$fecha_registro = date('Y-m-d H:i:s');
$estado_inicial = 'Pendiente';

// Verificación de campos obligatorios
if (!$nombre || !$email || !$telefono) {
    jsonResponse(false, "Faltan campos obligatorios (Nombre, Email, Teléfono).");
}


// --- 4. MySQL: CONEXIÓN, CREACIÓN DE TABLA Y ALMACENAMIENTO ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    error_log("Error de conexión a la BD: " . $conn->connect_error);
    jsonResponse(false, "Error interno del servidor (DB).");
}

// Crear la tabla 'contactos' si no existe (mismo código SQL del archivo anterior)
$sql_create = "CREATE TABLE IF NOT EXISTS contactos (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(50) NOT NULL,
    mensaje TEXT NOT NULL,
    estado ENUM('Pendiente', 'Contestado') DEFAULT 'Pendiente',
    fecha_registro DATETIME NOT NULL
)";
if (!$conn->query($sql_create)) {
    error_log("Error al crear la tabla: " . $conn->error);
    $conn->close();
    jsonResponse(false, "Error interno al configurar la tabla.");
}

// Inserción de datos usando sentencias preparadas (para prevenir inyección SQL)
$stmt = $conn->prepare("INSERT INTO contactos (nombre, email, telefono, mensaje, estado, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $nombre, $email, $telefono, $mensaje, $estado_inicial, $fecha_registro);

if (!$stmt->execute()) {
    error_log("Error al guardar el registro en MySQL: " . $stmt->error);
    $stmt->close();
    $conn->close();
    jsonResponse(false, "Error al guardar el registro en la base de datos.");
}

$contacto_id = $stmt->insert_id; // Obtener el ID que se acaba de generar en MySQL
$stmt->close();
$conn->close();


// --- 5. Firestore: NOTA SOBRE SINCRONIZACIÓN EN TIEMPO REAL ---
// Aquí es donde, en un sistema real, se usaría la API REST de Firestore o un SDK de servidor
// para replicar el registro. Dado que no se puede ejecutar código fuera del script PHP,
// el ID de MySQL ($contacto_id) es el identificador principal. El Panel de Admin del paso 3
// deberá usar un mecanismo para gestionar y sincronizar este estado en el futuro.
// Por ahora, solo usamos el ID para la respuesta y los correos.


// --- 6. ENVÍO DE CORREOS CON PHPMailer ---

$mail = new PHPMailer(true);

try {
    // Configuración SMTP para Gmail/Servidor
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // O el host de tu proveedor de correo (ej: mail.tudominio.com)
    $mail->SMTPAuth   = true;
    $mail->Username   = $jefa_email; 
    $mail->Password   = $jefa_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usar SSL o TLS. STARTTLS es el más común.
    $mail->Port       = 587; // Puerto para TLS/STARTTLS
    $mail->CharSet    = 'UTF-8';
    $mail->isHTML(true);
    $mail->setFrom($jefa_email, $nombre_empresa);


    // --- 6.1 Correo de Notificación a la Jefa ---
    $mail->clearAllRecipients();
    $mail->addAddress($jefa_address, 'Admin Contacto');
    $mail->Subject = '🚨 NUEVO CONTACTO PENDIENTE - ID: ' . $contacto_id;
    
    $cuerpo_jefa = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <h2 style='color: #EC0868;'>Notificación de Nuevo Contacto Registrado</h2>
        <p>Se ha recibido un nuevo mensaje a través del formulario de contacto. Ha sido marcado como <strong>Pendiente</strong> en la base de datos.</p>
        <div style='background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; margin: 25px 0; border-radius: 8px;'>
            <h3 style='color: #444; border-bottom: 2px solid #EC0868; padding-bottom: 10px;'>Detalles del Contacto</h3>
            <p><strong>ID de Base de Datos:</strong> #$contacto_id</p>
            <p><strong>Nombre:</strong> " . htmlspecialchars($nombre) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Teléfono:</strong> " . htmlspecialchars($telefono) . "</p>
            <p><strong>Fecha/Hora:</strong> $fecha_registro</p>
            <p><strong>Mensaje:</strong><br>" . nl2br(htmlspecialchars($mensaje)) . "</p>
        </div>
        
        <!-- Botón para ir a la Interfaz de Gestión -->
        <a href='$admin_url' style='display: inline-block; padding: 12px 25px; margin-top: 20px; background-color: #EC0868; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
            Ir al Panel de Gestión
        </a>
        <p style='margin-top: 30px; font-size: 0.8rem; color: #999;'>Este correo es automático, por favor, no responder.</p>
    </div>
    ";
    $mail->Body = $cuerpo_jefa;
    $mail->send();


    // --- 6.2 Correo de Confirmación al Usuario ---
    $mail->clearAllRecipients();
    $mail->addAddress($email, $nombre);
    $mail->Subject = 'Confirmación de Recepción de Mensaje - ' . $nombre_empresa;
    
    $cuerpo_usuario = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <h2 style='color: #4CAF50;'>¡Hola $nombre! Mensaje Recibido Correctamente.</h2>
        <p>Agradecemos tu contacto. Hemos recibido tu mensaje y nuestro equipo lo revisará a la brevedad.</p>
        <p><strong>Detalles de tu mensaje:</strong></p>
        <div style='background-color: #f5f5f5; padding: 15px; border-left: 4px solid #EC0868; margin: 20px 0; border-radius: 4px;'>
            <p><strong>Mensaje:</strong><br>" . nl2br(htmlspecialchars($mensaje)) . "</p>
        </div>
        <p>¡Te contactaremos pronto!</p>
        <p style='margin-top: 30px;'>Saludos cordiales,<br>El equipo de $nombre_empresa.</p>
    </div>
    ";
    $mail->Body = $cuerpo_usuario;
    $mail->send();

} catch (Exception $e) {
    // Si falla el correo, lo registramos pero el registro de BD es exitoso.
    error_log("Error al enviar correos: {$mail->ErrorInfo}");
    // No detenemos la ejecución, ya que el dato se guardó en MySQL, que es lo más importante.
}


// --- 7. RESPUESTA FINAL AJAX EXITOSA ---
jsonResponse(true, "Mensaje enviado y registrado.", [
    "id" => $contacto_id,
    "nombre" => $nombre
]);
?>