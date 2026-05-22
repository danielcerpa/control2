<?php include 'includes/header.php'; ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>grupos">Grupos</a></li>
        <li class="breadcrumb-item active">Materias del Grupo <?php echo e($grupo['nombre']); ?></li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">menu_book</span>Materias - Grupo <?php echo e($grupo['nombre']); ?></h1>
        <p class="text-muted mb-0">Grado <?php echo e($grupo['grado']); ?> &mdash; Sección <?php echo e($grupo['seccion']); ?></p>
    </div>
    <a href="<?php echo BASE_URL; ?>grupos" class="btn btn-outline-secondary" style="border-radius:8px; padding: 10px 20px; font-weight:600;">
        <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">arrow_back</span> Volver
    </a>
</div>

<div class="card shadow-sm border-0" style="border-radius: 12px;">
    <div class="card-body p-0">
        <?php if (empty($materias)): ?>
            <div class="p-5 text-center text-muted">
                <span class="material-symbols-outlined mb-2" style="font-size:48px; opacity:0.3;">menu_book</span>
                <p class="mt-2 mb-0">No hay materias asignadas a este grupo.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-top-0 pl-4">Materia</th>
                            <th class="border-top-0 text-center">Cupo Máximo</th>
                            <th class="border-top-0">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materias as $m): ?>
                            <tr>
                                <td class="pl-4 font-weight-bold" style="color: #197fe6;"><?php echo e($m['nombre']); ?></td>
                                <td class="text-center"><?php echo e($m['cupo_maximo']); ?> alumnos</td>
                                <td><span class="badge badge-success px-3 py-2" style="border-radius: 20px;">Activa</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
