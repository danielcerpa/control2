<?php
// app/Controllers/UsuariosController.php

class UsuariosController extends Controller
{
    /** @var Usuario */
    private $usuarioModel;

    public function __construct()
    {
        require_perm('usuarios');
        $this->usuarioModel = new Usuario();
    }

    public function index()
    {
        $usuarios = $this->usuarioModel->getAll();
        $this->view('usuarios/index', ['usuarios' => $usuarios]);
    }

    public function search_edit()
    {
        $usuarios = $this->usuarioModel->getAll();
        $this->view('usuarios/search_edit', [
            'usuarios' => $usuarios
        ]);
    }

    public function search_delete()
    {
        $usuarios = $this->usuarioModel->getAll();
        $this->view('usuarios/search_delete', [
            'usuarios' => $usuarios
        ]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre_usuario' => trim($_POST['nombre_usuario']),
                'estado' => isset($_POST['estado']) ? 1 : 0
            ];
            if (empty($_POST['contrasena'])) {
                redirect(BASE_URL . 'usuarios/create', 'Debes proporcionar una contraseña.', 'danger');
            }
            if (preg_match('/\s/', $_POST['contrasena'])) {
                redirect(BASE_URL . 'usuarios/create', 'La contraseña no puede contener espacios.', 'danger');
            }
            if ($_POST['contrasena'] !== ($_POST['contrasena2'] ?? '')) {
                redirect(BASE_URL . 'usuarios/create', 'Las contraseñas no coinciden.', 'danger');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $datos['nombre_usuario'])) {
                redirect(BASE_URL . 'usuarios/create', 'El nombre de usuario solo puede contener letras, números y guiones bajos.', 'danger');
            }

            try {
                $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
                $this->usuarioModel->create($datos, $contrasena);
                header('Location: ' . BASE_URL . 'usuarios');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    redirect(BASE_URL . 'usuarios/create', 'El nombre de usuario ya está en uso.', 'danger');
                }
                throw $e;
            }
        }
        $this->view('usuarios/create');
    }

    /**
     * @param int|string $id
     */
    public function edit($id)
    {
        $usuario = $this->usuarioModel->getById($id);
        if (!$usuario) {
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $estado_post = $_POST['estado'] ?? null;
            $estado_val = ($estado_post === 'on' || (int)$estado_post === 1) ? 1 : 0;
            if ($id == $_SESSION['usuario_id']) {
                $estado_val = 1; // Forzar activo si es el propio usuario logueado
            }
            
            $datos = [
                'nombre_usuario' => trim($_POST['nombre_usuario']),
                'estado' => $estado_val
            ];
            $nueva_pass = $_POST['password'] ?? $_POST['contrasena'] ?? '';
            if (!empty($nueva_pass) && preg_match('/\s/', $nueva_pass)) {
                redirect(BASE_URL . 'usuarios/edit/' . $id, 'La contraseña no puede contener espacios.', 'danger');
            }
            if (!empty($nueva_pass) && $nueva_pass !== ($_POST['contrasena2'] ?? $_POST['password2'] ?? '')) {
                redirect(BASE_URL . 'usuarios/edit/' . $id, 'Las contraseñas no coinciden.', 'danger');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $datos['nombre_usuario'])) {
                redirect(BASE_URL . 'usuarios/edit/' . $id, 'El nombre de usuario solo puede contener letras, números y guiones bajos.', 'danger');
            }

            try {
                $contrasena = !empty($nueva_pass) ? password_hash($nueva_pass, PASSWORD_DEFAULT) : null;
                $this->usuarioModel->update($id, $datos, $contrasena);

                // Si el usuario editado es el mismo en sesión, actualizar sesión
                if ($id == $_SESSION['usuario_id']) {
                    $_SESSION['usuario_login'] = $datos['nombre_usuario'];
                }

                // Sincronizar estado con alumno/docente asociado
                $nuevoEstado = $datos['estado'];
                $db = db_connect();
                $db->prepare("UPDATE alumnos SET estado = ? WHERE id_usuario = ?")->execute([$nuevoEstado, $id]);
                $db->prepare("UPDATE profesores SET estado = ? WHERE id_usuario = ?")->execute([$nuevoEstado, $id]);

                header('Location: ' . BASE_URL . 'usuarios');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                    redirect(BASE_URL . 'usuarios/edit/' . $id, 'El nombre de usuario ya está en uso.', 'danger');
                }
                throw $e;
            }
        }
        $this->view('usuarios/edit', ['usuario' => $usuario]);
    }

    /**
     * @param int|string $id
     */
    public function delete($id)
    {
        if ($id == $_SESSION['usuario_id']) {
            redirect(BASE_URL . 'usuarios', 'No puedes desactivar el usuario activo.', 'danger');
        }

        $usuario = $this->usuarioModel->getById($id);
        if ($id == 1 || ($usuario && $usuario['nombre_usuario'] === 'admin')) {
            redirect(BASE_URL . 'usuarios', "No se puede desactivar la cuenta principal del administrador.", 'danger');
        }

        try {
            $this->usuarioModel->delete($id);
            redirect(BASE_URL . 'usuarios', 'Usuario desactivado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                $tabla = 'otro módulo';
                if (preg_match('/a foreign key constraint fails \([^.]*\.`([^`]+)`/i', $e->getMessage(), $m)) {
                    $tabla = $m[1];
                }
                redirect(BASE_URL . 'usuarios', "No se puede desactivar porque está en uso o tiene registros asociados en: $tabla", 'danger');
            }
            throw $e;
        }
    }
}
