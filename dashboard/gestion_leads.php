<?php
session_start();

// ----------------- CONFIG DB -----------------
$DB_HOST = "localhost";
$DB_USER = "u894610526_formulario_g";
$DB_PASS = "DVero$2025$"; // <-- tu contraseña real
$DB_NAME = "u894610526_piedraenpunto";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Error DB: " . $conn->connect_error);

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
$csrf = $_SESSION['csrf_token'];

$result = $conn->query("SELECT id, nombre, empresa, email, comentario, estado, created_at FROM leads ORDER BY created_at DESC LIMIT 200");
function esc($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel AJAX — Piedra en Punto</title>
<style>
:root{
    --rosa:#f06292;
    --verde:#33614a;
    --bg:#f4f6f8;
    --card:#fff;
}
body{font-family:Inter,Arial,sans-serif;margin:0;background:var(--bg);color:#111}
.header{background:linear-gradient(90deg,var(--rosa),#e86aa5);color:#fff;padding:18px 24px;display:flex;align-items:center;justify-content:space-between}
.header img{height:44px;margin-right:10px}
.container{max-width:1100px;margin:24px auto;padding:0 20px}
.card{background:var(--card);border-radius:12px;padding:18px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:10px;border-bottom:1px solid #eef1f4;text-align:left;font-size:14px}
.table th{background:#fbfcfd;color:#333;font-weight:600}
.btn{padding:7px 12px;border:none;border-radius:6px;cursor:pointer;font-size:13px}
.btn-primary{background:var(--verde);color:#fff}
.btn-danger{background:#c62828;color:#fff}
.btn-muted{background:#e8e8e8;color:#333}
.badge{display:inline-block;padding:6px 8px;border-radius:12px;font-size:13px}
.badge-pend{background:#fff0f6;color:var(--rosa)}
.badge-cont{background:#ecf9f3;color:var(--verde)}
.toast{position:fixed;top:20px;right:20px;background:#fff;padding:12px 20px;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.1);opacity:0;transform:translateY(-10px);transition:.3s}
.toast.show{opacity:1;transform:translateY(0)}
</style>
</head>
<body>
<div class="header">
    <div style="display:flex;align-items:center">
        <img src="/imagenes/general/Icon Piedra en Punto.png" alt="logo">
        <div>
            <h2 style="margin:0;font-size:18px;">Panel de Leads</h2>
            <p style="margin:0;font-size:13px;">Versión interactiva (AJAX)</p>
        </div>
    </div>
    <a href="/" class="btn btn-muted">Ir al sitio</a>
</div>

<div class="container">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <h2 style="margin:0">Registros recientes</h2>
            <button id="deleteAll" class="btn btn-danger">🗑️ Borrar todos</button>
        </div>

        <table class="table">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Empresa</th><th>Email</th><th>Comentario</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody id="tablaBody">
                <?php while($row=$result->fetch_assoc()): ?>
                    <tr id="row<?= $row['id'] ?>">
                        <td><?= esc($row['id']) ?></td>
                        <td><?= esc($row['nombre']) ?></td>
                        <td><?= esc($row['empresa']) ?></td>
                        <td><?= esc($row['email']) ?></td>
                        <td><?= esc(mb_strimwidth($row['comentario'],0,50,'...')) ?></td>
                        <td><span class="badge <?= $row['estado']=='contestado'?'badge-cont':'badge-pend' ?>"><?= esc($row['estado']) ?></span></td>
                        <td>
                            <button class="btn btn-primary" onclick="toggleEstado(<?= $row['id'] ?>, '<?= $row['estado']=='pendiente'?'contestado':'pendiente' ?>')">
                                <?= $row['estado']=='pendiente'?'Marcar contestado':'Marcar pendiente' ?>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const csrf = "<?= $csrf ?>";

function showToast(msg, color="#333") {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.color = color;
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),2500);
}

async function toggleEstado(id, nuevoEstado){
    const formData = new FormData();
    formData.append('id', id);
    formData.append('new_status', nuevoEstado);
    formData.append('csrf_token', csrf);

    const resp = await fetch('update_status.php', { method:'POST', body:formData });
    if(resp.ok){
        const fila = document.getElementById('row'+id);
        if(fila){
            const badge = fila.querySelector('.badge');
            const btn = fila.querySelector('button');
            badge.textContent = nuevoEstado;
            if(nuevoEstado==='contestado'){
                badge.className = 'badge badge-cont';
                btn.textContent = 'Marcar pendiente';
            }else{
                badge.className = 'badge badge-pend';
                btn.textContent = 'Marcar contestado';
            }
        } 
        showToast('✅ Estado actualizado correctamente', '#33614a');
    }else{
        showToast('❌ Error al actualizar', '#c62828');
    }
}

document.getElementById('deleteAll').addEventListener('click', async ()=>{
    if(!confirm('¿Seguro que deseas borrar todos los registros?')) return;
    const formData = new FormData();
    formData.append('csrf_token', csrf);
    const resp = await fetch('truncate_leads.php', { method:'POST', body:formData });
    if(resp.ok){
        document.getElementById('tablaBody').innerHTML = '';
        showToast('🗑️ Todos los registros eliminados', '#c62828');
    }else{
        showToast('❌ Error al eliminar', '#c62828');
    }
});
</script>
</body>
</html>
<?php $conn->close(); ?>

