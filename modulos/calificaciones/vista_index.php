<?php
/**
 * @var array|null $ciclo
 * @var array $grupos
 * @var int|string $filtro_grupo
 * @var array $materias
 * @var int|string $filtro_materia
 * @var string $filtro_parcial
 * @var array $alumnos
 */
include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Calificaciones</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">check_circle</span>Captura de Calificaciones</h1>
        <p><?php if ($ciclo): ?>Ciclo Escolar: <strong><?php echo e($ciclo['nombre']); ?></strong><?php else: ?><span class="text-danger">Seleccione un ciclo activo en configuración</span><?php endif; ?></p>
    </div>
</div>

<!-- Filtros -->
<div class="filter-bar pr-3">
    <form method="get" action="<?php echo BASE_URL; ?>calificaciones" class="form-row align-items-end" id="filtro-form">
        <div class="col-12 col-md-4 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary text-uppercase" style="letter-spacing:1px;">Grupo</label>
            <select name="grupo" id="sel-grupo" class="form-control" onchange="this.form.submit()" style="border-radius:8px;">
                <option value="0">Seleccionar grupo...</option>
                <?php foreach ($grupos as $g): ?>
                    <option value="<?php echo $g['id_grupo']; ?>" <?php if ($filtro_grupo == $g['id_grupo']) echo 'selected'; ?>><?php echo e($g['nombre']) . ($g['turno'] ? ' - ' . ucfirst(strtolower($g['turno'])) : ''); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary text-uppercase" style="letter-spacing:1px;">Materia</label>
            <select name="materia" id="sel-materia" class="form-control" onchange="this.form.submit()" style="border-radius:8px;">
                <option value="0"><?php echo $filtro_grupo ? 'Seleccionar materia...' : 'Primero selecciona un grupo'; ?></option>
                <?php foreach ($materias as $m): ?>
                    <option value="<?php echo $m['id_materia']; ?>" <?php if ($filtro_materia == $m['id_materia']) echo 'selected'; ?>><?php echo e($m['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-2 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary text-uppercase" style="letter-spacing:1px;">Parcial</label>
            <select name="parcial" class="form-control" onchange="this.form.submit()" style="border-radius:8px;">
                <?php foreach (['P1' => 'Parcial 1', 'P2' => 'Parcial 2', 'P3' => 'Parcial 3'] as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php if ($filtro_parcial === $val) echo 'selected'; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-2">
            <label class="small font-weight-bold text-secondary text-uppercase" style="letter-spacing:1px; opacity:0;">-</label>
            <a href="<?php echo BASE_URL; ?>calificaciones" class="btn btn-outline-secondary btn-block" style="border-radius:8px;">
                <span class="material-symbols-outlined" style="font-size:20px; vertical-align:middle;">restart_alt</span>
            </a>
        </div>
    </form>
</div>

<?php if ($filtro_grupo && $filtro_materia && $alumnos): ?>
    <div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="font-weight-bold mb-0">
                <span class="material-symbols-outlined mr-2" style="font-size:20px; vertical-align:middle; color:#10b981;">edit_square</span>
                Lista de Alumnos — <span class="badge" style="background:#eef2ff; color:#4338ca; border-radius:8px; padding:4px 12px; font-size:13px;"><?php echo ['P1'=>'Parcial 1','P2'=>'Parcial 2','P3'=>'Parcial 3'][$filtro_parcial]; ?></span>
                <span class="badge badge-indigo ml-2" style="background:#f1f5f9; color:#475569; border:none;"><?php echo count($alumnos); ?> alumnos</span>
            </h5>
            <div class="text-secondary small font-weight-bold">
                Promedio Final Grupal: <span id="promedio-badge" class="badge badge-info p-2" style="border-radius:6px; font-size:14px;">—</span>
            </div>
        </div>
        <div class="card-body p-0">
            <form method="post" action="<?php echo BASE_URL; ?>calificaciones">
                <input type="hidden" name="grupo_id" value="<?php echo $filtro_grupo; ?>">
                <input type="hidden" name="materia_id" value="<?php echo $filtro_materia; ?>">
                <input type="hidden" name="parcial" value="<?php echo e($filtro_parcial); ?>">

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="text-uppercase" style="font-size:11px; letter-spacing:1px; background:#f8fafc;">
                                <th class="pl-4" style="width:50px;">#</th>
                                <th>Alumno</th>
                                <th class="text-center" style="width:110px;">P1</th>
                                <th class="text-center" style="width:110px;">P2</th>
                                <th class="text-center" style="width:110px;">P3</th>
                                <th class="text-center" style="width:110px; background:#eff6ff; color:#1e40af;">Final</th>
                                <th class="text-center" style="width:120px;">Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            foreach ($alumnos as $al): ?>
                                <tr>
                                    <td class="pl-4 text-muted align-middle"><?php echo $i++; ?></td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold" style="color:#334155;"><?php echo e($al['nombre_completo']); ?></div>
                                        <small class="text-muted"><?php echo e($al['matricula']); ?></small>
                                    </td>
                                    <?php foreach (['p1','p2','p3'] as $parcial_col): ?>
                                        <td class="align-middle text-center">
                                            <?php if (strtolower($filtro_parcial) === $parcial_col): ?>
                                                <input type="number"
                                                    name="calificaciones[<?php echo $al['id_inscripcion']; ?>]"
                                                    class="form-control cal-input text-center"
                                                    value="<?php echo ($al[$parcial_col] !== null ? number_format(floatval($al[$parcial_col]), 1) : ''); ?>"
                                                    min="0" max="10" step="0.1"
                                                    placeholder="—"
                                                    style="border-radius:8px; font-weight:bold; color:#197fe6; text-align:center;">
                                            <?php else: ?>
                                                <span class="<?php echo $al[$parcial_col] !== null ? ($al[$parcial_col] >= 6 ? 'text-success' : 'text-danger') : 'text-muted'; ?> font-weight-bold">
                                                    <?php echo $al[$parcial_col] !== null ? number_format(floatval($al[$parcial_col]), 1) : '—'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="align-middle text-center" style="background:#f8fbff;">
                                        <span class="font-weight-bold <?php echo $al['final'] !== null ? ($al['final'] >= 6 ? 'text-success' : 'text-danger') : 'text-muted'; ?>" style="font-size:15px;">
                                            <?php echo $al['final'] !== null ? number_format(floatval($al['final']), 1) : '—'; ?>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php
                                        $v = $al['final'] !== null ? floatval($al['final']) : null;
                                        if ($v === null) {
                                            echo '<span class="text-muted small">Pendiente</span>';
                                        } elseif ($v >= 9) {
                                            echo '<span class="status-pill status-excellent">Excelente</span>';
                                        } elseif ($v >= 8) {
                                            echo '<span class="status-pill status-good">Muy Bien</span>';
                                        } elseif ($v >= 6) {
                                            echo '<span class="status-pill status-pass">Aprobado</span>';
                                        } else {
                                            echo '<span class="status-pill status-fail">Reprobado</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0 p-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" style="background:#197fe6; border:none; border-radius:8px; padding:12px 35px; font-weight:600; box-shadow: 0 4px 6px rgba(25, 127, 230, 0.2);">
                        <span class="material-symbols-outlined mr-2" style="font-size:20px; vertical-align:middle;">save</span>
                        Guardar <?php echo ['P1'=>'Parcial 1','P2'=>'Parcial 2','P3'=>'Parcial 3'][$filtro_parcial]; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($filtro_grupo && $filtro_materia && !$alumnos): ?>
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:12px;">
        <span class="material-symbols-outlined text-muted mb-3" style="font-size:64px; opacity:0.3;">person</span>
        <h5 class="text-secondary">No hay alumnos inscritos en este grupo</h5>
    </div>

<?php else: ?>
    <div class="card border-0 shadow-sm" style="border-radius:12px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);">
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center mb-4" style="width:80px; height:80px; background:white; border-radius:50%; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                <span class="material-symbols-outlined text-primary" style="font-size:40px;">ads_click</span>
            </div>
            <h4 class="font-weight-bold text-dark mb-2">Comenzar Captura</h4>
            <p class="text-secondary mx-auto" style="max-width:400px;">Seleccione el grupo, la materia y el parcial para cargar la lista de alumnos y registrar sus calificaciones.</p>
        </div>
    </div>
<?php endif; ?>

<style>
    .cal-input:focus {
        border-color: #197fe6;
        box-shadow: 0 0 0 3px rgba(25, 127, 230, 0.15);
    }
    .status-pill {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-excellent { background: #dcfce7; color: #166534; }
    .status-good      { background: #e0f2fe; color: #075985; }
    .status-pass      { background: #fef9c3; color: #854d0e; }
    .status-fail      { background: #fee2e2; color: #991b1b; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calcular promedio de la columna FINAL
        const finales = document.querySelectorAll('td[style*="f8fbff"] span');
        const promedioBadge = document.getElementById('promedio-badge');
        if (promedioBadge) {
            let sum = 0, count = 0;
            finales.forEach(el => {
                const v = parseFloat(el.textContent);
                if (!isNaN(v)) { sum += v; count++; }
            });
            promedioBadge.textContent = count > 0 ? (sum / count).toFixed(2) : '—';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>