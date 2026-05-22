<?php
// app/Controllers/GruposController.php

class GruposController extends Controller
{
    /** @var Grupo */
    private $grupoModel;
    /** @var CicloEscolar */
    private $cicloModel;

    public function __construct()
    {
        require_perm('grupos');
        $this->grupoModel = new Grupo();
        $this->cicloModel = new CicloEscolar();
    }

    public function index()
    {
        $u = session_user();
        $grupos = $this->grupoModel->getAll();
        
        if ($u['rol'] === 'profesor' && $u['entidad_id']) {
            require_once 'modulos/materias/conexion.php';
            $materiaModel = new Materia();
            $materias_profesor = $materiaModel->getAll();
            $grupos_profesor_ids = [];
            
            foreach ($materias_profesor as $m) {
                if ($m['id_profesor'] == $u['entidad_id'] && $m['id_grupo']) {
                    $grupos_profesor_ids[] = $m['id_grupo'];
                }
            }
            
            $grupos = array_filter($grupos, function ($g) use ($grupos_profesor_ids) {
                return in_array($g['id_grupo'], $grupos_profesor_ids);
            });
        }

        $this->view('grupos/index', ['grupos' => $grupos]);
    }



    public function create()
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

        $errors = [];
        $datos  = [
            'nombre' => '', 'grado' => '', 'seccion' => '',
            'ciclo_escolar' => '', 'capacidad' => '', 'turno' => 'Matutino'
        ];
        $ciclos = $this->cicloModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre'       => trim($_POST['nombre']       ?? ''),
                'grado'        => trim($_POST['grado']        ?? ''),
                'seccion'      => trim($_POST['seccion']      ?? ''),
                'ciclo_escolar'=> trim($_POST['ciclo_id']     ?? ''),
                'capacidad'    => (int) ($_POST['capacidad_max'] ?? 0),
                'turno'        => strtoupper($_POST['turno'] ?? 'MATUTINO'),
            ];

            if ($datos['grado'] === '') {
                $errors[] = 'El grado es obligatorio.';
            }
            if (!empty($datos['seccion']) && !preg_match('/^[a-zA-Z0-9]+$/', $datos['seccion'])) {
                $errors[] = 'La sección solo puede contener letras y números sin espacios.';
            }

            if (!empty($datos['grado']) && !empty($datos['seccion'])) {
                $db = db_connect();
                $st = $db->prepare(
                    "SELECT id_grupo FROM grupos WHERE CAST(grado AS CHAR) = ? AND seccion = ? AND turno = ? LIMIT 1"
                );
                $st->execute([(string)$datos['grado'], $datos['seccion'], $datos['turno']]);
                if ($st->fetch()) {
                    $errors[] = 'Ya existe un grupo con el mismo Grado, Sección y Turno.';
                }
            }

            if (empty($errors)) {
                $this->grupoModel->create($datos);
                redirect(BASE_URL . 'grupos', 'Grupo creado correctamente');
            }
        }

        $this->view('grupos/create', [
            'errors' => $errors,
            'datos'  => $datos,
            'ciclos' => $ciclos,
        ]);
    }

    /**
     * @param int|string $id
     */
    public function edit($id)
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

        $grupo = $this->grupoModel->getById($id);
        if (!$grupo) {
            header('Location: ' . BASE_URL . 'grupos');
            exit;
        }

        $errors = [];
        $datos  = $grupo;
        $ciclos = $this->cicloModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = array_merge($grupo, [
                'nombre'       => trim($_POST['nombre']       ?? ''),
                'grado'        => trim($_POST['grado']        ?? ''),
                'seccion'      => trim($_POST['seccion']      ?? ''),
                'ciclo_escolar'=> trim($_POST['ciclo_id']     ?? ''),
                'capacidad'    => (int) ($_POST['capacidad_max'] ?? 0),
                'turno'        => strtoupper($_POST['turno'] ?? 'MATUTINO'),
            ]);

            if ($datos['grado'] === '') {
                $errors[] = 'El grado es obligatorio.';
            }
            if (!empty($datos['seccion']) && !preg_match('/^[a-zA-Z0-9]+$/', $datos['seccion'])) {
                $errors[] = 'La sección solo puede contener letras y números sin espacios.';
            }

            if (!empty($datos['grado']) && !empty($datos['seccion'])) {
                $db = db_connect();
                $st = $db->prepare(
                    "SELECT id_grupo FROM grupos WHERE CAST(grado AS CHAR) = ? AND seccion = ? AND turno = ? AND id_grupo != ? LIMIT 1"
                );
                $st->execute([(string)$datos['grado'], $datos['seccion'], $datos['turno'], $id]);
                if ($st->fetch()) {
                    $errors[] = 'Ya existe un grupo con el mismo Grado, Sección y Turno.';
                }
            }

            if (empty($errors)) {
                $this->grupoModel->update($id, $datos);
                redirect(BASE_URL . 'grupos', 'Se guardaron correctamente los cambios');
            }
        }

        $this->view('grupos/edit', [
            'errors' => $errors,
            'datos'  => $datos,
            'ciclos' => $ciclos,
        ]);
    }

    /**
     * @param int|string $id
     */
    public function delete($id)
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

        try {
            $this->grupoModel->delete($id);
            redirect(BASE_URL . 'grupos', 'Grupo eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                redirect(BASE_URL . 'grupos', delete_error_msg($e), 'danger');
            }
            throw $e;
        }
    }

    /**
     * @param int|string $id
     */
    public function materias($id)
    {
        $grupo = $this->grupoModel->getById($id);
        if (!$grupo) {
            header('Location: ' . BASE_URL . 'grupos');
            exit;
        }

        require_once 'modulos/materias/conexion.php';
        $materiaModel = new Materia();
        $materias = $materiaModel->getByGrupo($id);

        $this->view('grupos/materias', [
            'grupo'    => $grupo,
            'materias' => $materias
        ]);
    }
}
