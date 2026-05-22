<?php
// includes/sidebar.php — Menú lateral por áreas
$activo = isset($modulo_activo) ? $modulo_activo : '';
$u      = session_user();
$rol    = $u ? $u['rol'] : '';

/**
 * Ayuda: retorna 'active' si el módulo coincide.
 * Ahora recibe el módulo activo actual para evitar problemas de scope.
 */
function nav_active(string $modulo, string $actual): string
{
  return ($actual === $modulo) ? 'active' : '';
}

/**
 * Ayuda: genera un enlace de sidebar
 */
function nav_link(string $modulo, string $label, string $icon, string $url, string $actual): void
{
  $cls = nav_active($modulo, $actual);
  echo '<a href="' . htmlspecialchars($url) . '" class="nav-link ' . $cls . '">'
    . '<span class="material-symbols-outlined mr-2" style="font-size:20px;">' . $icon . '</span>' . htmlspecialchars($label)
    . '</a>';
}
?>

<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse" style="padding-right: 0;">
  <div class="sidebar-sticky pt-3 pb-0" style="display:flex; flex-direction:column; height:100%;">

    <!-- CONTENEDOR DE ENLACES (Flex 1 — scrollable) -->
    <div style="flex: 1; overflow-y: auto; overflow-x: hidden;">
      <!-- GENERAL -->
      <ul class="nav flex-column">
        <?php nav_link('dashboard', 'Inicio', 'dashboard', BASE_URL . 'dashboard', $activo); ?>
      </ul>

      <?php if (puede_ver('alumnos') || puede_ver('docentes')): ?>
        <p class="sidebar-heading">Comunidad Escolar</p>
        <ul class="nav flex-column">
          <?php if (puede_ver('alumnos')): ?>
            <?php nav_link('alumnos', 'Alumnos', 'person', BASE_URL . 'alumnos', $activo); ?>
          <?php endif; ?>
          <?php if (puede_ver('docentes')): ?>
            <?php nav_link('docentes', 'Docentes', 'badge', BASE_URL . 'docentes', $activo); ?>
          <?php endif; ?>
        </ul>
      <?php endif; ?>

      <?php if (puede_ver('materias') || puede_ver('grupos') || puede_ver('calificaciones') || puede_ver('ciclos')): ?>
        <p class="sidebar-heading">Académico</p>
        <ul class="nav flex-column">
          <?php if (puede_ver('materias')): ?>
            <?php nav_link('materias', 'Materias', 'menu_book', BASE_URL . 'materias', $activo); ?>
          <?php endif; ?>
          <?php if (puede_ver('grupos')): ?>
            <?php nav_link('grupos', 'Grupos', 'groups', BASE_URL . 'grupos', $activo); ?>
          <?php endif; ?>
          <?php if (puede_ver('calificaciones')): ?>
            <?php nav_link('calificaciones', 'Calificaciones', 'assignment_turned_in', BASE_URL . 'calificaciones', $activo); ?>
          <?php endif; ?>
          <?php if (puede_ver('ciclos')): ?>
            <?php nav_link('ciclos', 'Ciclos Escolares', 'event_repeat', BASE_URL . 'ciclos', $activo); ?>
          <?php endif; ?>
        </ul>
      <?php endif; ?>

      <?php 
        $muestra_horarios_admin = puede_ver('horarios') && $rol !== 'profesor' && $rol !== 'docente' && $rol !== 'alumno';
        $muestra_infraestructura = puede_ver('salones') || $muestra_horarios_admin;
      ?>
      <?php if ($muestra_infraestructura): ?>
        <p class="sidebar-heading">Infraestructura</p>
        <ul class="nav flex-column">
          <?php if (puede_ver('salones')): ?>
            <?php nav_link('salones', 'Salones', 'meeting_room', BASE_URL . 'salones', $activo); ?>
          <?php endif; ?>
          <?php if ($muestra_horarios_admin): ?>
            <?php nav_link('horarios', 'Horarios', 'calendar_month', BASE_URL . 'horarios', $activo); ?>
          <?php endif; ?>
        </ul>
      <?php endif; ?>

      <?php if (puede_ver('reportes') || puede_ver('usuarios')): ?>
        <p class="sidebar-heading">Administración</p>
        <ul class="nav flex-column">
          <?php if (puede_ver('reportes')): ?>
            <?php nav_link('reportes', 'Reportes', 'analytics', BASE_URL . 'reportes', $activo); ?>
          <?php endif; ?>
          <?php if (puede_ver('usuarios')): ?>
            <?php nav_link('usuarios', 'Usuarios', 'admin_panel_settings', BASE_URL . 'usuarios', $activo); ?>
          <?php endif; ?>
        </ul>
      <?php endif; ?>

      <?php if ($rol === 'alumno'): ?>
        <p class="sidebar-heading">Mi Espacio</p>
        <ul class="nav flex-column">
          <?php nav_link('mi_perfil', 'Mi Perfil', 'account_circle', BASE_URL . 'portal_alumno/perfil', $activo); ?>
          <?php nav_link('mi_horario', 'Mi Horario', 'calendar_month', BASE_URL . 'portal_alumno/horario', $activo); ?>
          <?php nav_link('mis_calificaciones', 'Mis Calificaciones', 'grade', BASE_URL . 'portal_alumno/calificaciones', $activo); ?>
          <?php nav_link('mis_materias', 'Mis Materias', 'menu_book', BASE_URL . 'portal_alumno/materias', $activo); ?>
        </ul>
      <?php endif; ?>

      <?php if ($rol === 'profesor'): ?>
        <p class="sidebar-heading">Mi Espacio</p>
        <ul class="nav flex-column">
          <?php nav_link('calificaciones', 'Calificaciones', 'edit_note', BASE_URL . 'calificaciones', $activo); ?>
          <?php nav_link('horarios', 'Mi Horario', 'calendar_month', BASE_URL . 'horarios', $activo); ?>
          <?php nav_link('grupos', 'Mis Grupos', 'groups', BASE_URL . 'grupos', $activo); ?>
          <?php nav_link('materias', 'Mis Materias', 'menu_book', BASE_URL . 'materias', $activo); ?>
        </ul>
      <?php endif ?>
    </div>

    <!-- FOOTER DEL SIDEBAR — siempre al fondo -->
    <div class="sidebar-footer d-flex justify-content-end align-items-center py-3 px-4">
      
      <!-- Botón de Modo Oscuro (Luna / Sol) visible directamente -->
      <button class="btn btn-action d-flex align-items-center justify-content-center mr-2" 
              type="button" 
              id="btn-sidebar-theme-toggle" 
              style="width: 40px; height: 40px; border: 1px solid #cbd5e1; background: transparent; transition: all 0.2s; border-radius: 8px;"
              title="Cambiar tema">
        <span id="sidebar-theme-icon" class="material-symbols-outlined" style="font-size: 22px;">dark_mode</span>
      </button>

      <!-- Botón de Configuración (Tuerca) — Solo Administrador y Director -->
      <?php if ($rol === 'admin' || $rol === 'director'): ?>
        <div class="dropup">
          <button class="btn btn-action d-flex align-items-center justify-content-center" 
                  type="button" 
                  id="sidebarConfigDropdown" 
                  data-toggle="dropdown" 
                  aria-haspopup="true" 
                  aria-expanded="false" 
                  style="width: 40px; height: 40px; border: 1px solid #cbd5e1; background: transparent; transition: all 0.2s; border-radius: 8px;"
                  title="Configuración">
            <span class="material-symbols-outlined" style="font-size: 22px;">settings</span>
          </button>
          <div class="dropdown-menu dropdown-menu-right shadow-sm py-2" 
               aria-labelledby="sidebarConfigDropdown" 
               style="border-radius: 10px; min-width: 200px; z-index: 1050; margin-bottom: 8px;">
            <a class="dropdown-item d-flex align-items-center py-2" href="<?php echo BASE_URL; ?>configuracion">
              <span class="material-symbols-outlined mr-2" style="font-size: 20px;">domain</span>
              <span>Datos de la Institución</span>
            </a>
          </div>
        </div>
      <?php endif; ?>

    </div>

  </div>
</nav>

<script>
(function () {
  var btn  = document.getElementById('btn-sidebar-theme-toggle');
  var icon = document.getElementById('sidebar-theme-icon');

  function applyTheme(dark) {
    if (dark) {
      document.documentElement.setAttribute('data-theme', 'dark');
      if (icon) {
        icon.textContent = 'light_mode';
        icon.style.color = '#fbbf24'; // Sol amarillo
      }
    } else {
      document.documentElement.removeAttribute('data-theme');
      if (icon) {
        icon.textContent = 'dark_mode';
        icon.style.color = ''; // Color de texto normal de la UI
      }
    }
  }

  // Cargar preferencia guardada
  var saved = localStorage.getItem('theme') === 'dark';
  applyTheme(saved);

  if (btn) {
    btn.addEventListener('click', function () {
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      var next   = !isDark;
      localStorage.setItem('theme', next ? 'dark' : 'light');
      applyTheme(next);
    });
  }
})();
</script>