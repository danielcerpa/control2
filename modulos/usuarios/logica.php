<?php
// app/Controllers/UsuariosController.php

class UsuariosController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        require_auth();
        // solo_director(); // O admin real
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
            if ($_POST['contrasena'] !== ($_POST['contrasena2'] ?? '')) {
                redirect(BASE_URL . 'usuarios/create', 'Las contraseñas no coinciden.', 'danger');
            }
            $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
            $this->usuarioModel->create($datos, $contrasena);
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }
        $this->view('usuarios/create');
    }

    public function edit($id)
    {
        if ($id == $_SESSION['usuario_id']) {
            redirect(BASE_URL . 'usuarios', 'No puedes editar el usuario activo.', 'danger');
        }
        $usuario = $this->usuarioModel->getById($id);
        if (!$usuario) {
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre_usuario' => trim($_POST['nombre_usuario']),
                'estado' => isset($_POST['estado']) ? 1 : 0
            ];
            if (!empty($_POST['contrasena']) && $_POST['contrasena'] !== ($_POST['contrasena2'] ?? '')) {
                redirect(BASE_URL . 'usuarios/edit/' . $id, 'Las contraseñas no coinciden.', 'danger');
            }
            $contrasena = !empty($_POST['contrasena']) ? password_hash($_POST['contrasena'], PASSWORD_DEFAULT) : null;
            $this->usuarioModel->update($id, $datos, $contrasena);
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }
        $this->view('usuarios/edit', ['usuario' => $usuario]);
    }

        public function delete($id)
    {
        try {
            $this->usuarioModel->delete($id);
            redirect(BASE_URL . 'usuarios', 'Registro eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                $tabla = 'otro módulo';
                if (preg_match('/a foreign key constraint fails \([^.]*\.`([^`]+)`/i', $e->getMessage(), $m)) {
                    $tabla = $m[1];
                }
                redirect(BASE_URL . 'usuarios', "No se puede eliminar porque está en uso o tiene registros asociados en: $tabla", 'danger');
            }
            throw $e;
        }
    }
}
