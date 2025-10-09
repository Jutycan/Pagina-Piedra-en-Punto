<?php
// Script para actualizar el estado del lead

// 🚨🚨🚨 INCLUYE TUS CREDENCIALES REALES AQUÍ 🚨🚨🚨
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u894610526_P_Formulario1'); 
define('DB_PASSWORD', 'Ejercicios$2021$'); 
define('DB_NAME', 'u894610526_Formulario_1_P'); 

header('Content-Type: application/json');

// 1. Validar que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$lead_id = $_POST['id'] ?? null;
$new_status = $_POST['status'] ?? null;

// 2. Validar datos
if (empty($lead_id) || !in_array($new_status, ['Pendiente', 'Contestado'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

// 3. Conexión a DB
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($link === false){
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// 4. Actualizar el estado
$sql = "UPDATE leads SET status = ? WHERE id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "si", $new_status, $lead_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Estado actualizado a ' . $new_status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la actualización: ' . mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta.']);
}

mysqli_close($link);
?>