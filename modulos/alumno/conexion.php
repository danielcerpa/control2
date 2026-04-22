<?php
// modulos/alumno/conexion.php — Modelo AlumnoPortal

class AlumnoPortal
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /**
     * Retorna el grupo al que pertenece el alumno.
     */
    public function getGrupoId($alumno_id, $ciclo_id)
    {
        $st = $this->db->prepare(
            "SELECT g.id_grupo
             FROM alumno_grupo ag
             JOIN grupos g ON ag.id_grupo = g.id_grupo
             WHERE ag.id_alumno = ?
             LIMIT 1"
        );
        $st->execute([$alumno_id]);
        $rel = $st->fetch();
        return $rel ? $rel['id_grupo'] : null;
    }

    /**
     * Retorna los datos del grupo (grado, sección, turno).
     */
    public function getGrupo($alumno_id)
    {
        $st = $this->db->prepare(
            "SELECT g.id_grupo, g.grado, g.seccion, g.turno, g.ciclo_escolar
             FROM alumno_grupo ag
             JOIN grupos g ON ag.id_grupo = g.id_grupo
             WHERE ag.id_alumno = ?
             LIMIT 1"
        );
        $st->execute([$alumno_id]);
        return $st->fetch() ?: null;
    }

    /**
     * Retorna los datos del alumno (matrícula, nombre, genero).
     */
    public function getAlumno($alumno_id)
    {
        $st = $this->db->prepare(
            "SELECT a.id_alumno, a.matricula, a.nombre, a.apellido_paterno,
                    a.apellido_materno, a.genero, a.fecha_nac, a.ruta_foto
             FROM alumnos a
             WHERE a.id_alumno = ?
             LIMIT 1"
        );
        $st->execute([$alumno_id]);
        return $st->fetch() ?: null;
    }

    /**
     * Retorna el id_alumno según id_usuario.
     */
    public function getAlumnoIdByUsuario($usuario_id)
    {
        $st = $this->db->prepare(
            "SELECT id_alumno FROM alumnos WHERE id_usuario = ? LIMIT 1"
        );
        $st->execute([$usuario_id]);
        $row = $st->fetch();
        return $row ? $row['id_alumno'] : null;
    }

    /**
     * Retorna el horario del grupo organizado por materia-horario.
     */
    public function getHorario($grupo_id, $ciclo_id)
    {
        $st = $this->db->prepare(
            "SELECT m.id_materia, m.nombre AS materia,
                    mh.dia, mh.hora_inicio, mh.hora_fin,
                    p.nombre_completo AS docente,
                    s.nombre AS salon
               FROM materias m
               JOIN materia_horarios mh ON mh.id_materia = m.id_materia
               LEFT JOIN profesores p ON p.id_profesor = m.id_profesor
               LEFT JOIN salones s ON s.id_salon = m.id_salon
              WHERE m.id_grupo = ?
              ORDER BY FIELD(mh.dia,'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'), mh.hora_inicio"
        );
        $st->execute([$grupo_id]);
        return $st->fetchAll();
    }

    /**
     * Retorna las calificaciones del alumno en el ciclo activo.
     */
    public function getCalificaciones($alumno_id, $ciclo_id)
    {
        $st = $this->db->prepare(
            "SELECT m.nombre AS materia, i.id_inscripcion,
                    c.puntaje, c.etiqueta_periodo, c.fecha_registro
               FROM inscripciones i
               JOIN materias m ON m.id_materia = i.id_materia
               LEFT JOIN calificaciones c ON c.id_inscripcion = i.id_inscripcion
              WHERE i.id_alumno = ?
              ORDER BY c.fecha_registro DESC, m.nombre"
        );
        $st->execute([$alumno_id]);
        return $st->fetchAll();
    }

    /**
     * Retorna las últimas N calificaciones (para el perfil).
     */
    public function getUltimasCalificaciones($alumno_id, $limite = 5)
    {
        $st = $this->db->prepare(
            "SELECT m.nombre AS materia, c.puntaje, c.etiqueta_periodo, c.fecha_registro
               FROM inscripciones i
               JOIN materias m ON m.id_materia = i.id_materia
               JOIN calificaciones c ON c.id_inscripcion = i.id_inscripcion
              WHERE i.id_alumno = ?
              ORDER BY c.fecha_registro DESC
              LIMIT " . intval($limite)
        );
        $st->execute([$alumno_id]);
        return $st->fetchAll();
    }

    /**
     * Retorna las materias del alumno (inscritas) con sus horarios y calificación más reciente.
     */
    public function getMaterias($alumno_id)
    {
        // 1. Obtener materias inscritas
        $st = $this->db->prepare(
            "SELECT m.id_materia, m.nombre, m.vigencia_inicio, m.vigencia_fin,
                    p.nombre_completo AS docente,
                    s.nombre AS salon,
                    i.estado,
                    (SELECT c.puntaje
                       FROM calificaciones c
                      WHERE c.id_inscripcion = i.id_inscripcion
                      ORDER BY c.fecha_registro DESC
                      LIMIT 1) AS calificacion
               FROM inscripciones i
               JOIN materias m ON m.id_materia = i.id_materia
               LEFT JOIN profesores p ON p.id_profesor = m.id_profesor
               LEFT JOIN salones s ON s.id_salon = m.id_salon
              WHERE i.id_alumno = ?
              ORDER BY m.nombre"
        );
        $st->execute([$alumno_id]);
        $materias = $st->fetchAll();

        // 2. Agregar horarios para cada materia
        foreach ($materias as &$mat) {
            $sh = $this->db->prepare(
                "SELECT dia, hora_inicio, hora_fin
                   FROM materia_horarios
                  WHERE id_materia = ?
                  ORDER BY FIELD(dia,'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'), hora_inicio"
            );
            $sh->execute([$mat['id_materia']]);
            $mat['horarios'] = $sh->fetchAll();
        }
        unset($mat);

        return $materias;
    }
}
