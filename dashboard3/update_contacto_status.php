<?php
header('Content-Type: application/json');

$host = "localhost";
$dbname = "u894610526_piedraenpunto";
$username = "u894610526_formulario_g";
$password = "Vero$2025$";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(["success" => false, "error" => "ID no proporcionado"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE contacto SET estado = 'Contestada' WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

