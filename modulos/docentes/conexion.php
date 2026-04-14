<?php
// app/Models/Docente.php

class Docente
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll()
    {
        $st = $this->db->query("SELECT p.id_profesor, p.numero_empleado, p.nombre_completo, p.curp, p.telefono, p.grado_academico, p.estado, p.ruta_foto, p.id_usuario, u.nombre_usuario as login_id FROM profesores p LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario ORDER BY p.nombre_completo");
        return $st->fetchAll();
    }

    public function getById($id)
    {
        $st = $this->db->prepare("SELECT * FROM profesores WHERE id_profesor = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    public function create($datos, $id_usuario = null)
    {
        $st = $this->db->prepare(
            "INSERT INTO profesores (id_usuario, numero_empleado, nombre_completo, curp, telefono, domicilio, escuela_procedencia, grado_academico, estado, ruta_foto)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        return $st->execute(array(
            $id_usuario,
            $datos['numero_empleado'],
            $datos['nombre_completo'],
            $datos['curp'],
            $datos['telefono'],
            $datos['domicilio'],
            $datos['escuela_procedencia'],
            $datos['grado_academico'],
            $datos['estado'] ?? 1,
            $datos['ruta_foto'] ?? null
        ));
    }

    public function update($id, $datos)
    {
        $sql = "UPDATE profesores SET numero_empleado=?, nombre_completo=?, curp=?, telefono=?, domicilio=?, escuela_procedencia=?, grado_academico=?, estado=?";
        $params = array(
            $datos['numero_empleado'],
            $datos['nombre_completo'],
            $datos['curp'],
            $datos['telefono'],
            $datos['domicilio'],
            $datos['escuela_procedencia'],
            $datos['grado_academico'],
            $datos['estado']
        );
        
        if (array_key_exists('ruta_foto', $datos)) {
            $sql .= ", ruta_foto=?";
            $params[] = $datos['ruta_foto'];
        }
        
        $sql .= " WHERE id_profesor=?";
        $params[] = $id;

        $st = $this->db->prepare($sql);
        return $st->execute($params);
    }
    
    public function updateIdUsuario($id_profesor, $id_usuario)
    {
        $st = $this->db->prepare("UPDATE profesores SET id_usuario = ? WHERE id_profesor = ?");
        return $st->execute([$id_usuario, $id_profesor]);
    }

    public function delete($id)
    {
        $st = $this->db->prepare("DELETE FROM profesores WHERE id_profesor = ?");
        return $st->execute([$id]);
    }
}
