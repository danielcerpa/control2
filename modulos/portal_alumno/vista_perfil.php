<?php
/**
 * @var array $u
 * @var array|null $alumno
 * @var array|null $grupo
 * @var array|null $ciclo
 * @var array $ultimas_calificaciones
 * @var int $total_materias
 * @var int $materias_aprobadas
 * @var int $materias_reprobadas
 * @var float $promedio_general
 */
include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?php echo BASE_URL; ?>dashboard">
                <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span>
            </a>
        </li>
        <li class="breadcrumb-item active">Mi Perfil</li>
    </ol>
</nav>

<!-- ===== HERO CARD ===== -->
<div class="alumno-hero mb-4">
    <div class="d-flex align-items-center" style="gap: 1.25rem; position: relative; z-index: 1;">
        <!-- Avatar -->
        <div class="hero-avatar">
            <?php if (!empty($u['foto'])): ?>
                <img src="<?php echo e($u['foto']); ?>" alt="Foto">
            <?php else: ?>
                <?php echo strtoupper(substr($u['nombre'], 0, 1)); ?>
            <?php endif; ?>
        </div>
        <!-- Info -->
        <div style="flex:1;">
            <h2 class="hero-name mb-0"><?php echo e($u['nombre']); ?></h2>
            <p class="hero-meta mb-1">
                <?php if (!empty($alumno['matricula'])): ?>
                    Matrícula: <strong><?php echo e($alumno['matricula']); ?></strong> &nbsp;·&nbsp;
                <?php endif; ?>
                <?php if (!empty($alumno['genero'])): ?>
                    <?php
                        $genero_map = ['M' => 'Masculino', 'F' => 'Femenino', 'H' => 'Masculino', 'O' => 'Otro'];
                        echo $genero_map[$alumno['genero']] ?? e($alumno['genero']);
                    ?>
                <?php endif; ?>
            </p>
            <span class="hero-badge">
                <span class="material-symbols-outlined mr-1" style="font-size:14px;">school</span>
                Alumno Activo
            </span>
            <?php if (!empty($ciclo)): ?>
                <span class="hero-badge ml-2">
                    <span class="material-symbols-outlined mr-1" style="font-size:14px;">event_note</span>
                    <?php echo e($ciclo['nombre']); ?>
                </span>
            <?php endif; ?>
        </div>
        <!-- Promedio rápido -->
        <?php if ($total_materias > 0): ?>
        <div class="d-none d-md-flex flex-column align-items-center" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; padding: 1rem 1.5rem; text-align:center; position:relative; z-index:1;">
            <div style="font-size: 2.2rem; font-weight: 800; line-height: 1; color: <?php echo $promedio_general >= 6 ? '#86efac' : '#fca5a5'; ?>;">
                <?php echo number_format($promedio_general, 1); ?>
            </div>
            <div style="font-size: 0.7rem; font-weight: 600; opacity: 0.75; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px;">Promedio</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== CHIPS DE DATOS RÁPIDOS ===== -->
<div class="d-flex flex-wrap mb-4" style="gap: 10px;">
    <?php if (!empty($grupo)): ?>
    <div class="alumno-stat-chip">
        <span class="material-symbols-outlined text-primary">groups</span>
        Grupo: <strong><?php echo e($grupo['grado'] . '°' . $grupo['seccion']); ?></strong>
        <?php if (!empty($grupo['turno'])): ?>
            &nbsp;·&nbsp; <?php echo ucfirst(strtolower($grupo['turno'])); ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="alumno-stat-chip">
        <span class="material-symbols-outlined" style="color:#7c3aed;">menu_book</span>
        <?php echo $total_materias; ?> Materia<?php echo $total_materias !== 1 ? 's' : ''; ?> inscrita<?php echo $total_materias !== 1 ? 's' : ''; ?>
    </div>
    <?php if ($materias_aprobadas > 0 || $materias_reprobadas > 0): ?>
    <div class="alumno-stat-chip stat-chip-aprobado">
        <span class="material-symbols-outlined text-success" style="font-size:16px;">check_circle</span>
        <?php echo $materias_aprobadas; ?> aprobada<?php echo $materias_aprobadas !== 1 ? 's' : ''; ?>
    </div>
    <?php if ($materias_reprobadas > 0): ?>
    <div class="alumno-stat-chip stat-chip-reprobado">
        <span class="material-symbols-outlined text-danger" style="font-size:16px;">cancel</span>
        <?php echo $materias_reprobadas; ?> reprobada<?php echo $materias_reprobadas !== 1 ? 's' : ''; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ===== ACCESOS RÁPIDOS ===== -->
<div class="mb-4">
    <h5 class="font-weight-bold mb-3" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
        <span class="material-symbols-outlined mr-2" style="font-size: 20px; color: #94a3b8;">apps</span>
        Mi espacio
    </h5>
    <div class="row" style="gap: 0;">
        <!-- Horario -->
        <div class="col-md-4 mb-3">
            <a href="<?php echo BASE_URL; ?>portal_alumno/horario" class="alumno-nav-card card-horario h-100">
                <div class="nav-card-icon">
                    <span class="material-symbols-outlined" style="font-size: 30px;">calendar_month</span>
                </div>
                <div class="nav-card-label">Mi Horario</div>
                <div class="nav-card-desc">Clases y horarios semanales</div>
            </a>
        </div>
        <!-- Calificaciones -->
        <div class="col-md-4 mb-3">
            <a href="<?php echo BASE_URL; ?>portal_alumno/calificaciones" class="alumno-nav-card card-calificaciones h-100">
                <div class="nav-card-icon">
                    <span class="material-symbols-outlined" style="font-size: 30px;">history_edu</span>
                </div>
                <div class="nav-card-label">Mis Calificaciones</div>
                <div class="nav-card-desc">Aprovechamiento académico</div>
            </a>
        </div>
        <!-- Materias -->
        <div class="col-md-4 mb-3">
            <a href="<?php echo BASE_URL; ?>portal_alumno/materias" class="alumno-nav-card card-materias h-100">
                <div class="nav-card-icon">
                    <span class="material-symbols-outlined" style="font-size: 30px;">menu_book</span>
                </div>
                <div class="nav-card-label">Mis Materias</div>
                <div class="nav-card-desc">Materias inscritas del ciclo</div>
            </a>
        </div>
    </div>
</div>

<!-- ===== ACTIVIDAD RECIENTE: últimas calificaciones ===== -->
<?php if (!empty($ultimas_calificaciones)): ?>
<div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
    <div class="card-header font-weight-bold">
        <span class="material-symbols-outlined mr-2 text-success" style="font-size:20px;">timeline</span>
        Últimas calificaciones registradas
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                    <th class="pl-4" style="color:#94a3b8; font-weight:600;">Materia</th>
                    <th class="text-center" style="color:#94a3b8; font-weight:600;">P1</th>
                    <th class="text-center" style="color:#94a3b8; font-weight:600;">P2</th>
                    <th class="text-center" style="color:#94a3b8; font-weight:600;">P3</th>
                    <th class="text-center" style="color:#94a3b8; font-weight:600;">Final</th>
                    <th class="text-center" style="color:#94a3b8; font-weight:600;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ultimas_calificaciones as $c): ?>
                <tr>
                    <td class="pl-4 font-weight-bold"><?php echo e($c['materia']); ?></td>
                    <td class="text-center"><?php echo $c['p1'] !== null ? number_format(floatval($c['p1']), 1) : '—'; ?></td>
                    <td class="text-center"><?php echo $c['p2'] !== null ? number_format(floatval($c['p2']), 1) : '—'; ?></td>
                    <td class="text-center"><?php echo $c['p3'] !== null ? number_format(floatval($c['p3']), 1) : '—'; ?></td>
                    <td class="text-center font-weight-bold">
                        <?php echo $c['final'] !== null ? number_format(floatval($c['final']), 1) : '—'; ?>
                    </td>
                    <td class="text-center align-middle">
                        <?php if ($c['final'] === null): ?>
                            <span class="badge-cursando">CURSANDO</span>
                        <?php elseif (floatval($c['final']) >= 6): ?>
                            <span class="cal-badge-aprobado">
                                <span class="material-symbols-outlined">check_circle</span> APROBADO
                            </span>
                        <?php else: ?>
                            <span class="cal-badge-reprobado">
                                <span class="material-symbols-outlined">cancel</span> REPROBADO
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($ultimas_calificaciones) >= 3): ?>
    <div class="card-footer text-center border-0 pb-3" style="background: transparent;">
        <a href="<?php echo BASE_URL; ?>portal_alumno/calificaciones" class="btn btn-sm btn-outline-success" style="border-radius:8px;">
            <span class="material-symbols-outlined mr-1" style="font-size:16px;">open_in_new</span>
            Ver todas las calificaciones
        </a>
    </div>
    <?php endif; ?>
</div>
<?php else: ?>
<!-- Sin calificaciones aun -->
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-body empty-state-alumno">
        <div class="empty-icon">
            <span class="material-symbols-outlined" style="font-size:56px; color:#cbd5e1;">assignment_late</span>
        </div>
        <h5>Sin calificaciones registradas</h5>
        <p class="small text-secondary mb-0">Aún no hay calificaciones capturadas para este ciclo escolar. Regresa pronto.</p>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
