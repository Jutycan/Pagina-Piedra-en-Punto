<?php
// ===============================================
// CONFIGURACIÓN GENERAL - Piedra en Punto
// ===============================================

// ---- Datos de conexión a la base de datos ----
$servername = "localhost";
$username = "u894610526_formulario_g";
$password = "Vero$2025$"; // 🔹 Escribe aquí tu contraseña real
$dbname = "u894610526_piedraenpunto";

// ---- Clave secreta de reCAPTCHA v3 ----
$secretKey = '6Ldk0OwrAAAAALN0Ru1tskiwsjLu-wZj_vIxrBET';

// ===============================================
// PROTECCIÓN ANTISPAM Y VALIDACIONES INICIALES
// ===============================================
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit;
}

if (!empty($_POST["website"])) {
    echo json_encode(["success" => false, "message" => "Detección de bot."]);
    exit;
}

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

if (!$nombre || !$email || !$comentario || !$politica_datos) {
    echo json_encode(["success" => false, "message" => "Faltan campos obligatorios."]);
    exit;
}

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
// CONEXIÓN CON BASE DE DATOS Y REGISTRO
// ===============================================
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
    exit;
}

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

require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/PHPMailer/Exception.php';

$mail = new PHPMailer(true);

try {
    // ---- CONFIGURACIÓN SMTP ----
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'cortes270k@gmail.com'; // Gmail de la jefa
    $mail->Password = 'bynhxhdosbcijffd'; // Contraseña de aplicación Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // ===============================================
    // 📩 CORREO PARA LA JEFA (FORMAL Y EJECUTIVO)
    // ===============================================
    $mail->setFrom('cortes270k@gmail.com', 'Formulario Piedra en Punto');
    $mail->addAddress('cortes270k@gmail.com', 'Equipo Piedra en Punto');
    $mail->isHTML(true);
    $mail->Subject = "📋 Nuevo registro recibido - Piedra en Punto";

    $mail->Body = "
    <div style='font-family:Roboto,Arial,sans-serif;background:#f9f9f9;padding:40px;'>
        <div style='max-width:600px;margin:auto;background:white;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.1);overflow:hidden;'>
            <div style='text-align:center;padding:20px;background:#f06292;'>
                <img src='https://piedraenpunto.com/imagenes/general/Icon Piedra en Punto.png' width='90' alt='Logo Piedra en Punto'>
                <h2 style='color:white;margin:10px 0;'>Nuevo registro en el formulario general</h2>
            </div>
            <div style='padding:30px;color:#333;'>
                <p><strong>Nombre:</strong> {$nombre}</p>
                <p><strong>Empresa:</strong> {$empresa}</p>
                <p><strong>Correo:</strong> {$email}</p>
                <p><strong>Comentario:</strong> {$comentario}</p>
                <hr style='border:0;border-top:1px solid #eee;margin:20px 0;'>
                <p><strong>Estado actual:</strong> <span style='color:#f06292;'>Pendiente</span></p>
                <div style='text-align:center;margin-top:25px;'>
                    <a href='https://piedraenpunto.com/dashboard/gestion_leads.php' style='background:#33614a;color:white;padding:12px 25px;border-radius:6px;text-decoration:none;font-weight:bold;'>Abrir Panel de Gestión</a>
                </div>
            </div>
            <div style='background:#f2f2f2;text-align:center;padding:15px;font-size:12px;color:#777;'>
                © " . date('Y') . " Piedra en Punto · Mensaje automático del sistema.
            </div>
        </div>
    </div>
    ";
    $mail->send();

    // ===============================================
    // 💌 CORREO PARA EL USUARIO (CÁLIDO Y CORPORATIVO)
    // ===============================================
    $mail2 = new PHPMailer(true);
    $mail2->isSMTP();
    $mail2->Host = 'smtp.gmail.com';
    $mail2->SMTPAuth = true;
    $mail2->Username = 'cortes270k@gmail.com';
    $mail2->Password = 'bynhxhdosbcijffd';
    $mail2->SMTPSecure = 'tls';
    $mail2->Port = 587;
    $mail2->CharSet = 'UTF-8';
    $mail2->SMTPOptions = $mail->SMTPOptions;

    $mail2->setFrom('cortes270k@gmail.com', 'Piedra en Punto');
    $mail2->addAddress($email);
    $mail2->isHTML(true);
    $mail2->Subject = "✨ ¡Gracias por contactarte con Piedra en Punto!";

    $mail2->Body = "
    <div style='font-family:Roboto,Arial,sans-serif;background:#f9f9f9;padding:40px;'>
        <div style='max-width:600px;margin:auto;background:white;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.1);overflow:hidden;'>
            <div style='text-align:center;padding:20px;background:#33614a;'>
                <img src='https://piedraenpunto.com/imagenes/general/Icon Piedra en Punto.png' width='90' alt='Logo Piedra en Punto'>
                <h2 style='color:white;margin:10px 0;'>¡Gracias por escribirnos, {$nombre}!</h2>
            </div>
            <div style='padding:30px;color:#333;'>
                <p>Hemos recibido tu solicitud correctamente. Nuestro equipo se pondrá en contacto contigo en breve.</p>
                <p>Mientras tanto, te invitamos a seguirnos y conocer más de nuestro trabajo:</p>
                <div style='text-align:center;margin:25px 0;'>
                    <a href='https://piedraenpunto.com' style='background:#f06292;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;'>Visitar nuestro sitio web</a>
                </div>
                <div style='text-align:center;'>
                    <a href='#' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733547.png' width='24'></a>
                    <a href='https://www.instagram.com/piedraenpunto?igsh=MWRpaWE3Z2Z1b2Njcw%3D%3D&utm_source=qr' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733558.png' width='24'></a>
                    <a href='https://www.linkedin.com/search/results/all/?heroEntityKey=urn%3Ali%3Aorganization%3A108482616&keywords=Piedra%20en%20Punto&origin=ENTITY_SEARCH_HOME_HISTORY&sid=yUC' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733561.png' width='24'></a>
                </div>
            </div>
            <div style='background:#f2f2f2;text-align:center;padding:15px;font-size:12px;color:#777;'>
                © " . date('Y') . " Piedra en Punto · Todos los derechos reservados.
            </div>
        </div>
    </div>
    ";
    $mail2->send();

    echo json_encode(["success" => true, "message" => "Formulario enviado correctamente."]);

} catch (Exception $e) {
    error_log("Error PHPMailer: " . $mail->ErrorInfo);
    echo json_encode(["success" => false, "message" => "Error al enviar correos: " . $mail->ErrorInfo]);
}
?>



