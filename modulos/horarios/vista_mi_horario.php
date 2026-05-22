<?php
/**
 * @var array $grid
 * @var array|null $ciclo
 */
include 'includes/header.php'; ?>

<?php
/* ─── Construir el grid de horario ─── */

$DIAS_KEYS  = ['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'];
$DIAS_LABEL = ['LUNES'=>'Lunes','MARTES'=>'Martes','MIERCOLES'=>'Miércoles','JUEVES'=>'Jueves','VIERNES'=>'Viernes','SABADO'=>'Sábado'];

// Paleta de colores
$PALETTE = ['#197fe6','#7c3aed','#059669','#dc2626','#d97706','#0891b2','#be185d','#16a34a','#9333ea','#ea580c'];

$horarios_plano = [];
foreach ($grid as $dia_norm => $clases) {
    foreach ($clases as $c) {
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
    $mid = $f['id_materia'] ?? $f['materia']; 
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

// Construir grid
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
            'docente' => $f['docente'] ?? '', // This will hold the group info now
            'color'   => $colorMap[$mid],
            'rowspan' => ($h === $hi) ? $rowspan : 0,
        ];
    }
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Mi Horario Semanal</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px; color:#4338ca;">calendar_today</span>Mi Horario Semanal</h1>
        <p>Clases que impartes en el ciclo: <strong><?php echo e($ciclo['nombre'] ?? 'Actual'); ?></strong></p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-primary d-none d-md-flex align-items-center" style="border-radius:8px;">
        <span class="material-symbols-outlined mr-1" style="font-size:20px;">print</span> Imprimir
    </button>
</div>

<?php if (empty($horarios_plano)): ?>
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:12px;">
        <span class="material-symbols-outlined text-muted mb-3" style="font-size:64px; opacity:.3;">event_busy</span>
        <h5 class="text-dark font-weight-bold">Sin clases registradas</h5>
        <p class="text-secondary">Aún no tienes horarios de clase asignados para este ciclo.</p>
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
                                    continue;
                                }
                            ?>
                            <?php if (!$celda): ?>
                                <td class="td-vacia"></td>
                            <?php else: ?>
                                <td class="td-materia" rowspan="<?php echo $celda['rowspan']; ?>">
                                    <div class="materia-chip" style="--materia-color: <?php echo $celda['color']; ?>;">
                                        <span class="mat-nombre"><?php echo e($celda['materia']); ?></span>
                                        <?php if (!empty($celda['docente'])): ?>
                                            <span class="mat-docente">
                                                <span class="material-symbols-outlined" style="font-size:11px;vertical-align:middle;">groups</span>
                                                <?php echo e($celda['docente']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($celda['salon'])): ?>
                                            <span class="mat-salon">
                                                <span class="material-symbols-outlined" style="font-size:11px;vertical-align:middle;">meeting_room</span>
                                                <?php echo e($celda['salon']); ?>
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

<style>
/* ─── Contenedor principal ─── */
.horario-full-wrap {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

/* ─── Tabla ─── */
.horario-full-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 13px;
}

/* ─── Cabecera ─── */
.horario-full-table thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 14px 12px;
    border-bottom: 2px solid #e2e8f0;
    text-align: center;
}
.horario-full-table thead th.col-hora {
    text-align: left;
    width: 110px;
    min-width: 95px;
    color: #94a3b8;
}

/* ─── Celdas tbody ─── */
.horario-full-table tbody td {
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    padding: 0;
}

/* Celda de hora */
.horario-full-table tbody td.td-hora {
    padding: 8px 14px;
    color: #64748b;
    font-weight: 700;
    font-size: 11.5px;
    white-space: nowrap;
    background: #f8fafc;
    border-right: 2px solid #e2e8f0;
    text-align: left;
}

/* Celda vacía */
.horario-full-table tbody td.td-vacia {
    background: #fff;
    min-height: 52px;
    height: 52px;
    transition: background .15s;
}
.horario-full-table tbody td.td-vacia:hover {
    background: #f8fafc;
}

/* Celda con materia */
.horario-full-table tbody td.td-materia {
    padding: 6px 8px;
    vertical-align: middle;
    text-align: center;
}

/* Chip de materia */
.materia-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    border-radius: 10px;
    padding: 8px 10px;
    color: #fff;
    font-weight: 600;
    font-size: 12px;
    line-height: 1.3;
    min-height: 48px;
    transition: transform .15s, box-shadow .15s;
    cursor: default;
}
.materia-chip:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 14px rgba(0,0,0,.18);
}
.mat-nombre {
    font-size: 12.5px;
    font-weight: 700;
}
.mat-salon, .mat-docente {
    font-size: 10px;
    opacity: .88;
    font-weight: 400;
    display: flex;
    align-items: center;
    gap: 2px;
}

/* ─── Print ─── */
@media print {
    .breadcrumb,
    .page-header button,
    .main-sidebar,
    .main-header,
    .navbar { display: none !important; }

    .content-wrapper { margin: 0 !important; padding: 0 !important; }

    .horario-full-wrap {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        border-radius: 0 !important;
    }
}

/* ─── Responsive: apilar en móvil ─── */
@media (max-width: 600px) {
    .horario-full-table thead th.col-hora { display: none; }
    .horario-full-table tbody td.td-hora  { display: none; }
}
</style>

<?php include 'includes/footer.php'; ?>
