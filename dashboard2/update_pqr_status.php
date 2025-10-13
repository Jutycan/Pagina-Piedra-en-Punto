<?php
// --------------------------------------------------------------------------
// Archivo: update_pqr_status.php
// Función: Recibe un ID de PQRS y un nuevo estado, y lo actualiza en la DB.
// --------------------------------------------------------------------------

// 🚨🚨🚨 CONFIGURACIÓN - ¡MODIFICA ESTO CON TUS DATOS REALES! 🚨🚨🚨
// --- Credenciales de la Base de Datos ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u894610526_P_Formulario1'); 
define('DB_PASSWORD', 'Ejercicios$2021$'); // Contraseña de la base de datos
define('DB_NAME', 'u894610526_Formulario_1_P'); 

header('Content-Type: application/json');

// 1. Validar la sesión de autenticación (Reutiliza la lógica de gestion_pqr.php)
session_start();
// La contraseña se verifica en gestion_pqr.php antes de que se pueda usar esta página.
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado. Por favor, inicia sesión.']);
    exit;
}

// 2. Conectar a la base de datos
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($link === false){
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// 3. Obtener y validar datos
$pqr_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

// Solo permitir los estados válidos
$valid_statuses = ['Pendiente', 'Contestado'];

if ($pqr_id === false || $pqr_id <= 0 || !in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Datos de solicitud o estado inválidos.']);
    mysqli_close($link);
    exit;
}

// 4. Preparar y ejecutar la consulta de actualización
$sql = "UPDATE pqrs SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $new_status, $pqr_id); // "s" por string (status), "i" por integer (id)
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Estado actualizado con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la actualización: ' . mysqli_error($link)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . mysqli_error($link)]);
}

mysqli_close($link);
?>