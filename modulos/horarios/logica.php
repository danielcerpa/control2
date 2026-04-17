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

    public function delete($id)
    {
        $this->horarioModel->delete($id);
        header('Location: ' . BASE_URL . 'horarios');
        exit;
    }
}
