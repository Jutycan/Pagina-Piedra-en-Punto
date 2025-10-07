<?php
// db_config.php - Archivo de configuración y conexión a la Base de Datos

// 🚨 1. DEFINICIÓN DE CONSTANTES (Asegúrate de reemplazar estos valores)
define('DB_SERVER', 'localhost'); // El servidor de Hostinger es local (localhost)
define('DB_USERNAME', 'u894610526_P_Formulario1');    // 🚨 REEMPLAZA ESTO con tu usuario real
define('DB_PASSWORD', 'Ejercicios$2021$'); // 🚨 REEMPLAZA ESTO con tu contraseña real
define('DB_NAME', 'u894610526_Formulario_1_P');        // 🚨 REEMPLAZA ESTO con tu nombre de BD real

// 2. INTENTO DE CONEXIÓN A MySQL
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// 3. VERIFICACIÓN DE LA CONEXIÓN
if($link === false){
    // Si la conexión falla, se envía una respuesta de error al JavaScript
    http_response_code(500); // Código de error del servidor
    die(json_encode([
        'success' => false, 
        'message' => 'Error de conexión con la base de datos (PHP). Por favor, revisa db_config.php'
    ]));
}

// Si la conexión es exitosa, el script simplemente termina aquí y la variable $link está disponible para otros archivos.
?>