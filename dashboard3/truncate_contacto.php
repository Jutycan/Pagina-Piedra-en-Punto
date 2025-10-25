<?php
$host = "localhost";
$dbname = "u894610526_piedraenpunto";
$username = "u894610526_formulario_g";
$password = "Vero$2025$";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("TRUNCATE TABLE contacto");

    header("Location: gestion_contacto.php");
    exit;
} catch (PDOException $e) {
    echo "❌ Error al borrar registros: " . $e->getMessage();
}
?>
