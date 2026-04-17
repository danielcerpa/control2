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
                <button class="btn-ver-horario" data-color="<?php echo $color; ?>"
                        onclick="verHorario(<?php echo $a['id_alumno']; ?>, this.closest('.alumno-card'))">
                    <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">calendar_month</span>
                    Ver Horario
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ===== MODAL DE HORARIO ===== -->
<div id="modalHorario" class="modal-horario-overlay" onclick="cerrarModal(event)">
    <div class="modal-horario-box" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
        <!-- Encabezado -->
        <div class="modal-horario-header" id="modalHeaderColor">
            <div class="modal-alumno-info">
                <div class="modal-avatar" id="modalAvatar"></div>
                <div>
                    <div class="modal-alumno-nombre" id="modalTitulo">—</div>
                    <div class="modal-alumno-sub" id="modalSub">—</div>
                </div>
            </div>
            <button class="modal-close-btn" onclick="document.getElementById('modalHorario').classList.remove('active')" aria-label="Cerrar">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Contenido / Tabla -->
        <div class="modal-horario-body">
            <div id="modalLoading" class="modal-loading">
                <div class="spinner"></div>
                <p>Cargando horario...</p>
            </div>
            <div id="modalError" class="modal-error d-none">
                <span class="material-symbols-outlined" style="font-size:48px;opacity:0.3;">event_busy</span>
                <p>Este alumno no tiene materias inscritas con horario registrado.</p>
            </div>
            <div id="modalTablaWrap" class="d-none">
                <div class="table-responsive">
                    <table class="horario-table" id="horarioTable">
                        <thead>
                            <tr>
                                <th class="col-hora">Hora</th>
                                <th>Lunes</th>
                                <th>Martes</th>
                                <th>Miércoles</th>
                                <th>Jueves</th>
                                <th>Viernes</th>
                            </tr>
                        </thead>
                        <tbody id="horarioBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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

/* =========================================
   MODAL OVERLAY
   ========================================= */
.modal-horario-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.55);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-horario-overlay.active {
    display: flex;
}

/* =========================================
   MODAL BOX
   ========================================= */
.modal-horario-box {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 860px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 24px 64px rgba(0,0,0,.22);
    overflow: hidden;
    animation: modalIn .25s cubic-bezier(.34,1.56,.64,1);
}
@keyframes modalIn {
    from { opacity:0; transform:scale(.94) translateY(12px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}

/* Header del modal */
.modal-horario-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    background: #1e293b;
    flex-shrink: 0;
}
.modal-alumno-info {
    display: flex;
    align-items: center;
    gap: 14px;
}
.modal-avatar {
    width: 48px; height: 48px;
    border-radius: 50%;
    background: #334155;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 700; color: #fff;
    overflow: hidden; flex-shrink: 0;
}
.modal-avatar img { width:100%; height:100%; object-fit:cover; }
.modal-alumno-nombre {
    font-weight: 700;
    color: #fff;
    font-size: 17px;
    line-height: 1.2;
}
.modal-alumno-sub {
    color: #94a3b8;
    font-size: 13px;
    margin-top: 2px;
}
.modal-close-btn {
    background: rgba(255,255,255,.1);
    border: none;
    border-radius: 8px;
    color: #fff;
    padding: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background .15s;
}
.modal-close-btn:hover { background: rgba(255,255,255,.2); }

/* Body del modal */
.modal-horario-body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

/* Loading */
.modal-loading {
    text-align: center;
    padding: 40px 0;
    color: #64748b;
}
.spinner {
    width: 40px; height: 40px;
    border: 3px solid #e2e8f0;
    border-top-color: #197fe6;
    border-radius: 50%;
    animation: spin .7s linear infinite;
    margin: 0 auto 14px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Error */
.modal-error {
    text-align: center;
    padding: 40px 0;
    color: #94a3b8;
}

/* =========================================
   TABLA DE HORARIO
   ========================================= */
.horario-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 13px;
}
.horario-table thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 10px 12px;
    border-bottom: 2px solid #e2e8f0;
    text-align: center;
}
.horario-table thead th.col-hora {
    text-align: left;
    width: 90px;
    min-width: 80px;
}
.horario-table tbody td {
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    padding: 0;
}
/* Celda de hora */
.horario-table tbody td.td-hora {
    padding: 8px 12px;
    color: #64748b;
    font-weight: 700;
    font-size: 12px;
    white-space: nowrap;
    background: #f8fafc;
    border-right: 2px solid #e2e8f0;
    text-align: left;
}
/* Celda vacía */
.horario-table tbody td.td-vacia {
    background: #fff;
    min-height: 44px;
    height: 44px;
}
/* Celda con materia (rowspan) */
.td-materia {
    padding: 6px 8px 6px 10px;
    vertical-align: middle;
    text-align: center;
    position: relative;
}
.materia-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    border-radius: 8px;
    padding: 8px 10px;
    color: #fff;
    font-weight: 600;
    font-size: 12px;
    line-height: 1.3;
    min-height: 40px;
    margin-left: 6px;
}
.materia-chip .mat-nombre {
    font-size: 12px;
    font-weight: 700;
}
.materia-chip .mat-salon {
    font-size: 10px;
    opacity: .85;
    font-weight: 400;
}
</style>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';

// Paleta de colores para materias
const PALETTE = [
    '#197fe6','#7c3aed','#059669','#dc2626',
    '#d97706','#0891b2','#be185d','#16a34a',
    '#9333ea','#ea580c'
];

function colorMateria(id) {
    return PALETTE[(id - 1) % PALETTE.length];
}

function verHorario(id_alumno, card) {
    const modal    = document.getElementById('modalHorario');
    const loading  = document.getElementById('modalLoading');
    const error    = document.getElementById('modalError');
    const tablaWrap = document.getElementById('modalTablaWrap');

    // Datos del alumno desde el card
    const nombre    = card.dataset.nombre;
    const matricula = card.dataset.matricula;
    const grupo     = card.dataset.grupo;

    // Avatar
    const avatar = document.getElementById('modalAvatar');
    const initials = nombre.split(' ').slice(0,2).map(p=>p[0]).join('').toUpperCase();
    const img = card.querySelector('.alumno-avatar img');
    if (img) {
        avatar.innerHTML = `<img src="${img.src}" alt="">`;
    } else {
        const color = card.querySelector('.btn-ver-horario').dataset.color || '#197fe6';
        avatar.innerHTML = `<span style="color:${color}">${initials}</span>`;
    }

    document.getElementById('modalTitulo').textContent = nombre;
    document.getElementById('modalSub').textContent    = `${matricula} · Grupo ${grupo}`;

    // Reset
    loading.classList.remove('d-none');
    error.classList.add('d-none');
    tablaWrap.classList.add('d-none');
    modal.classList.add('active');

    fetch(`${BASE_URL}horarios/horario/${id_alumno}`)
        .then(r => r.json())
        .then(data => {
            loading.classList.add('d-none');
            if (!data || data.length === 0) {
                error.classList.remove('d-none');
                return;
            }
            tablaWrap.classList.remove('d-none');
            renderHorario(data);
        })
        .catch(() => {
            loading.classList.add('d-none');
            error.classList.remove('d-none');
        });
}

function cerrarModal(e) {
    if (e.target === document.getElementById('modalHorario')) {
        document.getElementById('modalHorario').classList.remove('active');
    }
}

// ESC para cerrar
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('modalHorario').classList.remove('active');
});

/**
 * Genera la tabla de horario con rowspan para materias de varias horas.
 * Rango de horas: 07:00 – 20:00 (slots de 1 hora).
 */
function renderHorario(filas) {
    const DIAS = ['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES'];

    // 1. Determinar el rango de horas a mostrar
    let minH = 7, maxH = 14;
    filas.forEach(f => {
        const hi = parseInt(f.hora_inicio);
        const hf = parseInt(f.hora_fin);
        if (hi < minH) minH = hi;
        if (hf > maxH) maxH = hf;
    });

    // 2. Construir grid[dia][hora] = {materia, color, sesionId, rowspan, skip}
    // skip=true significa que la fila ya fue "consumida" por un rowspan superior
    const grid = {};
    DIAS.forEach(d => { grid[d] = {}; });

    // Asignar colores a materias (mismo color para todas las sesiones de la misma materia)
    const colorMap = {};
    let colorIdx = 0;
    filas.forEach(f => {
        if (!colorMap[f.id_materia]) {
            colorMap[f.id_materia] = PALETTE[colorIdx % PALETTE.length];
            colorIdx++;
        }
        const hi = parseInt(f.hora_inicio);
        const hf = parseInt(f.hora_fin);
        const rowspan = hf - hi; // Número de slots de 1 hora que ocupa
        for (let h = hi; h < hf; h++) {
            grid[f.dia][h] = {
                materia: f.materia,
                profesor: f.profesor,
                salon: f.salon,
                color: colorMap[f.id_materia],
                rowspan: (h === hi) ? rowspan : 0, // 0 = skip (colspan handled by first row)
                hi: hi
            };
        }
    });

    // 3. Renderizar
    const tbody = document.getElementById('horarioBody');
    tbody.innerHTML = '';

    for (let h = minH; h < maxH; h++) {
        const tr = document.createElement('tr');

        // Celda de hora
        const tdH = document.createElement('td');
        tdH.className = 'td-hora';
        tdH.textContent = `${String(h).padStart(2,'0')}:00 – ${String(h+1).padStart(2,'0')}:00`;
        tr.appendChild(tdH);

        // Celdas de cada día
        DIAS.forEach(dia => {
            const celda = grid[dia][h];

            if (!celda) {
                // Vacía
                const td = document.createElement('td');
                td.className = 'td-vacia';
                tr.appendChild(td);
                return;
            }

            if (celda.rowspan === 0) {
                // Esta fila es "absorbida" por el rowspan de una fila anterior → no añadir <td>
                return;
            }

            // Primera fila del span
            const td = document.createElement('td');
            td.className = 'td-materia';
            td.rowSpan = celda.rowspan;

            const chip = document.createElement('div');
            chip.className = 'materia-chip';
            chip.style.background = celda.color;

            let html = `<span class="mat-nombre">${escHtml(celda.materia)}</span>`;
            if (celda.salon) html += `<span class="mat-salon"><span class="material-symbols-outlined" style="font-size:11px;vertical-align:middle;">meeting_room</span> ${escHtml(celda.salon)}</span>`;

            chip.innerHTML = html;
            td.appendChild(chip);
            tr.appendChild(td);
        });

        tbody.appendChild(tr);
    }
}

function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php include 'includes/footer.php'; ?>