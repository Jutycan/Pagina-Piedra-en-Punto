<?php
session_start();

// -----------------
// CONFIG DB - pon tu contraseña localmente
// -----------------
$DB_HOST = "localhost";
$DB_USER = "u894610526_formulario_g";
$DB_PASS = "Vero$2025$"; // <-- Pega tu contraseña aquí (NO la compartas)
$DB_NAME = "u894610526_piedraenpunto";

// Conectar
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Error de conexión DB: " . $conn->connect_error);
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf = $_SESSION['csrf_token'];

// Búsqueda (opcional)
$search = trim($_GET['q'] ?? '');

// Preparar consulta (limitada a 200 registros para no cargar)
$sql = "SELECT id, nombre, empresa, email, comentario, recibir_info, politica_datos, estado, created_at 
        FROM leads";
$params = [];
if ($search !== '') {
    $sql .= " WHERE nombre LIKE ? OR email LIKE ? OR empresa LIKE ? OR comentario LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}
$sql .= " ORDER BY created_at DESC LIMIT 200";

$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel — Leads | Piedra en Punto</title>
<style>
/* Minimal styles: copia/ajusta según tu theme */
:root{
    --rosa:#f06292;
    --verde:#33614a;
    --bg:#f4f6f8;
    --card:#ffffff;
}
*{box-sizing:border-box}
body{font-family:Inter,Arial,Helvetica,sans-serif;margin:0;background:var(--bg);color:#111}
.header{background:linear-gradient(90deg,var(--rosa),#e86aa5); color:white;padding:18px 24px;display:flex;align-items:center;justify-content:space-between}
.header .brand{display:flex;align-items:center;gap:12px}
.header img{height:44px}
.container{max-width:1100px;margin:26px auto;padding:0 20px}
.card{background:var(--card);border-radius:10px;padding:18px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:10px 12px;border-bottom:1px solid #eef1f4;text-align:left;font-size:14px}
.table th{background:#fbfcfd;color:#333;font-weight:600}
.btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;border:none;cursor:pointer;font-size:13px}
.btn-primary{background:var(--verde);color:#fff}
.btn-danger{background:#c62828;color:#fff}
.btn-muted{background:#eef3f0;color:#333}
.small{font-size:13px;color:#666}
.actions form{display:inline-block;margin-right:6px}
.search{display:flex;gap:8px;align-items:center}
.notice{margin-top:12px;padding:10px;border-radius:8px;background:#fff3cd;color:#856404}
.footer{margin-top:18px;font-size:13px;color:#666}
.badge{display:inline-block;padding:6px 8px;border-radius:12px;font-size:13px}
.badge-pend{background:#fff0f6;color:var(--rosa);border:1px solid rgba(240,100,150,0.12)}
.badge-cont{background:#ecf9f3;color:var(--verde);border:1px solid rgba(50,120,90,0.08)}
.search input{padding:8px;border:1px solid #ddd;border-radius:6px}
</style>
</head>
<body>
<div class="header">
    <div class="brand">
        <img src="/imagenes/general/Icon Piedra en Punto.png" alt="Logo Piedra en Punto">
        <div>
            <div style="font-weight:700;font-size:18px">Panel de Leads — Piedra en Punto</div>
            <div class="small">Gestiona los registros recibidos desde el formulario</div>
        </div>
    </div>
    <div>
        <a href="/" class="btn btn-muted">Ver sitio</a>
    </div>
</div>

<div class="container">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
            <h2 style="margin:0">Registros (últimos 200)</h2>
            <div style="display:flex;gap:8px;align-items:center">
                <form method="get" class="search" action="">
                    <input type="text" name="q" placeholder="Buscar por nombre, email, empresa..." value="<?= esc($search) ?>">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </form>
                <form method="post" action="truncate_leads.php" onsubmit="return confirm('¿Seguro borrar todos los registros? Esta acción es irreversible.');">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <button class="btn btn-danger" type="submit">Borrar todos</button>
                </form>
            </div>
        </div>

        <table class="table" aria-describedby="listado">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Empresa</th>
                    <th>Email</th>
                    <th>Comentario</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= esc($row['id']) ?></td>
                        <td><?= esc($row['nombre']) ?></td>
                        <td><?= esc($row['empresa']) ?></td>
                        <td><a href="mailto:<?= esc($row['email']) ?>"><?= esc($row['email']) ?></a></td>
                        <td><?= esc(mb_strimwidth($row['comentario'], 0, 70, '...')) ?></td>
                        <td>
                            <?php if ($row['estado'] === 'contestado'): ?>
                                <span class="badge badge-cont">Contestado</span>
                            <?php else: ?>
                                <span class="badge badge-pend">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($row['created_at']) ?></td>
                        <td class="actions">
                            <?php if ($row['estado'] === 'pendiente'): ?>
                                <form method="post" action="update_status.php" style="display:inline">
                                    <input type="hidden" name="id" value="<?= esc($row['id']) ?>">
                                    <input type="hidden" name="new_status" value="contestado">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <button class="btn btn-primary" type="submit">Marcar contestado</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="update_status.php" style="display:inline">
                                    <input type="hidden" name="id" value="<?= esc($row['id']) ?>">
                                    <input type="hidden" name="new_status" value="pendiente">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <button class="btn btn-muted" type="submit">Marcar pendiente</button>
                                </form>
                            <?php endif; ?>

                            <button class="btn btn-muted" onclick="verDetalle(<?= esc($row['id']) ?>)">Ver</button>

                            <form method="post" action="truncate_leads.php" style="display:none">
                                <!-- placeholder -->
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="footer">
            <span class="small">Panel protegido por Hostinger — acceso restringido.</span>
        </div>
    </div>
</div>

<script>
// Función simple para mostrar detalle (puedes mejorar con modal)
function verDetalle(id){
    // Hacer fetch para traer detalles (opcional). Por ahora simple alerta con row data desde DOM no disponible.
    alert('Para ver detalles completos usa phpMyAdmin o implementa el modal de detalle.');
}
</script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
