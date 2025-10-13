<?php
// 🚨🚨🚨 CONFIGURACIÓN - ¡MODIFICA ESTO CON TUS DATOS REALES! 🚨🚨🚨
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u894610526_P_Formulario1'); 
define('DB_PASSWORD', 'Ejercicios$2021$'); // Contraseña de la base de datos
define('DB_NAME', 'u894610526_Formulario_1_P'); 

// --- Lógica de Autenticación de la Jefa ---
// NOTA: Para mantenerlo simple, usaremos una contraseña simple. 
// En un sistema real, usarías un sistema de login más robusto.
$PASSWORD_JEFA = "Piedraenpunto2$2025$"; // ⚠️ ¡CAMBIA ESTA CONTRASEÑA!
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === $PASSWORD_JEFA) {
            $_SESSION['authenticated'] = true;
        } else {
            $error_message = "Contraseña incorrecta. Inténtalo de nuevo.";
        }
    }
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Mostrar formulario de login si no está autenticada
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Restringido - Gestión PQRS</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); text-align: center; max-width: 400px; width: 90%; }
        h1 { color: #EC0868; margin-bottom: 20px; font-size: 1.5rem; }
        input[type="password"] { width: 100%; padding: 12px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { background-color: #33614a; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background-color 0.3s; }
        button:hover { background-color: #2e5743; }
        .error { color: #d32f2f; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Acceso Restringido al Dashboard PQRS</h1>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Ingresa la contraseña" required>
            <button type="submit">Acceder</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}
// --- FIN Lógica de Autenticación ---

// Conexión a la DB y carga de datos
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($link === false){
    die("ERROR: No se pudo conectar a la base de datos.");
}

$pqrs = [];
$sql = "SELECT * FROM pqrs ORDER BY fecha_registro DESC"; 
if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pqrs[] = $row;
    }
    mysqli_free_result($result);
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de PQRS - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #e6f1f0; }
        h1 { color: #33614a; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; font-size: 14px; vertical-align: top; }
        th { background-color: #33614a; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        /* Estilos de Estado */
        .status-pendiente { background-color: #ffe0b2; color: #e65100; font-weight: bold; padding: 5px; border-radius: 3px; }
        .status-contestado { background-color: #c8e6c9; color: #2e7d32; font-weight: bold; padding: 5px; border-radius: 3px; }
        .select-status { padding: 8px; border-radius: 5px; border: 1px solid #ccc; cursor: pointer; }
        #btn-truncate {
            background-color: #d32f2f; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            margin-bottom: 20px;
            font-weight: bold;
        }
        .data-actions { display: flex; justify-content: space-between; align-items: center; }
        /* Para mostrar todo el mensaje al pasar el mouse */
        .message-cell { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .message-cell:hover { white-space: normal; overflow: visible; }
    </style>
</head>
<body>
    <h1>Dashboard de Gestión de PQRS</h1>
    <div class="data-actions">
        <p>PQRS encontradas: <strong><?php echo count($pqrs); ?></strong></p>

        <!-- BOTÓN DE ELIMINAR REGISTROS (Llamará a truncate_pqr.php) -->
        <button id="btn-truncate">
            🚨 Limpiar/Eliminar TODOS los Registros PQRS
        </button>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha Reg.</th>
                <th>Clasificación</th>
                <th>Motivo</th>
                <th>Nombre</th>
                <th>Identif.</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Mensaje</th>
                <th>Email OK</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pqrs as $pqr): ?>
            <tr data-pqr-id="<?php echo $pqr['id']; ?>">
                <td><?php echo $pqr['id']; ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($pqr['fecha_registro'])); ?></td>
                <td><?php echo htmlspecialchars($pqr['clasificacion']); ?></td>
                <td><?php echo htmlspecialchars($pqr['motivo']); ?></td>
                <td><?php echo htmlspecialchars($pqr['nombres']); ?></td>
                <td><?php echo htmlspecialchars($pqr['identificacion']); ?></td>
                <td><?php echo htmlspecialchars($pqr['email']); ?></td>
                <td><?php echo htmlspecialchars($pqr['telefono']); ?></td>
                <td class="message-cell" title="<?php echo htmlspecialchars($pqr['mensaje']); ?>"><?php echo htmlspecialchars(substr($pqr['mensaje'], 0, 40)) . '...'; ?></td>
                <td><?php echo $pqr['email_enviado'] ? '✅' : '❌'; ?></td>
                <td>
                    <!-- Dropdown para cambiar el estado -->
                    <select class="select-status" data-pqr-id="<?php echo $pqr['id']; ?>">
                        <option value="Pendiente" <?php echo $pqr['status'] === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="Contestado" <?php echo $pqr['status'] === 'Contestado' ? 'selected' : ''; ?>>Contestado</option>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        // Lógica para cambiar el estado del PQRS
        document.querySelectorAll('.select-status').forEach(select => {
            // Inicializar el estado actual
            select.dataset.currentStatus = select.value; 

            select.addEventListener('change', async function() {
                const pqrId = this.dataset.pqrId;
                const newStatus = this.value;

                if (confirm(`¿Segura que quieres cambiar el estado del PQRS #${pqrId} a ${newStatus}?`)) {
                    
                    try {
                        // Llama al script que actualiza el estado
                        const response = await fetch('update_pqr_status.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id=${pqrId}&status=${newStatus}`
                        });
                        
                        const result = await response.json();

                        if (result.success) {
                            alert(`¡Éxito! Estado actualizado a ${newStatus}.`);
                            this.dataset.currentStatus = newStatus; // Actualizar estado guardado

                        } else {
                            alert('Error al actualizar: ' + result.message);
                            this.value = this.dataset.currentStatus; // Revertir
                        }
                    } catch (error) {
                        alert('Error de conexión con el servidor.');
                        this.value = this.dataset.currentStatus; // Revertir
                    }
                } else {
                    this.value = this.dataset.currentStatus; // Revertir
                }
            });
        });


        // Lógica para el botón de TRUNCATE (Limpiar todos los registros)
        document.getElementById('btn-truncate').addEventListener('click', async function() {
            // Pedir doble confirmación
            if (!confirm('🚨 ATENCIÓN: Esta acción ELIMINARÁ PERMANENTEMENTE TODOS los registros PQRS. ¿Estás absolutamente segura?')) {
                return; 
            }

            if (!confirm('❌ CONFIRMACIÓN FINAL: ¿De verdad quieres borrar TODO? Esta acción es IRREVERSIBLE.')) {
                return;
            }

            try {
                // Llamar al script PHP que vacía la tabla
                const response = await fetch('truncate_pqr.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: ''
                });
                
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload(); 
                } else {
                    alert('Error al intentar limpiar la tabla: ' + result.message);
                }
            } catch (error) {
                alert('Error de conexión con el script de limpieza del servidor.');
                console.error(error);
            }
        });
    </script>
</body>
</html>