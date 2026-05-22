<?php
// app/Controllers/HorariosController.php
// (Maneja las Ofertas de Horario)

class HorariosController extends Controller
{
    /** @var Horario */
    private $horarioModel;
    /** @var Materia */
    private $materiaModel;
    /** @var Docente */
    private $docenteModel;
    /** @var Salon */
    private $salonModel;
    /** @var Grupo */
    private $grupoModel;
    /** @var CicloEscolar */
    private $cicloModel;

    public function __construct()
    {
        require_perm('horarios');
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

        if ($u['rol'] === 'profesor' || $u['tipo'] === 'profesor' || $u['tipo'] === 'docente') {
            $pdo = db_connect();
            $id_profesor = null;
            if (!empty($u['entidad_id'])) {
                $id_profesor = $u['entidad_id'];
            } else {
                $st = $pdo->prepare("SELECT id_profesor FROM profesores WHERE id_usuario = ?");
                $st->execute([$u['id']]);
                $id_profesor = $st->fetchColumn();
            }

            if ($id_profesor) {
                // Get all schedules for this teacher
                $st = $pdo->prepare(
                    "SELECT mh.dia, mh.hora_inicio, mh.hora_fin, m.nombre AS materia, g.grado, g.seccion, s.nombre AS salon, m.id_materia
                     FROM materias m
                     JOIN materia_horarios mh ON mh.id_materia = m.id_materia
                     LEFT JOIN grupos g ON g.id_grupo = m.id_grupo
                     LEFT JOIN salones s ON s.id_salon = m.id_salon
                     WHERE m.id_profesor = ? OR EXISTS (SELECT 1 FROM profesor_materia pm WHERE pm.id_materia = m.id_materia AND pm.id_profesor = ?)
                     ORDER BY mh.hora_inicio"
                );
                $st->execute([$id_profesor, $id_profesor]);
                $horarios = $st->fetchAll(PDO::FETCH_ASSOC);

                // Organizar por día
                $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
                $grid = [];
                foreach ($dias as $d) $grid[$d] = [];
                foreach ($horarios as $h) {
                    $h['docente'] = (!empty($h['grado']) && !empty($h['seccion'])) ? "Grupo: {$h['grado']}{$h['seccion']}" : 'Sin grupo';
                    $dia_norm = ucfirst(strtolower($h['dia']));
                    if (isset($grid[$dia_norm])) {
                        $grid[$dia_norm][] = $h;
                    }
                }

                $this->view('horarios/mi_horario', [
                    'grid'          => $grid,
                    'dias'          => $dias,
                    'ciclo'         => $ciclo,
                    'page_title'    => 'Mi Horario Semanal',
                    'modulo_activo' => 'horarios',
                ]);
                exit; // stop executing the rest of index()
            }
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
    /**
     * @param int|string $id_alumno
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
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

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

    /**
     * @param int|string $id
     */
    public function edit($id)
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

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

    /**
     * @param int|string $id_alumno
     */
    public function show($id_alumno)
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

        $ciclo = $this->cicloModel->getActivo();
        require_once 'modulos/portal_alumno/conexion.php';
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
