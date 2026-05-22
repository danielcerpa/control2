<?php
/**
 * Variables inyectadas por Controller::view() via extract($data):
 * @var array $usuarios  Lista de todos los usuarios del sistema
 */
include 'includes/header.php'; ?>

<?php
// Clasificación de usuarios por rol
$admins = array_filter($usuarios, function($u) { return $u['rol'] === 'admin'; });
$docentes = array_filter($usuarios, function($u) { return $u['rol'] === 'docente'; });
$alumnos = array_filter($usuarios, function($u) { return $u['rol'] === 'alumno'; });

function renderizar_tabla_usuarios(array $lista_usuarios, string $vacia_msg, string $table_id) {
    ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0 tabla-usuarios-rol" id="<?php echo $table_id; ?>">
            <thead class="bg-light">
                <tr>
                    <th class="pl-4">Nombre de usuario</th>
                    <th class="text-center" style="width: 150px;">Estado</th>
                    <th class="text-right pr-4" style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista_usuarios)): ?>
                    <tr class="no-results-row-default">
                        <td colspan="3" class="text-center text-muted py-5">
                            <span class="material-symbols-outlined" style="font-size:48px; opacity:0.2;">group</span>
                            <p class="mt-2"><?php echo $vacia_msg; ?></p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lista_usuarios as $u): ?>
                        <tr>
                            <td class="pl-4 align-middle">
                                <span class="font-weight-bold text-dark nombre-usuario-val"><?php echo e($u['nombre_usuario']); ?></span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge" style="border-radius:20px; padding: 6px 15px; background: <?php echo $u['estado'] ? '#dcfce7' : '#f1f5f9'; ?>; color: <?php echo $u['estado'] ? '#166534' : '#475569'; ?>; font-weight: 600; font-size:11px;">
                                    <?php echo $u['estado'] ? 'ACTIVO' : 'INACTIVO'; ?>
                                </span>
                            </td>
                            <td class="align-middle text-right pr-4">
                                <div class="btn-group">
                                    <a href="<?php echo BASE_URL; ?>usuarios/edit/<?php echo $u['id_usuario']; ?>" class="btn btn-sm btn-outline-primary" title="Editar" style="border-radius:6px 0 0 6px;">
                                        <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">edit</span>
                                    </a>
                                    <?php if ($u['id_usuario'] == $_SESSION['usuario_id']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="No puedes eliminarte a ti mismo" style="border-radius:0 6px 6px 0; cursor:not-allowed;" disabled>
                                            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">delete</span>
                                        </button>
                                    <?php elseif ($u['id_usuario'] == 1 || $u['nombre_usuario'] === 'admin'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="No se puede eliminar la cuenta principal del administrador" style="border-radius:0 6px 6px 0; cursor:not-allowed;" disabled>
                                            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">delete</span>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDeleteUsuario(<?php echo $u['id_usuario']; ?>, '<?php echo addslashes($u['nombre_usuario']); ?>')" style="border-radius:0 6px 6px 0;">
                                            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">delete</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard"><span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">home</span></a></li>
        <li class="breadcrumb-item active">Usuarios</li>
    </ol>
</nav>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><span class="material-symbols-outlined mr-2" style="font-size:28px;">manage_accounts</span>Gestión de Usuarios</h1>
        <p>Administración de personal con acceso al sistema</p>
    </div>
    <div class="d-flex">
        <a href="<?php echo BASE_URL; ?>usuarios/create" class="btn btn-primary" style="background:#197fe6; border:none; border-radius:8px; padding: 10px 20px; font-weight:600;">
            <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">person_add</span> Nuevo Usuario
        </a>
    </div>
</div>

<!-- Barra de búsqueda interactiva -->
<div class="filter-bar mb-4">
    <div class="form-row">
        <div class="col-12 col-md-6">
            <label class="small font-weight-bold text-secondary">Buscar Usuario</label>
            <div class="position-relative">
                <span class="material-symbols-outlined" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:18px;">search</span>
                <input type="text" id="filtro_usuario" class="form-control pl-5" placeholder="Escribe el nombre de usuario para filtrar..." style="border-radius:8px; height: 42px;">
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs {
    border-bottom: 2px solid #e2e8f0;
    gap: 5px;
}
.nav-tabs .nav-link {
    border: none;
    color: #64748b;
    padding: 12px 20px;
    border-radius: 8px 8px 0 0;
    transition: all 0.2s ease-in-out;
    font-size: 14px;
}
.nav-tabs .nav-link:hover {
    color: #197fe6;
    background: #f8fafc;
    border-bottom: 3px solid #cbd5e1;
}
.nav-tabs .nav-link.active {
    color: #197fe6 !important;
    border-bottom: 3px solid #197fe6 !important;
    background: transparent;
}
</style>

<ul class="nav nav-tabs mb-0" id="userTabs" role="tablist" style="border-top-left-radius: 12px; border-top-right-radius: 12px; padding: 15px 20px 0 20px;">
  <li class="nav-item">
    <a class="nav-link active font-weight-bold" id="admins-tab" data-toggle="tab" href="#admins" role="tab" aria-controls="admins" aria-selected="true">
        <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">admin_panel_settings</span>
        Administradores (<span class="tab-badge-count" id="count-admins"><?php echo count($admins); ?></span>)
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link font-weight-bold" id="docentes-tab" data-toggle="tab" href="#docentes" role="tab" aria-controls="docentes" aria-selected="false">
        <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">badge</span>
        Docentes (<span class="tab-badge-count" id="count-docentes"><?php echo count($docentes); ?></span>)
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link font-weight-bold" id="alumnos-tab" data-toggle="tab" href="#alumnos" role="tab" aria-controls="alumnos" aria-selected="false">
        <span class="material-symbols-outlined mr-1" style="font-size:20px; vertical-align:middle;">school</span>
        Alumnos (<span class="tab-badge-count" id="count-alumnos"><?php echo count($alumnos); ?></span>)
    </a>
  </li>
</ul>

<div class="tab-content card border-0 shadow-sm mb-5" id="userTabsContent" style="border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; overflow: hidden;">
  <div class="tab-pane fade show active" id="admins" role="tabpanel" aria-labelledby="admins-tab">
    <?php renderizar_tabla_usuarios($admins, "No hay administradores registrados.", "tabla_admins"); ?>
  </div>
  <div class="tab-pane fade" id="docentes" role="tabpanel" aria-labelledby="docentes-tab">
    <?php renderizar_tabla_usuarios($docentes, "No hay docentes registrados.", "tabla_docentes"); ?>
  </div>
  <div class="tab-pane fade" id="alumnos" role="tabpanel" aria-labelledby="alumnos-tab">
    <?php renderizar_tabla_usuarios($alumnos, "No hay alumnos registrados.", "tabla_alumnos"); ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    var originals = {
        'tabla_admins': <?php echo count($admins); ?>,
        'tabla_docentes': <?php echo count($docentes); ?>,
        'tabla_alumnos': <?php echo count($alumnos); ?>
    };

    // Filtrar tabla en tiempo real
    $('#filtro_usuario').on('keyup input', function() {
        var query = $(this).val().toLowerCase().trim();
        var counts = {
            'tabla_admins': 0,
            'tabla_docentes': 0,
            'tabla_alumnos': 0
        };
        
        $('.tabla-usuarios-rol').each(function() {
            var $table = $(this);
            var tableId = $table.attr('id');
            var matchCount = 0;
            var originalEmpty = originals[tableId] === 0;
            
            if (originalEmpty) return;
            
            $table.find('tbody tr').each(function() {
                if ($(this).hasClass('no-results-row-search')) return;
                
                var username = $(this).find('.nombre-usuario-val').text().toLowerCase();
                if (username.startsWith(query)) {
                    $(this).show();
                    matchCount++;
                } else {
                    $(this).hide();
                }
            });
            
            counts[tableId] = matchCount;
            
            $table.find('.no-results-row-search').remove();
            if (matchCount === 0 && query !== '') {
                $table.find('tbody').append(
                    '<tr class="no-results-row-search">' +
                    '  <td colspan="3" class="text-center text-muted py-5">' +
                    '    <span class="material-symbols-outlined" style="font-size:48px; opacity:0.2;">group</span>' +
                    '    <p class="mt-2">No se encontraron usuarios coincidentes.</p>' +
                    '  </td>' +
                    '</tr>'
                );
            }
        });
        
        // Actualizar contadores visuales en las pestañas
        if (query === '') {
            $('#count-admins').text(originals['tabla_admins']);
            $('#count-docentes').text(originals['tabla_docentes']);
            $('#count-alumnos').text(originals['tabla_alumnos']);
        } else {
            $('#count-admins').text(counts['tabla_admins']);
            $('#count-docentes').text(counts['tabla_docentes']);
            $('#count-alumnos').text(counts['tabla_alumnos']);
        }
    });
});

function confirmDeleteUsuario(id, nombre) {
    Swal.fire({
        title: '¿Desactivar usuario?',
        html: 'Se desactivará al usuario <strong>' + nombre + '</strong>. No podrá iniciar sesión en el sistema.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-xl shadow-lg border-0',
            confirmButton: 'font-weight-bold px-4 rounded-lg',
            cancelButton: 'font-weight-bold px-4 rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo BASE_URL; ?>usuarios/delete/' + id;
        }
    })
}
</script>