<?php
// app/Controllers/AlumnosController.php

class AlumnosController extends Controller
{
    /** @var Alumno */
    private $alumnoModel;
    /** @var Grupo */
    private $grupoModel;

    public function __construct()
    {
        require_perm('alumnos');
        $this->alumnoModel = new Alumno();
        require_once 'modulos/grupos/conexion.php';
        $this->grupoModel = new Grupo();
    }

    public function index()
    {
        $filtros = [
            'q' => isset($_GET['q']) ? trim($_GET['q']) : '',
            'estado' => isset($_GET['estado']) ? trim($_GET['estado']) : '',
            'grupo' => isset($_GET['grupo']) ? trim($_GET['grupo']) : ''
        ];

        $alumnos = $this->alumnoModel->getAll();

        if ($filtros['estado']) {
            if ($filtros['estado'] === 'Activo') $estado_val = 1;
            elseif ($filtros['estado'] === 'Inactivo') $estado_val = 0;
            else $estado_val = null; // Baja etc. map it to 0 as fallback or let it show empty if needed

            if ($estado_val !== null) {
                $alumnos = array_filter($alumnos, function ($a) use ($estado_val) {
                    return (int)$a['estado'] === $estado_val;
                });
            } else {
                // Si es "Baja" y no está explícito en la DB como boolean, asumo que estado = 0
                $alumnos = array_filter($alumnos, function ($a) {
                    return (int)$a['estado'] === 0;
                });
            }
        }
        if ($filtros['q']) {
            $q = strtolower($filtros['q']);
            $alumnos = array_filter($alumnos, function ($a) use ($q) {
                return strpos(strtolower($a['nombre']), $q) !== false ||
                    strpos(strtolower($a['apellido_paterno']), $q) !== false ||
                    strpos(strtolower($a['matricula']), $q) !== false;
            });
        }
        
        if (!empty($filtros['grupo'])) {
            $grupo_id = (int)$filtros['grupo'];
            $db = db_connect();
            $st = $db->prepare("SELECT id_alumno FROM alumno_grupo WHERE id_grupo = ?");
            $st->execute([$grupo_id]);
            $valid_ids = $st->fetchAll(PDO::FETCH_COLUMN);
            $alumnos = array_filter($alumnos, function ($a) use ($valid_ids) {
                return in_array($a['id_alumno'], $valid_ids);
            });
        }

        require_once 'modulos/grupos/conexion.php';
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getAll();

        // Mapear estado textual para vista_index
        foreach ($alumnos as &$a) {
            $a['estado'] = $a['estado'] ? 'Activo' : 'Inactivo'; // fallback básico
        }

        $modulo_activo = 'alumnos';
        $this->view('alumnos/index', [
            'alumnos' => $alumnos,
            'modulo_activo' => $modulo_activo,
            'filtros' => $filtros,
            'grupos' => $grupos
        ]);
    }


    public function create()
    {
        $datos = [
            'matricula' => '',
            'nombre' => '',
            'apellido_paterno' => '',
            'apellido_materno' => '',
            'curp' => '',
            'genero' => '',
            'fecha_nac' => '',
            'escuela_procedencia' => '',
            'direccion' => '',
            'grupo_id' => '',
            'tutor_nombre' => '',
            'tutor_telefono' => '',
            'comentarios_familia' => '',
            'login_id' => '',
            'estado' => 1,
            'fecha_ingreso' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'matricula' => trim($_POST['matricula']),
                'nombre' => trim($_POST['nombre']),
                'apellido_paterno' => trim($_POST['apellido_p'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_m'] ?? ''),
                'curp' => trim($_POST['curp']),
                'genero' => isset($_POST['sexo']) ? substr(trim($_POST['sexo']), 0, 1) : '',
                'fecha_nac' => $_POST['fecha_nac'] ?? null,
                'domicilio' => strip_tags(trim($_POST['direccion'] ?? '')),
                'direccion' => strip_tags(trim($_POST['direccion'] ?? '')),
                'escuela_procedencia' => strip_tags(trim($_POST['escuela_procedencia'] ?? '')),
                'nombre_tutor' => trim($_POST['tutor_nombre'] ?? ''),
                'tutor_nombre' => trim($_POST['tutor_nombre'] ?? ''),
                'telefono_tutor' => trim($_POST['tutor_telefono'] ?? ''),
                'tutor_telefono' => trim($_POST['tutor_telefono'] ?? ''),
                'comentarios' => strip_tags(trim($_POST['comentarios_familia'] ?? '')),
                'comentarios_familia' => strip_tags(trim($_POST['comentarios_familia'] ?? '')),
                'estado' => isset($_POST['estado']) && $_POST['estado'] !== 'Inactivo' && $_POST['estado'] !== 'Baja' ? 1 : 0,
                'grupo_id' => trim($_POST['grupo_id'] ?? ''),
                'login_id' => trim($_POST['login_id'] ?? '')
            ];

            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            $errors = [];

            // Validaciones backend
            $required_fields = [
                'nombre' => 'El nombre es obligatorio.',
                'curp' => 'La CURP es obligatoria.',
                'tutor_telefono' => 'El teléfono del tutor es obligatorio.',
                'tutor_nombre' => 'El nombre del tutor es obligatorio.',
                'fecha_nac' => 'La fecha de nacimiento es obligatoria.',
                'genero' => 'El sexo es obligatorio.',
                'login_id' => 'El nombre de usuario (login) es obligatorio.'
            ];
            foreach ($required_fields as $field => $msg) {
                if (empty($datos[$field])) {
                    $errors[] = $msg;
                }
            }

            if (!empty($datos['matricula']) && !preg_match('/^[a-zA-Z0-9]+$/', $datos['matricula'])) {
                $errors[] = 'La matrícula solo puede contener letras y números.';
            }
            
            $curpRegex = '/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/i';
            if (!preg_match($curpRegex, $datos['curp'])) {
                $errors[] = 'La CURP no tiene una estructura válida de 18 caracteres.';
            }
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['nombre'])) {
                $errors[] = 'El nombre solo puede contener letras y acentos.';
            }
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['apellido_paterno'])) {
                $errors[] = 'El apellido paterno solo puede contener letras y acentos.';
            }
            if (!empty($datos['apellido_materno']) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['apellido_materno'])) {
                $errors[] = 'El apellido materno solo puede contener letras y acentos.';
            }
            if (!empty($datos['escuela_procedencia']) && !preg_match('/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ]+$/u', $datos['escuela_procedencia'])) {
                $errors[] = 'La escuela de procedencia solo puede contener letras, números, puntos y comas.';
            }
            if (!in_array($datos['genero'], ['M', 'F'])) {
                $errors[] = 'El género debe ser Masculino o Femenino.';
            }
            if (!empty($datos['tutor_nombre']) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['tutor_nombre'])) {
                $errors[] = 'El nombre del tutor solo puede contener letras y acentos.';
            }
            if (!preg_match('/^[0-9]{10}$/', $datos['tutor_telefono'])) {
                $errors[] = 'El teléfono del tutor debe tener exactamente 10 dígitos.';
            }

            $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
            $datos['fecha_ingreso'] = $fecha_ingreso ?: null;
            if (empty($datos['fecha_ingreso'])) {
                $errors[] = 'La fecha de ingreso es obligatoria.';
            } else {
                $d_ingreso = new DateTime($fecha_ingreso);
                $d_hoy = new DateTime();
                $d_hoy->setTime(23, 59, 59);
                if ($d_ingreso > $d_hoy) {
                    $errors[] = 'La fecha de ingreso no puede ser mayor a la fecha actual.';
                }
            }

            if (!empty($datos['fecha_nac']) && !empty($fecha_ingreso)) {
                $d1 = new DateTime($datos['fecha_nac']);
                $d2 = new DateTime($fecha_ingreso);
                if ($d2 <= $d1) {
                    $errors[] = 'La fecha de ingreso debe ser posterior a la fecha de nacimiento.';
                } else {
                    $diff = $d1->diff($d2)->y;
                    if ($diff < 5) {
                        $errors[] = 'El alumno debe tener al menos 5 años de edad en su fecha de ingreso.';
                    }
                }
            }

            if (!empty($datos['login_id']) || !empty($password)) {
                if ($password !== $password2) {
                    $errors[] = 'Las contraseñas no coinciden.';
                } elseif (empty($password)) {
                    $errors[] = 'Debe proporcionar una contraseña para el usuario.';
                }
            }

            $datos_vista = $datos;
            $datos_vista['estado'] = $_POST['estado'] ?? 'Activo';
            $datos_vista['genero'] = $_POST['sexo'] ?? '';

            if (!empty($errors)) {
                $modulo_activo = 'alumnos';
                return $this->view('alumnos/create', [
                    'modulo_activo' => $modulo_activo, 
                    'datos' => $datos_vista, 
                    'grupos' => $this->grupoModel->getAll(), 
                    'errors' => $errors
                ]);
            }

            // Procesar foto base64
            $datos['ruta_foto'] = null;
            if (!empty($_POST['foto_base64'])) {
                $base64 = $_POST['foto_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $data = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    $data = base64_decode($data);
                    if ($data !== false) {
                        if (strlen($data) > 5242880) { // 5MB en bytes
                            $modulo_activo = 'alumnos';
                            return $this->view('alumnos/create', [
                                'modulo_activo' => $modulo_activo, 
                                'datos' => $datos_vista, 
                                'grupos' => $this->grupoModel->getAll(), 
                                'errors' => ['La foto no debe superar los 5MB de tamaño.']
                            ]);
                        }
                        $dir = "assets/img/alumnos/";
                        if (!is_dir($dir)) mkdir($dir, 0777, true);
                        $filename = uniqid('alum_') . '.' . $type;
                        if (file_put_contents($dir . $filename, $data)) {
                            $datos['ruta_foto'] = BASE_URL . $dir . $filename;
                        }
                    }
                }
            }

            // Crear el usuario de acceso si se proporcionó
            $id_usuario = null;
            $db = db_connect();
            $db->beginTransaction();

            if (!empty($datos['login_id']) && !empty($password)) {
                require_once 'modulos/usuarios/conexion.php';
                $usuarioModel = new Usuario();
                
                // Verificar que el usuario no exista
                $st = $db->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?");
                $st->execute([$datos['login_id']]);
                if ($st->fetch()) {
                    $db->rollBack();
                    $modulo_activo = 'alumnos';
                    return $this->view('alumnos/create', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos_vista, 
                        'grupos' => [], 
                        'errors' => ['El nombre de usuario (login_id) ya está en uso.']
                    ]);
                }

                $datos_usu = ['nombre_usuario' => $datos['login_id'], 'estado' => $datos['estado']];
                $contrasena = password_hash($password, PASSWORD_DEFAULT);
                $id_usuario = $usuarioModel->create($datos_usu, $contrasena);
            }

            try {
                $this->alumnoModel->create($datos, $id_usuario);
                $db->commit();
                redirect(BASE_URL . 'alumnos', 'Alumno registrado exitosamente');
            } catch (PDOException $e) {
                $db->rollBack();
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    $error_msg = 'La matrícula o CURP ya está en uso por otro alumno';
                    if (strpos(strtolower($e->getMessage()), 'matricula') !== false) {
                        $error_msg = 'Esta matrícula ya está en uso por otro alumno';
                    } elseif (strpos(strtolower($e->getMessage()), 'curp') !== false) {
                        $error_msg = 'Esta CURP ya está en uso por otro alumno';
                    }
                    // Prevenir crash, volver al formulario con error sutil
                    $modulo_activo = 'alumnos';
                    return $this->view('alumnos/create', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos_vista, 
                        'grupos' => $this->grupoModel->getAll(), 
                        'errors' => ['matricula' => $error_msg]
                    ]);
                }
                throw $e; // Si es otro error, dejar que el sistema lo maneje
            }
        }
        $modulo_activo = 'alumnos';
        $this->view('alumnos/create', ['modulo_activo' => $modulo_activo, 'datos' => $datos, 'grupos' => $this->grupoModel->getAll(), 'errors' => []]);
    }

    /**
     * @param int|string $id
     */
    public function edit($id)
    {
        $alumno = $this->alumnoModel->getById($id);
        if (!$alumno) {
            header('Location: ' . BASE_URL . 'alumnos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id_alumno' => $id,
                'matricula' => trim($_POST['matricula']),
                'nombre' => trim($_POST['nombre']),
                'apellido_paterno' => trim($_POST['apellido_p'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_m'] ?? ''),
                'curp' => trim($_POST['curp']),
                'genero' => isset($_POST['sexo']) ? substr(trim($_POST['sexo']), 0, 1) : '',
                'fecha_nac' => $_POST['fecha_nac'] ?? null,
                'domicilio' => strip_tags(trim($_POST['direccion'] ?? '')),
                'direccion' => strip_tags(trim($_POST['direccion'] ?? '')),
                'escuela_procedencia' => strip_tags(trim($_POST['escuela_procedencia'] ?? '')),
                'nombre_tutor' => trim($_POST['tutor_nombre'] ?? ''),
                'tutor_nombre' => trim($_POST['tutor_nombre'] ?? ''),
                'telefono_tutor' => trim($_POST['tutor_telefono'] ?? ''),
                'tutor_telefono' => trim($_POST['tutor_telefono'] ?? ''),
                'comentarios' => strip_tags(trim($_POST['comentarios_familia'] ?? '')),
                'comentarios_familia' => strip_tags(trim($_POST['comentarios_familia'] ?? '')),
                'estado' => isset($_POST['estado']) && $_POST['estado'] !== 'Inactivo' && $_POST['estado'] !== 'Baja' ? 1 : 0,
                'grupo_id' => trim($_POST['grupo_id'] ?? ''),
                'login_id' => trim($_POST['login_id'] ?? '')
            ];

            $errors = [];

            $required_fields = [
                'nombre' => 'El nombre es obligatorio.',
                'curp' => 'La CURP es obligatoria.',
                'tutor_telefono' => 'El teléfono del tutor es obligatorio.',
                'tutor_nombre' => 'El nombre del tutor es obligatorio.',
                'fecha_nac' => 'La fecha de nacimiento es obligatoria.',
                'genero' => 'El sexo es obligatorio.'
                // 'login_id' ya no es obligatorio al editar
            ];
            foreach ($required_fields as $field => $msg) {
                if (empty($datos[$field])) {
                    $errors[] = $msg;
                }
            }

            // Validaciones backend
            if (!empty($datos['matricula']) && !preg_match('/^[a-zA-Z0-9]+$/', $datos['matricula'])) {
                $errors[] = 'La matrícula solo puede contener letras y números.';
            }
            $curpRegex = '/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/i';
            if (!preg_match($curpRegex, $datos['curp'])) {
                $errors[] = 'La CURP no tiene una estructura válida de 18 caracteres.';
            }
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['nombre'])) {
                $errors[] = 'El nombre solo puede contener letras y acentos.';
            }
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['apellido_paterno'])) {
                $errors[] = 'El apellido paterno solo puede contener letras y acentos.';
            }
            if (!empty($datos['apellido_materno']) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['apellido_materno'])) {
                $errors[] = 'El apellido materno solo puede contener letras y acentos.';
            }
            if (!empty($datos['escuela_procedencia']) && !preg_match('/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ]+$/u', $datos['escuela_procedencia'])) {
                $errors[] = 'La escuela de procedencia solo puede contener letras, números, puntos y comas.';
            }
            if (!in_array($datos['genero'], ['M', 'F'])) {
                $errors[] = 'El género debe ser Masculino o Femenino.';
            }
            if (!empty($datos['tutor_nombre']) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['tutor_nombre'])) {
                $errors[] = 'El nombre del tutor solo puede contener letras y acentos.';
            }
            if (!preg_match('/^[0-9]{10}$/', $datos['tutor_telefono'])) {
                $errors[] = 'El teléfono del tutor debe tener exactamente 10 dígitos.';
            }

            $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
            $datos['fecha_ingreso'] = $fecha_ingreso ?: null;

            $datos_vista = $datos;
            $datos_vista['estado'] = $_POST['estado'] ?? 'Activo';
            $datos_vista['genero'] = $_POST['sexo'] ?? '';
            if (empty($datos['fecha_ingreso'])) {
                $errors[] = 'La fecha de ingreso es obligatoria.';
            } else {
                $d_ingreso = new DateTime($fecha_ingreso);
                $d_hoy = new DateTime();
                $d_hoy->setTime(23, 59, 59);
                if ($d_ingreso > $d_hoy) {
                    $errors[] = 'La fecha de ingreso no puede ser mayor a la fecha actual.';
                }
            }

            if (!empty($datos['fecha_nac']) && !empty($fecha_ingreso)) {
                $d1 = new DateTime($datos['fecha_nac']);
                $d2 = new DateTime($fecha_ingreso);
                if ($d2 <= $d1) {
                    $errors[] = 'La fecha de ingreso debe ser posterior a la fecha de nacimiento.';
                } else {
                    $diff = $d1->diff($d2)->y;
                    if ($diff < 5) {
                        $errors[] = 'El alumno debe tener al menos 5 años de edad en su fecha de ingreso.';
                    }
                }
            }

            // Procesar foto base64 o si fue eliminada
            $datos['ruta_foto'] = $alumno['ruta_foto']; // Mantener la anterior por defecto
            $datos_vista['ruta_foto'] = $datos['ruta_foto'];
            if (isset($_POST['foto_base64']) && $_POST['foto_base64'] === 'quitar_foto') {
                $datos['ruta_foto'] = null;
                $datos_vista['ruta_foto'] = null;
            } elseif (!empty($_POST['foto_base64'])) {
                $base64 = $_POST['foto_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $data = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    $data = base64_decode($data);
                    if ($data !== false) {
                        if (strlen($data) > 5242880) { // 5MB en bytes
                            $modulo_activo = 'alumnos';
                            return $this->view('alumnos/edit', [
                                'modulo_activo' => $modulo_activo, 
                                'datos' => $datos_vista, 
                                'grupos' => $this->grupoModel->getAll(), 
                                'errors' => ['La foto no debe superar los 5MB de tamaño.']
                            ]);
                        }
                        $dir = "assets/img/alumnos/";
                        if (!is_dir($dir)) mkdir($dir, 0777, true);
                        $filename = uniqid('alum_') . '.' . $type;
                        if (file_put_contents($dir . $filename, $data)) {
                            $datos['ruta_foto'] = BASE_URL . $dir . $filename;
                            $datos_vista['ruta_foto'] = $datos['ruta_foto'];
                        }
                    }
                }
            }

            if (!empty($errors)) {
                $modulo_activo = 'alumnos';
                return $this->view('alumnos/edit', [
                    'modulo_activo' => $modulo_activo, 
                    'datos' => $datos_vista, 
                    'grupos' => $this->grupoModel->getAll(), 
                    'errors' => $errors
                ]);
            }

            $db = db_connect();
            $db->beginTransaction();

            // Actualizar o crear Usuario
            require_once 'modulos/usuarios/conexion.php';
            $usuarioModel = new Usuario();
            
            if (!empty($_POST['login_id']) || !empty($alumno['id_usuario'])) {
                $password = $_POST['password'] ?? '';
                $password2 = $_POST['password2'] ?? '';
                
                if (!empty($password) && $password !== $password2) {
                    $db->rollBack();
                    $modulo_activo = 'alumnos';
                    return $this->view('alumnos/edit', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos_vista, 
                        'grupos' => $this->grupoModel->getAll(), 
                        'errors' => ['password' => 'Las contraseñas no coinciden.']
                    ]);
                }

                $login_id = trim($_POST['login_id'] ?? '');

                if ($login_id !== '') {
                    // Verificar nombre de usuario (ignorando el actual)
                    $st = $db->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? AND id_usuario != ?");
                    $st->execute([$login_id, $alumno['id_usuario'] ?? 0]);
                    if ($st->fetch()) {
                        $db->rollBack();
                        $modulo_activo = 'alumnos';
                        return $this->view('alumnos/edit', [
                            'modulo_activo' => $modulo_activo, 
                            'datos' => $datos_vista, 
                            'grupos' => $this->grupoModel->getAll(), 
                            'errors' => ['login_id' => 'El nombre de usuario ya está en uso.']
                        ]);
                    }
                }

                // Si login_id está vacío, usar el original (si tenía usuario), sino no se crea
                if ($login_id === '' && !empty($alumno['id_usuario'])) {
                     $st = $db->prepare("SELECT nombre_usuario FROM usuarios WHERE id_usuario = ?");
                     $st->execute([$alumno['id_usuario']]);
                     $login_id = $st->fetchColumn();
                }

                if ($login_id !== '') {
                    $datos_usu = ['nombre_usuario' => $login_id, 'estado' => $datos['estado']];
                    $contrasena = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
                    
                    if (!empty($alumno['id_usuario'])) {
                        $usuarioModel->update($alumno['id_usuario'], $datos_usu, $contrasena);
                    } else {
                        if (empty($password)) {
                            $db->rollBack();
                            $modulo_activo = 'alumnos';
                            return $this->view('alumnos/edit', [
                                'modulo_activo' => $modulo_activo, 
                                'datos' => $datos_vista, 
                                'grupos' => $this->grupoModel->getAll(), 
                                'errors' => ['password' => 'Debe proporcionar una contraseña para crear la cuenta de usuario.']
                            ]);
                        }
                        $new_id = $usuarioModel->create($datos_usu, $contrasena);
                        if ($new_id) {
                            $db->prepare("UPDATE alumnos SET id_usuario = ? WHERE id_alumno = ?")->execute([$new_id, $id]);
                        }
                    }
                }
            }

            try {
                $this->alumnoModel->update($id, $datos);
                $db->commit();
                redirect(BASE_URL . 'alumnos', 'Alumno actualizado correctamente');
            } catch (PDOException $e) {
                $db->rollBack();
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    $error_msg = 'No se pudo guardar: La matrícula o CURP ya existe en otro registro';
                    if (strpos(strtolower($e->getMessage()), 'matricula') !== false) {
                        $error_msg = 'No se pudo guardar: Esta matrícula ya existe en otro registro';
                    } elseif (strpos(strtolower($e->getMessage()), 'curp') !== false) {
                        $error_msg = 'No se pudo guardar: Esta CURP ya existe en otro registro';
                    }
                    $modulo_activo = 'alumnos';
                    return $this->view('alumnos/edit', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos_vista, 
                        'grupos' => $this->grupoModel->getAll(), 
                        'errors' => ['matricula' => $error_msg]
                    ]);
                }
                throw $e;
            }
        }
        if ($alumno) {
            $alumno['direccion'] = $alumno['domicilio'];
            $alumno['tutor_nombre'] = $alumno['nombre_tutor'];
            $alumno['tutor_telefono'] = $alumno['telefono_tutor'];
            $alumno['comentarios_familia'] = $alumno['comentarios'];
            $alumno['estado'] = $alumno['estado'] ? 'Activo' : 'Inactivo';
            // Mapear género de M/F a Masculino/Femenino
            if ($alumno['genero'] == 'M') $alumno['genero'] = 'Masculino';
            if ($alumno['genero'] == 'F') $alumno['genero'] = 'Femenino';
            if ($alumno['genero'] == 'O') $alumno['genero'] = 'Otro';

            // Obtener el ID del grupo actual
            $db = db_connect();
            $st_g = $db->prepare("SELECT id_grupo FROM alumno_grupo WHERE id_alumno = ?");
            $st_g->execute([$id]);
            $alumno['grupo_id'] = $st_g->fetchColumn();

            // Obtener el login_id
            $alumno['login_id'] = '';
            if (!empty($alumno['id_usuario'])) {
                $st_u = $db->prepare("SELECT nombre_usuario FROM usuarios WHERE id_usuario = ?");
                $st_u->execute([$alumno['id_usuario']]);
                $alumno['login_id'] = $st_u->fetchColumn();
            }
        }
        $modulo_activo = 'alumnos';
        $this->view('alumnos/edit', ['datos' => $alumno, 'grupos' => $this->grupoModel->getAll(), 'modulo_activo' => $modulo_activo, 'errors' => []]);
    }

    /**
     * @param int|string $id
     */
    public function delete($id)
    {
        $db = db_connect();

        // Obtener id_usuario ANTES de dar de baja el alumno
        $id_usuario = $this->alumnoModel->getUsuarioId($id);

        $db->beginTransaction();
        try {
            $this->alumnoModel->delete($id);

            // Si el alumno tenía usuario de acceso, desactivarlo también
            if ($id_usuario) {
                $db->prepare("UPDATE usuarios SET estado = 0 WHERE id_usuario = ?")->execute([$id_usuario]);
            }

            $db->commit();
            redirect(BASE_URL . 'alumnos', 'Alumno dado de baja correctamente');
        } catch (PDOException $e) {
            $db->rollBack();
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                redirect(BASE_URL . 'alumnos', delete_error_msg($e), 'danger');
            }
            throw $e;
        }
    }
}
