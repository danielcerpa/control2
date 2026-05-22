<?php
/**
 * @var array $filtros
 * @var array $docentes
 */
$filtros = $filtros ?? ['q' => '', 'estado' => ''];
$docentes = $docentes ?? [];
include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Docentes</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">badge</span>Docentes</h1>
        <p>Gestión del personal docente</p>
    </div>
    <div class="d-flex">
        <a href="<?php echo BASE_URL; ?>docentes/create" class="btn btn-primary" style="background:#197fe6; border:none; border-radius:8px; padding: 10px 20px; font-weight:600;">
            <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">person_add</span> Nuevo Docente
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="filter-bar">
    <form method="get" action="<?php echo BASE_URL; ?>docentes" class="form-row align-items-end">
        <div class="col-12 col-md-5 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary">Buscar</label>
            <div class="position-relative">
                <span class="material-symbols-outlined" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:18px;">search</span>
                <input type="text" name="q" class="form-control pl-5" placeholder="Nombre, N° empleado, curp..." value="<?php echo e($filtros['q']); ?>">
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <label class="small font-weight-bold text-secondary">Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="Activo"   <?php if ($filtros['estado'] === 'Activo')   echo 'selected'; ?>>Activo</option>
                <option value="Inactivo" <?php if ($filtros['estado'] === 'Inactivo') echo 'selected'; ?>>Inactivo</option>
            </select>
        </div>
        <div class="col-12 col-md-4 d-flex">
            <button type="submit" class="btn btn-primary flex-fill mr-2" style="background:#197fe6; border:none; border-radius:8px;">
                Filtrar
            </button>
            <a href="<?php echo BASE_URL; ?>docentes" class="btn btn-outline-secondary" style="border-radius:8px;">
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
            Lista de Docentes <span class="badge badge-info ml-2" style="background:#eff6ff; color:#197fe6; border:none;"><?php echo count($docentes); ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="text-uppercase" style="font-size:11px; letter-spacing:1px;">
                        <th class="pl-4">Docente</th>
                        <th>N° Empleado</th>
                        <th>CURP</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th class="text-center">Materias Asig.</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$docentes): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <span class="material-symbols-outlined" style="font-size:48px; opacity:0.2;">person</span>
                                <p class="mt-2">No se encontraron docentes.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($docentes as $d): ?>
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-3">
                                        <?php if ($d['ruta_foto']): ?>
                                            <img src="<?php echo e($d['ruta_foto']); ?>" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                        <?php else: ?>
                                            <span><?php echo strtoupper(substr($d['nombre_completo'], 0, 1)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold text-dark"><?php echo e($d['nombre_completo']); ?></div>
                                        <div class="small text-secondary"><?php echo e($d['grado_academico'] ?: 'Docente'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="font-weight-600"><?php echo e($d['numero_empleado']); ?></td>
                            <td class="small text-muted"><?php echo e($d['curp'] ?: '—'); ?></td>
                            <td><?php echo e($d['telefono'] ?: '—'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $d['estado'] === 'Activo' ? 'success' : 'secondary'; ?>"><?php echo e($d['estado']); ?></span>
                            </td>
                            <td class="text-center align-middle">
                                <?php $cnt = count($d['materias']); ?>
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    style="border-radius:20px; font-size:12px;"
                                    data-toggle="modal"
                                    data-target="#modalMaterias<?php echo $d['id_profesor']; ?>">
                                    <span class="material-symbols-outlined mr-1" style="font-size:14px;vertical-align:middle;">menu_book</span>
                                    <?php echo $cnt; ?> materia<?php echo $cnt !== 1 ? 's' : ''; ?>
                                </button>
                            </td>
                            <td class="text-center align-middle">
                                <div class="btn-group">
                                    <a href="<?php echo BASE_URL; ?>docentes/edit/<?php echo $d['id_profesor']; ?>" class="btn btn-sm btn-outline-primary" title="Editar" style="border-radius:6px 0 0 6px;">
                                        <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">edit</span>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDeleteDocente(<?php echo $d['id_profesor']; ?>, '<?php echo addslashes($d['nombre_completo']); ?>')" style="border-radius:0 6px 6px 0;">
                                        <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">delete</span>
                                    </button>
                                </div>
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
function confirmDeleteDocente(id, nombre) {
    Swal.fire({
        title: '¿Dar de baja docente?',
        html: 'Se cambiará el estado de <strong>' + nombre + '</strong> a Inactivo. Se conservará su historial y se deshabilitará su acceso.',
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
            window.location.href = '<?php echo BASE_URL; ?>docentes/delete/' + id;
        }
    })
}
</script>

<?php foreach ($docentes as $d): ?>
<div class="modal fade" id="modalMaterias<?php echo $d['id_profesor']; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius:12px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold">
          <span class="material-symbols-outlined mr-2" style="font-size:22px;vertical-align:middle;color:#197fe6;">menu_book</span>
          Materias de <?php echo e($d['nombre_completo']); ?>
        </h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <?php if (!$d['materias']): ?>
          <p class="text-muted text-center py-3">No tiene materias asignadas actualmente.</p>
        <?php else: ?>
          <table class="table table-hover mb-0" style="font-size:14px;">
            <thead class="bg-light">
              <tr class="text-uppercase" style="font-size:11px;letter-spacing:1px;">
                <th>Materia</th><th>Grupo</th><th>Horario</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($d['materias'] as $mat): ?>
              <tr>
                <td class="font-weight-bold"><?php echo e($mat['nombre']); ?></td>
                 <td><?php echo $mat['grado'] ? e($mat['grado']).'&deg; '.e($mat['seccion']) . ($mat['turno'] ? ' - ' . ucfirst(strtolower($mat['turno'])) : '') : '<span class="text-muted">Sin grupo</span>'; ?></td>
                <td class="small text-muted"><?php echo e($mat['horario'] ?? '—'); ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>