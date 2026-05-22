<?php
class DocentesController extends Controller
{
    /** @var Docente */
    private $docenteModel;

    public function __construct()
    {
        require_perm('docentes');
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
            $d['materias'] = $this->docenteModel->getMateriasByDocente($d['id_profesor']);
        }

        $this->view('docentes/index', [
            'docentes' => $docentes,
            'filtros' => $filtros
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

        $db_count = db_connect();
        $total_docentes = (int)$db_count->query("SELECT COUNT(*) FROM profesores")->fetchColumn();

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
                'email' => trim($_POST['email'] ?? ''),
                'domicilio' => trim($_POST['domicilio'] ?? ''),
                'escuela_procedencia' => '',
                'grado_academico' => trim($_POST['grado_estudio'] ?? ''),
                'estado' => (isset($_POST['estado']) && $_POST['estado'] === 'Activo') ? 1 : 0
            ];

            $required_fields = [
                'nombre_completo' => 'El nombre es obligatorio.',
                'curp' => 'La CURP es obligatoria.',
                'email' => 'El correo electrónico es obligatorio.',
                'telefono' => 'El teléfono es obligatorio.',
                'numero_empleado' => 'El número de empleado es obligatorio.',
                'grado_estudio' => 'El grado de estudio es obligatorio.',
                'login_id' => 'El nombre de usuario (login) es obligatorio.'
            ];
            foreach ($required_fields as $field => $msg) {
                if (empty($datos[$field])) {
                    $errors[] = $msg;
                }
            }

            $curpRegex = '/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/i';
            if (!empty($datos['numero_empleado'])) {
                if (!preg_match('/^[0-9]+$/', $datos['numero_empleado'])) {
                    $errors[] = 'El número de empleado solo puede contener números.';
                } else {
                    $db_check = db_connect();
                    $st_max = $db_check->query("SELECT MAX(CAST(numero_empleado AS UNSIGNED)) FROM profesores");
                    $max_num = (int)$st_max->fetchColumn();
                    $allowed_max = $max_num > 0 ? $max_num + 1 : 1;
                    if ((int)$datos['numero_empleado'] > $allowed_max) {
                        $errors[] = "El número de empleado no puede saltar la secuencia. El máximo permitido es $allowed_max. Actualmente hay $total_docentes docente(s) registrado(s).";
                    }
                }
            }

            if (!empty($datos['curp']) && !preg_match($curpRegex, $datos['curp'])) {
                $errors[] = 'La CURP no tiene una estructura válida de 18 caracteres.';
            }
            if (!empty($nombre) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $nombre)) {
                $errors[] = 'El nombre solo puede contener letras y acentos.';
            }
            if (!empty($apellido_p) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $apellido_p)) {
                $errors[] = 'El apellido paterno solo puede contener letras y acentos.';
            }
            if (!empty($apellido_m) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $apellido_m)) {
                $errors[] = 'El apellido materno solo puede contener letras y acentos.';
            }
            if (!empty($datos['telefono']) && !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                $errors[] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
            }
            if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo electrónico no tiene un formato válido.';
            }

            if (!empty($errors)) {
                return $this->view('docentes/create', ['datos' => $datos, 'errors' => $errors, 'total_docentes' => $total_docentes]);
            }

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
                        'errors' => ['password' => 'Las contraseñas no coinciden.'],
                        'total_docentes' => $total_docentes
                    ]);
                } elseif (empty($password)) {
                    $db->rollBack();
                    return $this->view('docentes/create', [
                        'datos' => $datos, 
                        'errors' => ['password' => 'Debe proporcionar una contraseña para el usuario.'],
                        'total_docentes' => $total_docentes
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
                            'errors' => ['login_id' => 'El nombre de usuario ya está en uso.'],
                            'total_docentes' => $total_docentes
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
                        'errors' => ['num_empleado' => $error_msg],
                        'total_docentes' => $total_docentes
                    ]);
                }
                throw $e;
            }
        }
        $this->view('docentes/create', ['datos' => $datos, 'errors' => [], 'total_docentes' => $total_docentes]);
    }

    /**
     * @param int|string $id
     */
    public function edit($id)
    {
        $docente = $this->docenteModel->getById($id);
        if (!$docente) {
            header('Location: ' . BASE_URL . 'docentes');
            exit;
        }

        // Dividir el nombre completo para el formulario y agregar campos falsos
        $partes = explode(' ', trim($docente['nombre_completo']));
        $num_partes = count($partes);
        if ($num_partes == 1) {
            $docente['nombre_completo'] = $partes[0];
            $docente['apellido_paterno'] = '';
            $docente['apellido_materno'] = '';
        } elseif ($num_partes == 2) {
            $docente['nombre_completo'] = $partes[0];
            $docente['apellido_paterno'] = $partes[1];
            $docente['apellido_materno'] = '';
        } elseif ($num_partes == 3) {
            $docente['nombre_completo'] = $partes[0];
            $docente['apellido_paterno'] = $partes[1];
            $docente['apellido_materno'] = $partes[2];
        } else {
            $docente['apellido_materno'] = array_pop($partes);
            $docente['apellido_paterno'] = array_pop($partes);
            $docente['nombre_completo'] = implode(' ', $partes);
        }
        $docente['email'] = $docente['email'] ?? '';  // mantener email si viene de la BD
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

        $db_count = db_connect();
        $total_docentes = (int)$db_count->query("SELECT COUNT(*) FROM profesores")->fetchColumn();

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
                'email' => trim($_POST['email'] ?? ''),
                'domicilio' => trim($_POST['domicilio'] ?? ''),
                'escuela_procedencia' => '',
                'grado_academico' => trim($_POST['grado_estudio'] ?? ''),
                'estado' => (isset($_POST['estado']) && $_POST['estado'] === 'Activo') ? 1 : 0
            ];

            $required_fields = [
                'nombre_completo' => 'El nombre es obligatorio.',
                'curp' => 'La CURP es obligatoria.',
                'email' => 'El correo electrónico es obligatorio.',
                'telefono' => 'El teléfono es obligatorio.',
                'numero_empleado' => 'El número de empleado es obligatorio.',
                'grado_estudio' => 'El grado de estudio es obligatorio.',
                'login_id' => 'El nombre de usuario (login) es obligatorio.'
            ];
            $errors = [];
            foreach ($required_fields as $field => $msg) {
                if (empty($docente[$field])) {
                    $errors[] = $msg;
                }
            }

            $curpRegex = '/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/i';
            if (!empty($docente['numero_empleado'])) {
                if (!preg_match('/^[0-9]+$/', $docente['numero_empleado'])) {
                    $errors[] = 'El número de empleado solo puede contener números.';
                } else {
                    $db_check = db_connect();
                    // Excluimos al docente actual del MAX si es que cambió su número
                    $st_max = $db_check->prepare("SELECT MAX(CAST(numero_empleado AS UNSIGNED)) FROM profesores WHERE id_profesor != ?");
                    $st_max->execute([$id]);
                    $max_num = (int)$st_max->fetchColumn();
                    $allowed_max = $max_num > 0 ? $max_num + 1 : 1;
                    
                    if ((int)$docente['numero_empleado'] > $allowed_max) {
                        // Es posible que su número original ya sea el mayor (y por tanto mayor al max_num sin él)
                        // Para permitir guardar si no cambió el número, comparamos con su número anterior
                        $st_curr = $db_check->prepare("SELECT numero_empleado FROM profesores WHERE id_profesor = ?");
                        $st_curr->execute([$id]);
                        $curr_num = (int)$st_curr->fetchColumn();
                        
                        if ((int)$docente['numero_empleado'] !== $curr_num) {
                            $errors[] = "El número de empleado no puede saltar la secuencia. El máximo permitido es $allowed_max. Actualmente hay $total_docentes docente(s) registrado(s).";
                        }
                    }
                }
            }

            if (!empty($docente['curp']) && !preg_match($curpRegex, $docente['curp'])) {
                $errors[] = 'La CURP no tiene una estructura válida de 18 caracteres.';
            }
            if (!empty($nombre) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $nombre)) {
                $errors[] = 'El nombre solo puede contener letras y acentos.';
            }
            if (!empty($apellido_p) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $apellido_p)) {
                $errors[] = 'El apellido paterno solo puede contener letras y acentos.';
            }
            if (!empty($apellido_m) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $apellido_m)) {
                $errors[] = 'El apellido materno solo puede contener letras y acentos.';
            }
            if (!empty($docente['telefono']) && !preg_match('/^[0-9]{10}$/', $docente['telefono'])) {
                $errors[] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
            }
            if (!empty($docente['email']) && !filter_var($docente['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo electrónico no tiene un formato válido.';
            }

            if (!empty($errors)) {
                return $this->view('docentes/edit', ['datos' => $docente, 'errors' => $errors, 'total_docentes' => $total_docentes]);
            }

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
                        'errors' => ['password' => 'Las contraseñas no coinciden.'],
                        'total_docentes' => $total_docentes
                    ]);
                }
                
                // Verificar nombre de usuario (ignorando el actual)
                $st = $db->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? AND id_usuario != ?");
                $st->execute([trim($_POST['login_id']), $docente['id_usuario'] ?? 0]);
                if ($st->fetch()) {
                    $db->rollBack();
                    return $this->view('docentes/edit', [
                        'datos' => $docente, 
                        'errors' => ['login_id' => 'El nombre de usuario ya está en uso.'],
                        'total_docentes' => $total_docentes
                    ]);
                }

                $datos_usu = ['nombre_usuario' => trim($_POST['login_id']), 'estado' => $datos_guardar['estado']];
                $contrasena = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
                
                if (!empty($docente['id_usuario'])) {
                    // Actualizar
                    $usuarioModel->update($docente['id_usuario'], $datos_usu, $contrasena);
                } else {
                    // Crear
                    if (empty($password)) {
                        $db->rollBack();
                        return $this->view('docentes/edit', [
                            'datos' => $docente, 
                            'errors' => ['password' => 'Debe proporcionar una contraseña para crear la cuenta de usuario.'],
                            'total_docentes' => $total_docentes
                        ]);
                    }
                    $new_id = $usuarioModel->create($datos_usu, $contrasena);
                    if ($new_id) {
                        $this->docenteModel->updateIdUsuario($id, $new_id);
                    }
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
                        'errors' => ['num_empleado' => $error_msg],
                        'total_docentes' => $total_docentes
                    ]);
                }
                throw $e;
            }
        }
        $this->view('docentes/edit', ['datos' => $docente, 'errors' => [], 'total_docentes' => $total_docentes]);
    }

    /**
     * @param int|string $id
     */
    public function delete($id)
    {
        $docente = $this->docenteModel->getById($id);
        $id_usuario = $docente ? $docente['id_usuario'] : null;

        $db = db_connect();
        $db->beginTransaction();
        try {
            $this->docenteModel->delete($id);

            // Desactivar el usuario de acceso asociado si existe
            if ($id_usuario) {
                $db->prepare("UPDATE usuarios SET estado = 0 WHERE id_usuario = ?")->execute([$id_usuario]);
            }

            $db->commit();
            redirect(BASE_URL . 'docentes', 'Docente dado de baja correctamente');
        } catch (PDOException $e) {
            $db->rollBack();
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                redirect(BASE_URL . 'docentes', delete_error_msg($e), 'danger');
            }
            throw $e;
        }
    }
}
