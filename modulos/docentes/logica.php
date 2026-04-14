<?php
class DocentesController extends Controller
{
    private $docenteModel;

    public function __construct()
    {
        require_auth();
        $this->docenteModel = new Docente();
    }

    public function index()
    {
        $filtros = [
            'q' => isset($_GET['q']) ? trim($_GET['q']) : '',
            'estado' => isset($_GET['estado']) ? trim($_GET['estado']) : ''
        ];

        // Asumiendo que más adelante el modelo soporte filtrado, 
        // por ahora pasamos los filtros a la vista para que no marque Warning
        // y enviamos todos los docentes.
        $docentes = $this->docenteModel->getAll();

        // (Opcional) Filtrado básico en PHP si el modelo no lo hace aún:
        if ($filtros['estado']) {
            $docentes = array_filter($docentes, function ($d) use ($filtros) {
                return $d['estado'] === $filtros['estado'];
            });
        }
        if ($filtros['q']) {
            $q = strtolower($filtros['q']);
            $docentes = array_filter($docentes, function ($d) use ($q) {
                return strpos(strtolower($d['nombre_completo']), $q) !== false ||
                    strpos(strtolower($d['numero_empleado']), $q) !== false;
            });
        }

        $this->view('docentes/index', [
            'docentes' => $docentes,
            'filtros' => $filtros
        ]);
    }

    public function search_edit()
    {
        $docentes = $this->docenteModel->getAll();
        $this->view('docentes/search_edit', [
            'docentes' => $docentes
        ]);
    }

    public function search_delete()
    {
        $docentes = $this->docenteModel->getAll();
        $this->view('docentes/search_delete', [
            'docentes' => $docentes
        ]);
    }

    public function create()
    {
        $datos = [
            'numero_empleado' => '',
            'nombre_completo' => '',
            'apellido_paterno' => '',
            'apellido_materno' => '',
            'email' => '',
            'curp' => '',
            'telefono' => '',
            'domicilio' => '',
            'grado_estudio' => '',
            'login_id' => '',
            'estado' => 'Activo'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_p = trim($_POST['apellido_p'] ?? '');
            $apellido_m = trim($_POST['apellido_m'] ?? '');

            $datos_guardar = [
                'numero_empleado' => trim($_POST['num_empleado'] ?? ''),
                'nombre_completo' => trim("$nombre $apellido_p $apellido_m"),
                'curp' => trim($_POST['curp'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'domicilio' => trim($_POST['domicilio'] ?? ''),
                'escuela_procedencia' => '',
                'grado_academico' => trim($_POST['grado_estudio'] ?? ''),
                'estado' => (isset($_POST['estado']) && $_POST['estado'] === 'Activo') ? 1 : 0
            ];

            // Procesar foto base64
            $datos_guardar['ruta_foto'] = null;
            if (!empty($_POST['foto_base64'])) {
                $base64 = $_POST['foto_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $data = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    $data = base64_decode($data);
                    if ($data !== false) {
                        $dir = "assets/img/docentes/";
                        if (!is_dir($dir)) mkdir($dir, 0777, true);
                        $filename = uniqid('doc_') . '.' . $type;
                        if (file_put_contents($dir . $filename, $data)) {
                            $datos_guardar['ruta_foto'] = BASE_URL . $dir . $filename;
                        }
                    }
                }
            }

            // Crear el usuario de acceso si se proporcionó
            $id_usuario = null;
            if (!empty($_POST['login_id']) && !empty($_POST['password'])) {
                require_once 'modulos/usuarios/conexion.php';
                $usuarioModel = new Usuario();
                $datos_usu = ['nombre_usuario' => trim($_POST['login_id']), 'estado' => $datos_guardar['estado']];
                $contrasena = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $id_usuario = $usuarioModel->create($datos_usu, $contrasena);
            }

            $this->docenteModel->create($datos_guardar, $id_usuario);
            header('Location: ' . BASE_URL . 'docentes');
            exit;
        }
        $this->view('docentes/create', ['datos' => $datos, 'errors' => []]);
    }

    public function edit($id)
    {
        $docente = $this->docenteModel->getById($id);
        if (!$docente) {
            header('Location: ' . BASE_URL . 'docentes');
            exit;
        }

        // Dividir el nombre completo para el formulario y agregar campos falsos
        $partes = explode(' ', $docente['nombre_completo']);
        $docente['nombre_completo'] = $partes[0] ?? '';
        $docente['apellido_paterno'] = $partes[1] ?? '';
        $docente['apellido_materno'] = isset($partes[2]) ? implode(' ', array_slice($partes, 2)) : '';
        $docente['email'] = '';
        $docente['grado_estudio'] = $docente['grado_academico'] ?? '';
        // Obtener el login_id si existe
        $docente['login_id'] = '';
        if (!empty($docente['id_usuario'])) {
            require_once 'modulos/usuarios/conexion.php';
            $usuarioModel = new Usuario();
            $usu = $usuarioModel->getById($docente['id_usuario']);
            if ($usu) {
                $docente['login_id'] = $usu['nombre_usuario'];
            }
        }

        $docente['estado'] = $docente['estado'] ? 'Activo' : 'Inactivo';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_p = trim($_POST['apellido_p'] ?? '');
            $apellido_m = trim($_POST['apellido_m'] ?? '');

            $datos_guardar = [
                'numero_empleado' => trim($_POST['num_empleado'] ?? ''),
                'nombre_completo' => trim("$nombre $apellido_p $apellido_m"),
                'curp' => trim($_POST['curp'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'domicilio' => trim($_POST['domicilio'] ?? ''),
                'escuela_procedencia' => '',
                'grado_academico' => trim($_POST['grado_estudio'] ?? ''),
                'estado' => (isset($_POST['estado']) && $_POST['estado'] === 'Activo') ? 1 : 0
            ];

            // Procesar foto base64 o si fue eliminada
            $datos_guardar['ruta_foto'] = $docente['ruta_foto'] ?? null;
            if (isset($_POST['foto_base64']) && $_POST['foto_base64'] === 'quitar_foto') {
                $datos_guardar['ruta_foto'] = null;
            } elseif (!empty($_POST['foto_base64'])) {
                $base64 = $_POST['foto_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $data = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    $data = base64_decode($data);
                    if ($data !== false) {
                        $dir = "assets/img/docentes/";
                        if (!is_dir($dir)) mkdir($dir, 0777, true);
                        $filename = uniqid('doc_') . '.' . $type;
                        if (file_put_contents($dir . $filename, $data)) {
                            $datos_guardar['ruta_foto'] = BASE_URL . $dir . $filename;
                        }
                    }
                }
            }

            // Actualizar o crear Usuario
            require_once 'modulos/usuarios/conexion.php';
            $usuarioModel = new Usuario();
            
            if (!empty($_POST['login_id'])) {
                $datos_usu = ['nombre_usuario' => trim($_POST['login_id']), 'estado' => $datos_guardar['estado']];
                $contrasena = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
                
                if (!empty($docente['id_usuario'])) {
                    // Actualizar
                    $usuarioModel->update($docente['id_usuario'], $datos_usu, $contrasena);
                } else {
                    // Crear
                    if ($contrasena) {
                        $new_id = $usuarioModel->create($datos_usu, $contrasena);
                        if ($new_id) {
                            $this->docenteModel->updateIdUsuario($id, $new_id); // we need to create this method in Docente model or just include it in update
                        }
                    } // si no le ponen contraseña nueva, no se le puede crear el usuario
                }
            }

            $this->docenteModel->update($id, $datos_guardar);
            header('Location: ' . BASE_URL . 'docentes');
            exit;
        }
        $this->view('docentes/edit', ['datos' => $docente, 'errors' => []]);
    }

    public function delete($id)
    {
        $this->docenteModel->delete($id);
        header('Location: ' . BASE_URL . 'docentes');
        exit;
    }
}
