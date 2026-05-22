<?php
// includes/header.php — Cabecera HTML compartida
// Variables esperadas: $page_title (string), $modulo_activo (string)
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <!-- Aplicar tema oscuro ANTES del render para evitar parpadeo (FOUC) -->
  <script>if(localStorage.getItem('theme')==='dark')document.documentElement.setAttribute('data-theme','dark');</script>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/fonts.css">
  <!-- Bootstrap 4 LOCAL -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap-icons.css">
  <!-- Estilos propios -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/app.css?v=<?php echo filemtime(dirname(__FILE__) . '/../assets/css/app.css'); ?>">

  <?php
  // Cargar CSS específico por módulo si existe
  $modulos_css = array('alumnos', 'docentes', 'grupos', 'materias', 'horarios', 'calificaciones', 'usuarios', 'reportes', 'salones', 'ciclos');
  // Módulos del portal alumno usan alumno.css
  $modulos_alumno_css = array('mi_perfil', 'mi_horario', 'mis_calificaciones', 'mis_materias', 'alumno');
  if (isset($modulo_activo) && in_array($modulo_activo, $modulos_alumno_css)) {
    $css_file = dirname(__FILE__) . '/../assets/css/alumno.css';
    $version = file_exists($css_file) ? filemtime($css_file) : time();
    echo '  <link rel="stylesheet" href="' . BASE_URL . 'assets/css/alumno.css?v=' . $version . '">' . "\n";
  } elseif (isset($modulo_activo) && in_array($modulo_activo, $modulos_css)) {
    $css_file = dirname(__FILE__) . '/../assets/css/' . $modulo_activo . '.css';
    $version = file_exists($css_file) ? filemtime($css_file) : time();
    echo '  <link rel="stylesheet" href="' . BASE_URL . 'assets/css/' . $modulo_activo . '.css?v=' . $version . '">' . "\n";
  }
  ?>
  <!--[if lt IE 9]>
  <script src="<?php echo BASE_URL; ?>assets/js/html5shiv.min.js"></script>
  <![endif]-->
  <script>
    // Forzar recarga si la página se carga desde la caché de "Adelante/Atrás" del navegador
    window.onpageshow = function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    };
  </script>
</head>

<body>

  <?php
  // Mostrar flash si existe
  $flash = get_flash();
  if ($flash): ?>
    <div id="flash-container">
      <div class="alert alert-<?php echo e($flash['tipo']); ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
          <span class="material-symbols-outlined mr-2" style="font-size: 20px; line-height: 1; margin-top: 1px;">
            <?php
            switch ($flash['tipo']) {
              case 'danger': echo 'error'; break;
              case 'warning': echo 'warning'; break;
              case 'info': echo 'info'; break;
              default: echo 'check_circle'; break;
            }
            ?>
          </span>
          <div style="flex: 1; padding-right: 15px; font-weight: 500; font-size: 0.9rem; line-height: 1.4;">
            <?php echo e($flash['msg']); ?>
          </div>
        </div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); padding: 0; margin: 0; color: inherit; opacity: 0.5;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    </div>
  <?php endif; ?>

  <!-- NAVBAR SUPERIOR -->
  <nav class="navbar navbar-expand-md navbar-light sticky-top shadow-none">
    <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>dashboard">
      <span class="material-symbols-outlined mr-2" style="background:#197fe6; color:#fff; padding:4px; border-radius:4px;">school</span>
      <?php echo e(get_config('nombre_institucion') ?: 'Control Escolar'); ?>
    </a>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ml-auto align-items-center">
        <?php $u = session_user(); ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 5px 10px; border-radius: 8px;">
            <!-- Info -->
            <div class="text-right mr-2 d-none d-md-block">
              <div class="profile-name font-weight-bold" style="font-size: 0.9rem; line-height: 1.2;"><?php echo e($u['nombre']); ?></div>
              <div class="profile-role text-muted small" style="line-height: 1.2;">
                <?php
                $roles = ['director' => 'Director', 'admin' => 'Administrador', 'profesor' => 'Docente', 'docente' => 'Docente', 'alumno' => 'Alumno'];
                echo e($roles[$u['rol']] ?? $u['rol']);
                ?>
              </div>
            </div>
            <!-- Avatar -->
            <div class="profile-avatar">
              <?php if (!empty($u['foto'])): ?>
                <img src="<?php echo e($u['foto']); ?>" alt="Perfil" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <span class="profile-avatar-initials"><?php echo strtoupper(substr($u['nombre'], 0, 1)); ?></span>
              <?php endif; ?>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-right shadow-sm mt-2" aria-labelledby="navbarDropdown" style="border-radius: 8px; border: 1px solid rgba(0,0,0,0.05); min-width: 200px; padding: 8px;">
            <a class="dropdown-item text-danger d-flex align-items-center" href="<?php echo BASE_URL; ?>auth/logout" style="border-radius: 6px; padding: 8px 12px;">
              <span class="material-symbols-outlined mr-2" style="font-size: 18px;">logout</span>
              Cerrar sesión
            </a>
          </div>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- SIDEBAR -->
      <?php include dirname(__FILE__) . '/sidebar.php'; ?>

      <!-- CONTENIDO PRINCIPAL -->
      <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">