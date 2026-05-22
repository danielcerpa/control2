<?php
// app/Models/Reporte.php

class Reporte
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAlumnosByGrupo($id_grupo)
    {
        $st = $this->db->prepare(
            "SELECT a.matricula, a.nombre, a.apellido_paterno, a.apellido_materno, a.genero
               FROM alumnos a
               JOIN alumno_grupo ag ON ag.id_alumno = a.id_alumno
              WHERE ag.id_grupo = ? AND a.estado = 1
              ORDER BY a.apellido_paterno, a.apellido_materno, a.nombre"
        );
        $st->execute([$id_grupo]);
        return $st->fetchAll();
    }

    public function getAlumnoByMatricula($matricula)
    {
        $st = $this->db->prepare("SELECT * FROM alumnos WHERE matricula = ?");
        $st->execute([$matricula]);
        return $st->fetch();
    }

    public function getGrupoAlumno($id_alumno)
    {
        $st = $this->db->prepare(
            "SELECT g.grado, g.seccion, g.ciclo_escolar FROM grupos g 
               JOIN alumno_grupo ag ON ag.id_grupo = g.id_grupo
              WHERE ag.id_alumno = ? LIMIT 1"
        );
        $st->execute([$id_alumno]);
        return $st->fetch();
    }

    public function getCalificacionesBoleta($id_alumno, $ciclo_escolar = null)
    {
        $query = "SELECT m.nombre AS materia,
                         MAX(CASE WHEN c.etiqueta_periodo = 'P1'    THEN c.puntaje END) AS p1,
                         MAX(CASE WHEN c.etiqueta_periodo = 'P2'    THEN c.puntaje END) AS p2,
                         MAX(CASE WHEN c.etiqueta_periodo = 'P3'    THEN c.puntaje END) AS p3,
                         MAX(CASE WHEN c.etiqueta_periodo = 'FINAL' THEN c.puntaje END) AS final
                  FROM inscripciones i
                  JOIN materias m ON i.id_materia = m.id_materia
                  LEFT JOIN calificaciones c ON c.id_inscripcion = i.id_inscripcion
                  WHERE i.id_alumno = ? AND (c.estado = 'ACTIVO' OR c.estado IS NULL)";

        $params = [$id_alumno];
        if ($ciclo_escolar) {
            $query .= " AND m.ciclo_escolar = ?";
            $params[] = $ciclo_escolar;
        }

        $query .= " GROUP BY m.nombre, i.id_inscripcion ORDER BY m.nombre";

        $st = $this->db->prepare($query);
        $st->execute($params);
        return $st->fetchAll();
    }
}
