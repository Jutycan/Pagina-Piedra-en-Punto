<?php
// Script para ELIMINAR TODOS los registros de la tabla 'leads'

// 🚨🚨🚨 INCLUYE TUS CREDENCIALES REALES AQUÍ 🚨🚨🚨
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u894610526_P_Formulario1'); 
define('DB_PASSWORD', 'Ejercicios$2021$'); // ¡Asegúrate de que sean las reales!
define('DB_NAME', 'u894610526_Formulario_1_P'); 

header('Content-Type: application/json');

// 1. Validar que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// 2. Conexión a DB
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($link === false){
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// 3. Comando SQL para vaciar la tabla COMPLETAMENTE
// TRUNCATE TABLE es mucho más rápido que DELETE FROM para vaciar tablas.
$sql = "TRUNCATE TABLE leads"; 

if (mysqli_query($link, $sql)) {
    // Éxito: Retorna una respuesta de éxito a JavaScript
    echo json_encode([
        'success' => true, 
        'message' => '✅ ¡Tabla de Leads limpiada con éxito! La tabla ahora está vacía.'
    ]);
} else {
    // Error: Retorna un mensaje de error
    echo json_encode([
        'success' => false, 
        'message' => '❌ Error al vaciar la tabla: ' . mysqli_error($link)
    ]);
}

mysqli_close($link);
?>