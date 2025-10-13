<?php
// 🚨🚨🚨 INCLUYE TUS CREDENCIALES REALES AQUÍ 🚨🚨🚨
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u894610526_P_Formulario1'); 
define('DB_PASSWORD', 'Ejercicios$2021$'); // ¡REVISA ESTO!
define('DB_NAME', 'u894610526_Formulario_1_P'); 

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($link === false){
    die("ERROR: No se pudo conectar a la base de datos.");
}

$leads = [];
$sql = "SELECT id, nombre, empresa, email, comentario, opt_in, status, email_enviado, fecha_registro FROM leads ORDER BY fecha_registro DESC";
if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $leads[] = $row;
    }
    mysqli_free_result($result);
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Leads - Piedra en Punto</title>
    <style>
        /* Estilos CSS incluidos directamente para simplificar el despliegue */
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f7f3ed; }
        h1 { color: #33614a; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background-color: #33614a; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
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
    </style>
</head>
<body>
    <h1>Dashboard de Gestión de Leads</h1>
    <p>Leads encontrados: <?php echo count($leads); ?></p>

    <button id="btn-truncate">
        🚨 Limpiar/Eliminar TODOS los Registros
    </button>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Nombre</th>
                <th>Empresa</th>
                <th>Email</th>
                <th>Comentario</th>
                <th>Info</th>
                <th>Email OK</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leads as $lead): ?>
            <tr data-lead-id="<?php echo $lead['id']; ?>">
                <td><?php echo $lead['id']; ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($lead['fecha_registro'])); ?></td>
                <td><?php echo htmlspecialchars($lead['nombre']); ?></td>
                <td><?php echo htmlspecialchars($lead['empresa']); ?></td>
                <td><?php echo htmlspecialchars($lead['email']); ?></td>
                <td><?php echo htmlspecialchars(substr($lead['comentario'], 0, 50)) . '...'; ?></td>
                <td><?php echo $lead['opt_in'] ? 'Sí' : 'No'; ?></td>
                <td><?php echo $lead['email_enviado'] ? '✅' : '❌'; ?></td>
                <td>
                    <select class="select-status" data-lead-id="<?php echo $lead['id']; ?>">
                        <option value="Pendiente" <?php echo $lead['status'] === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="Contestado" <?php echo $lead['status'] === 'Contestado' ? 'selected' : ''; ?>>Contestado</option>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        // Lógica para cambiar el estado del Lead
        document.querySelectorAll('.select-status').forEach(select => {
            select.addEventListener('change', async function() {
                const leadId = this.dataset.leadId;
                const newStatus = this.value;
                const row = document.querySelector(`tr[data-lead-id="${leadId}"]`);

                if (confirm(`¿Segura que quieres cambiar el estado del Lead #${leadId} a ${newStatus}?`)) {
                    
                    try {
                        const response = await fetch('update_status.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id=${leadId}&status=${newStatus}`
                        });
                        
                        const result = await response.json();

                        if (result.success) {
                            alert(`¡Éxito! ${result.message}`);
                            
                            // 5. Actualizar la apariencia de la fila inmediatamente (Opcional: puedes recargar si lo prefieres)
                            // Si quieres recargar para simplificar: window.location.reload();

                        } else {
                            alert('Error al actualizar: ' + result.message);
                            // Revertir el select si falla
                            this.value = this.dataset.currentStatus; 
                        }
                    } catch (error) {
                        alert('Error de conexión con el servidor.');
                        // Revertir el select si falla
                        this.value = this.dataset.currentStatus;
                    }
                } else {
                    // Si cancela, revertir la selección
                    this.value = this.dataset.currentStatus;
                }
                // Guardar el estado actual después de una actualización exitosa o al cargar
                this.dataset.currentStatus = this.value; // Ya se actualizó this.value
            });
            // Inicializar el estado actual al cargar
            select.dataset.currentStatus = select.value;
        });


        // Lógica para el botón de TRUNCATE (Limpiar todos los registros)
        document.getElementById('btn-truncate').addEventListener('click', async function() {
            // Pedir doble confirmación para evitar accidentes
            if (!confirm('🚨 ATENCIÓN: Esta acción ELIMINARÁ PERMANENTEMENTE TODOS los leads. ¿Estás absolutamente segura?')) {
                return; 
            }

            if (!confirm('❌ CONFIRMACIÓN FINAL: ¿De verdad quieres borrar TODO? Esta acción es IRREVERSIBLE.')) {
                return;
            }

            try {
                // Llamar al script PHP que vacía la tabla
                const response = await fetch('truncate_leads.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '' // No necesitamos enviar datos
                });
                
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    
                    // Recargar la página para mostrar la tabla vacía
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