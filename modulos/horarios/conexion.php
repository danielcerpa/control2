<?php
// app/Models/Horario.php

class Horario
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll()
    {
        $st = $this->db->query(
            "SELECT m.*,
                    hm.dia,
                    hm.hora_inicio,
                    hm.hora_fin,
                    m.nombre AS materia,
                    p.nombre_completo AS profesor,
                    SUBSTRING_INDEX(p.nombre_completo, ' ', 1)  AS docente_n,
                    SUBSTRING_INDEX(p.nombre_completo, ' ', -2) AS docente_ap,
                    s.nombre AS salon,
                    CONCAT(g.grado, g.seccion) AS grupo
             FROM materias m
             JOIN materia_horarios hm ON m.id_materia = hm.id_materia
             LEFT JOIN profesores p ON m.id_profesor = p.id_profesor
             LEFT JOIN salones s ON m.id_salon = s.id_salon
             LEFT JOIN grupos g ON m.id_grupo = g.id_grupo
             ORDER BY m.ciclo_escolar DESC, FIELD(hm.dia, 'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'), hm.hora_inicio"
        );
        return $st->fetchAll();
    }

    /**
     * Obtiene todos los alumnos con su grupo para la vista de cards.
     */
    public function getAlumnos()
    {
        $st = $this->db->query(
            "SELECT a.id_alumno, a.nombre, a.apellido_paterno, a.apellido_materno,
                    a.matricula, a.ruta_foto, a.estado,
                    CONCAT(g.grado, g.seccion) AS grupo_nombre,
                    g.turno
             FROM alumnos a
             LEFT JOIN alumno_grupo ag ON a.id_alumno = ag.id_alumno
             LEFT JOIN grupos g ON ag.id_grupo = g.id_grupo
             ORDER BY a.apellido_paterno, a.nombre"
        );
        return $st->fetchAll();
    }

    /**
     * Obtiene el horario semanal completo de un alumno específico.
     */
    public function getHorarioPorAlumno($id_alumno)
    {
        $st = $this->db->prepare(
            "SELECT m.id_materia, m.nombre AS materia,
                    hm.dia, hm.hora_inicio, hm.hora_fin,
                    p.nombre_completo AS profesor,
                    s.nombre AS salon
             FROM inscripciones i
             JOIN materias m ON i.id_materia = m.id_materia
             JOIN materia_horarios hm ON hm.id_materia = m.id_materia
             LEFT JOIN profesores p ON m.id_profesor = p.id_profesor
             LEFT JOIN salones s ON m.id_salon = s.id_salon
             WHERE i.id_alumno = ? AND i.estado = 1
             ORDER BY FIELD(hm.dia,'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES'), hm.hora_inicio"
        );
        $st->execute([$id_alumno]);
        return $st->fetchAll();
    }

    public function getById($id)
    {
        $st = $this->db->prepare("SELECT * FROM materias WHERE id_materia = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    public function create($datos)
    {
        return false; // ahora se hace desde materias
    }

    public function update($id, $datos)
    {
        return false; // ahora se hace desde materias
    }

    public function delete($id)
    {
        return false; // ahora se hace desde materias
    }
}
