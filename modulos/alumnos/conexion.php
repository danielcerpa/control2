<?php
// app/Models/Alumno.php

class Alumno
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll()
    {
        $st = $this->db->query("SELECT * FROM alumnos ORDER BY nombre");
        return $st->fetchAll();
    }

    public function getById($id)
    {
        $st = $this->db->prepare("SELECT * FROM alumnos WHERE id_alumno = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    public function create($datos, $id_usuario = null)
    {
        $st = $this->db->prepare(
            "INSERT INTO alumnos (id_usuario, matricula, nombre, apellido_paterno, apellido_materno, curp, genero, fecha_nac, domicilio, escuela_procedencia, ruta_foto, nombre_tutor, telefono_tutor, comentarios, estado)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $result = $st->execute(array(
            $id_usuario,
            $datos['matricula'],
            $datos['nombre'],
            $datos['apellido_paterno'],
            $datos['apellido_materno'],
            $datos['curp'],
            $datos['genero'],
            $datos['fecha_nac'] ?? null,
            $datos['domicilio'],
            $datos['escuela_procedencia'],
            $datos['ruta_foto'] ?? null,
            $datos['nombre_tutor'],
            $datos['telefono_tutor'],
            $datos['comentarios'],
            $datos['estado'] ?? 1
        ));

        if ($result && !empty($datos['grupo_id'])) {
            $id_alumno = $this->db->lastInsertId();
            $st_ag = $this->db->prepare("INSERT INTO alumno_grupo (id_alumno, id_grupo) VALUES (?, ?)");
            $st_ag->execute([$id_alumno, $datos['grupo_id']]);

            // Auto-inscribir en las materias del grupo asignado
            $st_mat = $this->db->prepare("SELECT id_materia FROM materias WHERE id_grupo = ?");
            $st_mat->execute([$datos['grupo_id']]);
            $materias = $st_mat->fetchAll();
            if (!empty($materias)) {
                $st_ins = $this->db->prepare("INSERT IGNORE INTO inscripciones (id_alumno, id_materia, estado, fecha_inscripcion) VALUES (?, ?, 1, NOW())");
                foreach ($materias as $mat) {
                    $st_ins->execute([$id_alumno, $mat['id_materia']]);
                }
            }
        }

        return $result;
    }

    public function update($id, $datos)
    {
        $st = $this->db->prepare(
            "UPDATE alumnos SET matricula=?, nombre=?, apellido_paterno=?, apellido_materno=?, curp=?, genero=?, fecha_nac=?, domicilio=?, escuela_procedencia=?, ruta_foto=?, nombre_tutor=?, telefono_tutor=?, comentarios=?, estado=?
             WHERE id_alumno=?"
        );
        $result = $st->execute(array(
            $datos['matricula'],
            $datos['nombre'],
            $datos['apellido_paterno'],
            $datos['apellido_materno'],
            $datos['curp'],
            $datos['genero'],
            $datos['fecha_nac'] ?? null,
            $datos['domicilio'],
            $datos['escuela_procedencia'],
            $datos['ruta_foto'] ?? null,
            $datos['nombre_tutor'],
            $datos['telefono_tutor'],
            $datos['comentarios'],
            $datos['estado'],
            $id
        ));

        if ($result && isset($datos['grupo_id'])) {
            $st_del = $this->db->prepare("DELETE FROM alumno_grupo WHERE id_alumno = ?");
            $st_del->execute([$id]);

            if (!empty($datos['grupo_id'])) {
                $st_ag = $this->db->prepare("INSERT INTO alumno_grupo (id_alumno, id_grupo) VALUES (?, ?)");
                $st_ag->execute([$id, $datos['grupo_id']]);

                // Auto-inscribir en las materias del nuevo grupo asignado
                $st_mat = $this->db->prepare("SELECT id_materia FROM materias WHERE id_grupo = ?");
                $st_mat->execute([$datos['grupo_id']]);
                $materias = $st_mat->fetchAll();
                if (!empty($materias)) {
                    $st_ins = $this->db->prepare("INSERT IGNORE INTO inscripciones (id_alumno, id_materia, estado, fecha_inscripcion) VALUES (?, ?, 1, NOW())");
                    foreach ($materias as $mat) {
                        $st_ins->execute([$id, $mat['id_materia']]);
                    }
                }
            }
        }

        return $result;
    }

    public function getUsuarioId($alumno_id)
    {
        $st = $this->db->prepare("SELECT id_usuario FROM alumnos WHERE id_alumno = ? LIMIT 1");
        $st->execute([$alumno_id]);
        $row = $st->fetch();
        return $row ? $row['id_usuario'] : null;
    }

    public function delete($id)
    {
        // 1. Eliminar calificaciones asociadas a las inscripciones del alumno
        $st_cal = $this->db->prepare("DELETE c FROM calificaciones c JOIN inscripciones i ON c.id_inscripcion = i.id_inscripcion WHERE i.id_alumno = ?");
        $st_cal->execute([$id]);

        // 2. Eliminar inscripciones
        $st_ins = $this->db->prepare("DELETE FROM inscripciones WHERE id_alumno = ?");
        $st_ins->execute([$id]);

        // 3. Eliminar asociación con grupos
        $st_ag = $this->db->prepare("DELETE FROM alumno_grupo WHERE id_alumno = ?");
        $st_ag->execute([$id]);

        // 4. Eliminar el alumno
        $st = $this->db->prepare("DELETE FROM alumnos WHERE id_alumno = ?");
        return $st->execute([$id]);
    }
}
