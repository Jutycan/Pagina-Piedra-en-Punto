<?php
// ===============================================
// CONFIGURACIÓN GENERAL - Piedra en Punto
// ===============================================

// ---- Datos de conexión a la base de datos ----
$servername = "localhost";      // Normalmente "localhost" en Hostinger
$username = "u894610526_formulario_g";                 // Ejemplo: u123456789_admin
$password = "Vero$2025$";                 // Tu contraseña de la base de datos
$dbname = "u894610526_piedraenpunto";                   // Ejemplo: piedraenpunto_db

// ---- Clave secreta de reCAPTCHA v3 ----
$secretKey = '6Ldk0OwrAAAAALN0Ru1tskiwsjLu-wZj_vIxrBET';

// ===============================================
// PROTECCIÓN ANTISPAM Y VALIDACIONES INICIALES
// ===============================================

// Solo permitir método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit;
}

// Honeypot (campo oculto)
if (!empty($_POST["website"])) {
    echo json_encode(["success" => false, "message" => "Detección de bot."]);
    exit;
}

// Capturar IP y navegador
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// ===============================================
// FUNCIONES DE LIMPIEZA Y VALIDACIÓN
// ===============================================
function limpiar($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$nombre = limpiar($_POST['nombre'] ?? '');
$empresa = limpiar($_POST['empresa'] ?? '');
$email = limpiar($_POST['email'] ?? '');
$comentario = limpiar($_POST['comentario'] ?? '');
$recibir_info = isset($_POST['recibir-info']) ? 1 : 0;
$politica_datos = isset($_POST['politica-datos']) ? 1 : 0;
$pageUrl = limpiar($_POST['pageUrl'] ?? '');
$recaptchaResponse = $_POST['recaptcha_response'] ?? '';

// Validaciones básicas
if (!$nombre || !$email || !$comentario || !$politica_datos) {
    echo json_encode(["success" => false, "message" => "Faltan campos obligatorios."]);
    exit;
}

// Validar correo electrónico
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Correo inválido."]);
    exit;
}

// ===============================================
// VALIDAR reCAPTCHA v3
// ===============================================
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret' => $secretKey,
    'response' => $recaptchaResponse
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];
$context  = stream_context_create($options);
$verify = file_get_contents($url, false, $context);
$captchaSuccess = json_decode($verify);

if (!$captchaSuccess->success || $captchaSuccess->score < 0.5) {
    echo json_encode(["success" => false, "message" => "Error de validación reCAPTCHA."]);
    exit;
}

// ===============================================
// CONEXIÓN CON BASE DE DATOS Y REGISTRO DE DATOS
// ===============================================
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
    exit;
}

// Insertar datos de forma segura
$stmt = $conn->prepare("INSERT INTO leads (nombre, empresa, email, comentario, recibir_info, politica_datos, ip_address, user_agent, page_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssiisss", $nombre, $empresa, $email, $comentario, $recibir_info, $politica_datos, $ip, $userAgent, $pageUrl);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Error al guardar datos."]);
    $conn->close();
    exit;
}

$stmt->close();
$conn->close();

// ===============================================
// ENVÍO DE CORREOS CON PHPMailer
// ===============================================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

// Crear instancia
$mail = new PHPMailer(true);

try {
    // Configurar servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'cortes270k@gmail.com'; // Gmail de la jefa o tuyo
    $mail->Password = 'pkgwbezvtiyqiire'; // Contraseña de aplicación de Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Remitente
    $mail->setFrom('cortes270k@gmail.com', 'Formulario Piedra en Punto');

    // Correo para el equipo de Piedra en Punto
    $mail->addAddress('cortes270k@gmail.com', 'Equipo Piedra en Punto');
    $mail->isHTML(true);
    $mail->Subject = "📩 Nuevo registro - Formulario general Piedra en Punto";
    $mail->Body = "
        <h2 style='color:#33614a;'>Nuevo registro recibido</h2>
        <p><strong>Nombre:</strong> {$nombre}</p>
        <p><strong>Empresa:</strong> {$empresa}</p>
        <p><strong>Correo:</strong> {$email}</p>
        <p><strong>Comentario:</strong> {$comentario}</p>
        <hr>
        <p>Estado actual: <b style='color:#f06292;'>Pendiente</b></p>
        <p>Panel de gestión:</p>
        <a href='https://piedraenpunto.com/dashboard/gestion_leads.php' 
        style='background:#33614a;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;'>Abrir Panel</a>
    ";
    $mail->send();

    // Correo de confirmación al usuario (si aceptó recibir info)
    if ($recibir_info == 1) {
        $mail2 = new PHPMailer(true);
        $mail2->isSMTP();
        $mail2->Host = 'smtp.gmail.com';
        $mail2->SMTPAuth = true;
        $mail2->Username = 'cortes270k@gmail.com';
        $mail2->Password = 'pkgwbezvtiyqiire';
        $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail2->Port = 587;

        $mail2->setFrom('cortes270k@gmail.com', 'Piedra en Punto');
        $mail2->addAddress($email);
        $mail2->isHTML(true);
        $mail2->Subject = "¡Gracias por contactarte con Piedra en Punto!";
        $mail2->Body = "
            <div style='font-family:Roboto,Arial,sans-serif;color:#333'>
                <h2 style='color:#33614a;'>¡Gracias por escribirnos, {$nombre}!</h2>
                <p>Hemos recibido tu solicitud correctamente. En breve nuestro equipo se pondrá en contacto contigo.</p>
                <p>Mientras tanto, te invitamos a conocer más sobre nosotros:</p>
                <a href='https://piedraenpunto.com' style='color:#f06292;'>Visita nuestra página web</a><br>
                <a href='https://www.instagram.com/piedraenpunto.com' style='color:#33614a;'>Síguenos en Instagram</a>
                <hr>
                <p style='font-size:12px;color:#888;'>Este mensaje fue generado automáticamente por el sistema de contacto de Piedra en Punto.</p>
            </div>
        ";
        $mail2->send();
    }

    echo json_encode(["success" => true, "message" => "Formulario enviado correctamente."]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error al enviar correos: {$mail->ErrorInfo}"]);
}
?>

