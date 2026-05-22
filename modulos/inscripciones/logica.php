<?php
// app/Controllers/InscripcionesController.php

class InscripcionesController extends Controller
{
    /** @var Inscripcion */
    private $inscripcionModel;
    /** @var Alumno */
    private $alumnoModel;
    /** @var Horario */
    private $horarioModel; // Para oferta_horario

    public function __construct()
    {
        require_perm('inscripciones');
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
            $errors = [];

            $alumno = $this->alumnoModel->getById($datos['id_alumno']);
            if (!$alumno || (int)$alumno['estado'] !== 1) {
                $errors[] = 'El alumno debe estar Activo para ser inscrito.';
            }

            $db = db_connect();
            $st = $db->prepare("SELECT id_inscripcion FROM inscripciones WHERE id_alumno = ? AND id_materia = ?");
            $st->execute([$datos['id_alumno'], $datos['id_materia']]);
            if ($st->fetch()) {
                $errors[] = 'El alumno ya está inscrito en esta materia.';
            }

            if (empty($errors)) {
                require_once 'modulos/materias/conexion.php';
                $materiaModel = new Materia();
                $materia = $materiaModel->getById($datos['id_materia']);

                if ($materia) {
                    $stCount = $db->prepare("SELECT COUNT(*) FROM inscripciones WHERE id_materia = ? AND estado = 1");
                    $stCount->execute([$datos['id_materia']]);
                    $inscritos = $stCount->fetchColumn();

                    if ($inscritos >= $materia['horas']) {
                        $errors[] = 'La materia ha alcanzado su cupo máximo (' . $materia['horas'] . ').';
                    }

                    if ($materia['salon_id']) {
                        require_once 'modulos/salones/conexion.php';
                        $salonModel = new Salon();
                        $salon = $salonModel->getById($materia['salon_id']);
                        if ($salon && $inscritos >= $salon['capacidad']) {
                            $errors[] = 'La inscripción supera la capacidad del salón asignado (' . $salon['capacidad'] . ').';
                        }
                    }

                    $horariosNuevos = $materia['horarios'];
                    $stInscritas = $db->prepare("
                        SELECT m.id_materia
                        FROM inscripciones i
                        JOIN materias m ON i.id_materia = m.id_materia
                        WHERE i.id_alumno = ? AND i.estado = 1
                    ");
                    $stInscritas->execute([$datos['id_alumno']]);
                    $materiasInscritas = $stInscritas->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($materiasInscritas as $mi) {
                        $horariosExistentes = $materiaModel->getHorarios($mi['id_materia']);
                        foreach ($horariosNuevos as $hn) {
                            foreach ($horariosExistentes as $he) {
                                if ($hn['dia'] === $he['dia']) {
                                    $inicioN = strtotime($hn['hora_inicio']);
                                    $finN    = strtotime($hn['hora_fin']);
                                    $inicioE = strtotime($he['hora_inicio']);
                                    $finE    = strtotime($he['hora_fin']);
                                    
                                    if ($inicioN < $finE && $finN > $inicioE) {
                                        $errors[] = 'Cruce de horarios detectado el día ' . $hn['dia'] . ' con otra materia.';
                                        break 3;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (empty($errors)) {
                $this->inscripcionModel->create($datos);
                redirect(BASE_URL . 'inscripciones', 'Inscripción realizada exitosamente.', 'success');
            } else {
                redirect(BASE_URL . 'inscripciones/create', implode(' ', $errors), 'danger');
            }
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

    /**
     * @param int|string $id
     */
    public function delete($id)
    {
        try {
            $this->inscripcionModel->delete($id);
            redirect(BASE_URL . 'inscripciones', 'Inscripción eliminada correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                redirect(BASE_URL . 'inscripciones', delete_error_msg($e), 'danger');
            }
            throw $e;
        }
    }
}
