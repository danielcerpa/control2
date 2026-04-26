<?php
// app/Controllers/AuthController.php

class AuthController extends Controller
{

    public function index()
    {
        require_once 'config/init.php';

        if (session_user()) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre_usuario = isset($_POST['nombre_usuario']) ? trim($_POST['nombre_usuario']) : '';
            $contrasena  = isset($_POST['contrasena'])  ? $_POST['contrasena'] : '';

            if ($nombre_usuario === '' || $contrasena === '') {
                $error = 'Ingresa tu usuario y contraseña.';
            } else {
                $pdo = db_connect();

                $st = $pdo->prepare("SELECT id_usuario, nombre_usuario, contrasena, estado
                                           FROM usuarios WHERE nombre_usuario = ? LIMIT 1");
                $st->execute(array($nombre_usuario));
                $user = $st->fetch();

                if (!$user) {
                    $error = 'Usuario o contraseña incorrectos.';
                } elseif (!$user['estado']) {
                    $error = 'Tu cuenta está inactiva. Contacta al administrador.';
                    // NOTE: for now we fallback to simple check if hash doesn't match for backwards compatibility / simple setup
                } elseif (!password_verify($contrasena, $user['contrasena']) && $contrasena !== $user['contrasena']) {
                    $error = 'Usuario o contraseña incorrectos.';
                } else {
                    // Determinar el tipo de usuario
                    $tipo = 'admin'; // Defecto si no es alumno ni docente
                    $foto = null;

                    // Checar si es alumno
                    $st = $pdo->prepare("SELECT id_alumno, nombre, apellido_paterno, ruta_foto FROM alumnos WHERE id_usuario = ?");
                    $st->execute([$user['id_usuario']]);
                    if ($alumno = $st->fetch()) {
                        $tipo = 'alumno';
                        $_SESSION['usuario_entidad_id'] = $alumno['id_alumno'];
                        $nombre_completo = trim($alumno['nombre'] . ' ' . $alumno['apellido_paterno']);
                        $foto = $alumno['ruta_foto'];
                    } else {
                        // Checar si es profesor
                        $st = $pdo->prepare("SELECT id_profesor, nombre_completo, ruta_foto FROM profesores WHERE id_usuario = ?");
                        $st->execute([$user['id_usuario']]);
                        if ($profesor = $st->fetch()) {
                            $tipo = 'profesor';
                            $_SESSION['usuario_entidad_id'] = $profesor['id_profesor'];
                            $nombre_completo = $profesor['nombre_completo'];
                            $foto = $profesor['ruta_foto'];
                        } else {
                            $nombre_completo = 'Administrador';
                        }
                    }

                    $_SESSION['usuario_id']      = $user['id_usuario'];
                    $_SESSION['usuario_nombre']  = $nombre_completo;
                    $_SESSION['usuario_login']   = $user['nombre_usuario'];
                    $_SESSION['usuario_tipo']    = $tipo;
                    $_SESSION['usuario_rol']     = $tipo;
                    $_SESSION['usuario_foto']    = $foto;

                    // Load permissions later if needed, for admins

                    header('Location: ' . BASE_URL . 'dashboard');
                    exit;
                }
            }
        }

        $this->view('auth/login', ['error' => $error]);
    }

    public function logout()
    {
        require_once 'config/init.php';
        session_destroy();
        header('Location: ' . BASE_URL);
        exit;
    }
}
