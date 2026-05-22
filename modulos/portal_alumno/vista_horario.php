<?php
/**
 * @var array $grid
 * @var array|null $ciclo
 * @var int|null $grupo_id
 */
include 'includes/header.php'; ?>

<?php
/* ─── Construir el grid de horario (PHP-side, igual que el renderHorario() del modal) ─── */

$DIAS_KEYS  = ['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'];
$DIAS_LABEL = ['LUNES'=>'Lunes','MARTES'=>'Martes','MIERCOLES'=>'Miércoles','JUEVES'=>'Jueves','VIERNES'=>'Viernes','SABADO'=>'Sábado'];

// Paleta de colores (misma que el modal de admin)
$PALETTE = ['#197fe6','#7c3aed','#059669','#dc2626','#d97706','#0891b2','#be185d','#16a34a','#9333ea','#ea580c'];

// Aplanar $grid a un array plano con 'dia' en uppercase
// Cada elemento ya trae el campo 'dia' con el valor original de BD (ej: 'LUNES', 'MIERCOLES')
$horarios_plano = [];
foreach ($grid as $dia_norm => $clases) {
    foreach ($clases as $c) {
        // Usar el campo 'dia' original del registro (ya viene en uppercase desde la BD)
        // Si por alguna razón no viene, derivarlo de la key del grid
        if (empty($c['dia'])) {
            $c['dia'] = strtoupper($dia_norm);
        }
        $horarios_plano[] = $c;
    }
}

// Asignar colores por materia
$colorMap  = [];
$colorIdx  = 0;
foreach ($horarios_plano as $f) {
    $mid = $f['id_materia'] ?? $f['materia']; // fallback al nombre
    if (!isset($colorMap[$mid])) {
        $colorMap[$mid] = $PALETTE[$colorIdx % count($PALETTE)];
        $colorIdx++;
    }
}

// Calcular rango de horas
$minH = 7;
$maxH = 14;
foreach ($horarios_plano as $f) {
    $hi = (int) substr($f['hora_inicio'], 0, 2);
    $hf = (int) substr($f['hora_fin'],    0, 2);
    if ($hi < $minH) $minH = $hi;
    if ($hf > $maxH) $maxH = $hf;
}

// Construir grid[DIA][hora] = {materia, salon, docente, color, rowspan}
// rowspan = 0 significa "skip" (celda absorbida por la de arriba)
$schedGrid = [];
foreach ($DIAS_KEYS as $d) $schedGrid[$d] = [];

foreach ($horarios_plano as $f) {
    $dia = $f['dia'];
    if (!in_array($dia, $DIAS_KEYS)) continue;
    $mid = $f['id_materia'] ?? $f['materia'];
    $hi = (int) substr($f['hora_inicio'], 0, 2);
    $hf = (int) substr($f['hora_fin'],    0, 2);
    $rowspan = $hf - $hi;
    for ($h = $hi; $h < $hf; $h++) {
        $schedGrid[$dia][$h] = [
            'materia' => $f['materia'],
            'salon'   => $f['salon']   ?? '',
            'docente' => $f['docente'] ?? '',
            'color'   => $colorMap[$mid],
            'rowspan' => ($h === $hi) ? $rowspan : 0,
        ];
    }
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Mi Horario</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px; color:#4338ca;">calendar_today</span>Mi Horario Escolar</h1>
        <p>Clases programadas para el ciclo: <strong><?php echo e($ciclo['nombre']); ?></strong></p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-primary d-none d-md-flex align-items-center" style="border-radius:8px;">
        <span class="material-symbols-outlined mr-1" style="font-size:20px;">print</span> Imprimir
    </button>
</div>

<?php if (!$grupo_id): ?>
    <div class="card border-0 shadow-sm text-center py-5 no-grupo-card">
        <span class="material-symbols-outlined text-warning mb-3" style="font-size:64px;">warning</span>
        <h5 class="font-weight-bold">Aún no tienes grupo asignado</h5>
        <p class="text-secondary">Contacta con servicios escolares para regularizar tu inscripción al ciclo actual.</p>
    </div>
<?php elseif (empty($horarios_plano)): ?>
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:12px;">
        <span class="material-symbols-outlined text-muted mb-3" style="font-size:64px; opacity:.3;">event_busy</span>
        <h5 class="font-weight-bold">Sin clases registradas</h5>
        <p class="text-secondary">No hay materias con horario asignado para este ciclo.</p>
    </div>
<?php else: ?>

<div class="horario-full-wrap">
    <div class="table-responsive">
        <table class="horario-full-table">
            <thead>
                <tr>
                    <th class="col-hora">Hora</th>
                    <?php foreach ($DIAS_KEYS as $dk): ?>
                        <th><?php echo $DIAS_LABEL[$dk]; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($h = $minH; $h < $maxH; $h++): ?>
                    <tr>
                        <!-- Columna de hora -->
                        <td class="td-hora">
                            <?php
                                printf('%02d:00 – %02d:00', $h, $h + 1);
                            ?>
                        </td>

                        <?php foreach ($DIAS_KEYS as $dk): ?>
                            <?php
                                $celda = $schedGrid[$dk][$h] ?? null;

                                if ($celda && $celda['rowspan'] === 0) {
                                    // Absorbida por rowspan → no renderizar <td>
                                    continue;
                                }
                            ?>
                            <?php if (!$celda): ?>
                                <td class="td-vacia"></td>
                            <?php else: ?>
                                <td class="td-materia" rowspan="<?php echo $celda['rowspan']; ?>">
                                    <div class="materia-chip" style="--materia-color: <?php echo $celda['color']; ?>;">
                                        <span class="mat-nombre"><?php echo e($celda['materia']); ?></span>
                                        <?php if (!empty($celda['salon'])): ?>
                                            <span class="mat-salon">
                                                <span class="material-symbols-outlined" style="font-size:11px;vertical-align:middle;">meeting_room</span>
                                                <?php echo e($celda['salon']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($celda['docente'])): ?>
                                            <span class="mat-docente">
                                                <span class="material-symbols-outlined" style="font-size:11px;vertical-align:middle;">person</span>
                                                <?php echo e($celda['docente']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>