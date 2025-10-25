<?php
session_start();
$DB_HOST = "localhost";
$DB_USER = "u894610526_formulario_g";
$DB_PASS = "Vero$2025$";
$DB_NAME = "u894610526_piedraenpunto";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { http_response_code(403); echo 'token'; exit; }

$id = intval($_POST['id'] ?? 0);
$new_status = ($_POST['new_status']==='contestado') ? 'contestado' : 'pendiente';
if ($id <= 0) { http_response_code(400); exit; }

$conn = new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME); if ($conn->connect_error) { http_response_code(500); exit; }
$stmt = $conn->prepare("UPDATE pqrs SET estado = ? WHERE id = ?"); $stmt->bind_param("si",$new_status,$id); $stmt->execute(); $stmt->close(); $conn->close();
http_response_code(200);
