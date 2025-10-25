<?php
session_start();

// ==================================
// ⚙️ CONEXIÓN A LA BASE DE DATOS
// ==================================
$host = "localhost";
$dbname = "u894610526_piedraenpunto";
$username = "u894610526_formulario_g";
$password = "Vero$2025$";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// ==================================
// 📋 CONSULTAR MENSAJES
// ==================================
$stmt = $pdo->query("SELECT * FROM contacto ORDER BY fecha_envio DESC");
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Contactos - Piedra en Punto</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            padding: 40px;
        }
        h1 {
            color: #e91e63;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th {
            background-color: #e91e63;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .estado {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 6px;
        }
        .pendiente {
            background-color: #ffe4ec;
            color: #d81b60;
        }
        .contestada {
            background-color: #e6f4ea;
            color: #388e3c;
        }
        a.btn {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            color: white;
            font-size: 14px;
        }
        .btn-verde { background-color: #4CAF50; }
        .btn-rojo { background-color: #e53935; }
        .btn-gris { background-color: #607d8b; }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .btn-large {
            padding: 10px 18px;
            font-size: 15px;
        }
        @media screen and (max-width: 768px) {
            table, th, td {
                font-size: 12px;
            }
            .btn-large {
                display: block;
                margin: 10px auto;
                width: 90%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <h1>📨 Panel de Mensajes de Contacto</h1>
        <a href="truncate_contacto.php" class="btn btn-rojo btn-large" onclick="return confirm('¿Seguro que quieres borrar todos los registros?')">🗑️ Borrar todos</a>
    </div>

    <table>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Mensaje</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>

        <?php foreach ($mensajes as $fila): ?>
            <tr>
                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                <td><?= htmlspecialchars($fila['email']) ?></td>
                <td><?= htmlspecialchars($fila['telefono'] ?: '—') ?></td>
                <td><?= nl2br(htmlspecialchars($fila['mensaje'])) ?></td>
                <td><?= $fila['fecha_envio'] ?></td>
                <td>
                    <span class="estado <?= strtolower($fila['estado']) ?>">
                        <?= $fila['estado'] ?>
                    </span>
                </td>
                <td>
                    <?php if ($fila['estado'] === 'Pendiente'): ?>
                        <a class="btn btn-verde" href="update_contacto_status.php?id=<?= $fila['id'] ?>&estado=Contestada">Marcar contestada</a>
                    <?php else: ?>
                        <a class="btn btn-gris" href="update_contacto_status.php?id=<?= $fila['id'] ?>&estado=Pendiente">Marcar pendiente</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
