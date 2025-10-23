<?php
session_start();

// CONFIG DB
$DB_HOST = "localhost";
$DB_USER = "u894610526_formulario_g";
$DB_PASS = "Vero$2025$"; // <-- tu contraseña
$DB_NAME = "u894610526_piedraenpunto";

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: gestion_leads.php");
    exit;
}

// CSRF simple
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    die("Token inválido.");
}

$id = intval($_POST['id'] ?? 0);
$new_status = ($_POST['new_status'] === 'contestado') ? 'contestado' : 'pendiente';

if ($id <= 0) {
    header("Location: gestion_leads.php");
    exit;
}

// Conectar DB y actualizar
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Error DB");
}

$stmt = $conn->prepare("UPDATE leads SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $id);
$stmt->execute();
$stmt->close();
$conn->close();

// Redirigir de vuelta con un mensaje corto (podrías mostrar flash messages)
header("Location: gestion_leads.php");
exit;
