<?php
// ==========================
// CONEXIÓN A LA BASE DE DATOS
// ==========================
$host = "localhost";
$dbname = "u894610526_piedraenpunto";
$username = "u894610526_formulario_g";
$password = "Vero$2025$";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

// ==========================
// CONSULTAR LOS CONTACTOS
// ==========================
$stmt = $pdo->query("SELECT * FROM contacto ORDER BY fecha_envio DESC");
$contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Contactos | Piedra en Punto</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafc;
            color: #333;
            margin: 0;
            padding: 0;
        }
        h1 {
            background: linear-gradient(90deg, #ff007f, #ff80bf);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border-bottom: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f1f1f1;
            color: #333;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        button {
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-status {
            background-color: #4CAF50;
            color: white;
        }
        .btn-delete {
            background-color: #e53935;
            color: white;
        }
        .actions {
            text-align: center;
            margin-top: 25px;
        }
        .status-pendiente {
            color: #e53935;
            font-weight: 600;
        }
        .status-contestada {
            color: #4CAF50;
            font-weight: 600;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            tr {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 10px;
            }
            td {
                padding: 8px 10px;
                border: none;
            }
            th {
                display: none;
            }
            .actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <h1>📋 Panel de Contactos - Piedra en Punto</h1>
    <div class="container">
        <table id="tabla-contactos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Mensaje</th>
                    <th>Estado</th>
                    <th>Fecha Envío</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contactos as $contacto): ?>
                <tr data-id="<?= $contacto['id'] ?>">
                    <td><?= $contacto['id'] ?></td>
                    <td><?= htmlspecialchars($contacto['nombre']) ?></td>
                    <td><?= htmlspecialchars($contacto['email']) ?></td>
                    <td><?= htmlspecialchars($contacto['telefono'] ?: '-') ?></td>
                    <td><?= nl2br(htmlspecialchars($contacto['mensaje'])) ?></td>
                    <td class="<?= $contacto['estado'] == 'Pendiente' ? 'status-pendiente' : 'status-contestada' ?>">
                        <?= $contacto['estado'] ?>
                    </td>
                    <td><?= $contacto['fecha_envio'] ?></td>
                    <td>
                        <button class="btn-status" onclick="cambiarEstado(<?= $contacto['id'] ?>)">Cambiar estado</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="actions">
            <button class="btn-delete" onclick="borrarRegistros()">🗑 Borrar todos los registros</button>
        </div>
    </div>

    <script>
        // Cambiar estado con AJAX
        function cambiarEstado(id) {
            if (!confirm("¿Deseas cambiar el estado a 'Contestada'?")) return;

            fetch('update_contacto_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Estado actualizado correctamente ✅");
                    location.reload();
                } else {
                    alert("Error: " + data.error);
                }
            });
        }

        // Borrar todos los registros con AJAX
        function borrarRegistros() {
            if (!confirm("⚠️ Esto eliminará todos los registros de contacto. ¿Estás segura?")) return;

            fetch('truncate_contacto.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Todos los registros fueron eliminados ✅");
                    location.reload();
                } else {
                    alert("Error: " + data.error);
                }
            });
        }
    </script>
</body>
</html>

