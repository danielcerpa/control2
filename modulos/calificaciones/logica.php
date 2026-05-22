<?php
// app/Controllers/CalificacionesController.php

class CalificacionesController extends Controller
{
    /** @var Calificacion */
    private $calificacionModel;
    /** @var Inscripcion */
    private $inscripcionModel;
    /** @var Grupo */
    private $grupoModel;
    /** @var Materia */
    private $materiaModel;
    /** @var CicloEscolar */
    private $cicloModel;

    public function __construct()
    {
        require_perm('calificaciones');
        $this->calificacionModel = new Calificacion();
        $this->inscripcionModel  = new Inscripcion();
        $this->grupoModel        = new Grupo();
        $this->materiaModel      = new Materia();
        $this->cicloModel        = new CicloEscolar();
    }

    public function index()
    {
        $u = session_user();
        $ciclo         = $this->cicloModel->getActivo();
        $grupos        = $this->grupoModel->getAll();
        $filtro_grupo  = (int) ($_GET['grupo']   ?? 0);
        $filtro_materia= (int) ($_GET['materia'] ?? 0);
        $filtro_parcial=       ($_GET['parcial'] ?? 'P1');
        if (!in_array($filtro_parcial, ['P1','P2','P3'])) $filtro_parcial = 'P1';

        // Filtrar grupos para profesor
        if ($u['rol'] === 'profesor' && $u['entidad_id']) {
            $materias_profesor = $this->materiaModel->getAll();
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

        // Cargar materias filtradas por grupo
        $materias = $filtro_grupo
            ? $this->materiaModel->getByGrupo($filtro_grupo)
            : [];

        // Filtrar materias para profesor
        if ($u['rol'] === 'profesor' && $u['entidad_id']) {
            $materias = array_filter($materias, function ($m) use ($u) {
                return $m['id_profesor'] == $u['entidad_id'];
            });
        }

        // Cargar alumnos con pivot de parciales si hay grupo y materia
        $alumnos = [];
        if ($filtro_grupo && $filtro_materia) {
            $alumnos = $this->calificacionModel->getByFilter($filtro_grupo, $filtro_materia);
        }

        // Guardar calificaciones en masa (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calificaciones'])) {
            $parcial = $_POST['parcial'] ?? 'P1';
            if (!in_array($parcial, ['P1','P2','P3'])) $parcial = 'P1';

            $hubo_cambios = false;
            foreach ($_POST['calificaciones'] as $id_insc => $puntaje) {
                if ($puntaje !== '') {
                    $puntaje_val = max(0, min(10, (float)$puntaje));
                    $this->calificacionModel->saveByParcial((int)$id_insc, $parcial, $puntaje_val);
                    // Recalcular FINAL si los 3 parciales están completos
                    $this->calificacionModel->calcularFinal((int)$id_insc);
                    $hubo_cambios = true;
                }
            }

            $msg = $hubo_cambios
                ? "Calificaciones del {$parcial} guardadas correctamente."
                : 'No se realizaron cambios.';
            redirect(BASE_URL . 'calificaciones?grupo=' . $filtro_grupo . '&materia=' . $filtro_materia . '&parcial=' . $parcial, $msg);
        }

        $this->view('calificaciones/index', [
            'ciclo'          => $ciclo,
            'grupos'         => $grupos,
            'materias'       => $materias,
            'filtro_grupo'   => $filtro_grupo,
            'filtro_materia' => $filtro_materia,
            'filtro_parcial' => $filtro_parcial,
            'alumnos'        => $alumnos,
        ]);
    }

    public function create()
    {
        $u = session_user();
        if ($u['rol'] !== 'admin' && $u['rol'] !== 'director') {
            redirect(BASE_URL . 'dashboard', 'No tiene privilegios para realizar esta acción.', 'danger');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id_inscripcion'   => $_POST['id_inscripcion'],
                'etiqueta_periodo' => trim($_POST['etiqueta_periodo']),
                'puntaje'          => max(0, min(10, (float)$_POST['puntaje'])),
                'estado'           => $_POST['estado'] ?? 'ACTIVO'
            ];
            $this->calificacionModel->create($datos);
            redirect(BASE_URL . 'calificaciones', 'Las calificaciones se guardaron correctamente.');
        }

        $inscripciones = $this->inscripcionModel->getAll();
        $this->view('calificaciones/create', ['inscripciones' => $inscripciones]);
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

        $calificacion = $this->calificacionModel->getById($id);
        if (!$calificacion) {
            header('Location: ' . BASE_URL . 'calificaciones');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id_inscripcion'   => $_POST['id_inscripcion'],
                'etiqueta_periodo' => trim($_POST['etiqueta_periodo']),
                'puntaje'          => max(0, min(10, (float)$_POST['puntaje'])),
                'estado'           => $_POST['estado'] ?? 'ACTIVO'
            ];
            $this->calificacionModel->update($id, $datos);
            redirect(BASE_URL . 'calificaciones', 'Las calificaciones se actualizaron correctamente.');
        }

        $inscripciones = $this->inscripcionModel->getAll();
        $this->view('calificaciones/edit', [
            'calificacion'  => $calificacion,
            'inscripciones' => $inscripciones
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
            $this->calificacionModel->delete($id);
            redirect(BASE_URL . 'calificaciones', 'Registro eliminado correctamente');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
                $tabla = 'otro módulo';
                if (preg_match('/a foreign key constraint fails \([^.]*\.`([^`]+)`/i', $e->getMessage(), $m)) {
                    $tabla = $m[1];
                }
                redirect(BASE_URL . 'calificaciones', "No se puede eliminar porque está en uso o tiene registros asociados en: $tabla", 'danger');
            }
            throw $e;
        }
    }
}
