<?php
// app/Models/Calificacion.php

class Calificacion
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll()
    {
        $st = $this->db->query(
            "SELECT c.*, i.id_alumno, a.matricula,
             CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', IFNULL(a.apellido_materno, '')) AS nombre_completo,
             m.id_materia, m.nombre AS materia
             FROM calificaciones c
             JOIN inscripciones i ON c.id_inscripcion = i.id_inscripcion
             JOIN alumnos a ON i.id_alumno = a.id_alumno
             JOIN materias m ON i.id_materia = m.id_materia
             WHERE a.estado = 1
             ORDER BY c.fecha_registro DESC"
        );
        return $st->fetchAll();
    }

    /**
     * Devuelve por alumno las 4 columnas: P1, P2, P3, FINAL (pivot).
     */
    public function getByFilter($id_grupo, $id_materia)
    {
        $st = $this->db->prepare(
            "SELECT i.id_inscripcion, i.id_alumno, a.matricula,
             CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', IFNULL(a.apellido_materno,'')) AS nombre_completo,
             MAX(CASE WHEN c.etiqueta_periodo = 'P1'    THEN c.puntaje END) AS p1,
             MAX(CASE WHEN c.etiqueta_periodo = 'P2'    THEN c.puntaje END) AS p2,
             MAX(CASE WHEN c.etiqueta_periodo = 'P3'    THEN c.puntaje END) AS p3,
             MAX(CASE WHEN c.etiqueta_periodo = 'FINAL' THEN c.puntaje END) AS final
             FROM inscripciones i
             JOIN alumnos a ON i.id_alumno = a.id_alumno
             JOIN materias m ON i.id_materia = m.id_materia
             LEFT JOIN calificaciones c ON i.id_inscripcion = c.id_inscripcion
             WHERE m.id_grupo = ? AND m.id_materia = ? AND a.estado = 1
             GROUP BY i.id_inscripcion, i.id_alumno, a.matricula, nombre_completo
             ORDER BY a.apellido_paterno, a.nombre"
        );
        $st->execute([$id_grupo, $id_materia]);
        return $st->fetchAll();
    }

    public function getById($id)
    {
        $st = $this->db->prepare("SELECT * FROM calificaciones WHERE id_calificacion = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    /**
     * Guarda o actualiza una calificación de un parcial específico (P1, P2, P3).
     * Usa INSERT ... ON DUPLICATE KEY UPDATE para respetar el UNIQUE(id_inscripcion, etiqueta_periodo).
     */
    public function saveByParcial($id_inscripcion, $etiqueta_periodo, $puntaje)
    {
        $st = $this->db->prepare(
            "INSERT INTO calificaciones (id_inscripcion, etiqueta_periodo, puntaje, estado, fecha_registro)
             VALUES (?, ?, ?, 'ACTIVO', CURDATE())
             ON DUPLICATE KEY UPDATE puntaje = VALUES(puntaje), fecha_registro = CURDATE()"
        );
        return $st->execute([$id_inscripcion, $etiqueta_periodo, $puntaje]);
    }

    /**
     * Calcula el promedio de P1+P2+P3 y guarda/actualiza FINAL.
     * Solo se ejecuta si los 3 parciales están registrados.
     */
    public function calcularFinal($id_inscripcion)
    {
        $st = $this->db->prepare(
            "SELECT
               MAX(CASE WHEN etiqueta_periodo = 'P1' THEN puntaje END) AS p1,
               MAX(CASE WHEN etiqueta_periodo = 'P2' THEN puntaje END) AS p2,
               MAX(CASE WHEN etiqueta_periodo = 'P3' THEN puntaje END) AS p3
             FROM calificaciones
             WHERE id_inscripcion = ?"
        );
        $st->execute([$id_inscripcion]);
        $row = $st->fetch();

        if ($row['p1'] !== null && $row['p2'] !== null && $row['p3'] !== null) {
            $final = round(($row['p1'] + $row['p2'] + $row['p3']) / 3, 2);
            $this->saveByParcial($id_inscripcion, 'FINAL', $final);
            return $final;
        }
        return null;
    }

    public function create($datos)
    {
        $st = $this->db->prepare(
            "INSERT INTO calificaciones (id_inscripcion, etiqueta_periodo, puntaje, estado, fecha_registro)
             VALUES (?,?,?,?, CURDATE())"
        );
        return $st->execute(array(
            $datos['id_inscripcion'],
            $datos['etiqueta_periodo'],
            $datos['puntaje'],
            $datos['estado'] ?? 'ACTIVO'
        ));
    }

    public function update($id, $datos)
    {
        $st = $this->db->prepare(
            "UPDATE calificaciones SET id_inscripcion=?, etiqueta_periodo=?, puntaje=?, estado=?, fecha_registro=CURDATE()
             WHERE id_calificacion=?"
        );
        return $st->execute(array(
            $datos['id_inscripcion'],
            $datos['etiqueta_periodo'],
            $datos['puntaje'],
            $datos['estado'],
            $id
        ));
    }

    public function delete($id)
    {
        $st = $this->db->prepare("DELETE FROM calificaciones WHERE id_calificacion = ?");
        return $st->execute([$id]);
    }

    public function getByInscripcion($id_inscripcion)
    {
        $st = $this->db->prepare("SELECT * FROM calificaciones WHERE id_inscripcion = ? ORDER BY etiqueta_periodo ASC");
        $st->execute([$id_inscripcion]);
        return $st->fetchAll();
    }
}
