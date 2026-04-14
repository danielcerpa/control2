<?php
// app/Controllers/AlumnoController.php

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

    public function horario()
    {
        $ciclo = ciclo_activo();
        $alumno_id = $_SESSION['usuario_id'];

        $grupo_id = $this->portalModel->getGrupoId($alumno_id, $ciclo['id']);
        $horarios = $grupo_id ? $this->portalModel->getHorario($grupo_id, $ciclo['id']) : [];

        // Organizar por día
        $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];
        $grid = [];
        foreach ($dias as $d) $grid[$d] = [];
        foreach ($horarios as $h) {
            if (isset($grid[$h['dia']])) {
                $grid[$h['dia']][] = $h;
            }
        }

        $this->view('alumno/horario', [
            'grid'          => $grid,
            'dias'          => $dias,
            'ciclo'         => $ciclo,
            'grupo_id'      => $grupo_id,
            'page_title'    => 'Mi Horario',
            'modulo_activo' => 'mi_horario'
        ]);
    }

    public function calificaciones()
    {
        $ciclo = ciclo_activo();
        $alumno_id = $_SESSION['usuario_id'];

        $calificaciones = $this->portalModel->getCalificaciones($alumno_id, $ciclo['id']);

        $suma = 0;
        foreach ($calificaciones as $c) $suma += $c['calificacion'];
        $promedio = count($calificaciones) > 0 ? $suma / count($calificaciones) : 0;

        $this->view('alumno/calificaciones', [
            'calificaciones' => $calificaciones,
            'promedio'       => $promedio,
            'ciclo'          => $ciclo,
            'page_title'     => 'Mis Calificaciones',
            'modulo_activo'  => 'mis_calificaciones'
        ]);
    }
}
