<?php
// app/Controllers/AlumnosController.php

class AlumnosController extends Controller
{
    private $alumnoModel;

    public function __construct()
    {
        require_auth();
        $this->alumnoModel = new Alumno();
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

    public function search_edit()
    {
        $alumnos = $this->alumnoModel->getAll();
        
        // Incluir login_id en la lista para el buscador si no está
        foreach ($alumnos as &$a) {
            $a['login_id'] = $a['id_usuario'] ? 'ID:'.$a['id_usuario'] : 'Sin usuario';
            // Mapear para que el autocompletado lo tenga listo (opcional ya que el JS lo hace, pero mejor ser consistentes)
        }

        $modulo_activo = 'alumnos';
        $grupos = []; // TODO: Cargar grupos reales
        
        $this->view('alumnos/search_edit', [
            'alumnos' => $alumnos,
            'grupos' => $grupos,
            'modulo_activo' => $modulo_activo
        ]);
    }

    public function search_delete()
    {
        $alumnos = $this->alumnoModel->getAll();
        
        foreach ($alumnos as &$a) {
            $a['login_id'] = $a['id_usuario'] ? 'ID:'.$a['id_usuario'] : 'Sin usuario';
        }

        $modulo_activo = 'alumnos';
        $this->view('alumnos/search_delete', [
            'alumnos' => $alumnos,
            'modulo_activo' => $modulo_activo
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
            'estado' => 1
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
                'domicilio' => trim($_POST['direccion'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'escuela_procedencia' => trim($_POST['escuela_procedencia'] ?? ''),
                'nombre_tutor' => trim($_POST['tutor_nombre'] ?? ''),
                'tutor_nombre' => trim($_POST['tutor_nombre'] ?? ''),
                'telefono_tutor' => trim($_POST['tutor_telefono'] ?? ''),
                'tutor_telefono' => trim($_POST['tutor_telefono'] ?? ''),
                'comentarios' => trim($_POST['comentarios_familia'] ?? ''),
                'comentarios_familia' => trim($_POST['comentarios_familia'] ?? ''),
                'estado' => isset($_POST['estado']) && $_POST['estado'] !== 'Inactivo' && $_POST['estado'] !== 'Baja' ? 1 : 0,
                'grupo_id' => trim($_POST['grupo_id'] ?? ''),
                'login_id' => trim($_POST['login_id'] ?? '')
            ];

            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            $errors = [];

            if (!empty($datos['login_id']) || !empty($password)) {
                if ($password !== $password2) {
                    $errors[] = 'Las contraseñas no coinciden.';
                } elseif (empty($password)) {
                    $errors[] = 'Debe proporcionar una contraseña para el usuario.';
                }
            }

            if (!empty($errors)) {
                $modulo_activo = 'alumnos';
                return $this->view('alumnos/create', [
                    'modulo_activo' => $modulo_activo, 
                    'datos' => $datos, 
                    'grupos' => [], 
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
            if (!empty($datos['login_id']) && !empty($password)) {
                require_once 'modulos/usuarios/conexion.php';
                $usuarioModel = new Usuario();
                
                // Verificar que el usuario no exista
                $db = db_connect();
                $st = $db->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?");
                $st->execute([$datos['login_id']]);
                if ($st->fetch()) {
                    $modulo_activo = 'alumnos';
                    return $this->view('alumnos/create', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos, 
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
                redirect(BASE_URL . 'alumnos', 'Alumno registrado exitosamente');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    // Prevenir crash, volver al formulario con error sutil
                    $modulo_activo = 'alumnos';
                    $grupos = []; 
                    return $this->view('alumnos/create', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos, 
                        'grupos' => $grupos, 
                        'errors' => ['matricula' => 'La matrícula o CURP ya está en uso por otro alumno']
                    ]);
                }
                throw $e; // Si es otro error, dejar que el sistema lo maneje
            }
        }
        $modulo_activo = 'alumnos';
        $grupos = []; // TODO: Cargar grupos reales del modelo cuando exista
        $this->view('alumnos/create', ['modulo_activo' => $modulo_activo, 'datos' => $datos, 'grupos' => $grupos, 'errors' => []]);
    }

    public function edit($id)
    {
        $alumno = $this->alumnoModel->getById($id);
        if (!$alumno) {
            header('Location: ' . BASE_URL . 'alumnos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'matricula' => trim($_POST['matricula']),
                'nombre' => trim($_POST['nombre']),
                'apellido_paterno' => trim($_POST['apellido_p'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_m'] ?? ''),
                'curp' => trim($_POST['curp']),
                'genero' => isset($_POST['sexo']) ? substr(trim($_POST['sexo']), 0, 1) : '',
                'fecha_nac' => $_POST['fecha_nac'] ?? null,
                'domicilio' => trim($_POST['direccion'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'escuela_procedencia' => trim($_POST['escuela_procedencia'] ?? ''),
                'nombre_tutor' => trim($_POST['tutor_nombre'] ?? ''),
                'tutor_nombre' => trim($_POST['tutor_nombre'] ?? ''),
                'telefono_tutor' => trim($_POST['tutor_telefono'] ?? ''),
                'tutor_telefono' => trim($_POST['tutor_telefono'] ?? ''),
                'comentarios' => trim($_POST['comentarios_familia'] ?? ''),
                'comentarios_familia' => trim($_POST['comentarios_familia'] ?? ''),
                'estado' => isset($_POST['estado']) && $_POST['estado'] !== 'Inactivo' && $_POST['estado'] !== 'Baja' ? 1 : 0,
                'login_id' => trim($_POST['login_id'] ?? '')
            ];

            // Procesar foto base64 o si fue eliminada
            $datos['ruta_foto'] = $alumno['ruta_foto']; // Mantener la anterior por defecto
            if (isset($_POST['foto_base64']) && $_POST['foto_base64'] === 'quitar_foto') {
                $datos['ruta_foto'] = null;
            } elseif (!empty($_POST['foto_base64'])) {
                $base64 = $_POST['foto_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $data = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    $data = base64_decode($data);
                    if ($data !== false) {
                        $dir = "assets/img/alumnos/";
                        if (!is_dir($dir)) mkdir($dir, 0777, true);
                        $filename = uniqid('alum_') . '.' . $type;
                        if (file_put_contents($dir . $filename, $data)) {
                            $datos['ruta_foto'] = BASE_URL . $dir . $filename;
                        }
                    }
                }
            }

            // Actualizar o crear Usuario
            require_once 'modulos/usuarios/conexion.php';
            $usuarioModel = new Usuario();
            
            if (!empty($_POST['login_id'])) {
                $password = $_POST['password'] ?? '';
                $password2 = $_POST['password2'] ?? '';
                
                if (!empty($password) && $password !== $password2) {
                    $modulo_activo = 'alumnos';
                    return $this->view('alumnos/edit', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos, 
                        'grupos' => [], 
                        'errors' => ['password' => 'Las contraseñas no coinciden.']
                    ]);
                }

                // Verificar nombre de usuario (ignorando el actual)
                $db = db_connect();
                $st = $db->prepare("SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? AND id_usuario != ?");
                $st->execute([trim($_POST['login_id']), $alumno['id_usuario'] ?? 0]);
                if ($st->fetch()) {
                    $modulo_activo = 'alumnos';
                    return $this->view('alumnos/edit', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos, 
                        'grupos' => [], 
                        'errors' => ['login_id' => 'El nombre de usuario ya está en uso.']
                    ]);
                }

                $datos_usu = ['nombre_usuario' => trim($_POST['login_id']), 'estado' => $datos['estado']];
                $contrasena = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
                
                if (!empty($alumno['id_usuario'])) {
                    $usuarioModel->update($alumno['id_usuario'], $datos_usu, $contrasena);
                } else {
                    if ($contrasena) {
                        $new_id = $usuarioModel->create($datos_usu, $contrasena);
                        if ($new_id) {
                            $db->prepare("UPDATE alumnos SET id_usuario = ? WHERE id_alumno = ?")->execute([$new_id, $id]);
                        }
                    }
                }
            }

            try {
                $this->alumnoModel->update($id, $datos);
                redirect(BASE_URL . 'alumnos', 'Alumno actualizado correctamente');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    $modulo_activo = 'alumnos';
                    return $this->view('alumnos/edit', [
                        'modulo_activo' => $modulo_activo, 
                        'datos' => $datos, 
                        'grupos' => [], 
                        'errors' => ['matricula' => 'No se pudo guardar: La matrícula o CURP ya existe en otro registro']
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
        }
        $modulo_activo = 'alumnos';
        $this->view('alumnos/edit', ['datos' => $alumno, 'grupos' => [], 'modulo_activo' => $modulo_activo, 'errors' => []]);
    }

    public function delete($id)
    {
        $this->alumnoModel->delete($id);
        header('Location: ' . BASE_URL . 'alumnos');
        exit;
    }
}
