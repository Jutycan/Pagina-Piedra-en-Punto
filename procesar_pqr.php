<?php
// procesar_pqr.php
// Procesa el formulario PQRS: valida reCAPTCHA, guarda en tabla pqrs, envía correos (jefa + usuario).

// ---------- CONFIG (REEMPLAZA ESTOS VALORES) ----------
$DB_HOST = "localhost";
$DB_USER = "u894610526_formulario_g";
$DB_PASS = "Vero$2025$";              // <-- PON TU CONTRASEÑA DE DB AQUÍ
$DB_NAME = "u894610526_piedraenpunto";

$RECAPTCHA_SECRET = "6Ldk0OwrAAAAALN0Ru1tskiwsjLu-wZj_vIxrBET"; // <-- pega tu clave secreta reCAPTCHA v3 aquí

// Correo SMTP (Gmail)
$SMTP_HOST = "smtp.gmail.com";
$SMTP_USER = "cortes270k@gmail.com";       // correo remitente
$SMTP_PASS = "cwyutcwswbaslced";     // <-- pega la contraseña de aplicación de Gmail aquí
$SMTP_PORT = 587;

// Logo y enlaces
$LOGO_URL = "https://piedraenpunto.com/imagenes/general/Icon%20Piedra%20en%20Punto.png";
$PANEL_URL = "https://piedraenpunto.com/dashboard2/gestion_pqr.php";
$SITE_URL = "https://piedraenpunto.com";

// ---------- SETTINGS ----------
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

header('Content-Type: application/json; charset=utf-8');

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit;
}

// Honeypot check
if (!empty($_POST['website'] ?? '')) {
    echo json_encode(["success" => false, "message" => "Detección de bot."]);
    exit;
}

// Collect and sanitize input
function clean($v) {
    return htmlspecialchars(trim((string)$v), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$clasificacion = clean($_POST['clasificacion'] ?? '');
$nombres       = clean($_POST['nombres'] ?? '');
$fecha         = clean($_POST['fecha'] ?? '');
$motivo        = clean($_POST['motivo'] ?? '');
$email         = clean($_POST['email'] ?? '');
$telefono      = clean($_POST['telefono'] ?? '');
$mensaje       = clean($_POST['mensaje'] ?? '');
$politicas     = isset($_POST['politicas']) ? 1 : 0;
$pageUrl       = clean($_POST['pageUrl'] ?? '');
$recaptchaResp = $_POST['recaptcha_response'] ?? '';

// Basic validation
if (!$clasificacion || !$nombres || !$fecha || !$motivo || !$email || !$telefono || !$mensaje || !$politicas) {
    echo json_encode(["success" => false, "message" => "Faltan campos obligatorios."]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Correo inválido."]);
    exit;
}

// Verify reCAPTCHA server-side
$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
$postData = http_build_query([
    'secret' => $RECAPTCHA_SECRET,
    'response' => $recaptchaResp,
    'remoteip' => $_SERVER['REMOTE_ADDR']
]);
$opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $postData]];
$ctx = stream_context_create($opts);
$resp = @file_get_contents($verifyUrl, false, $ctx);
if ($resp === false) {
    error_log("procesar_pqr.php: No se pudo verificar reCAPTCHA (file_get_contents).");
    echo json_encode(["success" => false, "message" => "Error de validación de seguridad."]);
    exit;
}
$respJson = json_decode($resp, true);
if (empty($respJson['success']) || ($respJson['score'] ?? 0) < 0.45) {
    echo json_encode(["success" => false, "message" => "Error de validación reCAPTCHA."]);
    exit;
}

// DB insert
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    error_log("procesar_pqr.php: DB connection failed: " . $conn->connect_error);
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
    exit;
}
$stmt = $conn->prepare("INSERT INTO pqrs (clasificacion, nombres, fecha, motivo, email, telefono, mensaje, politica_datos, ip_address, user_agent, page_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    error_log("procesar_pqr.php: prepare failed: " . $conn->error);
    echo json_encode(["success" => false, "message" => "Error interno."]);
    $conn->close();
    exit;
}
$stmt->bind_param("sssssssssss", $clasificacion, $nombres, $fecha, $motivo, $email, $telefono, $mensaje, $politicas, $ip, $userAgent, $pageUrl);
if (!$stmt->execute()) {
    error_log("procesar_pqr.php: execute failed: " . $stmt->error);
    echo json_encode(["success" => false, "message" => "Error al guardar datos."]);
    $stmt->close();
    $conn->close();
    exit;
}
$insertedId = $stmt->insert_id;
$stmt->close();
$conn->close();

// ---------- Send emails with PHPMailer ----------
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/PHPMailer/Exception.php';

try {
    // Common SMTP options array
    $smtpOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // === Email to the manager (formal) ===
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = $SMTP_USER;
    $mail->Password = $SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    $mail->SMTPOptions = $smtpOptions;
    // Calcular fecha límite (15 días a partir de hoy)
    $fecha_limite = date('d/m/Y', strtotime('+8 days'));

    // ===============================================
    // 📩 CORREO PARA LA JEFA (FORMAL Y EJECUTIVO)
    // ===============================================
    $mail->setFrom($SMTP_USER, 'Formulario PQRS - Piedra en Punto');
    $mail->addAddress($SMTP_USER, 'Equipo Piedra en Punto'); // jefa
    $mail->isHTML(true);
    $mail->Subject = "📩 Nuevo PQRS recibido — Piedra en Punto (ID: {$insertedId})";

    $mail->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px 10px;'>
        <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;'>
            <div style='background-color: #f06292; padding: 30px; text-align: center;'>
                <img src='{$LOGO_URL}' width='80' alt='Piedra en Punto' style='margin-bottom: 15px;'>
                <h2 style='color: white; margin: 0; font-size: 22px; letter-spacing: 1px;'>Nuevo PQRS Recibido</h2>
            </div>
            <div style='padding: 30px;'>
                <p style='color: #555; font-size: 16px;'>Estimada Jefa, se ha registrado una nueva solicitud en el sistema de <strong>PQRS</strong> (ID: #{$insertedId}):</p>
                
                <div style='background: #f9f9f9; border-left: 4px solid #33614a; padding: 20px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>📋 Clasificación:</strong> <span style='color:#f06292; font-weight:bold;'>{$clasificacion}</span></p>
                    <p style='margin: 5px 0;'><strong>👤 Remitente:</strong> {$nombres}</p>
                    <p style='margin: 5px 0;'><strong>📅 Fecha del suceso:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>🎯 Motivo:</strong> {$motivo}</p>
                    <p style='margin: 5px 0;'><strong>📧 Correo:</strong> {$email}</p>
                    <p style='margin: 5px 0;'><strong>📞 Teléfono:</strong> {$telefono}</p>
                    <p style='margin: 15px 0 5px 0;'><strong>💬 Detalle del mensaje:</strong><br><span style='color: #666; font-style: italic;'>\"" . nl2br($mensaje) . "\"</span></p>
                </div>

                <div style='background: #fff5f7; border: 1px solid #f06292; border-radius: 6px; padding: 15px; margin-bottom: 25px; text-align: center;'>
                    <p style='margin: 0; color: #d81b60; font-size: 14px;'>
                        <i style='font-size: 18px;'>📅</i> <strong>Fecha límite sugerida para respuesta:</strong> {$fecha_limite}
                    </p>
                    <p style='margin: 5px 0 0 0; font-size: 12px; color: #888;'>Basado en los términos de ley para la atención de PQRS.</p>
                </div>

                <div style='text-align: center; margin-top: 10px;'>
                    <a href='{$PANEL_URL}' style='background: #33614a; color: white; padding: 14px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block;'>Gestionar en Panel PQRS</a>
                </div>

                <div style='margin-top: 25px; padding: 15px; border: 1px dashed #ccc; border-radius: 5px; background-color: #fffcf5;'>
                    <p style='margin: 0; font-size: 13px; color: #888;'>
                        <strong>💡 Ayuda de acceso:</strong><br>
                        Si no recuerda las credenciales de ingreso al Panel de Gestión, puede consultarlas en el siguiente documento: 
                        <a href='https://docs.google.com/document/d/1-udNBYlBHRfYaMroSjRCZkyfzWio0YA8PlrbKzYgoHM/edit?usp=sharing' style='color: #f06292; font-weight: bold;'>Ver Hoja de Claves Drive</a>.
                    </p>
                </div>
            </div>
            <div style='background-color: #eee; text-align: center; padding: 20px; font-size: 12px; color: #777;'>
                <p style='margin: 0;'>Este es un mensaje enviado automáticamente por el servidor.</p>
                <p style='margin: 5px 0 0 0;'>© 2026 Piedra en Punto · Mensaje automático del sistema.</p>
            </div>
        </div>
    </div>";
    $mail->send();

    // === Confirmation email to user (friendly) ===
    $mail2 = new PHPMailer(true);
    $mail2->isSMTP();
    $mail2->Host = $SMTP_HOST;
    $mail2->SMTPAuth = true;
    $mail2->Username = $SMTP_USER;
    $mail2->Password = $SMTP_PASS;
    $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail2->Port = $SMTP_PORT;
    $mail2->CharSet = 'UTF-8';
    $mail2->SMTPOptions = $smtpOptions;

    $mail2->setFrom($SMTP_USER, 'Piedra en Punto');
    $mail2->addAddress($email);
    $mail2->isHTML(true);
    $mail2->Subject = "Hemos recibido tu solicitud — Piedra en Punto";
    


    // ===============================================
    // 💌 CORREO PARA EL USUARIO (CÁLIDO Y CORPORATIVO)
    // ===============================================
    $mail2->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #fdfdfd; padding: 40px 10px;'>
        <div style='max-width: 600px; margin: auto; background: white; border-top: 5px solid #33614a; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            <div style='padding: 40px; text-align: center;'>
                <img src='{$LOGO_URL}' width='90' alt='Piedra en Punto' style='margin-bottom: 20px;'>
                <h1 style='color: #333; font-size: 24px; margin: 0;'>¡Hola, " . htmlspecialchars($nombres) . "!</h1>
                <p style='color: #666; font-size: 16px; line-height: 1.6; margin-top: 15px;'>
                    Hemos recibido formalmente tu <strong>{$clasificacion}</strong> con el radicado <strong>#{$insertedId}</strong>.
                </p>
                <p style='color: #666; font-size: 16px;'>
                    Para nosotros es muy importante escucharte. Nuestro equipo de atención revisará los detalles y te brindará una respuesta formal a la brevedad posible.
                </p>
                
                <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 25px 0; text-align: left; display: inline-block; width: 100%; box-sizing: border-box;'>
                    <p style='margin: 0; font-size: 14px; color: #555;'><strong>Radicado:</strong> #{$insertedId}</p>
                    <p style='margin: 5px 0 0 0; font-size: 14px; color: #555;'><strong>Motivo:</strong> {$motivo}</p>
                </div>
                
                <div style='margin-top: 30px;'>
                    <a href='{$SITE_URL}' style='background: #f06292; color: white; padding: 12px 25px; border-radius: 4px; text-decoration: none; font-weight: bold;'>Regresar al Sitio Web</a>
                </div>
                
                <div style='margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;'>
                    <p style='font-size: 14px; color: #999; margin-bottom: 10px;'>Conoce más de nosotros:</p>
                    <a href='#' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733547.png' width='24'></a>
                    <a href='#' style='text-decoration:none; margin: 0 10px;'><img src='https://cdn-icons-png.flaticon.com/512/2111/2111463.png' width='22'></a>
                    <a href='https://www.instagram.com/piedraenpunto' style='text-decoration:none; margin: 0 10px;'><img src='https://cdn-icons-png.flaticon.com/512/145/145807.png' width='22'></a>
                </div>
            </div>
            <div style='background-color: #33614a; text-align: center; padding: 25px; font-size: 12px; color: white; opacity: 0.9;'>
                <p style='margin: 0; font-weight: bold;'>Aviso importante:</p>
                <p style='margin: 5px 0;'>Este es un envío automático. Por favor, no responda directamente a este correo, ya que la cuenta no es monitoreada.</p>
                <p style='margin: 15px 0 0 0; font-size: 11px; color: #ccc;'>© 2026 Piedra en Punto · Todos los derechos reservados.</p>
            </div>
        </div>
    </div>";
    $mail2->send();

    echo json_encode(["success" => true, "message" => "PQRS enviado correctamente."]);
    exit;

} catch (Exception $e) {
    error_log("procesar_pqr.php PHPMailer Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error al enviar correos: " . $e->getMessage()]);
    exit;
}
