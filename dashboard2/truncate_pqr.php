<?php
// --------------------------------------------------------------------------
// Archivo: truncate_pqr.php
// Función: Elimina PERMANENTEMENTE todos los registros de la tabla 'pqrs'.
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
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado. Por favor, inicia sesión en el dashboard.']);
    exit;
}

// 2. Conectar a la base de datos
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($link === false){
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// 3. Ejecutar la sentencia TRUNCATE TABLE
// TRUNCATE TABLE es la forma más rápida de eliminar todos los registros y reiniciar el ID.
$sql = "TRUNCATE TABLE pqrs"; 

if (mysqli_query($link, $sql)) {
    echo json_encode([
        'success' => true, 
        'message' => '✅ ¡Éxito! Todos los registros PQRS han sido eliminados permanentemente.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => '❌ Error al intentar vaciar la tabla: ' . mysqli_error($link)
    ]);
}

mysqli_close($link);
?>