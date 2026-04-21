<?php
// app/Controllers/InscripcionesController.php

class InscripcionesController extends Controller
{
    private $inscripcionModel;
    private $alumnoModel;
    private $horarioModel; // Para oferta_horario

    public function __construct()
    {
        require_auth();
        $this->inscripcionModel = new Inscripcion();
        $this->alumnoModel = new Alumno();
        $this->horarioModel = new Horario();
    }

    public function index()
    {
        $inscripciones = $this->inscripcionModel->getAll();
        $this->view('inscripciones/index', ['inscripciones' => $inscripciones]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id_alumno' => $_POST['id_alumno'],
                'id_materia'=> $_POST['id_materia'] ?? $_POST['id_oferta'],
                'estado'    => isset($_POST['estado']) ? 1 : 0
            ];
            $this->inscripcionModel->create($datos);
            header('Location: ' . BASE_URL . 'inscripciones');
            exit;
        }

        $alumnos = $this->alumnoModel->getAll();
        $ofertas = $this->horarioModel->getAll(); // esto ahora retorna materias

        $this->view('inscripciones/create', [
            'alumnos'  => $alumnos,
            'materias' => $ofertas,
            'ofertas'  => $ofertas
        ]);
    }

        public function delete($id)
    {
        try {
            $this->inscripcionModel->delete($id);
            redirect(BASE_URL . 'inscripciones', 'Registro eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                $tabla = 'otro módulo';
                if (preg_match('/a foreign key constraint fails \([^.]*\.`([^`]+)`/i', $e->getMessage(), $m)) {
                    $tabla = $m[1];
                }
                redirect(BASE_URL . 'inscripciones', "No se puede eliminar porque está en uso o tiene registros asociados en: $tabla", 'danger');
            }
            throw $e;
        }
    }
}
