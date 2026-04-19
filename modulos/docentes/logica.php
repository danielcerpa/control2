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

        if ($filtros['estado']) {
            if ($filtros['estado'] === 'Activo') $estado_val = 1;
            elseif ($filtros['estado'] === 'Inactivo') $estado_val = 0;
            else $estado_val = null;

            if ($estado_val !== null) {
                $docentes = array_filter($docentes, function ($d) use ($estado_val) {
                    return (int)$d['estado'] === $estado_val;
                });
            }
        }
        if ($filtros['q']) {
            $q = strtolower($filtros['q']);
            $docentes = array_filter($docentes, function ($d) use ($q) {
                return strpos(strtolower($d['nombre_completo']), $q) !== false ||
                    strpos(strtolower($d['numero_empleado']), $q) !== false ||
                    strpos(strtolower($d['curp']), $q) !== false;
            });
        }

        foreach ($docentes as &$d) {
            $d['estado'] = $d['estado'] ? 'Activo' : 'Inactivo';
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

            $datos = [
                'numero_empleado' => trim($_POST['num_empleado'] ?? ''),
                'nombre_completo' => $nombre,
                'apellido_paterno' => $apellido_p,
                'apellido_materno' => $apellido_m,
                'email' => trim($_POST['email'] ?? ''),
                'curp' => trim($_POST['curp'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'domicilio' => trim($_POST['domicilio'] ?? ''),
                'grado_estudio' => trim($_POST['grado_estudio'] ?? ''),
                'login_id' => trim($_POST['login_id'] ?? ''),
                'estado' => $_POST['estado'] ?? 'Activo'
            ];

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

            $db = db_connect();
            $db->beginTransaction();

            // Crear el usuario de acceso si se proporcionó
            $id_usuario = null;
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            
            if (!empty($_POST['login_id']) || !empty($password)) {
                if ($password !== $password2) {
                    $db->rollBack();
                    return $this->view('docentes/create', [
                        'datos' => $datos, 
                        'errors' => ['password' => 'Las contraseñas no coinciden.']
                    ]);
                } elseif (empty($password)) {
                    $db->rollBack();
                    return $this->view('docentes/create', [
                        'datos' => $datos, 
                        'errors' => ['password' => 'Debe proporcionar una contraseña para el usuario.']
                    ]);
                } else {
                    require_once 'modulos/usuarios/conexion.php';
                    $usuarioModel = new Usuario();
                    
                    // Verificar que el usuario no exista
                    $st = $db->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?");
                    $st->execute([trim($_POST['login_id'])]);
                    if ($st->fetch()) {
                        $db->rollBack();
                        return $this->view('docentes/create', [
                            'datos' => $datos, 
                            'errors' => ['login_id' => 'El nombre de usuario ya está en uso.']
                        ]);
                    }

                    $datos_usu = ['nombre_usuario' => trim($_POST['login_id']), 'estado' => $datos_guardar['estado']];
                    $contrasena = password_hash($password, PASSWORD_DEFAULT);
                    $id_usuario = $usuarioModel->create($datos_usu, $contrasena);
                }
            }

            try {
                $this->docenteModel->create($datos_guardar, $id_usuario);
                $db->commit();
                redirect(BASE_URL . 'docentes', 'Docente registrado exitosamente');
            } catch (PDOException $e) {
                $db->rollBack();
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    $error_msg = 'El número de empleado o CURP ya está registrado';
                    if (strpos(strtolower($e->getMessage()), 'numero_empleado') !== false || strpos(strtolower($e->getMessage()), 'num_empleado') !== false) {
                        $error_msg = 'Este número de empleado ya está registrado por otro docente';
                    } elseif (strpos(strtolower($e->getMessage()), 'curp') !== false) {
                        $error_msg = 'Esta CURP ya está registrada por otro docente';
                    }
                    return $this->view('docentes/create', [
                        'datos' => $datos, 
                        'errors' => ['num_empleado' => $error_msg]
                    ]);
                }
                throw $e;
            }
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

            $docente['numero_empleado'] = trim($_POST['num_empleado'] ?? '');
            $docente['nombre_completo'] = $nombre;
            $docente['apellido_paterno'] = $apellido_p;
            $docente['apellido_materno'] = $apellido_m;
            $docente['email'] = trim($_POST['email'] ?? '');
            $docente['curp'] = trim($_POST['curp'] ?? '');
            $docente['telefono'] = trim($_POST['telefono'] ?? '');
            $docente['domicilio'] = trim($_POST['domicilio'] ?? '');
            $docente['grado_estudio'] = trim($_POST['grado_estudio'] ?? '');
            $docente['login_id'] = trim($_POST['login_id'] ?? '');
            $docente['estado'] = $_POST['estado'] ?? 'Activo';

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

            $db = db_connect();
            $db->beginTransaction();

            // Actualizar o crear Usuario
            require_once 'modulos/usuarios/conexion.php';
            $usuarioModel = new Usuario();
            
            if (!empty($_POST['login_id'])) {
                $password = $_POST['password'] ?? '';
                $password2 = $_POST['password2'] ?? '';
                
                if (!empty($password) && $password !== $password2) {
                    $db->rollBack();
                    return $this->view('docentes/edit', [
                        'datos' => $docente, 
                        'errors' => ['password' => 'Las contraseñas no coinciden.']
                    ]);
                }
                
                // Verificar nombre de usuario (ignorando el actual)
                $st = $db->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? AND id_usuario != ?");
                $st->execute([trim($_POST['login_id']), $docente['id_usuario'] ?? 0]);
                if ($st->fetch()) {
                    $db->rollBack();
                    return $this->view('docentes/edit', [
                        'datos' => $docente, 
                        'errors' => ['login_id' => 'El nombre de usuario ya está en uso.']
                    ]);
                }

                $datos_usu = ['nombre_usuario' => trim($_POST['login_id']), 'estado' => $datos_guardar['estado']];
                $contrasena = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
                
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

            try {
                $this->docenteModel->update($id, $datos_guardar);
                $db->commit();
                redirect(BASE_URL . 'docentes', 'Datos del docente actualizados');
            } catch (PDOException $e) {
                $db->rollBack();
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    $error_msg = 'Error: El número de empleado o CURP pertenece a otro docente';
                    if (strpos(strtolower($e->getMessage()), 'numero_empleado') !== false || strpos(strtolower($e->getMessage()), 'num_empleado') !== false) {
                        $error_msg = 'Error: Este número de empleado pertenece a otro docente';
                    } elseif (strpos(strtolower($e->getMessage()), 'curp') !== false) {
                        $error_msg = 'Error: Esta CURP pertenece a otro docente';
                    }
                    return $this->view('docentes/edit', [
                        'datos' => $docente, 
                        'errors' => ['num_empleado' => $error_msg]
                    ]);
                }
                throw $e;
            }
        }
        $this->view('docentes/edit', ['datos' => $docente, 'errors' => []]);
    }

        public function delete($id)
    {
        try {
            $this->docenteModel->delete($id);
            redirect(BASE_URL . 'docentes', 'Registro eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                $tabla = 'otro módulo';
                if (preg_match('/a foreign key constraint fails \([^.]*\.`([^`]+)`/i', $e->getMessage(), $m)) {
                    $tabla = $m[1];
                }
                redirect(BASE_URL . 'docentes', "No se puede eliminar porque está en uso o tiene registros asociados en: $tabla", 'danger');
            }
            throw $e;
        }
    }
}
