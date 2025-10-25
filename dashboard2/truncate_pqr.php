<?php
session_start();
$DB_HOST = "localhost";
$DB_USER = "u894610526_formulario_g";
$DB_PASS = "Vero$2025$";
$DB_NAME = "u894610526_piedraenpunto";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: gestion_pqr.php"); exit; }
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { die('Token inválido.'); }

$conn = new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if ($conn->connect_error) die("Error DB");
$conn->query("TRUNCATE TABLE pqrs");
$conn->close();
header("Location: gestion_pqr.php");
exit;
