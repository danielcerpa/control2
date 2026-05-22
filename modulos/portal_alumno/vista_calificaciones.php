<?php
/**
 * @var array|null $ciclo
 * @var float $promedio
 * @var array $calificaciones
 */
include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Mis Calificaciones</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px; color: #10b981;">history_edu</span>Mis Calificaciones</h1>
        <p>Avance académico del ciclo: <strong><?php echo e($ciclo['nombre']); ?></strong></p>
    </div>
    <div class="d-flex align-items-center" style="gap:12px;">
        <a href="<?php echo BASE_URL; ?>portal_alumno/kardex" target="_blank"
           class="btn btn-outline-success"
           style="border-radius:8px; padding:10px 20px; font-weight:600;">
            <span class="material-symbols-outlined mr-1" style="font-size:18px; vertical-align:middle;">download</span>
            Descargar Kardex
        </a>
        <div class="card border-0 shadow-sm px-4 py-2 text-center" style="border-radius:12px; min-width:180px;">
            <div class="small text-secondary font-weight-bold text-uppercase" style="letter-spacing:1px; font-size:10px;">Promedio General</div>
            <div class="h3 font-weight-bold mb-0 <?php echo $promedio >= 6 ? 'text-success' : 'text-danger'; ?>">
                <?php echo number_format($promedio, 2); ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
    <div class="card-header font-weight-bold pt-3 pb-2">
        <span class="material-symbols-outlined mr-2 text-success" style="font-size:20px; vertical-align:middle;">list_alt</span>
        Resumen de Aprovechamiento por Parcial
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr class="text-uppercase" style="font-size:11px; letter-spacing:1px;">
                    <th class="pl-4">Materia</th>
                    <th class="text-center" style="width:90px;">Parcial 1</th>
                    <th class="text-center" style="width:90px;">Parcial 2</th>
                    <th class="text-center" style="width:90px;">Parcial 3</th>
                    <th class="text-center col-calif-final" style="width:100px;">Calif. Final</th>
                    <th class="text-center" style="width:130px;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$calificaciones): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Aún no se han registrado calificaciones para este ciclo.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($calificaciones as $c): ?>
                    <tr>
                        <td class="pl-4 font-weight-bold align-middle"><?php echo e($c['materia']); ?></td>
                        <?php foreach (['p1','p2','p3'] as $col): ?>
                        <td class="text-center align-middle">
                            <?php if ($c[$col] !== null): ?>
                                <span class="font-weight-bold <?php echo floatval($c[$col]) >= 6 ? '' : 'text-danger'; ?>">
                                    <?php echo number_format(floatval($c[$col]), 1); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <td class="text-center align-middle td-calif-final">
                            <?php if ($c['final'] !== null): ?>
                                <span class="h5 font-weight-bold mb-0 <?php echo floatval($c['final']) >= 6 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format(floatval($c['final']), 1); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center align-middle">
                            <?php if ($c['final'] !== null && floatval($c['final']) >= 6): ?>
                                <span class="cal-badge-aprobado">
                                    <span class="material-symbols-outlined">check_circle</span> APROBADO
                                </span>
                            <?php elseif ($c['final'] !== null): ?>
                                <span class="cal-badge-reprobado">
                                    <span class="material-symbols-outlined">cancel</span> REPROBADO
                                </span>
                            <?php else: ?>
                                <span class="cal-badge-pendiente">PENDIENTE</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 p-4 border-0 shadow-sm info-box-green">
    <div class="d-flex">
        <span class="material-symbols-outlined text-success mr-3" style="font-size:24px;">info</span>
        <div>
            <h6 class="font-weight-bold mb-1">Información Importante</h6>
            <p class="small text-secondary mb-0">La calificación final se calcula automáticamente al promediar los 3 parciales. Para trámites legales o académicos oficiales, solicita tu boleta física sellada en las oficinas administrativas del plantel.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>