










<?php
header('Content-Type: application/json');

// ==========================
// CONFIGURACIÓN BASE
// ==========================
$host = "localhost"; 
$dbname = "u894610526_piedraenpunto"; // Reemplaza con tu BD real
$username = "u894610526_formulario_g"; // Reemplaza con tu usuario real
$password = "Vero$2025$"; // Reemplaza con tu contraseña real

$recaptcha_secret = "6Ldk0OwrAAAAALN0Ru1tskiwsjLu-wZj_vIxrBET"; 
$jefa_email = "verodlp@piedraenpunto11.com";
$LOGO_URL = "https://piedraenpunto.com/imagenes/general/Icon%20Piedra%20en%20Punto.png";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Método no permitido."]);
    exit;
}

$nombre   = trim($_POST["nombre"] ?? "");
$email    = trim($_POST["email"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$mensaje  = trim($_POST["mensaje"] ?? "");
$token    = $_POST["recaptchaResponse"] ?? "";

if (empty($nombre) || empty($email) || empty($mensaje) || empty($token)) {
    echo json_encode(["success" => false, "error" => "Faltan campos obligatorios."]);
    exit;
}

// Verification reCAPTCHA
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$token}");
$result = json_decode($response, true);

if (!$result["success"] || $result["score"] < 0.5) {
    echo json_encode(["success" => false, "error" => "reCAPTCHA no válido."]);
    exit;
}

// Guardar en BD
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO contacto (nombre, email, telefono, mensaje, estado, fecha_envio) VALUES (:nombre, :email, :telefono, :mensaje, 'Pendiente', NOW())");
    $stmt->execute([":nombre" => $nombre, ":email" => $email, ":telefono" => $telefono, ":mensaje" => $mensaje]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Error BD"]);
    exit;
}

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

try {
    // ===============================================
    // 📩 CORREO PARA LA JEFA ($mail)
    // ===============================================
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'verodlp@piedraenpunto11.com';
    $mail->Password = 'bgafvciimbgqwaqk'; // 16 dígitos de Google
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('contacto@piedraenpunto.com', 'Sistema Piedra en Punto');
    $mail->addAddress($jefa_email, 'Administración');
    $mail->isHTML(true);
    $mail->Subject = "📩 Nuevo Mensaje de Contacto: {$nombre}";

    $mail->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px 10px;'>
        <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;'>
            <div style='background-color: #33614a; padding: 30px; text-align: center;'>
                <img src='{$LOGO_URL}' width='80' alt='Logo' style='margin-bottom: 15px;'>
                <h2 style='color: white; margin: 0; font-size: 20px; letter-spacing: 1px;'>Nuevo Mensaje de Contacto</h2>
            </div>
            <div style='padding: 30px;'>
                <p style='color: #555; font-size: 16px;'>Se ha recibido una nueva consulta desde el formulario de contacto principal:</p>
                <div style='background: #f9f9f9; border-left: 4px solid #f06292; padding: 20px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>👤 Nombre:</strong> {$nombre}</p>
                    <p style='margin: 5px 0;'><strong>📧 Email:</strong> {$email}</p>
                    <p style='margin: 5px 0;'><strong>📞 Teléfono:</strong> {$telefono}</p>
                    <p style='margin: 15px 0 5px 0;'><strong>💬 Mensaje:</strong><br><span style='color: #666; font-style: italic;'>\"{$mensaje}\"</span></p>
                </div>
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='https://piedraenpunto.com/dashboard3/gestion_contacto.php' style='background: #33614a; color: white; padding: 14px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block;'>Abrir Panel de Control</a>
                </div>
                <div style='margin-top: 25px; padding: 15px; border: 1px dashed #ccc; border-radius: 5px; background-color: #fffcf5;'>
                    <p style='margin: 0; font-size: 13px; color: #888;'>
                        <strong>💡 Ayuda de acceso:</strong><br>
                        Si no recuerda las credenciales de ingreso al Panel de Gestión, puede consultarlas de forma segura en el siguiente documento: 
                        <a href='URL_DE_TU_DRIVE' style='color: #f06292; font-weight: bold;'>Ver Hoja de Claves Drive</a>.
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
    // 💌 CORREO PARA EL USUARIO ($mail2)
    // ===============================================
    $mail2 = new PHPMailer(true);
    $mail2->isSMTP();
    $mail2->Host = 'smtp.gmail.com';
    $mail2->SMTPAuth = true;
    $mail2->Username = 'verodlp@piedraenpunto11.com';
    $mail2->Password = 'bgafvciimbgqwaqk'; 
    $mail2->SMTPSecure = 'tls';
    $mail2->Port = 587;
    $mail2->CharSet = 'UTF-8';

    $mail2->setFrom('contacto@piedraenpunto.com', 'Piedra en Punto');
    $mail2->addAddress($email, $nombre);
    $mail2->isHTML(true);
    $mail2->Subject = "✨ Recibimos tu consulta - Piedra en Punto";

    $mail2->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #fdfdfd; padding: 40px 10px;'>
        <div style='max-width: 600px; margin: auto; background: white; border-top: 5px solid #33614a; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
            <div style='padding: 40px; text-align: center;'>
                <img src='{$LOGO_URL}' width='90' alt='Logo' style='margin-bottom: 20px;'>
                <h1 style='color: #333; font-size: 24px; margin: 0;'>¡Hola, {$nombre}!</h1>
                <p style='color: #666; font-size: 16px; line-height: 1.6; margin-top: 15px;'>
                    Gracias por escribirnos. Hemos recibido tu mensaje y nuestro equipo se pondrá en contacto contigo a la brevedad posible.
                </p>
                <div style='margin-top: 30px;'>
                    <a href='https://piedraenpunto.com' style='background: #f06292; color: white; padding: 12px 25px; border-radius: 4px; text-decoration: none; font-weight: bold;'>Visitar nuestra web</a>
                </div>
                <div style='margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;'>
                    <p style='font-size: 14px; color: #999; margin-bottom: 15px;'>Conoce más de nosotros:</p>
                    <a href='#' style='margin:0 5px;'><img src='https://cdn-icons-png.flaticon.com/512/733/733547.png' width='24'></a>
                    <a href='https://www.instagram.com/piedraenpunto' style='text-decoration:none; margin: 0 10px;'><img src='https://cdn-icons-png.flaticon.com/512/2111/2111463.png' width='24'></a>
                    <a href='#' style='text-decoration:none; margin: 0 10px;'><img src='https://cdn-icons-png.flaticon.com/512/145/145807.png' width='24'></a>
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

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Error al enviar: " . $e->getMessage()]);
}
?>
