<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex — <?php echo e($alumno['nombre'] ?? ''); ?> <?php echo e($alumno['apellido_paterno'] ?? ''); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1e293b; background: #fff; }

        .page { max-width: 780px; margin: 0 auto; padding: 32px 36px; }

        /* Header institucional */
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #197fe6; padding-bottom: 16px; margin-bottom: 20px; }
        .header-logo { font-size: 28px; font-weight: 800; color: #197fe6; letter-spacing: -1px; }
        .header-title { text-align: right; }
        .header-title h1 { font-size: 18px; font-weight: 700; color: #0f172a; }
        .header-title p  { font-size: 11px; color: #64748b; margin-top: 2px; }

        /* Datos alumno */
        .alumno-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; display: flex; gap: 32px; }
        .alumno-card .field { flex: 1; }
        .alumno-card .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 600; }
        .alumno-card .value { font-size: 14px; font-weight: 600; color: #0f172a; margin-top: 2px; }

        /* Tabla de calificaciones */
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { background: #1e40af; color: #fff; }
        thead th { padding: 10px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 9px 12px; border-bottom: 1px solid #e2e8f0; }
        .cal-num { font-size: 15px; font-weight: 700; }
        .aprobado { color: #166534; }
        .reprobado { color: #991b1b; }
        .pendiente { color: #64748b; }

        /* Resumen */
        .resumen { display: flex; gap: 16px; margin-bottom: 28px; }
        .resumen-card { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; text-align: center; }
        .resumen-card .num { font-size: 22px; font-weight: 800; }
        .resumen-card .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-top: 2px; }
        .num-promedio { color: <?php echo $promedio >= 6 ? '#166534' : '#991b1b'; ?>; }

        /* Firma */
        .firma { display: flex; justify-content: flex-end; margin-top: 40px; }
        .firma-box { text-align: center; width: 220px; }
        .firma-line { border-top: 1px solid #94a3b8; padding-top: 6px; font-size: 11px; color: #64748b; }

        /* Footer */
        .doc-footer { margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 12px; display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; }

        /* Print */
        .no-print { margin-bottom: 24px; }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
        }
    </style>
</head>
<body>
<div class="page">

    <!-- Botones (ocultos al imprimir) -->
    <div class="no-print" style="display:flex; gap:12px;">
        <button onclick="window.print()" style="background:#197fe6; color:#fff; border:none; border-radius:8px; padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer;">
            🖨️ Imprimir / Guardar PDF
        </button>
        <a href="javascript:history.back()" style="background:#f1f5f9; color:#334155; border:none; border-radius:8px; padding:10px 24px; font-size:14px; font-weight:600; text-decoration:none;">
            ← Regresar
        </a>
    </div>

    <!-- Encabezado -->
    <div class="header">
        <div class="header-logo">Control<br>Escolar</div>
        <div class="header-title">
            <h1>Kardex de Calificaciones</h1>
            <p>Ciclo: <?php echo e($ciclo['nombre'] ?? 'N/A'); ?> &nbsp;|&nbsp; Generado: <?php echo date('d/m/Y'); ?></p>
        </div>
    </div>

    <!-- Datos del alumno -->
    <div class="alumno-card">
        <div class="field">
            <div class="label">Alumno</div>
            <div class="value"><?php echo e(($alumno['nombre'] ?? '') . ' ' . ($alumno['apellido_paterno'] ?? '') . ' ' . ($alumno['apellido_materno'] ?? '')); ?></div>
        </div>
        <div class="field">
            <div class="label">Matrícula</div>
            <div class="value"><?php echo e($alumno['matricula'] ?? '—'); ?></div>
        </div>
        <div class="field">
            <div class="label">Grupo</div>
            <div class="value"><?php echo $grupo ? e($grupo['grado']) . '° ' . e($grupo['seccion']) : '—'; ?></div>
        </div>
        <div class="field">
            <div class="label">Turno</div>
            <div class="value"><?php echo e($grupo['turno'] ?? '—'); ?></div>
        </div>
    </div>

    <!-- Resumen estadístico -->
    <?php
        $aprobadas  = 0; $reprobadas = 0; $pendientes = 0;
        foreach ($calificaciones as $c) {
            if ($c['final'] === null)      $pendientes++;
            elseif ($c['final'] >= 6)      $aprobadas++;
            else                             $reprobadas++;
        }
    ?>
    <div class="resumen">
        <div class="resumen-card">
            <div class="num num-promedio"><?php echo number_format($promedio, 2); ?></div>
            <div class="lbl">Promedio General</div>
        </div>
        <div class="resumen-card">
            <div class="num" style="color:#166534;"><?php echo $aprobadas; ?></div>
            <div class="lbl">Aprobadas</div>
        </div>
        <div class="resumen-card">
            <div class="num" style="color:#991b1b;"><?php echo $reprobadas; ?></div>
            <div class="lbl">Reprobadas</div>
        </div>
        <div class="resumen-card">
            <div class="num" style="color:#64748b;"><?php echo $pendientes; ?></div>
            <div class="lbl">Pendientes</div>
        </div>
    </div>

    <!-- Tabla de calificaciones -->
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Materia</th>
                <th style="text-align:center;">P1</th>
                <th style="text-align:center;">P2</th>
                <th style="text-align:center;">P3</th>
                <th style="text-align:center; background:#eff6ff; color:#1e40af;">Final</th>
                <th style="text-align:center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$calificaciones): ?>
                <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">Sin calificaciones registradas en este ciclo.</td></tr>
            <?php endif; ?>
            <?php foreach ($calificaciones as $c): ?>
            <tr>
                <td style="font-weight:600;"><?php echo e($c['materia']); ?></td>
                <?php foreach (['p1','p2','p3'] as $col): ?>
                <td style="text-align:center;">
                    <?php if ($c[$col] !== null): ?>
                        <span class="<?php echo floatval($c[$col]) >= 6 ? 'aprobado' : 'reprobado'; ?>">
                            <?php echo number_format(floatval($c[$col]), 1); ?>
                        </span>
                    <?php else: ?>
                        <span class="pendiente">—</span>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
                <td style="text-align:center; background:#f8fbff;">
                    <?php if ($c['final'] !== null): ?>
                        <span class="cal-num <?php echo floatval($c['final']) >= 6 ? 'aprobado' : 'reprobado'; ?>">
                            <?php echo number_format(floatval($c['final']), 1); ?>
                        </span>
                    <?php else: ?>
                        <span class="pendiente">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($c['final'] === null): ?>
                        <span class="pendiente">Pendiente</span>
                    <?php elseif (floatval($c['final']) >= 6): ?>
                        <span class="aprobado">✓ Aprobado</span>
                    <?php else: ?>
                        <span class="reprobado">✗ Reprobado</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Firma -->
    <div class="firma">
        <div class="firma-box">
            <br><br>
            <div class="firma-line">Firma y sello de la institución</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="doc-footer">
        <span>Documento generado por el Sistema de Control Escolar</span>
        <span><?php echo date('d/m/Y H:i'); ?></span>
    </div>

</div>
</body>
</html>
