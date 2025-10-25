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
$SMTP_PASS = "bynhxhdosbcijffd";     // <-- pega la contraseña de aplicación de Gmail aquí
$SMTP_PORT = 587;

// Logo y enlaces
$LOGO_URL = "https://piedraenpunto.com/imagenes/general/Icon Piedra en Punto.png";
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

    $mail->setFrom($SMTP_USER, 'Formulario PQRS - Piedra en Punto');
    $mail->addAddress($SMTP_USER, 'Equipo Piedra en Punto'); // jefa
    $mail->isHTML(true);
    $mail->Subject = "📩 Nuevo PQRS recibido — Piedra en Punto (ID: {$insertedId})";

    $mail->Body = "
    <div style='font-family:Roboto,Arial,sans-serif;background:#f8f8f8;padding:30px;'>
        <div style='max-width:680px;margin:auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,0.06)'>
            <div style='background:#f06292;padding:20px;text-align:center;color:#fff;'>
                <img src='{$LOGO_URL}' alt='Logo' width='84' style='display:block;margin:0 auto 8px'>
                <h3 style='margin:0'>Nuevo PQRS recibido</h3>
            </div>
            <div style='padding:20px;color:#333;'>
                <p><strong>ID:</strong> {$insertedId}</p>
                <p><strong>Clasificación:</strong> {$clasificacion}</p>
                <p><strong>Nombre:</strong> {$nombres}</p>
                <p><strong>Fecha:</strong> {$fecha}</p>
                <p><strong>Motivo:</strong> {$motivo}</p>
                <p><strong>Correo:</strong> {$email}</p>
                <p><strong>Teléfono:</strong> {$telefono}</p>
                <p><strong>Mensaje:</strong><br>" . nl2br($mensaje) . "</p>
                <hr style='border:0;border-top:1px solid #eee;margin:16px 0'>
                <p><strong>Estado:</strong> <span style='color:#f06292;'>Pendiente</span></p>
                <div style='text-align:center;margin-top:18px'>
                    <a href='{$PANEL_URL}' style='background:#33614a;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:600;'>Abrir Panel PQRS</a>
                </div>
            </div>
            <div style='background:#fafafa;padding:12px;text-align:center;color:#777;font-size:13px'>
                © " . date('Y') . " Piedra en Punto · Mensaje automático
            </div>
        </div>
    </div>
    ";
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

    $mail2->Body = "
    <div style='font-family:Roboto,Arial,sans-serif;background:#f8f8f8;padding:28px;'>
        <div style='max-width:640px;margin:auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,.06)'>
            <div style='background:#33614a;padding:18px;text-align:center;color:#fff;'>
                <img src='{$LOGO_URL}' alt='Logo' width='78' style='display:block;margin:0 auto 8px'>
                <h3 style='margin:0'>Gracias por contactarnos, " . htmlspecialchars($nombres, ENT_QUOTES) . "</h3>
            </div>
            <div style='padding:18px;color:#333;'>
                <p>Hemos recibido tu solicitud (ID: <strong>{$insertedId}</strong>). Nuestro equipo la revisará y te contactaremos en el menor tiempo posible.</p>
                <p>Resumen:</p>
                <ul>
                    <li><strong>Clasificación:</strong> {$clasificacion}</li>
                    <li><strong>Motivo:</strong> {$motivo}</li>
                    <li><strong>Fecha:</strong> {$fecha}</li>
                </ul>
                <div style='text-align:center;margin-top:14px'>
                    <a href='{$SITE_URL}' style='background:#f06292;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600;'>Visita nuestra web</a>
                </div>
                <div style='text-align:center;margin-top:14px'>
                    <!-- redes: reemplaza tus URLs reales -->
                    <a href='#' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733547.png' width='24'></a>
                    <a href='https://www.instagram.com/piedraenpunto?igsh=MWRpaWE3Z2Z1b2Njcw%3D%3D&utm_source=qr' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733558.png' width='24'></a>
                    <a href='https://www.linkedin.com/search/results/all/?heroEntityKey=urn%3Ali%3Aorganization%3A108482616&keywords=Piedra%20en%20Punto&origin=ENTITY_SEARCH_HOME_HISTORY&sid=yUC' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733561.png' width='24'></a>
                </div>
            </div>
            <div style='background:#fafafa;padding:12px;text-align:center;color:#777;font-size:13px'>
                © " . date('Y') . " Piedra en Punto
            </div>
        </div>
    </div>
    ";
    $mail2->send();

    echo json_encode(["success" => true, "message" => "PQRS enviado correctamente."]);
    exit;

} catch (Exception $e) {
    error_log("procesar_pqr.php PHPMailer Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error al enviar correos: " . $e->getMessage()]);
    exit;
}
