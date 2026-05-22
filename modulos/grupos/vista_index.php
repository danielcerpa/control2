<?php include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Grupos</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">groups</span>Grupos</h1>
        <p>Gestión de grupos escolares</p>
    </div>
    <div class="d-flex">
        <?php if ($u['rol'] === 'director' || $u['rol'] === 'admin'): ?>

            <a href="<?php echo BASE_URL; ?>grupos/create" class="btn btn-primary" style="background:#197fe6; border:none; border-radius:8px; padding: 10px 20px; font-weight:600;">
                <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">group_add</span> Nuevo Grupo
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <?php if (!$grupos): ?>
        <div class="col-12">
            <div class="card p-5 text-center text-muted border-0 shadow-sm" style="border-radius:12px;">
                <span class="material-symbols-outlined" style="font-size:48px; opacity:0.2;">group</span>
                <p class="mt-2">No hay grupos registrados aún.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($grupos as $g): ?>
        <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
            <div class="card h-100 border-0 shadow-sm group-card" style="border-radius:12px; overflow:hidden; transition: transform 0.2s;">
                <div class="card-body text-center py-4">
                    <div class="mx-auto d-flex align-items-center justify-content-center mb-3 group-count"
                        style="width:72px; height:72px; background:#eff6ff; color:#197fe6; border-radius:16px; font-size:24px; font-weight:bold; border:1px solid #dbeafe;">
                        <?php echo e($g['nombre']); ?>
                    </div>
                    <h5 class="font-weight-bold mb-1" style="color:#0e141b;">Grupo <?php echo e($g['nombre']); ?></h5>
                    <p class="text-secondary small mb-3">Grado <?php echo e($g['grado']); ?> &mdash; Sección <?php echo e($g['seccion']); ?></p>

                    <div class="mb-3">
                        <span class="badge" style="background:#f1f5f9; color:#475569; border:none; padding:8px 12px; border-radius:20px; font-weight:600;">
                            <span class="material-symbols-outlined mr-1" style="font-size:16px; vertical-align:middle;">schedule</span>
                            Turno: <?php echo e($g['turno'] ?: 'M/D'); ?>
                        </span>
                    </div>

                    <p class="text-muted small mb-0 d-flex align-items-center justify-content-center">
                        <span class="material-symbols-outlined mr-1" style="font-size:16px;">calendar_today</span>
                        <?php echo e($g['ciclo_nombre'] ?? $g['ciclo_escolar']); ?>
                    </p>

                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>grupos/materias/<?php echo $g['id_grupo']; ?>" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-weight:600; padding: 6px 12px; width:100%;">
                            <span class="material-symbols-outlined mr-1" style="font-size: 18px; vertical-align: text-bottom;">list_alt</span> Ver Materias
                        </a>
                    </div>
                    <?php if ($u['rol'] === 'director' || $u['rol'] === 'admin'): ?>
                    <div class="d-flex justify-content-between mt-2">
                        <a href="<?php echo BASE_URL; ?>grupos/edit/<?php echo $g['id_grupo']; ?>" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight:600; flex:1; margin-right: 4px;" title="Editar">
                            <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: text-bottom;">edit</span>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteGrupo(<?php echo $g['id_grupo']; ?>, '<?php echo addslashes($g['nombre'] ?? ($g['grado'] . ' ' . $g['seccion'])); ?>')" style="border-radius: 8px; font-weight:600; flex:1; margin-left: 4px;" title="Eliminar">
                            <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: text-bottom;">delete</span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>



<style>
    .group-card:hover {
        transform: translateY(-5px);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteGrupo(id, nombre) {
    Swal.fire({
        title: '¿Eliminar grupo?',
        html: 'Se eliminará el grupo <strong>' + nombre + '</strong> permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-xl shadow-lg border-0',
            confirmButton: 'font-weight-bold px-4 rounded-lg',
            cancelButton: 'font-weight-bold px-4 rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo BASE_URL; ?>grupos/delete/' + id;
        }
    })
}
</script>

<?php include 'includes/footer.php'; ?>