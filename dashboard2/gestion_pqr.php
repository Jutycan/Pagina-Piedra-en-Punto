<?php
session_start();
$DB_HOST = "localhost";
$DB_USER = "u894610526_formulario_g";
$DB_PASS = "Vero$2025$"; // <-- pega tu contraseña
$DB_NAME = "u894610526_piedraenpunto";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Error DB: " . $conn->connect_error);

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
$csrf = $_SESSION['csrf_token'];

$result = $conn->query("SELECT id, clasificacion, nombres, fecha, motivo, email, telefono, mensaje, estado, created_at FROM pqrs ORDER BY created_at DESC LIMIT 200");
function esc($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel PQRS — Piedra en Punto</title>
<style>
:root{--rosa:#f06292;--verde:#33614a;--bg:#f4f6f8;--card:#fff}
body{font-family:Inter,Arial,sans-serif;margin:0;background:var(--bg)}
.header{background:linear-gradient(90deg,var(--rosa),#e86aa5);color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between}
.container{max-width:1100px;margin:20px auto;padding:0 20px}
.card{background:var(--card);border-radius:12px;padding:18px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:10px;border-bottom:1px solid #eef1f4;text-align:left;font-size:14px}
.btn{padding:7px 12px;border-radius:6px;border:none;cursor:pointer}
.btn-primary{background:var(--verde);color:#fff}
.btn-danger{background:#c62828;color:#fff}
.badge{padding:6px 10px;border-radius:12px;font-size:13px}
.badge-pend{background:#fff0f6;color:var(--rosa)}
.badge-cont{background:#ecf9f3;color:var(--verde)}
.toast{position:fixed;top:20px;right:20px;background:#fff;padding:12px 20px;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.1);opacity:0;transform:translateY(-10px);transition:.3s}
.toast.show{opacity:1;transform:translateY(0)}
</style>
</head>
<body>
<div class="header">
    <div style="display:flex;align-items:center;gap:12px">
        <img src="/imagenes/general/Icon Piedra en Punto.png" alt="logo" style="height:42px">
        <div><strong>Panel PQRS</strong><div style="font-size:13px">Gestiona PQRS recibidos</div></div>
    </div>
    <a href="/" style="background:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;color:#333">Ver sitio</a>
</div>

<div class="container">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0">PQRS recientes</h3>
            <button id="deleteAll" class="btn btn-danger">🗑 Borrar todos</button>
        </div>

        <table class="table">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Motivo</th><th>Email</th><th>Tel</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody id="tablaBody">
                <?php while($r = $result->fetch_assoc()): ?>
                    <tr id="row<?= $r['id'] ?>">
                        <td><?= esc($r['id']) ?></td>
                        <td><?= esc($r['nombres']) ?></td>
                        <td><?= esc($r['motivo']) ?></td>
                        <td><a href="mailto:<?= esc($r['email']) ?>"><?= esc($r['email']) ?></a></td>
                        <td><?= esc($r['telefono']) ?></td>
                        <td><span class="badge <?= $r['estado']=='contestado'?'badge-cont':'badge-pend' ?>"><?= esc($r['estado']) ?></span></td>
                        <td>
                            <button class="btn btn-primary" onclick="toggleEstado(<?= $r['id'] ?>,'<?= $r['estado']=='pendiente'?'contestado':'pendiente' ?>')">
                                <?= $r['estado']=='pendiente'?'Marcar contestado':'Marcar pendiente' ?>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
const csrf = "<?= $csrf ?>";
function showToast(msg,color='#333'){const t=document.getElementById('toast');t.textContent=msg;t.style.color=color;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2500);}

async function toggleEstado(id,nuevo){
    const fd=new FormData();fd.append('id',id);fd.append('new_status',nuevo);fd.append('csrf_token',csrf);
    const r = await fetch('update_pqr_status.php',{method:'POST',body:fd});
    if(r.ok){
        const row=document.getElementById('row'+id);
        if(row){
            const badge=row.querySelector('.badge');
            const btn=row.querySelector('button');
            badge.textContent=nuevo;
            if(nuevo==='contestado'){badge.className='badge badge-cont';btn.textContent='Marcar pendiente';}
            else {badge.className='badge badge-pend';btn.textContent='Marcar contestado';}
        }
        showToast('✅ Estado actualizado','#33614a');
    } else showToast('❌ Error al actualizar','#c62828');
}

document.getElementById('deleteAll').addEventListener('click', async ()=>{
    if(!confirm('¿Borrar todos los PQRS?')) return;
    const fd=new FormData();fd.append('csrf_token',csrf);
    const r = await fetch('truncate_pqr.php',{method:'POST',body:fd});
    if(r.ok){document.getElementById('tablaBody').innerHTML='';showToast('🗑️ Todos eliminados','#c62828');}
    else showToast('❌ Error al eliminar','#c62828');
});
</script>
</body>
</html>
<?php $conn->close(); ?>

