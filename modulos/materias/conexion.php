<?php
// app/Models/Materia.php

class Materia
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll()
    {
        $st = $this->db->query("SELECT * FROM materias ORDER BY nombre");
        $rows = $st->fetchAll();
        $formatted = array_map([$this, '_defaults'], $rows);
        // Anidamos los horarios de cada materia
        foreach ($formatted as &$row) {
            $row['horarios'] = $this->getHorarios($row['id_materia']);
        }
        return $formatted;
    }

    public function getById($id)
    {
        $st = $this->db->prepare("SELECT * FROM materias WHERE id_materia = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $formatted = $this->_defaults($row);
            $formatted['horarios'] = $this->getHorarios($id);
            return $formatted;
        }
        return false;
    }

    /** Rellena campos que pueden no existir en la BD para no generar Warnings */
    private function _defaults($row)
    {
        return array_merge([
            'clave'       => '',
            'area'        => '',
            'horas'       => 4,
            'grado'       => '',
            'ciclo_id'    => '',
            'estado'      => 'Activo',
            'descripcion' => '',
            'docente_id'  => '',
            'salon_id'    => '',
            'grupo_id'    => '',
            'horarios'    => [],
            'id_profesor' => '',
            'id_salon'    => '',
            'id_grupo'    => '',
            'ciclo_escolar'=> '',
        ], $row);
    }

    public function create($datos)
    {
        $st = $this->db->prepare(
            "INSERT INTO materias (nombre, cupo_maximo, vigencia_inicio, vigencia_fin, id_profesor, id_salon, id_grupo, ciclo_escolar)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $st->execute([
            $datos['nombre'],
            $datos['horas'] ?? ($datos['cupo_maximo'] ?? 0),
            $datos['vigencia_inicio'] ?? null,
            $datos['vigencia_fin']    ?? null,
            $datos['docente_id'] ?: null,
            $datos['salon_id']   ?: null,
            $datos['grupo_id']   ?: null,
            $datos['ciclo_id']    ?: null,
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $datos)
    {
        $st = $this->db->prepare(
            "UPDATE materias SET nombre=?, cupo_maximo=?, vigencia_inicio=?, vigencia_fin=?, id_profesor=?, id_salon=?, id_grupo=?, ciclo_escolar=?
             WHERE id_materia=?"
        );
        return $st->execute([
            $datos['nombre'],
            $datos['horas'] ?? ($datos['cupo_maximo'] ?? 0),
            $datos['vigencia_inicio'] ?? null,
            $datos['vigencia_fin']    ?? null,
            $datos['docente_id'] ?: null,
            $datos['salon_id']   ?: null,
            $datos['grupo_id']   ?: null,
            $datos['ciclo_id']    ?: null,
            $id
        ]);
    }

    public function delete($id)
    {
        $st = $this->db->prepare("DELETE FROM materias WHERE id_materia = ?");
        return $st->execute([$id]);
    }

    // Relación con profesores
    public function getProfesores($id_materia)
    {
        $st = $this->db->prepare(
            "SELECT p.id_profesor, p.nombre_completo 
             FROM profesor_materia pm 
             JOIN profesores p ON p.id_profesor = pm.id_profesor 
             WHERE pm.id_materia = ?"
        );
        $st->execute([$id_materia]);
        return $st->fetchAll();
    }

    public function addProfesor($id_materia, $id_profesor)
    {
        $st = $this->db->prepare("INSERT IGNORE INTO profesor_materia (id_profesor, id_materia) VALUES (?, ?)");
        return $st->execute([$id_profesor, $id_materia]);
    }

    public function clearProfesores($id_materia)
    {
        $st = $this->db->prepare("DELETE FROM profesor_materia WHERE id_materia = ?");
        return $st->execute([$id_materia]);
    }

    // --- HORARIOS MULTIPLES ---
    public function getHorarios($id_materia)
    {
        $st = $this->db->prepare("SELECT dia, hora_inicio, hora_fin FROM horarios_materia WHERE id_materia = ? ORDER BY FIELD(dia, 'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'), hora_inicio");
        $st->execute([$id_materia]);
        return $st->fetchAll();
    }

    public function syncHorarios($id_materia, $dias, $horas_i, $horas_f)
    {
        // 1. Borrar todos los horarios actuales de esta materia
        $st = $this->db->prepare("DELETE FROM horarios_materia WHERE id_materia = ?");
        $st->execute([$id_materia]);

        // 2. Insertar los nuevos (si vienen)
        if (!empty($dias) && is_array($dias)) {
            $stInsert = $this->db->prepare("INSERT INTO horarios_materia (id_materia, dia, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");
            foreach ($dias as $index => $dia) {
                if (!empty($dia) && !empty($horas_i[$index]) && !empty($horas_f[$index])) {
                    $stInsert->execute([$id_materia, strtoupper(trim($dia)), $horas_i[$index], $horas_f[$index]]);
                }
            }
        }
    }
}
