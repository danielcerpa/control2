<?php

class ConfiguracionController extends Controller
{
    /** @var Configuracion */
    private $configModel;

    public function __construct()
    {
        require_perm('configuracion');
        $this->configModel = new Configuracion();
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre     = trim($_POST['nombre_institucion'] ?? '');
            $cct        = strtoupper(trim($_POST['cct_institucion'] ?? ''));
            $turno      = trim($_POST['turno_institucion'] ?? 'MATUTINO');
            $direccion  = trim($_POST['direccion_institucion'] ?? '');
            $telefono   = trim($_POST['telefono_institucion'] ?? '');
            $correo     = trim($_POST['correo_institucion'] ?? '');

            $errores = [];

            // Nombre: obligatorio, entre 3 y 150 caracteres, solo caracteres seguros
            if (empty($nombre)) {
                $errores[] = 'El nombre de la institución es obligatorio.';
            } elseif (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 150) {
                $errores[] = 'El nombre de la institución debe tener entre 3 y 150 caracteres.';
            } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s0-9#.,\-]+$/u', $nombre)) {
                $errores[] = 'El nombre contiene caracteres especiales no permitidos.';
            }

            // CCT: opcional, exactamente 10 caracteres alfanuméricos
            if ($cct !== '' && !preg_match('/^[A-Z0-9]{10}$/', $cct)) {
                $errores[] = 'La CCT debe tener exactamente 10 caracteres alfanuméricos.';
            }

            // Turno: solo valores permitidos
            if (!in_array($turno, ['MATUTINO', 'VESPERTINO', 'NOCTURNO'], true)) {
                $errores[] = 'El turno seleccionado no es válido.';
            }

            // Dirección: opcional, max 200, caracteres seguros
            if ($direccion !== '' && mb_strlen($direccion) > 200) {
                $errores[] = 'La dirección no puede superar los 200 caracteres.';
            } elseif ($direccion !== '' && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s0-9#.,\-]+$/u', $direccion)) {
                $errores[] = 'La dirección contiene caracteres especiales no permitidos.';
            }

            // Teléfono: opcional, exactamente 10 dígitos si se proporciona
            if ($telefono !== '' && !preg_match('/^[0-9]{10}$/', $telefono)) {
                $errores[] = 'El teléfono debe contener exactamente 10 dígitos numéricos.';
            }

            // Correo: opcional, formato válido si se proporciona
            if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El correo electrónico no tiene un formato válido.';
            } elseif ($correo !== '' && mb_strlen($correo) > 100) {
                $errores[] = 'El correo no puede superar los 100 caracteres.';
            }

            if (!empty($errores)) {
                // Re-mostrar el formulario con los errores
                $config = [
                    'nombre_institucion'   => $nombre,
                    'cct_institucion'      => $cct,
                    'turno_institucion'    => $turno,
                    'direccion_institucion'=> $direccion,
                    'telefono_institucion' => $telefono,
                    'correo_institucion'   => $correo,
                ];
                $modulo_activo = 'configuracion';
                $_SESSION['flash_msg']  = implode(' ', $errores);
                $_SESSION['flash_tipo'] = 'danger';
                $this->view('configuracion/index', compact('config', 'modulo_activo'));
                return;
            }

            $this->configModel->set('nombre_institucion',    $nombre);
            $this->configModel->set('cct_institucion',       $cct);
            $this->configModel->set('turno_institucion',     $turno);
            $this->configModel->set('direccion_institucion', $direccion);
            $this->configModel->set('telefono_institucion',  $telefono);
            $this->configModel->set('correo_institucion',    $correo);

            // Limpiar caché para que el nuevo valor surta efecto inmediatamente
            get_config(null, true);

            redirect(BASE_URL . 'configuracion', 'Configuración guardada correctamente');
        }

        // Leer todos los valores actuales
        $config = [];
        foreach ($this->configModel->getAll() as $row) {
            $config[$row['clave']] = $row['valor'];
        }

        $modulo_activo = 'configuracion';
        $this->view('configuracion/index', compact('config', 'modulo_activo'));
    }
}
