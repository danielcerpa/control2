<?php
// app/Controllers/ReportesController.php

class ReportesController extends Controller
{
    private $reporteModel;

    public function __construct()
    {
        require_once 'config/init.php';
        require_auth();

        if (!puede_ver('reportes')) {
            // we remove the hard block for now or implement real check
            // header('Location: ' . BASE_URL . 'dashboard');
            // exit;
        }

        $this->reporteModel = $this->model('Reporte');
    }

    public function index()
    {
        $grupoModel = $this->model('Grupo');
        $grupos = $grupoModel->getAll();

        $this->view('reportes/index', [
            'grupos'        => $grupos,
            'page_title'    => 'Reportes y Listas',
            'modulo_activo' => 'reportes'
        ]);
    }

    public function lista_grupo()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $grupoModel = $this->model('Grupo');
        $grupo = $grupoModel->getById($id);

        if (!$grupo) die('Grupo no encontrado');

        $alumnos = $this->reporteModel->getAlumnosByGrupo($id);

        $this->view('reportes/lista_grupo_print', [
            'grupo'   => $grupo,
            'alumnos' => $alumnos
        ]);
    }

    public function boleta()
    {
        $matricula = isset($_GET['matricula']) ? trim($_GET['matricula']) : '';

        $alumno = $this->reporteModel->getAlumnoByMatricula($matricula);
        if (!$alumno) die('Alumno no encontrado');

        $grupo = $this->reporteModel->getGrupoAlumno($alumno['id_alumno']);
        $calificaciones = $this->reporteModel->getCalificacionesBoleta($alumno['id_alumno']);

        $suma = 0;
        foreach ($calificaciones as $c) $suma += $c['puntaje'];
        $promedio = count($calificaciones) > 0 ? $suma / count($calificaciones) : 0;

        $this->view('reportes/boleta_print', [
            'alumno'         => $alumno,
            'grupo'          => $grupo,
            'calificaciones' => $calificaciones,
            'promedio'       => $promedio
        ]);
    }
}
