<?php
// app/Controllers/DashboardController.php

class DashboardController extends Controller
{

    public function index()
    {
        require_once 'config/init.php';
        require_auth();

        $u   = session_user();
        $pdo = db_connect();

        // Los alumnos van directamente a su portal
        if ($u['rol'] === 'alumno') {
            header('Location: ' . BASE_URL . 'portal_alumno/perfil');
            exit;
        }


        $mis_horarios_hoy = array();
        $mis_materias = array();

        if ($u['tipo'] === 'profesor' || $u['rol'] === 'profesor' || $u['tipo'] === 'docente') {
            $dias_es = array(
                'Monday'    => 'LUNES',
                'Tuesday'   => 'MARTES',
                'Wednesday' => 'MIERCOLES',
                'Thursday'  => 'JUEVES',
                'Friday'    => 'VIERNES',
                'Saturday'  => 'SABADO',
            );
            $hoy = $dias_es[date('l')] ?? '';
            // Get id_profesor based on user id_usuario
            $stProf = $pdo->prepare("SELECT id_profesor FROM profesores WHERE id_usuario = ?");
            $stProf->execute([$u['id']]);
            $profesor = $stProf->fetchColumn();

            if ($profesor) {
                // 1. Horario de hoy
                if ($hoy) {
                    $st = $pdo->prepare(
                        "SELECT mh.hora_inicio, mh.hora_fin, m.nombre AS materia, g.grado, g.seccion, s.nombre AS salon
                               FROM materias m
                               JOIN materia_horarios mh ON mh.id_materia = m.id_materia
                               LEFT JOIN grupos g ON g.id_grupo = m.id_grupo
                               LEFT JOIN salones  s ON s.id_salon = m.id_salon
                              WHERE (m.id_profesor = ? OR EXISTS (SELECT 1 FROM profesor_materia pm WHERE pm.id_materia = m.id_materia AND pm.id_profesor = ?)) AND mh.dia = ? 
                              ORDER BY mh.hora_inicio"
                    );
                    $st->execute(array($profesor, $profesor, $hoy));
                    $mis_horarios_hoy = $st->fetchAll();
                }

                // 2. Todas las materias impartidas
                $stMat = $pdo->prepare(
                    "SELECT m.id_materia, m.nombre AS materia, g.grado, g.seccion, s.nombre AS salon
                       FROM materias m
                       LEFT JOIN grupos g ON g.id_grupo = m.id_grupo
                       LEFT JOIN salones  s ON s.id_salon = m.id_salon
                      WHERE m.id_profesor = ? OR EXISTS (SELECT 1 FROM profesor_materia pm WHERE pm.id_materia = m.id_materia AND pm.id_profesor = ?)
                      ORDER BY m.nombre"
                );
                $stMat->execute([$profesor, $profesor]);
                $mis_materias = $stMat->fetchAll(PDO::FETCH_ASSOC);

                // Obtener horarios para cada materia
                $stH = $pdo->prepare("SELECT dia, hora_inicio, hora_fin FROM materia_horarios WHERE id_materia = ? ORDER BY FIELD(dia, 'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'), hora_inicio");
                foreach ($mis_materias as &$mat) {
                    $stH->execute([$mat['id_materia']]);
                    $mat['horarios'] = $stH->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }

        $this->view('dashboard/index', [
            'u' => $u,
            'mis_horarios_hoy' => $mis_horarios_hoy,
            'mis_materias'     => $mis_materias
        ]);
    }
}
