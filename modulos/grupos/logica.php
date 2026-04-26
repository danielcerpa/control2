<?php
// app/Controllers/GruposController.php

class GruposController extends Controller
{
    private $grupoModel;
    private $cicloModel;

    public function __construct()
    {
        require_auth();
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

    public function search_edit()
    {
        $grupos = $this->grupoModel->getAll();
        $ciclos = $this->cicloModel->getAll();
        $this->view('grupos/search_edit', [
            'grupos'  => $grupos,
            'ciclos'  => $ciclos
        ]);
    }

    public function search_delete()
    {
        $grupos = $this->grupoModel->getAll();
        $this->view('grupos/search_delete', [
            'grupos'  => $grupos
        ]);
    }

    public function create()
    {
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
                'turno'        => $_POST['turno']             ?? 'Matutino',
            ];

            if ($datos['nombre'] === '') {
                $errors[] = 'El nombre del grupo es obligatorio.';
            }
            if ($datos['grado'] === '') {
                $errors[] = 'El grado es obligatorio.';
            }

            if (empty($errors)) {
                $this->grupoModel->create($datos);
                header('Location: ' . BASE_URL . 'grupos');
                exit;
            }
        }

        $this->view('grupos/create', [
            'errors' => $errors,
            'datos'  => $datos,
            'ciclos' => $ciclos,
        ]);
    }

    public function edit($id)
    {
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
                'turno'        => $_POST['turno']             ?? 'Matutino',
            ]);

            if ($datos['nombre'] === '') {
                $errors[] = 'El nombre del grupo es obligatorio.';
            }

            if (empty($errors)) {
                $this->grupoModel->update($id, $datos);
                header('Location: ' . BASE_URL . 'grupos');
                exit;
            }
        }

        $this->view('grupos/edit', [
            'errors' => $errors,
            'datos'  => $datos,
            'ciclos' => $ciclos,
        ]);
    }

        public function delete($id)
    {
        try {
            $this->grupoModel->delete($id);
            redirect(BASE_URL . 'grupos', 'Registro eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                $tabla = 'otro módulo';
                if (preg_match('/a foreign key constraint fails \([^.]*\.`([^`]+)`/i', $e->getMessage(), $m)) {
                    $tabla = $m[1];
                }
                redirect(BASE_URL . 'grupos', "No se puede eliminar porque está en uso o tiene registros asociados en: $tabla", 'danger');
            }
            throw $e;
        }
    }
}
