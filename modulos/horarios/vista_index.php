<?php include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Horarios</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">calendar_month</span>Horarios</h1>
        <p>Consulta el horario semanal de cada alumno<?php if ($ciclo): ?> &mdash; Ciclo <strong><?php echo e($ciclo['nombre']); ?></strong><?php endif; ?></p>
    </div>
</div>

<!-- Filtros -->
<div class="filter-bar mb-4">
    <form method="get" action="<?php echo BASE_URL; ?>horarios" class="form-row align-items-end">
        <div class="col-12 col-md-5 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary">Buscar alumno</label>
            <div class="position-relative">
                <span class="material-symbols-outlined" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:18px;">search</span>
                <input type="text" name="q" class="form-control pl-5" placeholder="Nombre o matrícula..." value="<?php echo e($filtros['q']); ?>">
            </div>
        </div>
        <div class="col-12 col-md-4 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary">Grupo</label>
            <select name="grupo" class="form-control" style="border-radius:8px;">
                <option value="0">Todos los grupos</option>
                <?php foreach ($grupos as $g): ?>
                    <option value="<?php echo $g['id_grupo']; ?>" <?php if ($filtros['grupo_id'] == $g['id_grupo']) echo 'selected'; ?>>
                        <?php echo e($g['grado'] . $g['seccion'] . ' — ' . $g['turno']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex">
            <button type="submit" class="btn btn-primary flex-fill mr-2" style="background:#197fe6;border:none;border-radius:8px;">
                Filtrar
            </button>
            <a href="<?php echo BASE_URL; ?>horarios" class="btn btn-outline-secondary" style="border-radius:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">restart_alt</span>
            </a>
        </div>
    </form>
</div>

<!-- Grid de Cards de Alumnos -->
<?php if (!$alumnos): ?>
    <div class="text-center py-5 text-muted">
        <span class="material-symbols-outlined" style="font-size:64px;opacity:0.2;">person_search</span>
        <p class="mt-3">No se encontraron alumnos.</p>
    </div>
<?php else: ?>
<div class="row" id="alumnosGrid">
    <?php foreach ($alumnos as $a): ?>
        <?php
            $iniciales = strtoupper(substr($a['nombre'], 0, 1) . substr($a['apellido_paterno'], 0, 1));
            $colores = ['#197fe6','#7c3aed','#059669','#dc2626','#d97706','#0891b2','#be185d'];
            $color = $colores[($a['id_alumno'] - 1) % count($colores)];
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-4">
            <div class="alumno-card" data-id="<?php echo $a['id_alumno']; ?>"
                 data-nombre="<?php echo e($a['nombre'] . ' ' . $a['apellido_paterno'] . ' ' . $a['apellido_materno']); ?>"
                 data-matricula="<?php echo e($a['matricula']); ?>"
                 data-grupo="<?php echo e($a['grupo_nombre'] ?? '—'); ?>">

                <!-- Avatar -->
                <div class="alumno-avatar" style="background:<?php echo $color; ?>15; border:3px solid <?php echo $color; ?>30;">
                    <?php if ($a['ruta_foto']): ?>
                        <img src="<?php echo e($a['ruta_foto']); ?>" alt="<?php echo e($a['nombre']); ?>">
                    <?php else: ?>
                        <span class="alumno-iniciales" style="color:<?php echo $color; ?>"><?php echo $iniciales; ?></span>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="alumno-info">
                    <div class="alumno-nombre"><?php echo e($a['apellido_paterno'] . ' ' . $a['apellido_materno']); ?></div>
                    <div class="alumno-nombre-p"><?php echo e($a['nombre']); ?></div>
                    <div class="alumno-meta">
                        <span class="alumno-badge" style="background:<?php echo $color; ?>18; color:<?php echo $color; ?>">
                            <?php echo e($a['matricula']); ?>
                        </span>
                        <?php if ($a['grupo_nombre']): ?>
                            <span class="alumno-badge-grupo">
                                <span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle;">groups</span>
                                <?php echo e($a['grupo_nombre']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Botón -->
                <a href="<?php echo BASE_URL; ?>horarios/show/<?php echo $a['id_alumno']; ?>" class="btn-ver-horario d-block text-center text-decoration-none" style="background: <?php echo $color; ?>10; color: <?php echo $color; ?>; padding: 10px; border-radius: 8px; font-weight: 600; margin-top: 15px;">
                    <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">calendar_month</span>
                    Ver Horario
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
/* =========================================
   CARDS DE ALUMNOS
   ========================================= */
.alumno-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px 20px 18px;
    text-align: center;
    transition: transform .22s ease, box-shadow .22s ease;
    cursor: default;
    height: 100%;
}
.alumno-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,.12);
}
.alumno-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 14px;
    flex-shrink: 0;
}
.alumno-avatar img {
    width: 100%; height: 100%; object-fit: cover;
}
.alumno-iniciales {
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -1px;
}
.alumno-info { flex: 1; width: 100%; }
.alumno-nombre {
    font-weight: 700;
    color: #1e293b;
    font-size: 15px;
    line-height: 1.2;
    margin-bottom: 2px;
}
.alumno-nombre-p {
    color: #64748b;
    font-size: 13px;
    margin-bottom: 10px;
}
.alumno-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.alumno-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .3px;
}
.alumno-badge-grupo {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    background: #f1f5f9;
    color: #475569;
}
.btn-ver-horario {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    color: #fff;
    background: #197fe6;
    transition: background .18s, transform .18s;
    margin-top: auto;
    width: 100%;
    justify-content: center;
}
.btn-ver-horario:hover {
    background: #1565c0;
    transform: translateY(-1px);
}
</style>

<?php include 'includes/footer.php'; ?>