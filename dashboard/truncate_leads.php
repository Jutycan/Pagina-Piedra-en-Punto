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

// CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    die("Token inválido.");
}

// Conectar
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Error DB");
}

// TRUNCATE
$conn->query("TRUNCATE TABLE leads");
$conn->close();

// Regresar
header("Location: gestion_leads.php");
exit;
