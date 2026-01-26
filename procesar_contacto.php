<?php
header('Content-Type: application/json');

// ==========================
// CONFIGURACIÓN BASE
// ==========================
$host = "localhost";        // o el host de tu hosting (ej: "localhost" en Hostinger)
$dbname = "u894610526_piedraenpunto"; // reemplaza con el nombre real de tu BD
$username = "u894610526_formulario_g";    // reemplaza con tu usuario MySQL
$password = "Vero$2025$";      // reemplaza con tu contraseña MySQL

// Clave secreta reCAPTCHA v3
$recaptcha_secret = "6Ldk0OwrAAAAALN0Ru1tskiwsjLu-wZj_vIxrBET"; 

// Correo de la jefa
$jefa_email = "verodlp@piedraenpunto11.com";

// ==========================
// 1️⃣ Verificar que vengan datos POST
// ==========================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Método no permitido."]);
    exit;
}

// ==========================
// 2️⃣ Capturar los datos del formulario
// ==========================
$nombre   = trim($_POST["nombre"] ?? "");
$email    = trim($_POST["email"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$mensaje  = trim($_POST["mensaje"] ?? "");
$token    = $_POST["recaptchaResponse"] ?? "";

// Validar campos requeridos
if (empty($nombre) || empty($email) || empty($mensaje) || empty($token)) {
    echo json_encode(["success" => false, "error" => "Faltan campos obligatorios."]);
    exit;
}

// ==========================
// 3️⃣ Verificar reCAPTCHA con Google
// ==========================
$recaptcha_url = "https://www.google.com/recaptcha/api/siteverify";
$recaptcha_data = [
    "secret" => $recaptcha_secret,
    "response" => $token
];

$options = [
    "http" => [
        "method"  => "POST",
        "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
        "content" => http_build_query($recaptcha_data)
    ]
];
$context  = stream_context_create($options);
$response = file_get_contents($recaptcha_url, false, $context);
$result   = json_decode($response, true);

if (!$result["success"] || $result["score"] < 0.5) {
    echo json_encode(["success" => false, "error" => "reCAPTCHA no válido."]);
    exit;
}

// ==========================
// 4️⃣ Guardar en la base de datos
// ==========================
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO contacto (nombre, email, telefono, mensaje, estado, fecha_envio)
        VALUES (:nombre, :email, :telefono, :mensaje, 'Pendiente', NOW())
    ");

    $stmt->execute([
        ":nombre" => $nombre,
        ":email" => $email,
        ":telefono" => $telefono,
        ":mensaje" => $mensaje
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Error al guardar en BD: " . $e->getMessage()]);
    exit;
}

// ==========================
// 5️⃣ Enviar correos con PHPMailer
// ==========================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

$mail = new PHPMailer(true);

try {
    // --- CONFIGURACIÓN GENERAL SMTP ---
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'verodlp@piedraenpunto11.com';
    $mail->Password = 'bgafvciimbgqwaqk';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // --- ENVÍO A LA JEFA ---
    $mail->setFrom('verodlp@piedraenpunto11.com', 'Sistema Web Piedra en Punto');
    $mail->addAddress($jefa_email, 'Jefa Piedra en Punto');
    $mail->isHTML(true);
    $mail->Subject = "📩 Nuevo mensaje de contacto recibido";

    $mail->Body = "
    <h2>Nuevo mensaje de contacto recibido</h2>
    <p><strong>Nombre:</strong> $nombre</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Teléfono:</strong> $telefono</p>
    <p><strong>Mensaje:</strong><br>$mensaje</p>
    <p><strong>Estado actual:</strong> Pendiente</p>
    <hr>
    <p style='margin-top:20px;'>
        <a href='https://piedraenpunto.com/dashboard3/gestion_contacto.php' style='
            display:inline-block;
            background-color:#e91e63;
            color:#fff;
            font-weight:bold;
            padding:12px 25px;
            text-decoration:none;
            border-radius:8px;
            box-shadow:0 2px 5px rgba(0,0,0,0.15);
        '>📋 Ir al panel de gestión de contactos</a>
    </p>
    <p style='margin-top:10px;font-size:13px;color:#666;'>
        ⚠️ Este correo se generó automáticamente por el sistema web de <strong>Piedra en Punto</strong>.
    </p>
";


    $mail->send();

    // --- ENVÍO AL USUARIO ---
    $mail->clearAddresses();
    $mail->addAddress($email, $nombre);
    $mail->Subject = "✅ Hemos recibido tu mensaje";
    $mail->Body = "
    <h2>Hola, $nombre 👋</h2>
    <p>Gracias por contactarte con <strong>Piedra en Punto</strong>.</p>
    <p>Hemos recibido tu mensaje y nuestro equipo te responderá pronto.</p>
    <p>Saludos cordiales,<br>El equipo de Piedra en Punto</p>
    ";

    $mail->send();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Error al enviar correo: " . $mail->ErrorInfo]);
    exit;
}
?>
