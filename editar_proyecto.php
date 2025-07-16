<?php
session_start();

if (!isset($_GET['id'])) {
    die("ID de proyecto no especificado.");
}
if (!isset($_SESSION['user'])) {
    $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}
if ($_SESSION['rol'] !== 'admin') {
    header('Location: no_autorizado.php');
    exit();
}
include 'db/conn.php';
include_once('classes/Projects.class.php');
$id = intval($_GET['id']);
try {
    $proyecto = Project::getProject($id, $conexion);
} catch (Exception $e) {
    die($e->getMessage());
}

// Formatear fecha para <input type=date>
$fechaVal = ($proyecto['fecha'] === '0000-00-00') ? '' : $proyecto['fecha'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proyecto</title>
    <link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #e9ecef; min-height: 100vh; display: flex; flex-direction: column; }
        .container-main { flex: 1; width: 90%; max-width: 1200px; margin: 30px auto; }
        .card-registro { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: #007bff; color: white; padding: 25px 40px; border-bottom: 3px solid #0056b3; }
        .card-header h2 { margin: 0; font-size: 28px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; }
        .card-body { padding: 40px; }
        .form-group { margin-bottom: 25px; }
        .form-label { display: block; font-weight: 600; color: #333; margin-bottom: 8px; font-size: 16px; }
        .form-control, .form-select { width:100%; padding:12px 20px; border:2px solid #007bff; border-radius:25px; font-size:16px; transition:all .3s ease; background-color:rgba(0,123,255,0.05); }
        .form-control:focus, .form-select:focus { border-color:#0056b3; box-shadow:0 0 10px rgba(0,123,255,0.2); }
        small.rule { display:none; font-size:12px; color:#555; margin-top:5px; }
        textarea.form-control { height:150px; resize:vertical; }
        .button-container { display:flex; flex-direction:column; gap:15px; margin-top:30px; }
        .btn-primary { background-color:#007bff!important; border:none; padding:15px 30px!important; border-radius:25px!important; font-size:16px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
        .btn-primary:hover { background-color:#0056b3!important; transform:translateY(-2px); }
        .btn-secondary { background-color:#6c757d!important; border-radius:25px!important; }
        .is-invalid { border-color:#dc3545 !important; }
        .invalid-feedback { display:none; font-size:12px; color:#dc3545; }
        input.is-invalid + .invalid-feedback, textarea.is-invalid + .invalid-feedback { display:block; }
    </style>
</head>
<body>
<div class="container-main">
    <div class="card-registro">
        <div class="card-header">
            <h2>Editar Proyecto</h2>
        </div>
        <div class="card-body">
            <form id="formEdit" action="endpoints/update_project.php" method="POST" novalidate>
                <input type="hidden" name="id" value="<?= $proyecto['id'] ?>">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="titulo">Título del Proyecto</label>
                            <input type="text" id="titulo" name="titulo" class="form-control text-uppercase" maxlength="150" placeholder="En mayúsculas" value="<?= htmlspecialchars($proyecto['titulo']) ?>" required>
                            <small class="rule">Máx. 150 caracteres, solo mayúsculas.</small>
                            <div class="invalid-feedback">Título obligatorio.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="autores">Autor(es)</label>
                            <input type="text" id="autores" name="autores" class="form-control" maxlength="150" placeholder="Juan, María" pattern="^([^,]+)(,\s*[^,]+)*$" value="<?= htmlspecialchars($proyecto['autores']) ?>" required>
                            <small class="rule">Separe autores con comas.</small>
                            <div class="invalid-feedback">Formato incorrecto.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="ente">Ente/Institución</label>
                            <input type="text" id="ente" name="ente" class="form-control" maxlength="100" placeholder="Nombre de la institución" value="<?= htmlspecialchars($proyecto['ente']) ?>" required>
                            <small class="rule">Obligatorio.</small>
                            <div class="invalid-feedback">Campo obligatorio.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="tutor">Tutor(es)</label>
                            <input type="text" id="tutor" name="tutor" class="form-control" maxlength="150" placeholder="Separar tutores con comas" pattern="^([^,]+)(,\s*[^,]+)*$" value="<?= htmlspecialchars($proyecto['tutor']) ?>" required>
                            <small class="rule">Separe tutores con comas.</small>
                            <div class="invalid-feedback">Formato incorrecto.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="fecha">Fecha del Proyecto</label>
                            <input type="date" id="fecha" name="fecha" class="form-control" min="2000-01-01" max="<?= date('Y-m-d') ?>" value="<?= $fechaVal ?>" required>
                            <small class="rule">Entre 2000 y hoy.</small>
                            <div class="invalid-feedback">Fecha inválida.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="linea_investigacion">Línea de Investigación</label>
                            <select id="linea_investigacion" name="linea_investigacion" class="form-select" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach(['Desarrollo y Caracterización de materiales','Materiales compuestos y avanzados','Integridad Mecánica','Tribología y desgaste','Aseguramiento de calidad','Corrosión y oxidación'] as $op): ?>
                                    <option value="<?= $op ?>" <?= $proyecto['linea_investigacion']===$op?'selected':'' ?>><?= $op ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="rule">Obligatorio.</small>
                            <div class="invalid-feedback">Selecciona una línea.</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label" for="descripcion">Resumen</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="5" maxlength="2000" placeholder="Máx. 256 palabras" required><?= htmlspecialchars($proyecto['descripcion']) ?></textarea>
                            <small class="rule" id="contadorEdit">Máx. 256 palabras, 0 de 256</small>
                            <div class="invalid-feedback">Resumen inválido.</div>
                        </div>
                    </div>
                    <div class="col-12 button-container">
                        <button id="btnSave" type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
                        <a href="panel_admin.php" class="btn btn-secondary w-100"><i class="fas fa-times me-2"></i>Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="js/bootstrap.js"></script>
<script>
// Contador de palabras edición
const txt = document.getElementById('descripcion');
const cnt = document.getElementById('contadorEdit');
txt.addEventListener('input', ()=>{
    const palabras = txt.value.trim().split(/\s+/).filter(Boolean);
    cnt.textContent = `Máx. 256 palabras, ${palabras.length} de 256`;
    palabras.length>256?txt.classList.add('is-invalid'):txt.classList.remove('is-invalid');
});

// Validación y envío
const form = document.getElementById('formEdit');
form.addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        this.querySelectorAll(':invalid').forEach(i => i.classList.add('is-invalid'));
        return;
    }
    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
});

// Mostrar reglas
document.querySelectorAll('.form-control, .form-select').forEach(i=>{
    const rule = i.parentElement.querySelector('.rule');
    i.addEventListener('focus', ()=>rule.style.display='block');
    i.addEventListener('blur', ()=>rule.style.display='none');
});
</script>
</body>
</html>
