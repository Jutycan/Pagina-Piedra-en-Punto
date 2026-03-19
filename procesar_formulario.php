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
    $mail->Password = 'cwyutcwswbaslced'; // Contraseña de aplicación Gmail
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

    // URL del Logo corregida (con %20 para los espacios)
    $logoUrl = 'https://piedraenpunto.com/imagenes/general/Icon%20Piedra%20en%20Punto.png';

    // ===============================================
    // 📩 CORREO PARA LA JEFA (FORMAL Y EJECUTIVO)
    // ===============================================
    $mail->setFrom('info@piedraenpunto.com', '|Formulario General| Piedra en Punto');
    $mail->addAddress('cortes270k@gmail.com', 'Equipo Piedra en Punto');
    $mail->isHTML(true);
    $mail->Subject = "📋 Nuevo registro recibido - Piedra en Punto";

    $mail->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px 10px;'>
        <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;'>
            <div style='background-color: #33614a; padding: 30px; text-align: center;'>
                <img src='{$logoUrl}' width='80' alt='Piedra en Punto' style='margin-bottom: 15px;'>
                <h2 style='color: white; margin: 0; font-size: 22px; letter-spacing: 1px;'>Nuevo Registro Detectado</h2>
            </div>
            <div style='padding: 30px;'>
                <p style='color: #555; font-size: 16px;'>Estimada Jefa, se ha recibido una nueva solicitud a través del <strong>Formulario General</strong> del sitio web:</p>
                
                <div style='background: #f9f9f9; border-left: 4px solid #f06292; padding: 20px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>👤 Nombre:</strong> {$nombre}</p>
                    <p style='margin: 5px 0;'><strong>🏢 Empresa:</strong> {$empresa}</p>
                    <p style='margin: 5px 0;'><strong>📧 Correo:</strong> <a href='mailto:{$email}' style='color:#33614a;'>{$email}</a></p>
                    <p style='margin: 15px 0 5px 0;'><strong>💬 Comentario:</strong><br><span style='color: #666; font-style: italic;'>\"{$comentario}\"</span></p>
                </div>

                <div style='text-align: center; margin-top: 30px;'>
                    <a href='https://piedraenpunto.com/dashboard/gestion_leads.php' style='background: #33614a; color: white; padding: 14px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block;'>Acceder al Panel de Leads</a>
                </div>

                <div style='margin-top: 25px; padding: 15px; border: 1px dashed #ccc; border-radius: 5px; background-color: #fffcf5;'>
                    <p style='margin: 0; font-size: 13px; color: #888;'>
                        <strong>💡 Ayuda de acceso:</strong><br>
                        Si no recuerda las credenciales de ingreso al Panel de Gestión, puede consultarlas de forma segura en el siguiente documento: 
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

    // ===============================================
    // 💌 CORREO PARA EL USUARIO (CÁLIDO Y CORPORATIVO)
    // ===============================================
    $mail2 = new PHPMailer(true);
    $mail2->isSMTP();
    $mail2->Host = 'smtp.gmail.com';
    $mail2->SMTPAuth = true;
    $mail2->Username = 'cortes270k@gmail.com';
    $mail2->Password = 'cwyutcwswbaslced';
    $mail2->SMTPSecure = 'tls';
    $mail2->Port = 587;
    $mail2->CharSet = 'UTF-8';
    $mail2->SMTPOptions = $mail->SMTPOptions;

    $mail2->setFrom('info@piedraenpunto.com', 'Piedra en Punto');
    $mail2->addAddress($email);
    $mail2->isHTML(true);
    $mail2->Subject = "✨ ¡Gracias por contactarte con Piedra en Punto!";

    $mail2->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #fdfdfd; padding: 40px 10px;'>
        <div style='max-width: 600px; margin: auto; background: white; border-top: 5px solid #33614a; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            <div style='padding: 40px; text-align: center;'>
                <img src='{$logoUrl}' width='90' alt='Piedra en Punto' style='margin-bottom: 20px;'>
                <h1 style='color: #333; font-size: 24px; margin: 0;'>¡Hola, {$nombre}!</h1>
                <p style='color: #666; font-size: 16px; line-height: 1.6; margin-top: 15px;'>
                    Gracias por ponerte en contacto con <strong>Piedra en Punto</strong>. Hemos recibido tus datos correctamente y nuestro equipo ya está revisando tu solicitud.
                </p>
                <p style='color: #666; font-size: 16px; margin-bottom: 30px;'>
                    En breve nos comunicaremos contigo para dar seguimiento a tu mensaje.
                </p>
                
                <a href='https://piedraenpunto.com' style='background: #f06292; color: white; padding: 12px 25px; border-radius: 4px; text-decoration: none; font-weight: bold;'>Visitar nuestro Sitio</a>
                
                <div style='margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;'>
                    <p style='font-size: 14px; color: #999; margin-bottom: 10px;'>Síguenos en nuestras redes:</p>
                    <a href='#' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733547.png' width='24'></a>
                    <a href='https://www.instagram.com/piedraenpunto' style='text-decoration:none; margin: 0 10px;'><img src='https://cdn-icons-png.flaticon.com/512/2111/2111463.png' width='24'></a>
                    <a href='https://www.linkedin.com' style='text-decoration:none; margin: 0 10px;'><img src='https://cdn-icons-png.flaticon.com/512/145/145807.png' width='24'></a>
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

    echo json_encode(["success" => true, "message" => "Formulario enviado correctamente."]);

} catch (Exception $e) {
    error_log("Error PHPMailer: " . $mail->ErrorInfo);
    echo json_encode(["success" => false, "message" => "Error al enviar correos: " . $mail->ErrorInfo]);
}
?>



