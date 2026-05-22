<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Acceso al Sistema — Control Escolar</title>

    <script>
        // Cargar y aplicar tema guardado antes de renderizar
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/fonts.css">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/app.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../assets/css/app.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css?v=<?php echo filemtime(dirname(__FILE__) . '/../../assets/css/login.css'); ?>">
</head>

<body class="login-page">

    <!-- Botón flotante para Modo Oscuro en Login -->
    <button id="btn-dark-mode-login" class="btn d-flex align-items-center justify-content-center" 
            type="button" 
            style="position: fixed; top: 20px; right: 20px; width: 44px; height: 44px; border: 1px solid #cbd5e1; background: #fff; border-radius: 50%; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: all 0.2s;">
        <span id="dm-icon" class="material-symbols-outlined" style="font-size: 22px; color: #475569;">dark_mode</span>
    </button>

    <div class="login-container">
        <div class="login-card fade-in">
            <div class="login-header-img">
                <div class="login-header-overlay"></div>
            </div>

            <div class="card-body px-4 pb-4 pt-0 text-center">
                <div class="brand-icon fade-in delay-1">
                    <span class="material-symbols-outlined" style="font-size:32px;">school</span>
                </div>

                <h3 class="login-title fade-in delay-1">Control Escolar</h3>
                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-modern fade-in delay-1" role="alert">
                        <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
                        <span class="small font-weight-medium"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="" autocomplete="off" class="text-left mt-4">
                    <div class="form-group mb-3 fade-in delay-2">
                        <label>USUARIO</label>
                        <div class="input-icon-wrap position-relative">
                            <span class="material-symbols-outlined input-icon">person</span>
                            <input type="text" name="nombre_usuario" class="form-control" placeholder="Número de Control/Matrícula" required autofocus>
                        </div>
                    </div>

                    <div class="form-group mb-4 fade-in delay-2">
                        <label>CONTRASEÑA</label>
                        <div class="input-icon-wrap position-relative">
                            <span class="material-symbols-outlined input-icon">lock</span>
                            <input type="password" name="contrasena" class="form-control" placeholder="Ingresa tu contraseña" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-login fade-in delay-3">
                        <span>Iniciar Sesión</span>
                        <span class="material-symbols-outlined" style="font-size: 18px;">login</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>

    <script>
    (function () {
      var btn  = document.getElementById('btn-dark-mode-login');
      var icon = document.getElementById('dm-icon');
    
      function updateIcon(dark) {
        if (dark) {
          icon.textContent = 'light_mode';
          icon.style.color = '#fbbf24'; // Sol amarillo
        } else {
          icon.textContent = 'dark_mode';
          icon.style.color = '#475569'; // Media luna gris
        }
      }
    
      // Inicializar estado del icono según el tema actual
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      updateIcon(isDark);
    
      btn.addEventListener('click', function () {
        var currentDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var nextDark    = !currentDark;
    
        if (nextDark) {
          document.documentElement.setAttribute('data-theme', 'dark');
          localStorage.setItem('theme', 'dark');
        } else {
          document.documentElement.removeAttribute('data-theme');
          localStorage.setItem('theme', 'light');
        }
        updateIcon(nextDark);
      });
    })();
    </script>
</body>

</html>