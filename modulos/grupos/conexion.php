<?php
class Grupo
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll()
    {
        $st = $this->db->query("SELECT * FROM grupos ORDER BY ciclo_escolar DESC, grado, seccion");
        $rows = $st->fetchAll();
        return array_map([$this, '_defaults'], $rows);
    }

    public function getById($id)
    {
        $st = $this->db->prepare("SELECT * FROM grupos WHERE id_grupo = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ? $this->_defaults($row) : false;
    }

    /** Rellena campos calculados/virtuales que la vista necesita */
    private function _defaults($row)
    {
        $nombre = ($row['grado'] ?? '') . ($row['seccion'] ?? '');
        $total_alumnos = 0;
        if (isset($row['id_grupo'])) {
            $st = $this->db->prepare("SELECT COUNT(*) FROM alumno_grupo WHERE id_grupo = ?");
            $st->execute([$row['id_grupo']]);
            $total_alumnos = $st->fetchColumn();
        }

        return array_merge([
            'nombre'       => $nombre ?: 'Sin nombre',
            'total_alumnos'=> $total_alumnos,
            'capacidad'    => $row['capacidad'] ?? 50,
            'ciclo_nombre' => $row['ciclo_escolar'] ?? 'Sin ciclo',
        ], $row);
    }

    public function create($datos)
    {
        $st = $this->db->prepare(
            "INSERT INTO grupos (grado, seccion, ciclo_escolar, turno, capacidad)
             VALUES (?,?,?,?,?)"
        );
        $st->execute(array(
            $datos['grado'],
            $datos['seccion'],
            $datos['ciclo_escolar'],
            $datos['turno'],
            $datos['capacidad']
        ));
        return $this->db->lastInsertId();
    }

    public function update($id, $datos)
    {
        $st = $this->db->prepare(
            "UPDATE grupos SET grado=?, seccion=?, ciclo_escolar=?, turno=?, capacidad=?
             WHERE id_grupo=?"
        );
        return $st->execute(array(
            $datos['grado'],
            $datos['seccion'],
            $datos['ciclo_escolar'],
            $datos['turno'],
            $datos['capacidad'],
            $id
        ));
    }

    public function delete($id)
    {
        $st = $this->db->prepare("DELETE FROM grupos WHERE id_grupo = ?");
        return $st->execute([$id]);
    }
}
