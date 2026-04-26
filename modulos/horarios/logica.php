<?php
// app/Controllers/HorariosController.php
// (Maneja las Ofertas de Horario)

class HorariosController extends Controller
{
    private $horarioModel;
    private $materiaModel;
    private $docenteModel;
    private $salonModel;
    private $grupoModel;
    private $cicloModel;

    public function __construct()
    {
        require_auth();
        $this->horarioModel = new Horario();
        $this->materiaModel = new Materia();
        $this->docenteModel = new Docente();
        $this->salonModel   = new Salon();
        $this->grupoModel   = new Grupo();
        $this->cicloModel   = new CicloEscolar();
    }

    public function index()
    {
        $u     = session_user();
        $ciclo = $this->cicloModel->getActivo();

        $filtros = [
            'q'        => trim($_GET['q']     ?? ''),
            'grupo_id' => (int) ($_GET['grupo'] ?? 0),
        ];

        $alumnos = $this->horarioModel->getAlumnos();
        $grupos  = $this->grupoModel->getAll();

        if ($u['rol'] === 'profesor' && $u['entidad_id']) {
            require_once 'modulos/materias/conexion.php';
            require_once 'modulos/inscripciones/conexion.php';
            $materiaModel = new Materia();
            $inscripcionModel = new Inscripcion();
            
            // Get materias of this profesor
            $materias_profesor = array_filter($materiaModel->getAll(), function($m) use ($u) {
                return $m['id_profesor'] == $u['entidad_id'];
            });
            $ids_materias_prof = array_column($materias_profesor, 'id_materia');
            
            // Get inscripciones for these materias
            $inscripciones = $inscripcionModel->getAll();
            $ids_alumnos_prof = [];
            foreach ($inscripciones as $insc) {
                if (in_array($insc['id_materia'], $ids_materias_prof)) {
                    $ids_alumnos_prof[] = $insc['id_alumno'];
                }
            }
            $ids_alumnos_prof = array_unique($ids_alumnos_prof);
            
            // Filter alumnos
            $alumnos = array_filter($alumnos, function($a) use ($ids_alumnos_prof) {
                return in_array($a['id_alumno'], $ids_alumnos_prof);
            });
            
            // Filter grupos
            $grupos_profesor_ids = array_column($materias_profesor, 'id_grupo');
            $grupos = array_filter($grupos, function($g) use ($grupos_profesor_ids) {
                return in_array($g['id_grupo'], $grupos_profesor_ids);
            });
        }

        // Filtrar en PHP
        if ($filtros['q']) {
            $q = strtolower($filtros['q']);
            $alumnos = array_filter($alumnos, function ($a) use ($q) {
                return strpos(strtolower($a['nombre']), $q) !== false
                    || strpos(strtolower($a['apellido_paterno']), $q) !== false
                    || strpos(strtolower($a['matricula']), $q) !== false;
            });
        }
        if ($filtros['grupo_id']) {
            $nombreGrupo = '';
            foreach ($grupos as $g) {
                if ($g['id_grupo'] == $filtros['grupo_id']) {
                    $nombreGrupo = $g['grado'] . $g['seccion'];
                    break;
                }
            }
            if ($nombreGrupo) {
                $alumnos = array_filter($alumnos, function ($a) use ($nombreGrupo) {
                    return ($a['grupo_nombre'] ?? '') === $nombreGrupo;
                });
            }
        }

        $this->view('horarios/index', [
            'u'       => $u,
            'ciclo'   => $ciclo,
            'filtros' => $filtros,
            'alumnos' => array_values($alumnos),
            'grupos'  => $grupos,
        ]);
    }

    /**
     * Devuelve el horario semanal de un alumno en JSON (para el modal).
     * URL: horarios/horario/{id_alumno}
     */
    public function horario($id_alumno)
    {
        header('Content-Type: application/json');
        $filas = $this->horarioModel->getHorarioPorAlumno((int)$id_alumno);
        echo json_encode($filas);
        exit;
    }

    public function create()
    {
        $errors   = [];
        $warnings = [];
        $datos    = [
            'materia_id'  => '', 'docente_id' => '', 'salon_id'   => '',
            'grupo_id'    => '', 'dia'        => '', 'hora_inicio' => '',
            'hora_fin'    => '', 'ciclo_id'   => '',
        ];

        $materias = $this->materiaModel->getAll();
        $docentes = $this->docenteModel->getAll();
        $salones  = $this->salonModel->getAll();
        $grupos   = $this->grupoModel->getAll();
        $ciclos   = $this->cicloModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'materia_id'  => $_POST['materia_id']  ?? '',
                'docente_id'  => $_POST['docente_id']  ?? '',
                'salon_id'    => $_POST['salon_id']    ?? '',
                'grupo_id'    => $_POST['grupo_id']    ?? '',
                'dia'         => $_POST['dia']         ?? '',
                'hora_inicio' => $_POST['hora_inicio'] ?? '',
                'hora_fin'    => $_POST['hora_fin']    ?? '',
                'ciclo_id'    => $_POST['ciclo_id']    ?? '',
            ];

            if (empty($datos['materia_id'])) $errors[] = 'La materia es obligatoria.';
            if (empty($datos['docente_id'])) $errors[] = 'El docente es obligatorio.';
            if (empty($datos['dia']))        $errors[] = 'El dia es obligatorio.';
            if (empty($datos['hora_inicio'])) $errors[] = 'La hora de inicio es obligatoria.';
            if (empty($datos['hora_fin']))   $errors[] = 'La hora de fin es obligatoria.';

            if (empty($errors)) {
                $guardado = [
                    'id_materia'    => $datos['materia_id'],
                    'id_profesor'   => $datos['docente_id'],
                    'id_salon'      => $datos['salon_id']   ?: null,
                    'id_grupo'      => $datos['grupo_id']   ?: null,
                    'dia'           => strtoupper($datos['dia']),
                    'hora_inicio'   => $datos['hora_inicio'],
                    'hora_fin'      => $datos['hora_fin'],
                    'ciclo_escolar' => $datos['ciclo_id']   ?? '',
                    'estado'        => 1,
                ];
                $this->horarioModel->create($guardado);
                header('Location: ' . BASE_URL . 'horarios');
                exit;
            }
        }

        $this->view('horarios/create', [
            'errors'   => $errors,
            'warnings' => $warnings,
            'datos'    => $datos,
            'materias' => $materias,
            'docentes' => $docentes,
            'salones'  => $salones,
            'grupos'   => $grupos,
            'ciclos'   => $ciclos,
        ]);
    }

    public function edit($id)
    {
        $oferta = $this->horarioModel->getById($id);
        if (!$oferta) {
            header('Location: ' . BASE_URL . 'horarios');
            exit;
        }

        $errors   = [];
        $warnings = [];
        $datos    = [
            'materia_id'  => $oferta['id_materia']   ?? '',
            'docente_id'  => $oferta['id_profesor']  ?? '',
            'salon_id'    => $oferta['id_salon']      ?? '',
            'grupo_id'    => $oferta['id_grupo']      ?? '',
            'dia'         => $oferta['dia']           ?? '',
            'hora_inicio' => $oferta['hora_inicio']   ?? '',
            'hora_fin'    => $oferta['hora_fin']      ?? '',
            'ciclo_id'    => $oferta['ciclo_escolar'] ?? '',
        ];

        $materias = $this->materiaModel->getAll();
        $docentes = $this->docenteModel->getAll();
        $salones  = $this->salonModel->getAll();
        $grupos   = $this->grupoModel->getAll();
        $ciclos   = $this->cicloModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'materia_id'  => $_POST['materia_id']  ?? '',
                'docente_id'  => $_POST['docente_id']  ?? '',
                'salon_id'    => $_POST['salon_id']    ?? '',
                'grupo_id'    => $_POST['grupo_id']    ?? '',
                'dia'         => $_POST['dia']         ?? '',
                'hora_inicio' => $_POST['hora_inicio'] ?? '',
                'hora_fin'    => $_POST['hora_fin']    ?? '',
                'ciclo_id'    => $_POST['ciclo_id']    ?? '',
            ];

            if (empty($datos['materia_id'])) $errors[] = 'La materia es obligatoria.';

            if (empty($errors)) {
                $guardado = [
                    'id_materia'    => $datos['materia_id'],
                    'id_profesor'   => $datos['docente_id'],
                    'id_salon'      => $datos['salon_id']   ?: null,
                    'id_grupo'      => $datos['grupo_id']   ?: null,
                    'dia'           => strtoupper($datos['dia']),
                    'hora_inicio'   => $datos['hora_inicio'],
                    'hora_fin'      => $datos['hora_fin'],
                    'ciclo_escolar' => $datos['ciclo_id'] ?? '',
                    'estado'        => isset($_POST['estado']) ? 1 : 0,
                ];
                $this->horarioModel->update($id, $guardado);
                header('Location: ' . BASE_URL . 'horarios');
                exit;
            }
        }

        $this->view('horarios/edit', [
            'oferta'   => $oferta,
            'errors'   => $errors,
            'warnings' => $warnings,
            'datos'    => $datos,
            'materias' => $materias,
            'docentes' => $docentes,
            'salones'  => $salones,
            'grupos'   => $grupos,
            'ciclos'   => $ciclos,
        ]);
    }

    public function show($id_alumno)
    {
        $ciclo = $this->cicloModel->getActivo();
        require_once 'modulos/alumno/conexion.php';
        $portalModel = new AlumnoPortal();

        $alumno = $portalModel->getAlumno($id_alumno);
        if (!$alumno) {
            header('Location: ' . BASE_URL . 'horarios');
            exit;
        }

        $grupo_id = $portalModel->getGrupoId($id_alumno, $ciclo['id'] ?? null);
        $horarios = $portalModel->getHorarioByAlumno($id_alumno, $ciclo['id'] ?? null);

        // Organizar por día
        $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $grid = [];
        foreach ($dias as $d) $grid[$d] = [];
        foreach ($horarios as $h) {
            $dia_norm = ucfirst(strtolower($h['dia']));
            if (isset($grid[$dia_norm])) {
                $grid[$dia_norm][] = $h;
            }
        }

        $this->view('horarios/show', [
            'alumno'        => $alumno,
            'grid'          => $grid,
            'dias'          => $dias,
            'ciclo'         => $ciclo,
            'grupo_id'      => $grupo_id,
        ]);
    }

    public function delete($id)
    {
        try {
            $this->horarioModel->delete($id);
            redirect(BASE_URL . 'horarios', 'Registro eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                $tabla = 'otro módulo';
                if (preg_match('/a foreign key constraint fails \([^.]*\.`([^`]+)`/i', $e->getMessage(), $m)) {
                    $tabla = $m[1];
                }
                redirect(BASE_URL . 'horarios', "No se puede eliminar porque está en uso o tiene registros asociados en: $tabla", 'danger');
            }
            throw $e;
        }
    }
}
