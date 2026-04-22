<?php include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?php echo BASE_URL; ?>dashboard">
                <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span>
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?php echo BASE_URL; ?>alumno/perfil">Mi Perfil</a>
        </li>
        <li class="breadcrumb-item active">Mis Materias</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 1rem;">
    <div>
        <h1>
            <span class="material-symbols-outlined mr-2" style="font-size:28px; color:#2563eb;">menu_book</span>
            Mis Materias
        </h1>
        <p>Materias en las que estás inscrito — Ciclo: <strong><?php echo e($ciclo['nombre'] ?? 'N/A'); ?></strong></p>
    </div>
    <!-- Totalizador -->
    <div class="d-flex" style="gap: 10px; flex-wrap: wrap;">
        <div class="alumno-stat-chip">
            <span class="material-symbols-outlined" style="color:#2563eb;">menu_book</span>
            <strong><?php echo count($materias); ?></strong> materia<?php echo count($materias) !== 1 ? 's' : ''; ?>
        </div>
        <?php if (!empty($grupo)): ?>
        <div class="alumno-stat-chip">
            <span class="material-symbols-outlined" style="color:#7c3aed;">groups</span>
            Grupo <?php echo e($grupo['grado'] . '°' . $grupo['seccion']); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($materias)): ?>
<!-- Estado vacío -->
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-body empty-state-alumno">
        <div class="empty-icon">
            <span class="material-symbols-outlined" style="font-size:64px; color:#cbd5e1;">library_books</span>
        </div>
        <h5>Sin materias inscritas</h5>
        <p class="text-secondary small mb-0">
            Aún no tienes materias registradas para este ciclo escolar.<br>
            Contacta con servicios escolares para regularizar tu inscripción.
        </p>
    </div>
</div>

<?php else: ?>

<!-- ===== GRID DE MATERIAS ===== -->
<div class="row">
    <?php foreach ($materias as $m): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="materia-card h-100">

            <!-- Badge de estado (Activo / Inactivo) -->
            <div class="materia-badge-estado">
                <?php if ($m['estado']): ?>
                    <span class="badge" style="background:#dcfce7; color:#166534; border-radius:20px; padding:4px 12px; font-size:10px; font-weight:700;">
                        <span class="material-symbols-outlined mr-1" style="font-size:12px;">check_circle</span>ACTIVO
                    </span>
                <?php else: ?>
                    <span class="badge" style="background:#f3f4f6; color:#6b7280; border-radius:20px; padding:4px 12px; font-size:10px; font-weight:700;">
                        INACTIVO
                    </span>
                <?php endif; ?>
            </div>

            <!-- Nombre de la materia -->
            <div class="materia-nombre pr-4"><?php echo e($m['nombre']); ?></div>

            <hr style="border-color:#f1f5f9; margin: 0.75rem 0;">

            <!-- Metadata -->
            <?php if (!empty($m['docente'])): ?>
            <div class="materia-meta mb-2">
                <span class="material-symbols-outlined" style="color:#64748b;">person</span>
                <span><?php echo e($m['docente']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($m['salon'])): ?>
            <div class="materia-meta mb-2">
                <span class="material-symbols-outlined" style="color:#64748b;">meeting_room</span>
                <span>Aula: <strong><?php echo e($m['salon']); ?></strong></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($m['vigencia_inicio']) && !empty($m['vigencia_fin'])): ?>
            <div class="materia-meta mb-2">
                <span class="material-symbols-outlined" style="color:#64748b;">date_range</span>
                <span>
                    <?php echo date('d/m/Y', strtotime($m['vigencia_inicio'])); ?>
                    &ndash;
                    <?php echo date('d/m/Y', strtotime($m['vigencia_fin'])); ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($m['horarios'])): ?>
            <hr style="border-color:#f1f5f9; margin: 0.75rem 0;">
            <div class="materia-meta mb-1">
                <span class="material-symbols-outlined" style="color:#4338ca;">schedule</span>
                <span class="font-weight-bold" style="color:#4338ca;">Horarios:</span>
            </div>
            <div class="pl-1" style="margin-top: 4px;">
                <?php foreach ($m['horarios'] as $h): ?>
                <div class="materia-meta mb-1">
                    <span style="width: 6px; height: 6px; background: #4338ca; border-radius: 50%; display: inline-block; margin-right: 4px; flex-shrink:0;"></span>
                    <span><?php echo ucfirst(strtolower($h['dia'])); ?>
                        — <?php echo substr($h['hora_inicio'], 0, 5); ?> a <?php echo substr($h['hora_fin'], 0, 5); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($m['calificacion'])): ?>
            <hr style="border-color:#f1f5f9; margin: 0.75rem 0;">
            <div class="d-flex align-items-center justify-content-between">
                <span class="materia-meta">
                    <span class="material-symbols-outlined" style="color:#64748b;">grade</span>
                    Última calificación
                </span>
                <div class="score-circle <?php echo $m['calificacion'] >= 6 ? 'aprobado' : 'reprobado'; ?>" style="width:38px; height:38px; font-size:0.85rem;">
                    <?php echo number_format($m['calificacion'], 1); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Nota informativa -->
<div class="mt-2 p-3" style="border-radius:12px; background: #eff6ff; border: 1px solid #bfdbfe;">
    <div class="d-flex align-items-start">
        <span class="material-symbols-outlined text-primary mr-3" style="font-size:20px; flex-shrink:0;">info</span>
        <p class="small text-secondary mb-0">
            Este listado muestra únicamente las materias del ciclo escolar activo. Para consultar materias de ciclos anteriores dirígete a las oficinas administrativas.
        </p>
    </div>
</div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
