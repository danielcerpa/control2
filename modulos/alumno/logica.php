<?php
// modulos/alumno/logica.php — Controlador AlumnoController

class AlumnoController extends Controller
{
    private $portalModel;

    public function __construct()
    {
        require_once 'config/init.php';
        require_auth();

        if ($_SESSION['usuario_rol'] !== 'alumno') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $this->portalModel = $this->model('AlumnoPortal');
    }

    // ----------------------------------------------------------------
    // Utilidad privada: resuelve el id_alumno desde la sesión actual
    // ----------------------------------------------------------------
    private function getAlumnoId()
    {
        // AuthController ya guarda el id_alumno en sesión
        if (!empty($_SESSION['usuario_entidad_id'])) {
            return $_SESSION['usuario_entidad_id'];
        }
        // Fallback: buscar en BD por id_usuario
        $alumno_id = $this->portalModel->getAlumnoIdByUsuario($_SESSION['usuario_id']);
        if ($alumno_id) {
            $_SESSION['usuario_entidad_id'] = $alumno_id;
        }
        return $alumno_id;
    }

    // ----------------------------------------------------------------
    // Perfil del alumno (página de inicio del módulo)
    // ----------------------------------------------------------------
    public function perfil()
    {
        $ciclo      = ciclo_activo();
        $alumno_id  = $this->getAlumnoId();
        $u          = session_user();

        $alumno               = $alumno_id ? $this->portalModel->getAlumno($alumno_id)               : null;
        $grupo                = $alumno_id ? $this->portalModel->getGrupo($alumno_id)                : null;
        $ultimas_calificaciones = $alumno_id ? $this->portalModel->getUltimasCalificaciones($alumno_id, 5) : [];
        $materias_data        = $alumno_id ? $this->portalModel->getMaterias($alumno_id)             : [];

        // Estadísticas rápidas
        $total_materias      = count($materias_data);
        $materias_aprobadas  = 0;
        $materias_reprobadas = 0;
        $suma_promedio       = 0;
        $con_calificacion    = 0;

        foreach ($materias_data as $m) {
            if ($m['calificacion'] !== null) {
                $con_calificacion++;
                $suma_promedio += $m['calificacion'];
                if ($m['calificacion'] >= 6) {
                    $materias_aprobadas++;
                } else {
                    $materias_reprobadas++;
                }
            }
        }
        $promedio_general = $con_calificacion > 0 ? $suma_promedio / $con_calificacion : 0;

        $this->view('alumno/perfil', [
            'u'                     => $u,
            'alumno'                => $alumno,
            'grupo'                 => $grupo,
            'ciclo'                 => $ciclo,
            'ultimas_calificaciones'=> $ultimas_calificaciones,
            'total_materias'        => $total_materias,
            'materias_aprobadas'    => $materias_aprobadas,
            'materias_reprobadas'   => $materias_reprobadas,
            'promedio_general'      => $promedio_general,
            'page_title'            => 'Mi Perfil',
            'modulo_activo'         => 'mi_perfil',
        ]);
    }

    // ----------------------------------------------------------------
    // Horario semanal
    // ----------------------------------------------------------------
    public function horario()
    {
        $ciclo      = ciclo_activo();
        $alumno_id  = $this->getAlumnoId();

        $grupo_id = $alumno_id ? $this->portalModel->getGrupoId($alumno_id, $ciclo['id'] ?? null) : null;
        $horarios = $alumno_id ? $this->portalModel->getHorarioByAlumno($alumno_id, $ciclo['id'] ?? null) : [];

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

        $this->view('alumno/horario', [
            'grid'          => $grid,
            'dias'          => $dias,
            'ciclo'         => $ciclo,
            'grupo_id'      => $grupo_id,
            'page_title'    => 'Mi Horario',
            'modulo_activo' => 'mi_horario',
        ]);
    }

    // ----------------------------------------------------------------
    // Calificaciones
    // ----------------------------------------------------------------
    public function calificaciones()
    {
        $ciclo      = ciclo_activo();
        $alumno_id  = $this->getAlumnoId();

        $calificaciones = $alumno_id
            ? $this->portalModel->getCalificaciones($alumno_id, $ciclo['id'] ?? null)
            : [];

        $suma = 0;
        $cnt  = 0;
        foreach ($calificaciones as $c) {
            if ($c['puntaje'] !== null) {
                $suma += $c['puntaje'];
                $cnt++;
            }
        }
        $promedio = $cnt > 0 ? $suma / $cnt : 0;

        $this->view('alumno/calificaciones', [
            'calificaciones' => $calificaciones,
            'promedio'       => $promedio,
            'ciclo'          => $ciclo,
            'page_title'     => 'Mis Calificaciones',
            'modulo_activo'  => 'mis_calificaciones',
        ]);
    }

    // ----------------------------------------------------------------
    // Materias inscritas
    // ----------------------------------------------------------------
    public function materias()
    {
        $ciclo      = ciclo_activo();
        $alumno_id  = $this->getAlumnoId();

        $materias = $alumno_id ? $this->portalModel->getMaterias($alumno_id) : [];
        $grupo    = $alumno_id ? $this->portalModel->getGrupo($alumno_id)    : null;

        $this->view('alumno/materias', [
            'materias'      => $materias,
            'grupo'         => $grupo,
            'ciclo'         => $ciclo,
            'page_title'    => 'Mis Materias',
            'modulo_activo' => 'mis_materias',
        ]);
    }
}
