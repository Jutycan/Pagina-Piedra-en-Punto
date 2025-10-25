<?php
$host = "localhost";
$dbname = "u894610526_piedraenpunto";
$username = "u894610526_formulario_g";
$password = "Vero$2025$";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id'], $_GET['estado'])) {
        $id = intval($_GET['id']);
        $estado = $_GET['estado'] === 'Contestada' ? 'Contestada' : 'Pendiente';

        $stmt = $pdo->prepare("UPDATE contacto SET estado = :estado WHERE id = :id");
        $stmt->execute([':estado' => $estado, ':id' => $id]);

        header("Location: gestion_contacto.php");
        exit;
    } else {
        echo "❌ Parámetros inválidos.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
