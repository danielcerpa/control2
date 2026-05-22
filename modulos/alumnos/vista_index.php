<?php
/**
 * @var array $filtros
 * @var array $grupos
 * @var array $alumnos
 */
include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Alumnos</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">person</span>Alumnos</h1>
        <p>Gestión de estudiantes registrados</p>
    </div>
    <?php if (puede_ver('alumnos') && ($GLOBALS['_SESSION']['usuario_rol'] ?? '') !== 'profesor'): ?>
        <div class="d-flex">
            <a href="<?php echo BASE_URL; ?>alumnos/create" class="btn btn-primary" style="background:#197fe6; border:none; border-radius:8px; padding: 10px 20px; font-weight:600;">
                <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">person_add</span> Nuevo Alumno
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="filter-bar">
    <form method="get" action="<?php echo BASE_URL; ?>alumnos" class="form-row align-items-end">
        <div class="col-12 col-md-4 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary">Buscar</label>
            <div class="position-relative">
                <span class="material-symbols-outlined" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:18px;">search</span>
                <input type="text" name="q" class="form-control pl-5" placeholder="Nombre, matrícula..." value="<?php echo e($filtros['q']); ?>">
            </div>
        </div>
        <div class="col-6 col-md-2 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary">Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="Activo" <?php if ($filtros['estado'] === 'Activo')   echo 'selected'; ?>>Activo</option>
                <option value="Inactivo" <?php if ($filtros['estado'] === 'Inactivo') echo 'selected'; ?>>Inactivo</option>
                <option value="Baja" <?php if ($filtros['estado'] === 'Baja')     echo 'selected'; ?>>Baja</option>
            </select>
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary">Grupo</label>
            <select name="grupo" class="form-control">
                <option value="0">Todos los grupos</option>
                <?php foreach ($grupos as $g): ?>
                    <option value="<?php echo $g['id_grupo']; ?>" <?php if ($filtros['grupo'] == $g['id_grupo']) echo 'selected'; ?>>
                        <?php echo e($g['nombre'] ?? ($g['grado'] . ' ' . $g['seccion'])); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex">
            <button type="submit" class="btn btn-primary flex-fill mr-2" style="background:#197fe6; border:none; border-radius:8px;">
                Filtrar
            </button>
            <a href="<?php echo BASE_URL; ?>alumnos" class="btn btn-outline-secondary" style="border-radius:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">restart_alt</span>
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
        <h5 class="font-weight-bold mb-0">
            <span class="material-symbols-outlined mr-2" style="font-size:20px; vertical-align:middle; color:#197fe6;">list</span>
            Lista de Alumnos <span class="badge badge-info ml-2" style="background:#eff6ff; color:#197fe6; border:none;"><?php echo count($alumnos); ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="text-uppercase" style="font-size:11px; letter-spacing:1px;">
                        <th class="pl-4">Alumno</th>
                        <th>Matrícula</th>
                        <th>CURP</th>
                        <th>Sexo</th>
                        <th>Estado</th>
                        <th class="text-right pr-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$alumnos): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <span class="material-symbols-outlined" style="font-size:48px; opacity:0.2;">person</span>
                                <p class="mt-2">No se encontraron alumnos.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($alumnos as $a): ?>
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-3">
                                        <?php if ($a['ruta_foto']): ?>
                                            <img src="<?php echo e($a['ruta_foto']); ?>" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                        <?php else: ?>
                                            <span><?php echo strtoupper(substr($a['nombre'], 0, 1)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold text-dark"><?php echo e($a['apellido_paterno'] . ' ' . $a['apellido_materno']); ?></div>
                                        <div class="small text-secondary"><?php echo e($a['nombre']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="font-weight-600"><?php echo e($a['matricula']); ?></td>
                            <td class="small text-muted"><?php echo e($a['curp']); ?></td>
                            <td>
                                <?php if ($a['genero'] === 'M' || $a['genero'] === 'Masculino'): ?>
                                    <span class="text-primary font-weight-bold">M</span>
                                <?php elseif ($a['genero'] === 'O' || $a['genero'] === 'Otro'): ?>
                                    <span class="text-success font-weight-bold">O</span>
                                <?php else: ?>
                                    <span class="text-danger font-weight-bold">F</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $bc = array('Activo' => 'success', 'Inactivo' => 'secondary', 'Baja' => 'danger', 'Egresado' => 'info');
                                $badge = isset($bc[$a['estado']]) ? $bc[$a['estado']] : 'secondary';
                                ?>
                                <span class="badge badge-<?php echo $badge; ?>"><?php echo e($a['estado']); ?></span>
                            </td>
                            <td class="text-right pr-4">
                                <?php if (puede_ver('alumnos') && ($GLOBALS['_SESSION']['usuario_rol'] ?? '') !== 'profesor'): ?>
                                    <div class="btn-group">
                                        <a href="<?php echo BASE_URL; ?>alumnos/edit/<?php echo $a['id_alumno']; ?>" class="btn btn-sm btn-outline-primary" title="Editar" style="border-radius:6px 0 0 6px;">
                                            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">edit</span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDeleteAlumno(<?php echo $a['id_alumno']; ?>, '<?php echo addslashes($a['nombre'] . ' ' . $a['apellido_paterno']); ?>')" style="border-radius:0 6px 6px 0;">
                                            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">delete</span>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteAlumno(id, nombre) {
    Swal.fire({
        title: '¿Dar de baja alumno?',
        html: 'Se cambiará el estado de <strong>' + nombre + '</strong> a Inactivo. Su historial académico y acceso al sistema se conservarán.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, dar de baja',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-xl shadow-lg border-0',
            confirmButton: 'font-weight-bold px-4 rounded-lg',
            cancelButton: 'font-weight-bold px-4 rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo BASE_URL; ?>alumnos/delete/' + id;
        }
    })
}
</script>



<?php include 'includes/footer.php'; ?>