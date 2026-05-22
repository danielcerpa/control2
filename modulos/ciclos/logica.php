<?php
// modulos/ciclos/logica.php — Controlador CiclosController

class CiclosController extends Controller
{
    /** @var CicloEscolar */
    private $cicloModel;

    public function __construct()
    {
        require_perm('ciclos');
        $this->cicloModel = new CicloEscolar();
    }

    public function index()
    {
        $ciclos = $this->cicloModel->getAll();

        $this->view('ciclos/index', [
            'ciclos' => $ciclos
        ]);
    }

    public function create()
    {
        $errors = [];
        $datos  = ['nombre' => '', 'fecha_inicio' => '', 'fecha_fin' => '', 'estado' => 'Proximo'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre'       => trim($_POST['nombre'] ?? ''),
                'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                'fecha_fin'    => $_POST['fecha_fin']    ?? '',
                'estado'       => $_POST['estado']       ?? 'Proximo',
            ];

            if ($datos['nombre'] === '') {
                $errors[] = 'El nombre del ciclo es obligatorio.';
            } elseif (!preg_match('/^[a-zA-Z0-9\-\(\)\s]+$/', $datos['nombre'])) {
                $errors[] = 'El nombre del ciclo solo permite letras, números, espacios, guiones y paréntesis.';
            }
            if ($datos['fecha_inicio'] === '') {
                $errors[] = 'La fecha de inicio es obligatoria.';
            }
            if ($datos['fecha_fin'] === '') {
                $errors[] = 'La fecha de término es obligatoria.';
            }
            if ($datos['fecha_inicio'] && $datos['fecha_fin'] && $datos['fecha_fin'] <= $datos['fecha_inicio']) {
                $errors[] = 'La fecha de término debe ser posterior y diferente a la de inicio.';
            } elseif ($datos['fecha_inicio'] && $datos['fecha_fin']) {
                $inicio = new DateTime($datos['fecha_inicio']);
                $fin    = new DateTime($datos['fecha_fin']);
                $dias   = $inicio->diff($fin)->days;
                if ($dias > 366) {
                    $errors[] = 'La duración del ciclo no puede superar los 366 días (1 año).';
                }
            }

            if (empty($errors)) {
                $db = db_connect();
                $st = $db->prepare("SELECT id FROM ciclos_escolares WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
                $st->execute([$datos['nombre']]);
                if ($st->fetch()) {
                    $errors[] = 'Ya existe un ciclo escolar con ese nombre.';
                }
            }

            if (empty($errors)) {
                $db = db_connect();
                $st = $db->prepare("SELECT id FROM ciclos_escolares WHERE (fecha_inicio <= ? AND fecha_fin >= ?)");
                $st->execute([$datos['fecha_fin'], $datos['fecha_inicio']]);
                if ($st->fetch()) {
                    $errors[] = 'Las fechas se empalman con un ciclo escolar existente.';
                }
            }

            if (empty($errors)) {
                $this->cicloModel->create($datos);
                header('Location: ' . BASE_URL . 'ciclos');
                exit;
            }
        }

        $this->view('ciclos/create', [
            'errors' => $errors,
            'datos'  => $datos,
        ]);
    }

    /**
     * @param int|string $id
     */
    public function activar($id)
    {
        if ($id) {
            $this->cicloModel->activar($id);
        }
        header('Location: ' . BASE_URL . 'ciclos');
        exit;
    }

    public function search_edit()
    {
        $ciclos = $this->cicloModel->getAll();

        $this->view('ciclos/search_edit', [
            'ciclos' => $ciclos,
            'errors' => [],
        ]);
    }

    public function search_delete()
    {
        $ciclos = $this->cicloModel->getAll();

        $this->view('ciclos/search_delete', [
            'ciclos' => $ciclos,
            'errors' => [],
        ]);
    }

        /**
     * @param int|string $id
     */
    public function delete($id)
    {
        // Los ciclos escolares NO pueden eliminarse — contienen el historial académico completo.
        redirect(BASE_URL . 'ciclos', 'Los ciclos escolares no pueden eliminarse. Representan el historial académico de la institución.', 'danger');
    }

    /**
     * @param int|string $id
     */
    public function edit($id)
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre'       => trim($_POST['nombre'] ?? ''),
                'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                'fecha_fin'    => $_POST['fecha_fin']    ?? '',
                'estado'       => $_POST['estado']       ?? 'Proximo',
            ];

            if ($datos['nombre'] === '') {
                $errors[] = 'El nombre del ciclo es obligatorio.';
            } elseif (!preg_match('/^[a-zA-Z0-9\-\(\)\s]+$/', $datos['nombre'])) {
                $errors[] = 'El nombre del ciclo solo permite letras, números, espacios, guiones y paréntesis.';
            }
            if ($datos['fecha_inicio'] === '') {
                $errors[] = 'La fecha de inicio es obligatoria.';
            }
            if ($datos['fecha_fin'] === '') {
                $errors[] = 'La fecha de término es obligatoria.';
            }
            if ($datos['fecha_inicio'] && $datos['fecha_fin'] && $datos['fecha_fin'] <= $datos['fecha_inicio']) {
                $errors[] = 'La fecha de término debe ser posterior y diferente a la de inicio.';
            } elseif ($datos['fecha_inicio'] && $datos['fecha_fin']) {
                $inicio = new DateTime($datos['fecha_inicio']);
                $fin    = new DateTime($datos['fecha_fin']);
                $dias   = $inicio->diff($fin)->days;
                if ($dias > 366) {
                    $errors[] = 'La duración del ciclo no puede superar los 366 días (1 año).';
                }
            }

            if (empty($errors)) {
                $db = db_connect();
                $st = $db->prepare("SELECT id FROM ciclos_escolares WHERE LOWER(nombre) = LOWER(?) AND id != ? LIMIT 1");
                $st->execute([$datos['nombre'], $id]);
                if ($st->fetch()) {
                    $errors[] = 'Ya existe un ciclo escolar con ese nombre.';
                }
            }

            if (empty($errors)) {
                $db = db_connect();
                $st = $db->prepare("SELECT id FROM ciclos_escolares WHERE (fecha_inicio <= ? AND fecha_fin >= ?) AND id != ?");
                $st->execute([$datos['fecha_fin'], $datos['fecha_inicio'], $id]);
                if ($st->fetch()) {
                    $errors[] = 'Las fechas se empalman con un ciclo escolar existente.';
                }
            }

            if (empty($errors)) {
                $this->cicloModel->update($id, $datos);
                header('Location: ' . BASE_URL . 'ciclos');
                exit;
            }
        }

        $ciclos = $this->cicloModel->getAll();

        $this->view('ciclos/search_edit', [
            'ciclos' => $ciclos,
            'errors' => $errors,
        ]);
    }
}
